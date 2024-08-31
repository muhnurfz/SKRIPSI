<?php
include('conn.php'); // Ensure you include your database connection

// Set the content type to JSON
header('Content-Type: application/json');

// Query to fetch tariff data
$sql = "SELECT route, tarif FROM tarif_tiket";
$result = $conn->query($sql);

$tariffs = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tariffs[strtoupper($row['route'])] = $row['tarif'];
    }
}

// Close connection
$conn->close();

// Output the JSON data
echo json_encode($tariffs);
?>
