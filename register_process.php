<?php
include('conn.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

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
                $sql = "INSERT INTO data_pnp (kode_penumpang, passenger_name, passenger_phone, email, password, status_konfirmasi) VALUES (?, ?, ?, ?, ?, 0)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $kode_penumpang, $passenger_name, $passenger_phone, $email, $hashed_password);

                if ($stmt->execute()) {
                    // Send confirmation email
                    $mail = new PHPMailer(true);
                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host = 'smtp.hostinger.com'; // Host SMTP Anda
                        $mail->SMTPAuth = true;
                        $mail->Username = 'customer.service@tiket.agungindahtrav.com'; // Alamat email pengirim
                        $mail->Password = '1Customer.service'; // Password email pengirim
                        $mail->SMTPSecure = 'ssl'; // Atau 'tls' jika menggunakan port 587
                        $mail->Port = 465; // Gunakan port 465 untuk 'ssl' atau port 587 untuk 'tls'

                        // Penerima dan pengirim
                        $mail->setFrom('customer.service@tiket.agungindahtrav.com', 'Tiket Agung Indah Travel');
                        $mail->addAddress($email);

                        // Konten email
                        $mail->isHTML(true); // Set email format to HTML
                        $mail->Subject = 'Konfirmasi Registrasi';
                        $mail->Body    = '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; }
        .header { background-color: #007bff; color: #fff; padding: 10px; text-align: center; }
        .content { padding: 20px; }
        .button { display: inline-block; padding: 10px 20px; font-size: 16px; color: #fff; background-color: #007bff; text-decoration: none; border-radius: 5px; }
        .footer { text-align: center; font-size: 12px; color: #777; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Selamat Datang di Tiket Agung Indah Travel</h1>
        </div>
        <div class="content">
            <p>Terima kasih telah mendaftar di situs kami. Untuk menyelesaikan proses pendaftaran, silakan klik tombol di bawah ini untuk mengkonfirmasi akun Anda.</p>
            <a href="http://example.com/konfirmasi_pnp.php?email=' . urlencode($email) . '&code=' . urlencode($kode_penumpang) . '" class="button">Konfirmasi Akun</a>
            <p>Jika Anda tidak melakukan pendaftaran, abaikan email ini.</p>
        </div>
        <div class="footer">
            &copy; ' . date('Y') . ' Tiket Agung Indah Travel. All rights reserved.
        </div>
    </div>
</body>
</html>
';

                        $mail->send();
                        header('Location: register.php?success=Registrasi berhasil! Silakan cek email Anda untuk konfirmasi.');
                    } catch (Exception $e) {
                        header('Location: register.php?error=Registrasi berhasil, tetapi gagal mengirim email konfirmasi: ' . $mail->ErrorInfo);
                    }
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
?>
