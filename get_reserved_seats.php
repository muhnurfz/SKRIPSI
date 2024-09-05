<?php
include('conn.php');

// Mendapatkan data dari permintaan
$route = $_GET['route'];
$departure_date = $_GET['departure_date'];

// Mendapatkan kursi yang sudah dipesan berdasarkan rute dan tanggal keberangkatan
$sql = "SELECT selected_seats FROM orders WHERE route = ? AND departure_date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $route, $departure_date);
$stmt->execute();
$result = $stmt->get_result();

$reserved_seats = [];
while ($row = $result->fetch_assoc()) {
    $seats = explode(",", $row['selected_seats']);
    $reserved_seats = array_merge($reserved_seats, $seats);
}

// Get the count of previously selected seats
$seat_count_sql = "SELECT COUNT(*) as seat_count FROM orders WHERE route = ? AND departure_date = ?";
$seat_count_stmt = $conn->prepare($seat_count_sql);
$seat_count_stmt->bind_param("ss", $route, $departure_date);
$seat_count_stmt->execute();
$seat_count_result = $seat_count_stmt->get_result();
$seat_count_row = $seat_count_result->fetch_assoc();
$seat_count = $seat_count_row['seat_count'];

echo json_encode([
    'seats' => $reserved_seats,
    'seat_count' => $seat_count
]);

$stmt->close();
$seat_count_stmt->close();
$conn->close();
?>
