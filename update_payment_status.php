<?php
session_start();
include('conn.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $status_pembayaran = $_POST['status_pembayaran'];

    // Update status pembayaran di database
    $stmt = $conn->prepare("UPDATE orders SET status_pembayaran = ? WHERE id = ?");
    $stmt->bind_param("si", $status_pembayaran, $id);
    $stmt->execute();

    // Redirect kembali ke halaman utama setelah update
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}
?>
