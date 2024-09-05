<?php
// Include config file for database connection
include('conn.php');

// Query to get the history from the orders table
$sql = "SELECT id, passenger_name, booking_code, 
               tanggal_pembayaran, 
               tanggal_pengajuan_batal, 
               tanggal_img_batal, 
               tanggal_edit 
        FROM orders 
        ORDER BY tanggal_edit DESC";

// Execute the query
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // Output data of each row
    echo "<table border='1'>";
    echo "<tr>
            <th>ID</th>
            <th>Passenger Name</th>
            <th>Booking Code</th>
            <th>Tanggal Pembayaran</th>
            <th>Tanggal Pengajuan Batal</th>
            <th>Tanggal Img Batal</th>
            <th>Tanggal Edit</th>
          </tr>";

    while($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>".$row['id']."</td>
                <td>".$row['passenger_name']."</td>
                <td>".$row['booking_code']."</td>
                <td>".$row['tanggal_pembayaran']."</td>
                <td>".$row['tanggal_pengajuan_batal']."</td>
                <td>".$row['tanggal_img_batal']."</td>
                <td>".$row['tanggal_edit']."</td>
              </tr>";
    }
    echo "</table>";
} else {
    echo "0 results";
}

// Close connection
$conn->close();
?>
