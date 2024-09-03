<?php
include('conn.php');
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Menyertakan file PHPMailer secara manual
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

$message = '';
$alertClass = '';

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
        $reset_link = "https://www.tiket.agungindahtrav.com/reset_pass_pnp.php?kode_penumpang=" . urlencode($kode_penumpang);

        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.hostinger.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'customer.service@tiket.agungindahtrav.com'; // Ganti dengan email Anda
            $mail->Password = '1Customer.service'; // Ganti dengan password email Anda
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            // Penerima dan pengirim
            $mail->setFrom('customer.service@tiket.agungindahtrav.com', 'Tiket Agung Indah Travel');
            $mail->addAddress($email);

            // Konten email
            $mail->isHTML(true);
            $mail->Subject = 'Reset Password Akun Anda';
        // Konten email dengan HTML dan CSS untuk UI/UX yang lebih menarik
$mail->Body = '
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333333;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            text-align: center;
            padding-bottom: 20px;
        }
        .email-header img {
            width: 100px;
            margin-bottom: 20px;
        }
        .email-content {
            padding: 20px;
        }
        .email-content h1 {
            font-size: 24px;
            color: #333333;
        }
        .email-content p {
            font-size: 16px;
            line-height: 1.5;
            color: #666666;
        }
        .email-content a {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            font-size: 16px;
            color: #ffffff;
            background-color: #28a745;
            text-decoration: none;
            border-radius: 5px;
        }
        .email-footer {
            text-align: center;
            padding-top: 20px;
            font-size: 14px;
            color: #999999;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <img src="https://example.com/logo.png" alt="Company Logo">
        </div>
        <div class="email-content">
            <h1>Reset Password Anda</h1>
            <p>Halo,</p>
            <p>Kami menerima permintaan untuk mereset password akun Anda. Jika Anda tidak melakukan permintaan ini, abaikan saja email ini.</p>
            <p>Jika Anda ingin melanjutkan, klik tombol di bawah ini untuk mereset password Anda:</p>
            <a href="' . $reset_link . '">Reset Password</a>
            <p>Link ini hanya berlaku selama 24 jam.</p>
        </div>
        <div class="email-footer">
            <p>Jika tombol di atas tidak berfungsi, salin dan tempel link berikut ini di browser Anda:</p>
            <p><a href="' . $reset_link . '">' . $reset_link . '</a></p>
            <p>Terima kasih,<br>Tim Tiket Agung Indah Travel</p>
        </div>
    </div>
</body>
</html>
';

// Alternative plain text body in case the recipient's email client does not support HTML emails
$mail->AltBody = 'Kami menerima permintaan untuk mereset password akun Anda. Jika Anda tidak melakukan permintaan ini, abaikan email ini. Jika Anda ingin melanjutkan, salin dan tempel link berikut ini di browser Anda: ' . $reset_link;

            // Kirim email
            $mail->send();
            $message = 'Link reset password telah dikirim ke email Anda.';
            $alertClass = 'alert-success';
        } catch (Exception $e) {
            $message = "Email tidak dapat dikirim. Mailer Error: {$mail->ErrorInfo}";
            $alertClass = 'alert-danger';
        }
    } else {
        $message = 'Email tidak ditemukan di database kami.';
        $alertClass = 'alert-danger';
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
        .alert {
            text-align: center;
        }

/* Adjust button font size for mobile */
@media (max-width: 576px) {
    .btn {
        font-size: 0.875rem; /* Slightly smaller font size on mobile */
    }
}
    </style>
</head>
<body>
<div class="forgot-password-container">
    <h2 class="text-center">Lupa Password</h2>
    <?php if (!empty($message)): ?>
        <div class="alert <?= $alertClass; ?>">
            <?= $message; ?>
        </div>
    <?php endif; ?>
    <form action="" method="POST">
        <div class="form-group">
            <label for="email">Alamat Email</label>
            <input type="email" name="email" class="form-control" id="email" placeholder="contoh@gmail.com" required>
        </div>
        <div class="form-group row">
        <div class="col-12 col-md-6 mb-2 mb-md-0">
        <a class="btn btn-secondary btn-block" href="login_penumpang.php">Kembali</a>
    </div>
    <div class="col-12 col-md-6">
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
