<?php
include('conn.php');
session_start();

// Set session timeout period (in seconds)
$timeout_duration = 1800; // 30 minutes

// Check if the session is active and if the timeout has expired
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();     // Unset $_SESSION variables
    session_destroy();   // Destroy session data in storage
    header("Location: login.php"); // Redirect to login page
    exit();
}

// Update the last activity time
$_SESSION['LAST_ACTIVITY'] = time();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get the username from the session
$username = $_SESSION['username'];

// Initialize search variables
$booking_code = isset($_POST['booking_code']) ? $conn->real_escape_string($_POST['booking_code']) : '';
$departure_date = isset($_POST['departure_date']) ? $conn->real_escape_string($_POST['departure_date']) : '';
$route = isset($_POST['route']) ? $conn->real_escape_string($_POST['route']) : '';

// Build query with optional filters
$sql = "SELECT * FROM order_logs WHERE 1=1";
if ($booking_code) {
    $sql .= " AND booking_code LIKE '%$booking_code%'";
}
if ($departure_date) {
    $sql .= " AND order_id IN (SELECT id FROM orders WHERE departure_date = '$departure_date')";
}
if ($route) {
    $sql .= " AND order_id IN (SELECT id FROM orders WHERE route LIKE '%$route%')";
}
$sql .= " ORDER BY changed_at DESC";

// Execute query
$result = $conn->query($sql);

// Log this query activity into `order_logs` table
if ($conn->error) {
    $log_query = "INSERT INTO order_logs (order_id, booking_code, column_changed, old_value, new_value, changed_at) VALUES (0, '', 'Query Error', '', '".$conn->error."', NOW())";
    $conn->query($log_query);
}

// Assuming there's more logic here to handle the results, and you want to log successful queries
if ($result) {
    $log_query = "INSERT INTO order_logs (order_id, booking_code, column_changed, old_value, new_value, changed_at) VALUES (0, '', 'Search Query', '', 'Executed', NOW())";
    $conn->query($log_query);
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat data penumpang</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Custom styles for mobile responsiveness */
        @media (max-width: 768px) {
            .card-body {
                padding: 1.5rem;
            }
            .table-responsive {
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="card p-4">
            <div class="card-body">
                <h2 class="card-title">Riwayat data penumpang</h2>

                <!-- Search Form -->
                <form method="post" class="mb-4">
                    <div class="row">
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label for="departure_date">Tanggal Keberangkatan :</label>
                                <input type="date" class="form-control" id="departure_date" name="departure_date">
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label for="route">BUS:</label>
                                <select class="form-control" id="route" name="route">
                                    <option value="">Pilih Rute BUS</option>
                                    <option value="ponorogo">Ponorogo</option>
                                    <option value="solo">Solo</option>
                                    <option value="bojonegoro">Bojonegoro</option>
                                    <option value="gemolong">Gemolong</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12 mb-3">
                            <div class="form-group">
                                <label for="booking_code">Kode Booking:</label>
                                <input type="text" class="form-control" id="booking_code" name="booking_code" placeholder="Masukkan kode booking">
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="search" class="btn btn-primary">Cari</button>
                </form>

                <div class="table-responsive">
                    <table class="table table-striped mt-4">
                        <thead>
                            <tr>
                                <th>ID log</th>
                                <th>ID orders</th>
                                <th>Kode booking</th>
                                <th>Data yang diganti</th>
                                <th>Data sebelum</th>
                                <th>Status/data sesudah</th>
                                <th>Diganti pada</th>
                                <th>Nama pegawai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                // Output data of each row
                                while ($row = $result->fetch_assoc()) {
                                    // Create a DateTime object from the UTC timestamp
                                    $utcDateTime = new DateTime($row["changed_at"], new DateTimeZone('UTC'));
                                    
                                    // Convert to GMT+7 timezone
                                    $utcDateTime->setTimezone(new DateTimeZone('Asia/Jakarta')); // GMT+7 timezone
                                    
                                    // Format the datetime as DD/MM/YYYY HH:MM:SS
                                    $formattedDateTime = $utcDateTime->format('d/m/Y H:i:s');

                                    echo "<tr>
                                            <td>" . $row["log_id"] . "</td>
                                            <td>" . $row["order_id"] . "</td>
                                            <td>" . $row["booking_code"] . "</td>
                                            <td>" . $row["column_changed"] . "</td>
                                            <td>" . $row["old_value"] . "</td>
                                            <td>" . $row["new_value"] . "</td>
                                            <td>" . $formattedDateTime . "</td>
                                            <td>"  . $row["username"] . "</td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='8'>No logs found</td></tr>";
                            }

                            $conn->close();
                            ?>
                        </tbody>
                    </table>
                </div>

                <!-- Back Button -->
                <a href="crud.php" class="btn btn-secondary">Kembali</a>

            </div>
        </div>
    </div>
</body>
</html>