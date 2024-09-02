<?php
include('conn.php');
session_start();

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

// Fetch transaction history from `orders` based on the logged-in user's email
$sql_orders = "SELECT * FROM orders WHERE email = ?";
$stmt_orders = $conn->prepare($sql_orders);
$stmt_orders->bind_param("s", $logged_in_email);
$stmt_orders->execute();
$result_orders = $stmt_orders->get_result();

// Fetch the data from the orders table
if ($result_orders->num_rows > 0) {
    while ($order = $result_orders->fetch_assoc()) {
        // Assuming you want to handle multiple orders, iterate through them
        $status_pembayaran = $order['status_pembayaran']; // Get status_pembayaran from the orders table
        
        // Status messages
        $status_messages = [
            'verified' => 'LUNAS', 
            'paid' => 'Menunggu verfikasi',
            'pending' => 'Menunggu Pembayaran',
            'cancelled' => 'Batal',
            'unknown' => 'Unknown Status'
        ];

        // Get the status message
        $status_message = isset($status_messages[$status_pembayaran]) ? $status_messages[$status_pembayaran] : $status_messages['unknown'];

        // Determine the CSS class for the status
        $status_class = [
            'verified' => 'verified', 
            'paid' => 'pending',
            'cancelled' => 'unpaid',
            'unknown' => 'unpaid'
        ][$status_pembayaran] ?? 'unpaid';

        // Display or use the status message and class
        echo "<td><span class='status $status_class'>" . ucfirst($status_message) . "</span></td>";
    }
} else {
    echo "No orders found for this user.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penumpang</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .container {
            margin-top: 50px;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
            margin-bottom: 20px;
        }
        .card-title {
            margin-bottom: 15px;
        }
        .btn-primary, .btn-danger {
            border-radius: 30px;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .table-wrapper {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
        }
        .status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    text-align: center;
    color: #fff;
}

.verified {
    background-color: #28a745; /* Green */
}

.paid {
    background-color: #4cbccc; /* Light Blue */
}

.pending {
    background-color: #ffc107; /* Yellow */
    color: black;
}

.unpaid {
    background-color: #dc3545; /* Red */
}

    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Dashboard Penumpang</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="#">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout_penumpang.php">Logout</a>
                </li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Card for Booking Tickets -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Pesan Tiket</h5>
                <a href="pesan_tiket_pnp.php" class="btn btn-primary">Pesan Tiket</a>
            </div>
        </div>

        <!-- Card for Transaction History -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Riwayat Transaksi</h5>
                <div class="table-wrapper">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kode Booking</th>
                                <th>Nama Penumpang</th>
                                <th>Tujuan</th>
                                <th>Tanggal Berangkat</th>
                                <th>Status Pembayaran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_orders->num_rows > 0): ?>
                                <?php while($row = $result_orders->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $row['id']; ?></td>
                                        <td><?php echo $row['booking_code']; ?></td>
                                        <td><?php echo $row['passenger_name']; ?></td>
                                        <td><?php echo $row['destination']; ?></td>
                                        <td><?php echo $row['departure_date']; ?></td>
                                        <td><div class="status <?php echo htmlspecialchars($status_class); ?>"><?php echo htmlspecialchars($status_message); ?></div></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada riwayat transaksi</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>
