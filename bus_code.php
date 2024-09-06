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

// Initialize notification variables
$notification = '';
$notification_type = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['search']) || isset($_POST['update_all'])) {
        $departure_date = $_POST['departure_date'];
        $route = $_POST['route'];
        
        // Fetch matching records
        $stmt = $conn->prepare("SELECT * FROM orders WHERE departure_date = ? AND route = ?");
        $stmt->bind_param("ss", $departure_date, $route);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if (isset($_POST['update_all']) && $result->num_rows > 0) {
            $bus_code = $_POST['bus_code'];
            // Update bus code for all matching records
            $updateStmt = $conn->prepare("UPDATE orders SET bus_code = ? WHERE departure_date = ? AND route = ?");
            $updateStmt->bind_param("sss", $bus_code, $departure_date, $route);
            
            if ($updateStmt->execute()) {
                $notification = 'No Body telah diupdate.';
                $notification_type = 'success';
            } else {
                $notification = 'Gagal update No body: ' . $updateStmt->error;
                $notification_type = 'error';
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alokasi Nomor Body BUS</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <style>
        /* Sidebar and page styling */
        body {
            overflow-x: hidden;
        }
               
        #sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            width: 250px;
            background: #f8f9fa;
            transition: transform 0.3s ease;
            padding-top: 10px;
            transform: translateX(-250px); /* Sidebar initially hidden */
        }

        #sidebar.hidden {
            transform: translateX(0); /* Show sidebar when the hidden class is removed */
        }

        #sidebar-toggler {
            position: absolute;
            top: 10px;
            right: -45px;
            z-index: 101;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            padding: 5px 10px;
        }

        .sidebar-wrapper {
            height: calc(100vh - 50px); /* Adjust height to accommodate user info */
            overflow-y: auto; /* Enables vertical scrolling */
        }

        .sidebar-wrapper::-webkit-scrollbar {
            width: 8px; /* Custom scrollbar width */
        }

        .sidebar-wrapper::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.5); /* Custom scrollbar thumb color */
            border-radius: 4px;
        }

        .sidebar-wrapper::-webkit-scrollbar-track {
            background-color: #f1f1f1; /* Custom scrollbar track color */
        }

        .nav-section-header {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 10px 0px 0px 0px; /* Removed margin to fit the layout */
            padding: 10px;
            border-bottom: 1px solid #444;
        }

        .nav-item .nav-link {
            padding: 10px 20px;
            color: #333;
            transition: color 0.3s ease-in-out, background-color 0.3s ease-in-out;
        }

        .nav-link:hover {
            background-color: #d1d1d1;
        }

        .nav-link.active {
            background-color: #337ab7;
            color: #fff;
        }
        
        .container {
            transition: margin-left 0.3s ease;
            margin-left: 10px;
            margin-top: 50px;
            padding : 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .container.shifted {
            margin-left: 250px;
        }
        .table-responsive {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            background-color: #fff;
            overflow-x: auto;
            box-sizing: border-box;
        }
           /* Styles for table row hover effect */
           .table-responsive table tbody tr:hover {
            background-color: #c7c7c7 !important; /* Color when hovered */
            cursor: pointer; /* Pointer cursor on hover */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Shadow effect */
            border-radius: 4px; /* Rounded corners on hover */
        }

        /* Optional: Additional styling for table */
        .table {
            border-collapse: collapse;
            width: 100%;
        }
        .table th, .table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table thead th {
            background-color: #f8f9fa;
        }

        /* Notification Container */
#notification-container {
    position: fixed;
    top: 20px;
    right: 20px;
    width: 300px;
    z-index: 1000;
}

.notification {
    background-color: #4CAF50; /* Default to success color */
    color: white;
    padding: 16px;
    margin-bottom: 10px;
    border-radius: 5px;
    box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
    opacity: 0;
    transform: translateY(-20px);
    transition: opacity 0.3s, transform 0.3s;
}

.notification.success {
    background-color: #4CAF50; /* Green for success */
}

.notification.error {
    background-color: #f44336; /* Red for error */
}

.notification.show {
    opacity: 1;
    transform: translateY(0);
}

    </style>
</head>
<body>
<div id="notification-container">
    <?php if (!empty($notification)): ?>
    <div class="notification <?= htmlspecialchars($notification_type) ?>">
        <?= htmlspecialchars($notification) ?>
    </div>
    <?php endif; ?>
</div>


<nav id="sidebar" class="bg-light">
    <button id="sidebar-toggler" class="btn btn-primary">☰</button>
    <div class="sidebar-sticky">
        <div class="user-info text-center p-3 bg-dark text-white">
            <h5>Hallo, <?php echo htmlspecialchars($username); ?>!</h5>
        </div>
        <div class="sidebar-wrapper">
            <ul class="nav flex-column">
                <!-- Alokasi Data Penumpang -->
                <li class="nav-item">
                    <div class="nav-section-header bg-dark text-white p-2">PEMESANAN TIKET</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="bi bi-house-door"></i> Homepage</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pesan_tiket.php"><i class="bi bi-ticket"></i> Pesan Tiket</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cari_tiket.php"><i class="bi bi-printer"></i> Cetak Tiket</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cash_payment.php"><i class="bi bi-wallet2"></i> Proses Pembayaran</a>
                </li>
                <!-- Alokasi Kepegawaian -->
                <li class="nav-item">
                    <div class="nav-section-header bg-dark text-white p-2">ALOKASI KEAGENAN</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="crud.php"><i class="bi bi-gear-fill"></i> Lihat daftar penumpang</a>
                </li>
                <li class="nav-item">
                <a class="nav-link" href="crud.php"><i class="fas fa-clock"></i> Riwayat data penumpang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="bus_code.php"><i class="bi bi-bus-front"></i> Alokasi No Body</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="refund_payment.php"><i class="bi bi-x-circle"></i> Alokasi penumpang batal</a>
                </li>
                <li class="nav-item">
                    <?php
                    // Cek apakah user memiliki hak akses yang diperlukan
                    if ($_SESSION['accessLevel'] == 1) {
                        echo ' <a class="nav-link" href="manage_akun.php"><i class="bi bi-person"></i>Kelola Pegawai</a>';
                    }
                    ?>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="bi bi-door-closed"></i> Log out</a>
                </li>
            </ul>
        </div>
    </div>
</nav>



<div class="container mt-5">
    <h2 class="text-center mb-4">Alokasi Nomor Body BUS</h2>

    <!-- Search Form -->
    <form method="post" class="mb-4">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="departure_date">Tanggal keberangkatan :</label>
                    <input type="date" class="form-control" id="departure_date" name="departure_date" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group mb-3">
                    <label for="route">BUS: </label>
                    <select class="form-control" id="route" name="route" required>
                        <option value="0">Pilih rute BUS</option>
                        <option value="ponorogo">Ponorogo</option>
                        <option value="solo">Solo</option>
                        <option value="bojonegoro">Bojonegoro</option>
                        <option value="gemolong">Gemolong</option>
                    </select>
                </div>
            </div>
        </div>
        <button type="submit" name="search" class="btn btn-primary">Cari</button>
    </form>

    <!-- Results Section -->
    <?php if (isset($result) && $result->num_rows > 0): ?>
    <h4 class="mb-3">Data penumpang</h4>
    <form method="post" class="mb-4">
        <div class="form-group">
            <label for="bus_code">Masukan No Body BUS</label>
            <input type="text" class="form-control" id="bus_code" name="bus_code" placeholder="No Body" required>
        </div>
        <input type="hidden" name="departure_date" value="<?= htmlspecialchars($departure_date) ?>">
        <input type="hidden" name="route" value="<?= htmlspecialchars($route) ?>">
        <button type="submit" name="update_all" class="btn btn-success mt-3">Update semua</button>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode booking</th>
                    <th>Nama penumpang</th>
                    <th>Tanggal keberangkatan</th>
                    <th>Kursi</th>
                    <th>Asal keberangkatan</th>
                    <th>Kota tujuan</th>
                    <th>No Body BUS</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['booking_code']) ?></td>
                    <td><?= htmlspecialchars($row['passenger_name']) ?></td>
                    <td><?php echo (new DateTime($row['departure_date']))->format('d/m/Y'); ?></td>
                    <td><?= htmlspecialchars($row['selected_seats']) ?></td>
                    <td><?= htmlspecialchars($row['departure']) ?></td>
                    <td><?= htmlspecialchars($row['destination']) ?></td>
                    <td><?= htmlspecialchars($row['bus_code']) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php elseif (isset($result)): ?>
        <div class="alert alert-info">Tidak ada data penumpang.</div>
    <?php endif; ?>
</div>

<script>
     document.addEventListener('DOMContentLoaded', function() {
            // Notification functionality
            const notification = document.querySelector('.notification');
            if (notification) {
                notification.classList.add('show');
                
                setTimeout(function() {
                    notification.classList.remove('show');
                    
                    setTimeout(function() {
                        notification.remove();
                    }, 300); // Match the transition duration
                }, 3000); // Display duration
            }

            // Date input functionality
            const today = new Date();
            const minDate = today.toISOString().split('T')[0]; // Format as YYYY-MM-DD

            const maxDate = new Date();
            maxDate.setDate(today.getDate() + 10);
            const maxDateFormatted = maxDate.toISOString().split('T')[0]; // Format as YYYY-MM-DD

            const departureDateInput = document.getElementById('departure_date');
            if (departureDateInput) {
                departureDateInput.setAttribute('min', minDate);
                departureDateInput.setAttribute('max', maxDateFormatted);
            }

            // Sidebar toggle functionality
            const sidebarToggler = document.getElementById('sidebar-toggler');
            const sidebar = document.getElementById('sidebar');
            const container = document.querySelector('.container');
            
            if (sidebarToggler && sidebar && container) {
                sidebarToggler.addEventListener('click', function() {
                    sidebar.classList.toggle('hidden');
                    container.classList.toggle('shifted');
                });
            }
        });
</script>

</body>
</html>
