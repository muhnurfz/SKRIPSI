<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ticket_booking";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM orders WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

$sql = "SELECT * FROM orders";
$result = $conn->query($sql);

if ($result === FALSE) {
    die("Error: " . $conn->error);
}

// Function to generate a unique booking code
function generateBookingCode($length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $bookingCode = '';
    for ($i = 0; $i < $length; $i++) {
        $bookingCode .= $characters[rand(0, $charactersLength - 1)];
    }
    return $bookingCode;
}
?>
