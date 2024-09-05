<?php
include('conn.php');

// Mendapatkan data dari permintaan
$route = $_GET['route'];
$departure_date = $_GET['departure_date'];

// Mendapatkan kursi yang sudah dipesan dan total kursi berdasarkan rute dan tanggal keberangkatan
$sql = "SELECT selected_seats, total_seats FROM orders WHERE route = ? AND departure_date = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $route, $departure_date);
$stmt->execute();
$result = $stmt->get_result();

$reserved_seats = [];
$total_seats = 0;
while ($row = $result->fetch_assoc()) {
    $seats = explode(",", $row['selected_seats']);
    $reserved_seats = array_merge($reserved_seats, $seats);
    $total_seats = $row['total_seats']; // Mengambil nilai total_seats
}

echo json_encode([
    'reserved_seats' => $reserved_seats,
    'total_seats' => $total_seats
]);

$stmt->close();
$conn->close();
?>
