<?php
include('conn.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Menyertakan file PHPMailer secara manual
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include('conn.php'); // Koneksi ke database

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Periksa apakah email ada di database
    $query = "SELECT * FROM data_pnp WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Email ditemukan, kirim email reset password
        $row = $result->fetch_assoc();
        $kode_penumpang = $row['kode_penumpang'];

        // Buat link reset password
        $reset_link = "https://www.example.com/reset_pass_pnp.php?kode_penumpang=" . $kode_penumpang;

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'customer.service@tiket.agungindahtrav.com';
            $mail->Password = '1Customer.service';
            $mail->SMTPSecure = 'ssl';
            $mail->Port = 465;

            // Penerima dan pengirim
            $mail->setFrom('customer.service@tiket.agungindahtrav.com', 'Tiket Agung Indah Travel');
            $mail->addAddress($email);

            // Konten email
            $mail->isHTML(true);
            $mail->Subject = 'Reset Password';
            $mail->Body    = 'Klik link berikut untuk mereset password Anda: <a href="' . $reset_link . '">Reset Password</a>';

            // Kirim email
            $mail->send();
            $message = 'Link reset password telah dikirim ke email Anda.';
        } catch (Exception $e) {
            $message = "Email tidak dapat dikirim. Mailer Error: {$mail->ErrorInfo}";
        }
    } else {
        $message = 'Email tidak ditemukan di database kami.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Lupa Password</h3>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-info">
                                <?= $message; ?>
                            </div>
                        <?php endif; ?>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="email">Alamat Email</label>
                                <input type="email" name="email" class="form-control" id="email" required>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Kirim Link Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
