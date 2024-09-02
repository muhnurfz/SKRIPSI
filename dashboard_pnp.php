<?php
session_start();
include('conn.php');

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
            display: flex;
            flex-wrap: nowrap;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif; /* Ensure consistent font */
        }

        .sidebar {
            width: 250px;
            background-color: #f8f9fa;
            padding-top: 20px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            transition: transform 0.3s ease, width 0.3s ease;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            transform: translateX(0); /* Show by default */
            border-right: 1px solid #dee2e6; /* Subtle border for separation */
        }

        .sidebar.collapsed {
            transform: translateX(-250px); /* Hide sidebar */
        }

        .sidebar a {
            padding: 15px 20px; /* Increased padding for better touch targets */
            display: block;
            font-size: 1.2rem;
            color: #343a40;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .sidebar a:hover {
            background-color: #e2e6ea;
            color: #007bff;
        }

        .sidebar .profile {
            text-align: center;
            margin-bottom: 20px;
        }

        .sidebar .profile h5 {
            margin: 0;
            color: #495057;
            font-size: 1.2rem;
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
            transition: margin-left 0.3s ease;
        }

        .content.sidebar-collapsed {
            margin-left: 0;
        }

        .navbar-toggler {
            margin-left: auto;
        }

        .container {
            margin-top: 50px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
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
            overflow-x: auto;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }

        .table {
            margin-bottom: 0;
        }

        .table th, .table td {
            vertical-align: middle;
            white-space: nowrap;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }

.status {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 12px;
    text-align: center;
    color: #fff;
    font-weight: bold;
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

@media (max-width: 767.98px) {
    .table-responsive-sm {
        display: block;
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }

    .table-wrapper {
        padding: 10px;
    }

    .status {
        font-size: 10px;
        padding: 3px 6px;
    }
}

    </style>
</head>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penumpang</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .sidebar a {
            padding: 10px 15px;
            display: block;
            font-size: 1.1rem;
            color: #343a40;
            text-decoration: none;
        }
        .sidebar a:hover {
            background-color: #e9ecef;
            color: #007bff;
        }
        .content {
            flex-grow: 1;
            padding: 20px;
        }
        .profile {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #f1f1f1;
            border-radius: 5px;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="btn btn-primary" id="toggleSidebar">☰</button>
        <div class="profile">
            <h5>Hallo, <?php echo htmlspecialchars($passenger['passenger_name']); ?>!</h5>
        </div>
        <a href="dashboard_pnp.php">Home</a>
        <a href="pesan_tiket_pnp.php">Pesan Tiket</a>
        <a href="riwayat_transaksi_pnp.php">Riwayat Transaksi</a>
        <a href="logout_penumpang.php">Logout</a>
    </div>

    <!-- Content Area -->
    <div class="content" id="content">
        <div class="container">
            <!-- Card for Booking Tickets -->
            <div class="card mb-4">
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
                        <table class="table table-bordered table-striped table-responsive-sm">
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
                                        <?php
                                            // Status messages and CSS classes
                                            $status_messages = [
                                                'verified' => 'LUNAS', 
                                                'paid' => 'Menunggu Verifikasi',
                                                'pending' => 'Menunggu Pembayaran',
                                                'cancelled' => 'Batal',
                                                'unknown' => 'Unknown Status'
                                            ];
                                            $status_class = [
                                                'verified' => 'verified', 
                                                'paid' => 'paid',
                                                'pending' => 'pending',
                                                'cancelled' => 'unpaid',
                                                'unknown' => 'unpaid'
                                            ][$row['status_pembayaran']] ?? 'unpaid';
                                            $status_message = $status_messages[$row['status_pembayaran']] ?? 'Unknown Status';
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['booking_code']); ?></td>
                                            <td><?php echo htmlspecialchars($row['passenger_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['destination']); ?></td>
                                            <td><?php echo htmlspecialchars($row['departure_date']); ?></td>
                                            <td><span class="status <?php echo $status_class; ?>"><?php echo ucfirst($status_message); ?></span></td>
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

        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            const content = document.getElementById('content');
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('sidebar-collapsed');
        });
    </script>
</body>
</html>

<?php
$conn->close();
?>
