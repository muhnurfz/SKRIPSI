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

echo json_encode($reserved_seats);

$stmt->close();
$conn->close();
?>

