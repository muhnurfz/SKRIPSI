<?php
// Include your database connection file
include('conn.php');

// Hitung pesanan baru dalam 2 jam terakhir
$sql_new_orders_last_2_hours = "SELECT COUNT(*) AS count_new_orders FROM orders WHERE purchase_date >= NOW() - INTERVAL 2 HOUR";
$result_new_orders = $conn->query($sql_new_orders_last_2_hours);
$count_new_orders = $result_new_orders->fetch_assoc()['count_new_orders'];

// Hitung pesanan dengan `pengajuan_batal` = 'ya'
$sql_batal_orders = "SELECT COUNT(*) AS count_batal_orders FROM orders WHERE pengajuan_batal = 'ya'";
$result_batal_orders = $conn->query($sql_batal_orders);
$count_batal_orders = $result_batal_orders->fetch_assoc()['count_batal_orders'];

// Hitung total pesanan dalam 7 hari terakhir
$sql_orders_last_7_days = "SELECT COUNT(*) AS count_orders_last_7_days FROM orders WHERE purchase_date >= NOW() - INTERVAL 7 DAY";
$result_orders_last_7_days = $conn->query($sql_orders_last_7_days);
$count_orders_last_7_days = $result_orders_last_7_days->fetch_assoc()['count_orders_last_7_days'];

// Menghitung jumlah order yang diedit dalam 2 jam terakhir
$two_hours_ago = date("Y-m-d H:i:s", strtotime('-2 hours'));
$sql_edited_orders_last_2_hours = "SELECT COUNT(*) AS count_edited_orders FROM orders WHERE purchase_date >= '$two_hours_ago'";
$result_edited_orders = $conn->query($sql_edited_orders_last_2_hours);
$count_edited_orders = $result_edited_orders->fetch_assoc()['count_edited_orders'];

// Tampilkan semua record data berdasarkan kriteria
$sql_all_records = "SELECT * FROM orders";
$result_all_records = $conn->query($sql_all_records);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Penumpang</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-5">
    <h1>Riwayat Penumpang</h1>
    
    <div class="alert alert-info">
        <strong>Pesanan Baru dalam 2 Jam Terakhir:</strong> <?php echo $count_new_orders; ?>
    </div>
    <div class="alert alert-warning">
        <strong>Pesanan Pembatalan:</strong> <?php echo $count_batal_orders; ?>
    </div>
    <div class="alert alert-success">
        <strong>Total Pesanan dalam 7 Hari Terakhir:</strong> <?php echo $count_orders_last_7_days; ?>
    </div>
    <div class="alert alert-secondary">
        <strong>Jumlah Order yang Diedit dalam 2 Jam Terakhir:</strong> <?php echo $count_edited_orders; ?>
    </div>

    <h2 class="mt-4">Semua Rekaman Data</h2>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Departure</th>
                <th>Route</th>
                <th>Destination</th>
                <th>Departure Date</th>
                <th>Passenger Name</th>
                <th>Passenger Phone</th>
                <th>Booking Code</th>
                <th>Purchase Date</th>
                <th>Selected Seats</th>
                <th>Total Tariff</th>
                <th>Total Seats</th>
                <th>Check In Status</th>
                <th>Bus Code</th>
                <th>Status Pembayaran</th>
                <th>Bukti Pembayaran</th>
                <th>Comments</th>
                <th>Email</th>
                <th>Tarif ID</th>
                <th>User ID Admin</th>
                <th>Passenger ID</th>
                <th>Route ID</th>
                <th>Pengajuan Batal</th>
                <th>Img Batal</th>
                <th>Uang Muka</th>
                <th>ID Refund</th>
                <th>PNP Dewasa</th>
                <th>PNP Balita</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result_all_records->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['departure']); ?></td>
                    <td><?php echo htmlspecialchars($row['route']); ?></td>
                    <td><?php echo htmlspecialchars($row['destination']); ?></td>
                    <td><?php echo htmlspecialchars($row['departure_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['passenger_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['passenger_phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['booking_code']); ?></td>
                    <td><?php echo htmlspecialchars($row['purchase_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['selected_seats']); ?></td>
                    <td><?php echo htmlspecialchars($row['total_tariff']); ?></td>
                    <td><?php echo htmlspecialchars($row['total_seats']); ?></td>
                    <td><?php echo htmlspecialchars($row['check_in_status']); ?></td>
                    <td><?php echo htmlspecialchars($row['bus_code']); ?></td>
                    <td><?php echo htmlspecialchars($row['status_pembayaran']); ?></td>
                    <td><?php echo $row['bukti_pembayaran'] ? 'Available' : 'Not Available'; ?></td>
                    <td><?php echo htmlspecialchars($row['comments']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['tarif_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['user_id_admin']); ?></td>
                    <td><?php echo htmlspecialchars($row['passenger_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['route_id']); ?></td>
                    <td><?php echo htmlspecialchars($row['pengajuan_batal']); ?></td>
                    <td><?php echo $row['img_batal'] ? 'Available' : 'Not Available'; ?></td>
                    <td><?php echo htmlspecialchars($row['uang_muka']); ?></td>
                    <td><?php echo htmlspecialchars($row['id_refund']); ?></td>
                    <td><?php echo htmlspecialchars($row['pnp_dewasa']); ?></td>
                    <td><?php echo htmlspecialchars($row['pnp_balita']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>
