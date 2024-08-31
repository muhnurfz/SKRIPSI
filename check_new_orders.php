<?php
include('conn.php');

// Cek apakah ada pesanan baru yang belum dilihat oleh admin
$sql = "SELECT COUNT(*) as new_orders FROM orders WHERE seen_by_admin = 0";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

echo json_encode(['new_orders' => $row['new_orders']]);
?>
