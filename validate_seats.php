<?php
include('conn.php');

// Mendapatkan data dari permintaan
$route = $_POST['route'];
$departure_date = $_POST['departure_date'];
$selected_seats_count = $_POST['selected_seats_count'];

// Mendapatkan jumlah kursi yang sudah dipesan
$sql = "SELECT COUNT(*) AS reserved_count FROM orders WHERE route = ? AND departure_date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $route, $departure_date);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$previous_reserved_seats_count = $row['reserved_count'];

$response = [
    'valid' => ($selected_seats_count === (int)$previous_reserved_seats_count)
];

echo json_encode($response);

$stmt->close();
$conn->close();
?>
