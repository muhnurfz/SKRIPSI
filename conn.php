<?php
$servername = "localhost";
$username = "u162416084_agungindah";
$password = "ticket_booking123A";
$dbname = "u162416084_ticket_booking";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

