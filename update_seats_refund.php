<?php
include('conn.php');
// Mengambil ID order dari form
$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

// Mengambil kursi yang dipilih dari form
$selected_seats = isset($_POST['seats']) ? $_POST['seats'] : [];

// Mengubah array kursi yang dipilih menjadi string yang dipisahkan koma
$selected_seats_str = implode(',', $selected_seats);

// Memperbarui nilai `selected_seats` di database
$sql = "UPDATE orders SET selected_seats = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $selected_seats_str, $order_id);

if ($stmt->execute()) {
    echo "Seats updated successfully.";
} else {
    echo "Error updating seats: " . $stmt->error;
}

// Menutup koneksi
$stmt->close();
$conn->close();
?>
