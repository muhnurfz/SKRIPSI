<?php
include('conn.php');
session_start();

// Set session timeout period (in seconds)
$timeout_duration = 1800; // 30 minutes

// Check if the session is active and if the timeout has expired
if (isset($_SESSION['LAST_ACTIVITY'])) {
    if ((time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
        session_unset();     // Unset $_SESSION variables
        session_destroy();   // Destroy session data in storage
        header("Location: login.php"); // Redirect to login page
        exit();
    }
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
?>
<html>
<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <style>
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
            margin-left: 250px; /* Adjust this value when sidebar is shown */
            transition: margin-left 0.3s ease;
            padding: 20px;
        }

        .container.shifted {
            margin-left: 10px; /* Adjust this value when sidebar is hidden */
        }

        .user-info {
            padding: 10px;
            border-bottom: 1px solid #495057;
            background-color: #343a40;
            color: white;
            text-align: center;
        }

        .user-info h5 {
            margin: 0;
        }

    </style>
</head>
<body>
    
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
                    <a class="nav-link" href="edit_penumpang.php"><i class="bi bi-pencil-square"></i> Edit Penumpang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cari_tiket.php"><i class="bi bi-printer"></i> Cetak Tiket</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="payment_form.php"><i class="bi bi-wallet2"></i> Proses Pembayaran</a>
                </li>
                <!-- Alokasi Kepegawaian -->
                <li class="nav-item">
                    <div class="nav-section-header bg-dark text-white p-2">ALOKASI PENUMPANG</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="crud.php"><i class="bi bi-gear-fill"></i> Lihat daftar penumpang</a>
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


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> <!-- Pilih salah satu jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> <!-- Pilih salah satu Bootstrap JS -->
 <script>
        // Sidebar toggle functionality
        document.getElementById('sidebar-toggler').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('hidden');
        });
    </script>
</body>

</html>