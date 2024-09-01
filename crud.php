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

// Handle delete request
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    // Check if the record has been checked in
    $check_in_check_sql = "SELECT check_in_status FROM orders WHERE id=$id";
    $check_in_result = $conn->query($check_in_check_sql);
    if ($check_in_result && $check_in_result->num_rows > 0) {
        $check_in_row = $check_in_result->fetch_assoc();
        if ($check_in_row['check_in_status']) {
            die("<div class='alert alert-danger'>Cannot delete record: The passenger has already checked in.</div>");
        } else {
            $sql = "DELETE FROM orders WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<div class='alert alert-danger'>Error deleting record: " . $conn->error . "</div>";
            }
        }
    } else {
        echo "<div class='alert alert-danger'>Record not found.</div>";
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $check_in_check_sql = "SELECT check_in_status FROM orders WHERE id=$id";
    $check_in_result = $conn->query($check_in_check_sql);
    if ($check_in_result && $check_in_result->num_rows > 0) {
        $check_in_row = $check_in_result->fetch_assoc();
        // Cek nilai check_in_status
        if ($check_in_row['check_in_status'] === 'checked_in') {
            die("<div class='alert alert-danger'>Cannot delete record: The passenger has already checked in.</div>");
        } else {
            $sql = "DELETE FROM orders WHERE id=$id";
            if ($conn->query($sql) === TRUE) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<div class='alert alert-danger'>Error deleting record: " . $conn->error . "</div>";
            }
        }
    } else {
        echo "<div class='alert alert-danger'>Record not found.</div>";
    }
}

// Handle check-in request
if (isset($_GET['check_in'])) {
    $id = intval($_GET['check_in']);
    $sql = "UPDATE orders SET check_in_status = TRUE WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error updating record: " . $conn->error . "</div>";
    }
}

// Handle search
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$phone_search = isset($_GET['phone_search']) ? $conn->real_escape_string($_GET['phone_search']) : '';
$date_filter = isset($_GET['date_filter']) ? $conn->real_escape_string($_GET['date_filter']) : '';

// Normalize phone search input
$phone_search_normalized = preg_replace('/\D/', '', $phone_search);

// Build search and date filter conditions
$search_condition = '';
if (!empty($search)) {
    if (is_numeric($search)) {
        $search_condition = " WHERE id = $search";
    } else {
        $search_condition = " WHERE booking_code LIKE '%$search%'";
    }
} elseif (!empty($phone_search)) {
    // Normalize phone numbers in the database
    $search_condition = " WHERE REPLACE(REPLACE(REPLACE(passenger_phone, '-', ''), ' ', ''), '(', '') LIKE '%$phone_search_normalized%'";
}

// Add date filter to the condition if provided
if (!empty($date_filter)) {
    $date_filter = date('Y-m-d', strtotime($date_filter));
    if (empty($search_condition)) {
        $search_condition = " WHERE DATE(departure_date) = '$date_filter'";
    } else {
        $search_condition .= " AND DATE(departure_date) = '$date_filter'";
    }
}

// Handle sorting
$sort_column = isset($_GET['sort_column']) ? $_GET['sort_column'] : 'purchase_date';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'DESC';
$valid_columns = ['id', 'booking_code', 'purchase_date', 'passenger_name', 'departure', 'route', 'destination', 'departure_date', 'passenger_phone', 'selected_seats', 'total_tariff', 'check_in_status', 'status_pembayaran', 'bukti_pembayaran'];
if (!in_array($sort_column, $valid_columns)) {
    $sort_column = 'id';
}
$sort_order = ($sort_order === 'DESC') ? 'DESC' : 'ASC';

// Retrieve orders with optional search filter, date filter, and sorting
$sql = "SELECT * FROM orders $search_condition ORDER BY $sort_column $sort_order";
$result = $conn->query($sql);

if ($result === FALSE) {
    die("<div class='alert alert-danger'>Error: " . $conn->error . "</div>");
}

// Handle comment update request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['comments'])) {
    $id = intval($_POST['id']);
    $comments = $conn->real_escape_string($_POST['comments']);
    $sql = "UPDATE orders SET comments='$comments' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error updating comments: " . $conn->error . "</div>";
    }
}

// mengganti status pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['id']) && isset($_POST['status_pembayaran'])) {
        $id = $_POST['id'];
        $status_pembayaran = $_POST['status_pembayaran'];

        $stmt = $conn->prepare("UPDATE orders SET status_pembayaran = ? WHERE id = ?");
        $stmt->bind_param("si", $status_pembayaran, $id);

        ob_clean(); // Bersihkan buffer output

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }

        exit();
    }
}


// Pagination
$records_per_page = 5;
$total_records = $result->num_rows; // Total records from SQL query
$total_pages = ceil($total_records / $records_per_page);

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$page = ($page > 0) ? $page : 1; // Ensure page is at least 1
$offset = ($page - 1) * $records_per_page;

// Retrieve paginated orders with optional search filter, date filter, and sorting
$sql = "SELECT * FROM orders $search_condition ORDER BY $sort_column $sort_order LIMIT $offset, $records_per_page";
$result = $conn->query($sql);

if ($result === FALSE) {
    die("<div class='alert alert-danger'>Error: " . $conn->error . "</div>");
}


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

$sql_count_recent_edits = "SELECT COUNT(*) AS recent_edits_count FROM edit_logs WHERE edit_timestamp >= ?";
if ($stmt_count = $conn->prepare($sql_count_recent_edits)) {
    $stmt_count->bind_param("s", $two_hours_ago);
    $stmt_count->execute();
    $stmt_count->bind_result($recent_edits_count);
    $stmt_count->fetch();
    $stmt_count->close();
} else {
    die("Failed to prepare count statement: " . $conn->error);
}



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
   <style>
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
        
        
        #user-info {
            border-bottom: 1px solid #495057;
            margin-bottom: 15px;
        }

        #user-info strong {
            font-size: 1.2em;
            display: block;
            margin-bottom: 5px;
        }

        #user-info p {
            font-size: 0.9em;
            color: #ced4da;
            margin-bottom: 0;
        }

        .container {
            transition: margin-left 0.3s ease, width 0.3s ease;
            margin-left: 10px; /* Margin when sidebar is hidden */
            margin-top: 50px;
        }
        
        .container.shifted {
            margin-left: 270px; /* Margin when sidebar is shown */
            width: 70%; /* Adjust width when sidebar is shown */
        }

        .content {
            transition: margin-left 0.3s ease;
            margin-left: 250px;
        }

        .sort-symbol {
            font-size: 0.8em;
        }
        .action-button {
            margin: 0 5px;
            display: inline-block;
            gap: 50px;
        }
        .btn-group {
            display: flex;
            gap: 5px; /* Adjust spacing between buttons if needed */
        }

        .btn-group .btn {
            margin: 0; /* Ensure no extra margins around buttons */
        }
        .thead-dark {
            background-color: #007bff;
            color: white;
        }
        .table {
            width: 100%; /* Ensure the table takes the full width */
        }
                     
        tbody tr {
            transition: background-color 0.3s ease, box-shadow 0.3s ease; /* Transisi halus untuk warna dan bayangan */
        }

        tbody tr:hover {
            background-color: #c7c7c7!important; /* Warna latar belakang saat hover */
            cursor: pointer; /* Kursor pointer saat hover */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Bayangan untuk efek kedalaman */
            border-radius: 4px; /* Radius sudut untuk efek lembut pada hover */
        }

        .table th, .table td {
            vertical-align: middle;
        }
        .table-responsive {
            margin-top: 20px;
            padding: 15px; /* Add padding for better spacing */
            border: 1px solid #dee2e6; /* Light gray border */
            border-radius: 5px; /* Rounded corners */
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); /* Subtle shadow */
            background-color: #fff; /* White background */
            width: 120%; /* Full width when sidebar is hidden */
            overflow-x: auto; /* Allows horizontal scrolling if necessary */
            box-sizing: border-box; /* Include padding and border in the element's total width and height */
            transition: width 0.3s ease; /* Smooth transition for width change */
        }

        .table-responsive.shifted {
            width: 80%; /* Adjust width when sidebar is shown */
        }
        .table-responsive th {
            white-space: nowrap; /* Prevents text from wrapping */
        }

        .table-responsive table {
            width: 100%; /* Ensure the table takes up 100% of the container's width */
            border-collapse: collapse; /* Ensures borders are collapsed into a single line */
        }

        .table-responsive th, .table-responsive td {
            padding: 8px; /* Add padding for better spacing */
            text-align: left; /* Align text to the left */
            border-bottom: 1px solid #dee2e6; /* Light gray border for rows */
        }

        .disabled-button {
            pointer-events: none;
            opacity: 0.6;
        }
        .Verified {
            background-color: #28a745; /* Green */
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            text-align: center;
        }
        .paid {
            background-color:  #4cbccc; 
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            text-align: center;
        }
        .unpaid {
            background-color: #dc3545; /* Red */
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            text-align: center.
        }
        .pending {
            background-color: #ffc107; /* Yellow */
            color: black;   
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            text-align: center.
        }
        #notification-container {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 300px;
            z-index: 1000;
        }

        .notification {
            background-color: #4CAF50; /* Warna hijau untuk sukses */
            color: white;
            padding: 16px;
            margin-bottom: 10px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            opacity: 0;
            transform: translateY(-20px);
            transition: opacity 0.3s, transform 0.3s;
        }

        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }

        .notification.error {
            background-color: #f44336; /* Warna merah untuk error */
        }
        .card-container {
    display: flex;
    flex-wrap: wrap;
    gap: 10px; /* Jarak antara kartu */
}

.card {
    flex: 1 1 200px; /* Ukuran fleksibel dengan lebar minimum */
    max-width: 300px;
    margin: 10px; /* Jarak antara kartu */
}

    </style>
   
</head>
<body>
<!-- Modal view -->
<div class="modal fade" id="viewBuktiModal" tabindex="-1" aria-labelledby="viewBuktiModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-body">
        <div class="image-container" id="buktiPembayaranContent">
          <!-- Gambar akan dimuat di sini melalui AJAX -->
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Konfirmasi -->
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmModalLabel">Konfirmasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin memverifikasi pembayaran ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="confirmBtn">Konfirmasi</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal Konfirmasi Penghapusan -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Penghapusan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="deleteMessage">Apakah Anda yakin ingin menghapus record ini?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <a id="deleteConfirmBtn" class="btn btn-danger">Hapus</a>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Check-In -->
<div class="modal fade" id="checkInModal" tabindex="-1" role="dialog" aria-labelledby="checkInModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="checkInModalLabel">Konfirmasi Check-In</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="checkInMessage">Apakah Anda yakin penumpang ini telah check-in?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <a id="checkInConfirmBtn" class="btn btn-warning">Check-In</a>
            </div>
        </div>
    </div>
</div>
<!-- Modal Pesan Berhasil -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Verifikasi Sukses</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="successMessage">
                <!-- Pesan akan diisi dengan JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<div id="notification-container"></div>


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

<div class="container">
<div class="row" style="margin: 10px 0;">
    <!-- Card untuk Pesanan Baru dalam 2 Jam Terakhir -->
    <div class="col-md-3" style="margin-bottom: 10px;">
        <div class="card text-white bg-primary mb-3">
            <div class="card-header">Pesanan baru (2 jam terakhir)</div>
            <div class="card-body">
                <h5 class="card-title"><?= $count_new_orders; ?></h5>
                <p class="card-text">Pesanan baru dalam 2 jam terakhir.</p>
            </div>
        </div>
    </div>

    <!-- Card untuk Total Pesanan dalam 7 Hari Terakhir -->
    <div class="col-md-3" style="margin-bottom: 10px;">
        <div class="card text-white bg-success mb-3">
            <div class="card-header">Pesanan (dalam 7 hari terakhir)</div>
            <div class="card-body">
                <h5 class="card-title"><?= $count_orders_last_7_days; ?></h5>
                <p class="card-text">Pesanan dalam waktu 7 hari terakhir.</p>
            </div>
        </div>
    </div>

    <!-- Card untuk Penumpang yang Mengupdate Data dalam 2 Jam Terakhir -->
    <div class="col-md-3" style="margin-bottom: 10px;">
        <div class="card text-white bg-warning mb-3">
            <div class="card-header">Penumpang edit data</div>
            <div class="card-body">
                <h5 class="card-title"><?= $recent_edits_count; ?></h5>
                <p class="card-text">Jumlah pesanan yang edit data dalam 2 jam.</p>
            </div>
        </div>
    </div>

    <!-- Card untuk Pesanan dengan Pengajuan Batal = 'Ya' -->
    <div class="col-md-3" style="margin-bottom: 10px;">
        <div class="card text-white bg-danger mb-3">
            <div class="card-header">Jumlah pengajuan batal penumpang</div>
            <div class="card-body">
                <h5 class="card-title"><?= $count_batal_orders; ?></h5>
                <p class="card-text">Pesanan yang mengajukan batal.</p>
            </div>
        </div>
    </div>
</div>

    <!-- Search form container -->
    <div class="card p-4 mb-4">
        <div class="container-heading"><h2 class="mb-4">Lihat Daftar Penumpang</h2></div>
        <form class="form-inline mb-4" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
            <div class="input-group mr-sm-2 mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                </div>
                <input id="search" class="form-control" type="text" name="search" placeholder="Cari kode booking" value="<?php echo htmlspecialchars($search); ?>" oninput="toggleSearchFields()">
            </div>

            <div class="input-group mr-sm-2 mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                </div>
                <input id="phone_search" class="form-control" type="text" name="phone_search" placeholder="Cari nomor telepon penumpang" value="<?php echo htmlspecialchars($phone_search); ?>" oninput="toggleSearchFields()">
            </div>

            <button class="btn btn-primary my-2 my-sm-0" type="submit"><i class="bi bi-search"></i> Cari</button>
        </form>

        <!-- Date filter form -->
        <form class="form-inline" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
            <div class="input-group mr-sm-2 mb-2">
                <div class="input-group-prepend">
                        </div>
                <input class="form-control" type="date" name="date_filter" placeholder="Pilih tanggal" value="<?php echo htmlspecialchars($date_filter); ?>">
            </div>
            <button class="btn btn-secondary my-2 my-sm-0" type="submit"><i class="bi bi-calendar-check"></i> Cari tanggal keberangkatan</button>
        </form>
    </div>

        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <?php
                        function sortLink($column, $label) {
                            global $sort_column, $sort_order, $search, $date_filter;
                            $new_order = ($sort_column === $column && $sort_order === 'ASC') ? 'DESC' : 'ASC';
                            $symbol = ($sort_column === $column) ? ($sort_order === 'ASC' ? '⬆️' : '⬇️') : '';
                            return "<a href=\"?sort_column=$column&sort_order=$new_order&search=".htmlspecialchars($search)."&date_filter=".htmlspecialchars($date_filter)."\" style=\"color: white; text-decoration: none;\">$label <span class=\"sort-symbol\">$symbol</span></a>";
                        }
                        ?>
                        <th><?php echo sortLink('id', 'ID'); ?></th>
                        <th><?php echo sortLink('booking_code', 'Kode Booking'); ?></th>
                        <th><?php echo sortLink('purchase_date', 'Tanggal Pembelian'); ?></th>
                        <th><?php echo sortLink('passenger_name', 'Nama Penumpang'); ?></th>
                        <th><?php echo sortLink('departure', 'Asal Keberangkatan'); ?></th>
                        <th><?php echo sortLink('route', 'BUS'); ?></th>
                        <th><?php echo sortLink('destination', 'Kota Tujuan'); ?></th>
                        <th><?php echo sortLink('departure_date', 'Tanggal Keberangkatan'); ?></th>
                        <th><?php echo sortLink('comment', 'Keterangan'); ?></th>
                        <th><?php echo sortLink('passenger_phone', 'No Telepon Penumpang'); ?></th>
                        <th><?php echo sortLink('selected_seats', 'Kursi yang Dipilih'); ?></th>
                        <th><?php echo sortLink('total_tariff', 'Uang muka'); ?></th>
                        <th><?php echo sortLink('total_tariff', 'Tarif tiket'); ?></th>
                        <th><?php echo sortLink('check_in_status', 'Status Check-In'); ?></th>
                        <th><?php echo sortLink('status_pembayaran', 'Status Pembayaran'); ?></th>
                        <th><?php echo sortLink('bukti_pembayaran', 'Bukti Pembayaran'); ?></th>
                        <th>Perintah</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['route'] . '-' . $row['id']; ?></td>
                                <td><?php echo $row['booking_code']; ?></td>
                                <td><?php echo (new DateTime($row['purchase_date']))->format('d/m/Y'); ?></td>
                                <td><?php echo $row['passenger_name']; ?></td>
                                <td><?php echo $row['departure']; ?></td>
                                <td><?php echo $row['route']; ?></td>
                                <td><?php echo $row['destination']; ?></td>
                                <td><?php echo (new DateTime($row['departure_date']))->format('d/m/Y'); ?></td>
                                <td>
                                <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
                            <textarea name="comments" rows="3" cols="30"><?php echo htmlspecialchars($row['comments']); ?></textarea>
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        </form>
                </td>
                                <td>
    <?php 
      $phone_number = $row['passenger_phone'];
  
      // Remove non-numeric characters
      $clean_phone = preg_replace('/[^0-9]/', '', $phone_number);
  
      // Remove leading zero and add +62
      if (substr($clean_phone, 0, 1) === '0') {
          $formatted_phone = '+62' . substr($clean_phone, 1);
      } else {
          $formatted_phone = '+62' . $clean_phone;
      }
  
      $wa_link = 'https://wa.me/' . $formatted_phone;
      echo '<a href="' . $wa_link . '" target="_blank">' . htmlspecialchars($phone_number) . '</a>';
      ?>
</td>
                                <td><?php echo $row['selected_seats']; ?></td>
                                <td style="white-space: nowrap;">Rp <?php echo number_format($row['uang_muka'], 0, ',', '.'); ?></td>
                                <td style="white-space: nowrap;">Rp <?php echo number_format($row['total_tariff'], 0, ',', '.'); ?></td>
                                <td> <?php if ($row['check_in_status']) : ?>
        <span style="color: green; font-size: 18px;">
            <i class="fas fa-check-circle"></i> Checked In
        </span>
    <?php else : ?>
        <span style="color: red; font-size: 18px;">
            <i class="fas fa-times-circle"></i> Not Checked In
        </span>
    <?php endif; ?></td>
                                <td style="white-space: nowrap;">
    <?php 
        $status = ucfirst($row['status_pembayaran']);
        $classes = [ 
            'Verified' => 'Verified',
            'Paid' => 'paid',
            'Cancelled' => 'unpaid',
            'Pending' => 'pending',
        ];
        $messages = [
            'Verified' => 'LUNAS',
            'Paid' => 'Menunggu verifikasi',
            'Pending' => 'Belum lunas',
            'Cancelled' => 'BATAL',
        ];

        // Definisikan opsi status yang tersedia
        $options = ['Verified', 'Paid', 'Pending', 'Cancelled'];
    ?>
    <select onchange="updateStatus('<?php echo $row['id']; ?>', this.value)" class="<?php echo $classes[$status]; ?>">
        <?php foreach($options as $option): ?>
            <option value="<?php echo $option; ?>" <?php echo ($option == $status) ? 'selected' : ''; ?>>
                <?php echo $messages[$option]; ?>
            </option>
        <?php endforeach; ?>
    </select>
</td>

<td>
    <?php if($row['bukti_pembayaran']): ?>
        <button class="btn btn-info btn-sm" onclick="viewBukti(<?php echo $row['id']; ?>)">View</button>
        <button class="btn btn-success btn-sm" onclick="confirmVerification(<?php echo $row['id']; ?>, '<?php echo addslashes($row['booking_code']); ?>')">Verifikasi</button>
    <?php else: ?>
        No Bukti
    <?php endif; ?>
</td>
     <td>
    <div class="btn-group">
            <a class="btn btn-success btn-sm action-button" href="edit.php?id=<?php echo $row['id']; ?>">
                <i class="fas fa-edit"></i> Edit
            </a>
          <a class="btn btn-danger btn-sm action-button" onclick="confirmAction('Apakah anda yakin ingin menghapus data?', 'crud.php?delete=<?php echo $row['id']; ?>')">
                <i class="fas fa-trash"></i> Delete
            </a>
            <a class="btn btn-warning btn-sm action-button" onclick="confirmAction('Apakah anda yakin penumpang sudah hadir diagen?', 'crud.php?check_in=<?php echo $row['id']; ?>')">
                <i class="fas fa-check-circle"></i> Check-In
            </a>
    </div>
</td>
</td>
                            </tr>
                        <?php endwhile; ?>
                    
                        <?php else: ?>
                        <tr>
                            <td colspan="15" class="text-center">Tidak ada data</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <nav aria-label="Page navigation">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>&search=<?php echo htmlspecialchars($search); ?>&date_filter=<?php echo htmlspecialchars($date_filter); ?>&sort_column=<?php echo htmlspecialchars($sort_column); ?>&sort_order=<?php echo htmlspecialchars($sort_order); ?>">Sebelumnya</a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Sebelumnya</span></li>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>&date_filter=<?php echo htmlspecialchars($date_filter); ?>&sort_column=<?php echo htmlspecialchars($sort_column); ?>&sort_order=<?php echo htmlspecialchars($sort_order); ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>&search=<?php echo htmlspecialchars($search); ?>&date_filter=<?php echo htmlspecialchars($date_filter); ?>&sort_column=<?php echo htmlspecialchars($sort_column); ?>&sort_order=<?php echo htmlspecialchars($sort_order); ?>">Selanjutnya</a>
                    </li>
                <?php else: ?>
                    <li class="page-item disabled"><span class="page-link">Selanjutnya</span></li>
                <?php endif; ?>
            </ul>
        </nav>

        <div class="mt-4">
            <a class="btn btn-secondary " href="index.php">Kembali</a>
            <?php
// Cek apakah user memiliki hak akses yang diperlukan
if ($_SESSION['accessLevel'] == 2 || $_SESSION['accessLevel'] == 1) { // Misalnya aksesLevel 1 (owner) atau 2 (berangkat) yang diizinkan
    echo '<a class="btn btn-primary" href="SPJ.php">Buat Surat Perintah Jalan</a>';
}
?>

            <!-- <a class="btn btn-info" href="crud.php">Lihat Daftar Penumpang</a> -->
        </div>
    </div>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
         $(document).ready(function() {
            $('#sidebar-toggler').click(function() {
                $('#sidebar').toggleClass('hidden');
                $('.container').toggleClass('shifted'); 
            });

            // Run toggleSearchFields on page load to set the initial state of the fields
            toggleSearchFields();

            // Attach the toggleSearchFields function to the input event of the search fields
            $('#search, #phone_search').on('input', function() {
                toggleSearchFields();
            });
        });

        function confirmAction(message, url) {
            if (confirm(message)) {
                window.location.href = url;
            }
        }

        function toggleSearchFields() {
            var searchField = document.getElementById('search');
            var phoneSearchField = document.getElementById('phone_search');

            if (searchField.value.trim() !== '') {
                phoneSearchField.value = ''; // Clear phone search field
                phoneSearchField.disabled = true; // Disable phone search field
            } else if (phoneSearchField.value.trim() !== '') {
                searchField.value = ''; // Clear booking code search field
                searchField.disabled = true; // Disable booking code search field
            } else {
                phoneSearchField.disabled = false; // Enable phone search field
                searchField.disabled = false; // Enable booking code search field
            }
        }

      

// Fungsi untuk menampilkan modal konfirmasi verifikasi
function confirmVerification(id, bookingCode) {
    $('#confirmModal').modal('show');
    
    $('#confirmBtn').off('click').on('click', function() {
        $('#confirmModal').modal('hide');
        
        $('#successMessage').text('Kode booking ' + bookingCode + ' telah di konfirmasi');
        $('#successModal').modal('show');
        
        $('#successModal').on('hidden.bs.modal', function () {
            window.location.href = 'verifikasi_pembayaran.php?id=' + id;
        });
    });
}

function showNotification(message, type = 'success') {
    var container = document.getElementById('notification-container');
    
    // Buat elemen notifikasi
    var notification = document.createElement('div');
    notification.className = 'notification ' + (type === 'error' ? 'error' : '');
    notification.innerText = message;

    // Tambahkan notifikasi ke dalam container
    container.appendChild(notification);

    // Tampilkan notifikasi
    setTimeout(function() {
        notification.classList.add('show');
    }, 10);

    // Hilangkan notifikasi setelah 3 detik
    setTimeout(function() {
        notification.classList.remove('show');
        // Hapus notifikasi dari DOM setelah animasi
        setTimeout(function() {
            container.removeChild(notification);
        }, 300);
    }, 3000);
}

// fungsi untuk view bukti pembayaran
function viewBukti(id) {
    // Show the modal
    var viewBuktiModal = new bootstrap.Modal(document.getElementById('viewBuktiModal'));
    viewBuktiModal.show();

    // Load content via AJAX
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'view_bukti.php?id=' + id, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById('buktiPembayaranContent').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}
 // Event listener for when the modal is completely hidden
 $('#viewBuktiModal').on('hidden.bs.modal', function () {
        location.reload(); // Refresh the page
    });

// fungsi untuk update status pembayaran
function updateStatus(id, newStatus) {
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "crud.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    xhr.onreadystatechange = function() {
    if (xhr.readyState == 4 && xhr.status == 200) {
        var response = xhr.responseText.trim();
        if (response === "success") {
            showNotification("Status pembayaran berhasil diperbarui!");
            setTimeout(function() {
                location.reload(); // Refresh halaman setelah delay
            }, 3000); // Delay 3 detik
        } else {
            showNotification("Gagal memperbarui status pembayaran.", "error");
        }
    }
};


    xhr.send("id=" + id + "&status_pembayaran=" + encodeURIComponent(newStatus));
}

// Fungsi untuk menampilkan modal konfirmasi aksi (delete atau check-in)
function confirmAction(message, url) {
    $('#deleteMessage').text(message);
    $('#checkInMessage').text(message);
    
    if (url.includes('delete')) {
        $('#deleteConfirmBtn').attr('href', url);
        $('#deleteModal').modal('show');
    } else if (url.includes('check_in')) {
        $('#checkInConfirmBtn').attr('href', url);
        $('#checkInModal').modal('show');
    }
}
// Fungsi untuk menampilkan modal sukses dan menghapusnya setelah 3 detik
function showSuccessModal(message) {
    // Menampilkan modal dengan pesan sukses
    $('#successModal .modal-body').text(message);
    $('#successModal').modal('show');

    // Mengatur timeout untuk menutup dan menghapus modal setelah 3 detik
    setTimeout(function() {
        $('#successModal').modal('hide'); // Menutup modal
        $('#successModal').on('hidden.bs.modal', function() {
            $(this).remove(); // Menghapus modal dari DOM
            location.reload(); // Reload halaman setelah modal dihapus
        });
    }, 3000);
}


// Menangani klik tombol Hapus
$('#deleteConfirmBtn').click(function(event) {
    event.preventDefault();
    $.ajax({
        url: $(this).attr('href'),
        method: 'POST',
        success: function(response) {
            showSuccessModal('Data penumpang telah dihapus.');
        },
        error: function() {
            alert('Ada kesalahan server saat menghapus data.');
        }
    });
});

// Menangani klik tombol Check-In
$('#checkInConfirmBtn').click(function(event) {
    event.preventDefault();
    $.ajax({
        url: $(this).attr('href'),
        method: 'POST',
        success: function(response) {
            showSuccessModal('Penumpang telah di-check-in dengan sukses.');
        },
        error: function() {
            alert('Terjadi kesalahan saat melakukan check-in.');
        }
    });
});

// Sidebar collapse functionality
document.getElementById('sidebar-toggler').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('collapsed');
});

    </script>
</body>
</html>

<?php $conn->close(); ?>
