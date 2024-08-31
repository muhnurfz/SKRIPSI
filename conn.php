<?php
$servername = "localhost";
$username = "andro";
$password = "1";
$dbname = "ticket_booking";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
