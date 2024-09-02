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

// // Fetch transaction history from `orders` based on the logged-in user's email
// $sql_orders = "SELECT * FROM orders WHERE email = ?";
// $stmt_orders = $conn->prepare($sql_orders);
// $stmt_orders->bind_param("s", $logged_in_email);
// $stmt_orders->execute();
// $result_orders = $stmt_orders->get_result();


// // Array of Indonesian day names
// $days_in_indonesian = [
//     'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
// ];

// // Array of Indonesian month names
// $months_in_indonesian = [
//     'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
//     'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
// ];

// // Get Indonesian day name
// $day_index = date('w', strtotime($departure_date));
// $day_name = $days_in_indonesian[$day_index];

// // Get Indonesian month name
// $month_index = date('n', strtotime($departure_date)) - 1;
// $month_name = $months_in_indonesian[$month_index];

// // Format date in Indonesian
// $formatted_date = $day_name . ', ' . date('d', strtotime($departure_date)) . ' ' . $month_name . ' ' . date('Y', strtotime($departure_date));


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penumpang</title>
    <!-- Bootstrap CSS -->
    <style>
      .terms, .container-details {
    margin-left: 260px; /* Sesuaikan dengan lebar sidebar */
    padding: 20px; /* Opsional, untuk memberi ruang di sekitar konten */
}

.terms, .container-details {
    z-index: 900; /* Pastikan ini lebih tinggi dari z-index sidebar */
}
@media (max-width: 768px) {
    .sidebar {
        width: 100%; /* Sidebar akan menempati seluruh lebar pada tampilan kecil */
    }

    .terms, .container-details {
        margin-left: 0;
    }
}
    </style>
</head>
<body>
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
        <!-- <div class="row">
            <div class="col-md-6"> -->
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
            <!-- <div class="col-md-6">
                <div class="detail-item">
                    <h3>Kelas dan Jadwal Bus</h3>
                    <p>Berikut adalah informasi tentang kelas dan jadwal bus:</p>
                    <ul>
                        <li><strong>Kelas Ekonomi:</strong> Fasilitas standar, kursi yang nyaman, dan layanan dasar.</li>
                        <li><strong>Kelas Bisnis:</strong> Fasilitas lebih lengkap, kursi lebih nyaman, dan layanan prioritas.</li>
                        <li><strong>Kelas VIP:</strong> Fasilitas premium, kursi recliner, dan layanan khusus.</li>
                    </ul>
                    <a href="jadwal_bus.php" class="btn btn-primary">Lihat Jadwal Bus</a>
                </div>
            </div> -->
        </div>
    </div>
</div>
</body>
</html>
