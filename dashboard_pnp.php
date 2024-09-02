<?php
session_start();
include('conn.php');
include('navbar.php');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login_penumpang.php");
    exit();
}

// Get the email of the logged-in user
$logged_in_email = $_SESSION['email'];

// Fetch passenger data from `data_pnp` based on the logged-in user's email
$sql_pnp = "SELECT * FROM data_pnp WHERE email = ?";
$stmt_pnp = $conn->prepare($sql_pnp);
$stmt_pnp->bind_param("s", $logged_in_email);
$stmt_pnp->execute();
$result_pnp = $stmt_pnp->get_result();
$passenger = $result_pnp->fetch_assoc();

// // Fetch transaction history from `orders` based on the logged-in user's email
// $sql_orders = "SELECT * FROM orders WHERE email = ?";
// $stmt_orders = $conn->prepare($sql_orders);
// $stmt_orders->bind_param("s", $logged_in_email);
// $stmt_orders->execute();
// $result_orders = $stmt_orders->get_result();


// // Array of Indonesian day names
// $days_in_indonesian = [
//     'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
// ];

// // Array of Indonesian month names
// $months_in_indonesian = [
//     'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
//     'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
// ];

// // Get Indonesian day name
// $day_index = date('w', strtotime($departure_date));
// $day_name = $days_in_indonesian[$day_index];

// // Get Indonesian month name
// $month_index = date('n', strtotime($departure_date)) - 1;
// $month_name = $months_in_indonesian[$month_index];

// // Format date in Indonesian
// $formatted_date = $day_name . ', ' . date('d', strtotime($departure_date)) . ' ' . $month_name . ' ' . date('Y', strtotime($departure_date));


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penumpang</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
      
    </style>
</head>
<body>

</body>
</html>
