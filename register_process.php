<?php
include('conn.php');

// Generate kode penumpang
function generateKodePenumpang($name) {
    $nameParts = explode(' ', $name);
    $initials = strtoupper(substr($nameParts[0], 0, 2));
    $monthYear = date('mY');
    $randomDigits = sprintf("%02d", mt_rand(0, 99));
    $kode_penumpang = $initials . $monthYear . $randomDigits;
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passenger_name = $_POST['username'];
    $passenger_phone = $_POST['passenger_phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (!empty($passenger_name) && !empty($passenger_phone) && !empty($email) && !empty($password) && $password === $confirm_password) {
        $kode_penumpang = getUniqueKodePenumpang($conn, $passenger_name);

        $hashed_password = md5($password); // Pastikan hashing ini aman sesuai kebutuhan Anda

        $sql = "INSERT INTO data_pnp (kode_penumpang, passenger_name, passenger_phone, email) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $kode_penumpang, $passenger_name, $passenger_phone, $email);

        if ($stmt->execute()) {
            header('Location: register.php?success=Registration successful!');
        } else {
            header('Location: register.php?error=Terjadi kesalahan: ' . $stmt->error);
        }
        $stmt->close();
    } else {
        header('Location: register.php?error=Semua field harus diisi dan password harus cocok!');
    }
} else {
    header('Location: register.php?error=Invalid request method.');
}

$conn->close();
