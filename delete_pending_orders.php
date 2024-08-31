<?php
include('conn.php');
date_default_timezone_set('Asia/Jakarta'); // Sesuaikan dengan timezone yang Anda inginkan

echo "Cron job dimulai...\n"; // Log output saat cron job dimulai

// Durasi dalam detik (2 jam = 7200 detik)
$time_limit_in_seconds = 7200;

// Query untuk menghapus record di edit_logs yang terkait dengan orders yang `status_pembayaran` masih pending lebih dari 2 jam
$sql_logs = "DELETE FROM edit_logs WHERE order_id IN (
                SELECT id 
                FROM orders 
                WHERE status_pembayaran = 'pending' 
                AND (UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(purchase_date)) > $time_limit_in_seconds
            )";

if ($conn->query($sql_logs) === TRUE) {
    echo "menghapus record di edit_logs terkait dengan orders yang `status_pembayaran` masih pending lebih dari 2 jam berhasil dihapus.\n";
} else {
    echo "Error (edit_logs): " . $conn->error . "\n";
}

// Set timezone untuk sesi MySQL
$conn->query("SET time_zone = '+07:00';");

// Query untuk menghapus record yang `status_pembayaran` masih pending lebih dari 2 jam
$sql_delete = "DELETE FROM `orders` 
               WHERE status_pembayaran = 'pending' 
               AND purchase_date < NOW() - INTERVAL 2 HOUR;";

if ($conn->query($sql_delete) === TRUE) {
    // Cek berapa banyak record yang dihapus
    $affected_rows = $conn->affected_rows;
    if ($affected_rows > 0) {
        echo "$affected_rows pesanan pending lebih dari 2 jam berhasil dihapus.\n";
    } else {
        echo "Tidak ada pesanan pending yang dihapus.\n"; // Jika tidak ada record yang dihapus
    }
} else {
    echo "Error (orders): " . $conn->error . "\n"; // Output jika terjadi error
}

echo "Cron job selesai.\n"; // Log output saat cron job selesai

$conn->close();

?>
