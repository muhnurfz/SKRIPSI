<?php
session_start();
include('conn.php');
include('navbar.php');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login_penumpang.php");
    exit();
}

// Get the email of the logged-in user
$logged_in_email = $_SESSION['email'];

// Fetch passenger data from `data_pnp` based on the logged-in user's email
$sql_pnp = "SELECT * FROM data_pnp WHERE email = ?";
$stmt_pnp = $conn->prepare($sql_pnp);
$stmt_pnp->bind_param("s", $logged_in_email);
$stmt_pnp->execute();
$result_pnp = $stmt_pnp->get_result();
$passenger = $result_pnp->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penumpang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #333;
            color: white;
            padding: 10px;
            text-align: center;
        }
        .terms, .container-details {
            margin: 20px auto;
            padding: 20px;
            background-color: #f7f7f7;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            max-width: 800px;
        }
        .terms h2, .container-details h3 {
            margin-top: 0;
        }
        .container-details {
            margin-top: 30px;
        }
        .detail-item {
            margin-bottom: 20px;
        }
        ul {
            padding-left: 20px;
        }
        li {
            margin-bottom: 10px;
        }
        @media (max-width: 768px) {
            .terms, .container-details {
                margin: 10px;
                padding: 15px;
            }
            ul {
                padding-left: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <?php include('navbar.php'); ?>
    </div>
    <div class="terms">
        <h2>Ketentuan Perjalanan</h2>
        <p>Berikut adalah ketentuan yang berlaku untuk perjalanan Anda:</p>
        <ul>
            <li>Harap tiba di boarding point setidaknya 30 menit sebelum keberangkatan.</li>
            <li>Pastikan membawa tiket dan identitas diri saat boarding.</li>
            <li>Untuk anak usia 3 tahun keatas atau dengan tinggi 90cm maka WAJIB DIBELIKAN 1 TIKET.</li>
            <li>Apabila bus mengalami masalah dan keberangkatan dibatalkan maka uang akan dikembalikan 100%</li>
            <li>Pembayaran DownPayment minimal 50% dari tarif tiket.</li>
            <li>Kami tidak bertanggung jawab atas barang hilang atau rusak selama perjalanan.</li>
        </ul>
    </div>
    <div class="container-details">
        <div class="detail-item">
            <h3>Perubahan dan Pembatalan Tiket</h3>
            <p>Informasi mengenai perubahan atau pembatalan tiket Anda:</p>
            <ul>
                <li>Perubahan jadwal tiket dapat dilakukan jika dilakukan paling lambat 24 jam sebelum keberangkatan.</li>
                <li>Perubahan jadwal tiket dapat dilakukan maksimal 2x.</li>
                <li>Pembatalan tiket dikenakan biaya administrasi sebesar Rp 30.000 dari tarif tiket dan dilakukan paling lambat 24 jam sebelum keberangkatan.</li>
                <li>Pembatalan tiket yang di setujui, maka dana akan dilakukan maksimal 2x24 jam.</li>
                <li>Tiket tidak dapat diubah atau dibatalkan setelah melewati batas waktu yang ditentukan.</li>
            </ul>
        </div>
    </div>
</body>
</html>
