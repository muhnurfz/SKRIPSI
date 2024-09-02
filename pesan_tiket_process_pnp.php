<?php
include('conn.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_penumpang = $_POST['kode_penumpang'];
    $password = md5($_POST['password']); // Menggunakan MD5 untuk hashing password

    $query = "SELECT * FROM data_pnp WHERE kode_penumpang = '$kode_penumpang' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        $_SESSION['kode_penumpang'] = $user['kode_penumpang'];
        $_SESSION['passenger_name'] = $user['passenger_name'];
        $_SESSION['passenger_phone'] = $user['passenger_phone'];
        $_SESSION['email'] = $user['email'];

        header('Location: pesan_tiket_pnp.php'); // Redirect ke halaman form pemesanan
        exit();
    } else {
        echo "Login gagal! Kode penumpang atau password salah.";
    }
}
?>