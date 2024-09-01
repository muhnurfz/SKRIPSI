<?php
include('conn.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Menyertakan file PHPMailer secara manual
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

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
        $reset_link = "https://www.example.com/reset_pass_pnp.php?kode_penumpang=" . urlencode($kode_penumpang);

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'customer.service@tiket.agungindahtrav.com';
            $mail->Password = '1Customer.service';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            // Penerima dan pengirim
            $mail->setFrom('customer.service@tiket.agungindahtrav.com', 'Tiket Agung Indah Travel');
            $mail->addAddress($email);

            // Konten email
            $mail->isHTML(true);
            $mail->Subject = 'Reset Password Akun Anda';
            $mail->Body    = 'Klik link berikut untuk mereset password Anda: <a href="' . $reset_link . '">Reset Password</a>';
            $mail->AltBody = 'Klik link berikut untuk mereset password Anda: ' . $reset_link;

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
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('your-background-image.jpg'); /* Specify your background image URL here */
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .forgot-password-container {
            background-color: rgba(255, 255, 255, 0.85);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(10px);
        }
        .btn-primary, .btn-secondary {
            transition: background-color 0.3s, border-color 0.3s, box-shadow 0.3s;
            border-radius: 30px;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #004494; /* Darker shade */
            border-color: #003a75;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        .btn-secondary:hover, .btn-secondary:focus {
            background-color: #5a6268; /* Darker shade */
            border-color: #4e555b;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        .loading {
            display: none;
            text-align: center;
            margin-top: 10px;
        }
        .loading.active {
            display: block;
        }
        .form-control {
            border-radius: 0.5rem;
        }
        h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #333;
        }
        .form-footer {
            margin-top: 20px;
            text-align: center;
        }
        .form-footer a {
            color: #007bff;
            text-decoration: none;
        }
        .form-footer a:hover {
            text-decoration: underline;
        }
        .alert-info {
            text-align: center;
        }
    </style>
</head>
<body>
<div class="forgot-password-container">
    <h2 class="text-center">Lupa Password</h2>
    <?php if (!empty($message)): ?>
        <div class="alert alert-info">
            <?= $message; ?>
        </div>
    <?php endif; ?>
    <form action="" method="POST">
        <div class="form-group">
            <label for="email">Alamat Email</label>
            <input type="email" name="email" class="form-control" id="email" placeholder="contoh@gmail.com" required>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <a class="btn btn-secondary btn-block" href="login_penumpang.php">Kembali</a>
            </div>
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary btn-block">Kirim Link Reset</button>
            </div>
        </div>
    </form>
    <div id="loading" class="loading">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <div class="form-footer">
        <p>Sudah ingat password? <a href="login_penumpang.php">Login</a></p>
    </div>
</div>
</body>
</html>
