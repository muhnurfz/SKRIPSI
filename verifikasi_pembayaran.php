<?php
// Pastikan Anda sudah menghubungkan ke database
include('conn.php'); // Sesuaikan dengan konfigurasi koneksi database Anda

// Dapatkan ID dari parameter URL
$id = $_GET['id'];

// Lakukan query untuk memperbarui status pembayaran menjadi 'verified'
$sql = "UPDATE orders SET status_pembayaran = 'verified' WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);

if ($stmt->execute()) {
    // Jika berhasil, arahkan kembali ke halaman utama atau halaman lain yang relevan
    header("Location: crud.php?success=1");
} else {
    // Jika gagal, tampilkan pesan error
    echo "Error: " . $conn->error;
}

// Tutup koneksi
$stmt->close();
$conn->close();
?>
