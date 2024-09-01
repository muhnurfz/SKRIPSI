<?php
include('conn.php');

// Generate kode penumpang
function generateKodePenumpang($name) {
    $nameParts = explode(' ', $name);
    $initials = strtoupper(substr($nameParts[0], 0, 2));
    $month = date('m'); // MM format
    $year = substr(date('Y'), -2); // YY format
    $randomChars = '';
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    for ($i = 0; $i < 2; $i++) {
        $randomChars .= $characters[mt_rand(0, strlen($characters) - 1)];
    }
    $kode_penumpang = $initials . $month . $year . $randomChars;
    return $kode_penumpang;
}

// Check if kode_penumpang exists
function getUniqueKodePenumpang($conn, $name) {
    do {
        $kode_penumpang = generateKodePenumpang($name);
        $sql = "SELECT id FROM data_pnp WHERE kode_penumpang = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $kode_penumpang);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);
    $stmt->close();
    return $kode_penumpang;
}

// Check if email already exists
function emailExists($conn, $email) {
    $sql = "SELECT id FROM data_pnp WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();
    $exists = $stmt->num_rows > 0;
    $stmt->close();
    return $exists;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passenger_name = $_POST['username'];
    $passenger_phone = $_POST['passenger_phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($passenger_name) && !empty($passenger_phone) && !empty($email) && !empty($password)) {
        if ($password === $confirm_password) {
            if (!emailExists($conn, $email)) {
                $kode_penumpang = getUniqueKodePenumpang($conn, $passenger_name);

                // Hash the password
                $hashed_password = md5($password); // Consider using a more secure hashing method

                // Insert the data into the database including the hashed password
                $sql = "INSERT INTO data_pnp (kode_penumpang, passenger_name, passenger_phone, email, password) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $kode_penumpang, $passenger_name, $passenger_phone, $email, $hashed_password);

                if ($stmt->execute()) {
                    header('Location: register.php?success=Registrasi berhasil!');
                } else {
                    header('Location: register.php?error=Terjadi kesalahan: ' . $stmt->error);
                }
                $stmt->close();
            } else {
                header('Location: register.php?error=Email sudah digunakan!');
            }
        } else {
            header('Location: register.php?error=Password dan konfirmasi password tidak cocok!');
        }
    } else {
        header('Location: register.php?error=Mohon isi data dengan benar!');
    }
} else {
    header('Location: register.php?error=Invalid request method.');
}

$conn->close();
