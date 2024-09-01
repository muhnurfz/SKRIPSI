<?php
include('conn.php');
session_start();

// Set session timeout period
$timeout_duration = 1800; // 0.5 hour

// Check if the session is active
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();     // Unset $_SESSION variable for the run-time
    session_destroy();   // Destroy session data in storage
    header("Location: login.php"); // Redirect to login page
    exit();
}

$_SESSION['LAST_ACTIVITY'] = time(); // Update last activity time

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Ensure accessLevel is set in session
if (!isset($_SESSION['accessLevel'])) {
    // Redirect if accessLevel is not set (e.g., login was not handled properly)
    header("Location: login.php");
    exit();
}

// Check if the user has accessLevel 3 (pelayanan)
if ($_SESSION['accessLevel'] == 3) {
    // Redirect to denied page
    header("Location: denied.php");
    exit();
}
// Get the username from the session
$username = $_SESSION['username'];

// Mengambil data dari database dengan filter
$filter_date = isset($_GET['departure_date']) ? $_GET['departure_date'] : '';
$filter_route = isset($_GET['route']) ? $_GET['route'] : '';

// Base SQL query (tanpa filter status pembayaran dan check-in)
$sql = "SELECT * FROM orders WHERE 1=1";
if ($filter_date) {
    $sql .= " AND departure_date = '$filter_date'";
}
if ($filter_route) {
    $sql .= " AND route = '$filter_route'";
}


// Tambahkan filter untuk status pembayaran
$sql .= " AND status_pembayaran IN ('paid', 'verified')";

$result = $conn->query($sql);

// Initialize variables
$no_body = '';
$nama_crew1 = '';
$nama_crew2 = '';
$no_telp_crew = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_body = isset($_POST['no_body']) ? htmlspecialchars($_POST['no_body']) : '';
    $nama_crew1 = isset($_POST['nama_crew1']) ? htmlspecialchars($_POST['nama_crew1']) : '';
    $nama_crew2 = isset($_POST['nama_crew2']) ? htmlspecialchars($_POST['nama_crew2']) : '';
    $no_telp_crew = isset($_POST['no_telp_crew']) ? htmlspecialchars($_POST['no_telp_crew']) : '';
}

// Mengambil tarif dari tabel tarif_tiket
$tarif_sql = "SELECT route, tarif FROM tarif_tiket";
$tarif_result = $conn->query($tarif_sql);
$tarif_data = [
    'Ponorogo' => 250000,
    'Solo' => 250000,
    'Bojonegoro' => 210000,
    'Gemolong' => 210000
];

while ($tarif_row = $tarif_result->fetch_assoc()) {
    $tarif_data[$tarif_row['route']] = $tarif_row['tarif'];
}

// Menghitung jumlah penumpang dan setoran per asal keberangkatan
$passenger_count = [
    'Balaraja' => 0,
    'BSD Serpong' => 0,
    'Samsat BSD' => 0,
    'Cilenggang' => 0,
];
$setoran = [
    'Balaraja' => 0,
    'BSD Serpong' => 0,
    'Samsat BSD' => 0,
    'Cilenggang' => 0,
];
$total_passenger_count = 0;
$total_setoran = 0;

$passenger_rows = "";
$no = 1;
$biaya_tetap = 30000;
if ($total_passenger_count > 0) {
    $setoran_satuan = $total_setoran / $total_passenger_count;
} else {
    $setoran_satuan = 0;
}
// Format setoran satuan (opsional)
$setoran_satuan_formatted = number_format($setoran_satuan, 0, ',', '.');


// Retrieve the tariff value from the tarif_tiket table
$route = $filter_route; // Replace with the actual route value
$tarif_sql = "SELECT tarif FROM tarif_tiket WHERE route = '$route'";
$tarif_result = $conn->query($tarif_sql);

if ($tarif_result) {
    $tarif_row = $tarif_result->fetch_assoc();
    if ($tarif_row) {
        $tarif = $tarif_row['tarif'];
    } else {
        $tarif = 0; // Default value or handle the case when tariff is not found
    }
} else {
    // Handle query error
    echo "Error fetching tariff: " . $conn->error;
    $tarif = 0; // Default value or handle the case appropriately
}

$setoran_amount = $tarif - $biaya_tetap; // Menghitung setoran dengan mengurangi komisi tetap

while ($row = $result->fetch_assoc()) {
    $departure = $row['departure'];
    $route = $row['destination'];
    
    // Pisahkan selected_seats berdasarkan koma dan hitung jumlah kursi
    $selected_seats_array = explode(',', $row['selected_seats']);
    $number_of_seats = count($selected_seats_array);

    if (isset($passenger_count[$departure])) {
        $passenger_count[$departure] += $number_of_seats;
        $setoran[$departure] += $setoran_amount * $number_of_seats;
    }

    $total_passenger_count += $number_of_seats;
    $total_setoran += $setoran_amount * $number_of_seats;

    $passenger_rows .= "<tr>
        <td>{$no}</td>
        <td>" . $row['booking_code'] . "</td>
        <td>{$row['passenger_name']}</td>
        <td>{$row['passenger_phone']}</td>
        <td>{$row['selected_seats']}</td>
        <td>{$row['departure']}</td>
        <td>{$row['destination']}</td>
         <td>{$row['bus_code']}</td>
        <td>" . (($row['check_in_status'] == 1) ? 'Sudah' : 'Belum') . "</td>
    </tr>";
    $no++;
}
// Set the timezone to Asia/Jakarta
date_default_timezone_set('Asia/Jakarta');
// Function to format date and time in Indonesian style
function formatIndonesianDateTime($dateTime) {
    // Array of Indonesian days of the week
    $daysOfWeek = [
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu',
        'Sunday'    => 'Minggu'
    ];
    
    // Convert date and time to required format
    $dayOfWeek = $daysOfWeek[date('l', strtotime($dateTime))];
    $formattedDate = date('d/m/Y', strtotime($dateTime));
    $formattedTime = date('H:i', strtotime($dateTime));
    
    return "$dayOfWeek, $formattedDate $formattedTime";
}

// Get current date and time
$currentDateTime = formatIndonesianDateTime(date('Y-m-d H:i:s'));

// Fungsi untuk memformat angka dengan separator Rupiah
function formatRupiah($number) {
    return "Rp " . number_format($number, 0, ',', '.');
}

// Menutup koneksi
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Perintah Jalan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color : black;
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
            margin-left: 20px;
            margin-top: 50px;
            padding : 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .container.shifted {
            margin-left: 270px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            color: #333;
        }

        .inner-container {
            border: 2px solid #007bff;
            border-radius: 8px;
            padding: 20px;
            background-color: #ffffff;
        }

        .inner-container h3 {
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
            min-width: 250px;
        }

        .form-group label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        .btn-primary {
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            text-align: center;
            display: inline-block;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .output {
            background-color: #f9f9f9;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .output p {
            margin-bottom: 10px;
        }

        .output strong {
            font-weight: bold;
            color: #333;
        }

        .header-section {
            margin-bottom: 20px;
        }

        .info-card {
            display: flex;
            flex-direction: column;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .info-header {
            margin-bottom: 15px;
        }

        .info-body {
            display: flex;
            justify-content: space-between;
        }

        .info-left,
        .info-right {
            flex: 1;
        }

        .info-left {
            margin-right: 20px;
        }

        .info-group {
            margin-bottom: 10px;
        }

        .info-group label {
            display: block;
            font-weight: bold;
            color: #333;
        }

        .info-group label strong {
            color: #007bff;
        }

        .summary-section {
            display: flex;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 5px;
            padding: 5px;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .summary-item {
            flex: 1;
            margin: 0;
        }

        .summary-item label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #333;
            font-size: 16px;
        }

        .summary-item input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            background-color: #f9f9f9;
            color: #333;
            box-sizing: border-box;
            text-align: right;
        }

        .tables-section {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .table-container, .table-container2 {
            margin-top: 20px;
            max-width: 100%;
        }

        .table-container2 {
            max-width: 100%;
            margin-left: 0; /* Center on small screens */
        }

        .table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        .table thead {
            background-color: #007bff;
            color: white;
        }

        .table th,
        .table td {
            border: 1px solid #dee2e6;
            padding: 12px;
            text-align: center;
        }

        .table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-center {
            text-align: center;
        }

      /* Style for the admin-info element */
.admin-info {
    padding: 2px;
    margin: 10px 0; /* Optional: Add margin to create space around */
    border: 1px solid #ddd;
    border-radius: 5px;
    background-color: #f9f9f9;
    font-size: 12px;
    color: #333;
    text-align: center; 
    margin-left: 70%; 
}

        .signature-box {
            padding: 50px 10px 10px 10px;
            margin: 20px 0;
            height: 50px;
            text-align: center;
            color: #666;
            align-self: flex-end; /* Aligns to the end (right) within a flex container */
        }

        .timestamp {
            font-size: 12px;
            color: #666;
        }

        
        .btn-print {
    display: inline-block;
    font-size: 16px;
    font-weight: bold;
    padding: 10px 20px;
    color: #fff; /* White text color */
    background-color: #007bff; /* Blue background color */
    border: none;
    border-radius: 5px;
    cursor: pointer;
    text-decoration: none; /* Remove underline */
    transition: background-color 0.3s ease;
    text-align: center; /* Center the text */
    line-height: 1.5; /* Improve text line height */
}

.btn-print:hover {
    background-color: #0056b3; /* Darker blue on hover */
}

/* Optional: Button container to manage button layout */
.button-container {
    display: flex;
    justify-content: flex-start;
    gap: 10px;
    margin-top: 20px;
}


/* Optional: Back link styling */
.back-link {
    background-color: #8a8a8a;
    color: #fff;
    border: none;
    border-radius: 5px;
    padding: 10px 20px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    transition: background-color 0.3s ease;
    text-decoration: none; /* Remove underline */
}

.back-link:hover {
    background-color: #525252;
    color: white;
    text-decoration: none; /* Remove underline */
}


        @media (max-width: 768px) {
            .form-group {
                width: 100%;
            }

            .summary-section {
                flex-direction: column;
            }

            .info-body {
                flex-direction: column;
            }

            .info-left,
            .info-right {
                margin-right: 0;
                margin-bottom: 20px;
            }

            .info-right {
                margin-bottom: 0;
            }

            .table-container,
            .table-container2 {
                width: 100%;
                margin-bottom: 20px;
            }

            .admin-info {
                margin-left: 0;
                margin-top: 10px;
                padding: 5px;
            }

            .signature-box {
                padding: 5px;
                height: auto;
            }

            .button-container {
                flex-direction: column;
            }
        }

        @media print {
    body {
        background-color: white; /* Ensures the background is white */
        color: black; /* Ensures the text is visible */
    }

    #sidebar, 
    #sidebar-toggler, 
    .header,
    .button-container, 
    {
        display: none; /* Hides elements that are not needed in print */
    }

    .table-container, 
    .table-container2 {
        width: 100%; /* Ensures the table spans the full width */
        margin: 0; /* Removes margin */
    }

    .table {
        border-collapse: collapse; /* Ensures the table borders are visible */
        width: 100%;
    }

    .table thead {
        background-color: #333; /* Ensures header background is printed */
        color: white; /* Ensures header text is visible */
    }

    .table th, 
    .table td {
        border: 1px solid black; /* Ensures table borders are visible */
        color: black; /* Ensures table text is visible */
    }

    .form-group label, 
    .form-control, 
    .summary-item label, 
    .summary-item input {
        color: black; /* Ensures all form text is visible */
    }
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
    <h2 class="mb-4">Surat Perintah Jalan</h2>
  <!-- Combined Form Filter -->
        <form method="GET" action="" class="form-row">
            <div class="form-group">
                <label for="departure_date">Tanggal Keberangkatan</label>
                <input type="date" class="form-control" id="departure_date" name="departure_date" 
                value="<?php echo htmlspecialchars($filter_date); ?>" 
                min="<?php echo date('Y-m-d'); ?>" 
                max="<?php echo date('Y-m-d', strtotime('+10 days')); ?>">
            </div>
            <div class="form-group">
                <label for="route">Route</label>
                <select class="form-control" id="route" name="route">
                    <option value="">Pilih Route</option>
                    <option value="Ponorogo" <?php if ($filter_route == 'Ponorogo') echo 'selected'; ?>>Ponorogo</option>
                    <option value="Solo" <?php if ($filter_route == 'Solo') echo 'selected'; ?>>Solo</option>
                    <option value="Bojonegoro" <?php if ($filter_route == 'Bojonegoro') echo 'selected'; ?>>Bojonegoro</option>
                    <option value="Gemolong" <?php if ($filter_route == 'Gemolong') echo 'selected'; ?>>Gemolong</option>
                </select>
            </div>
            <div class="form-group">
                <button type="submit" id="filter-button" class="btn-primary">Filter</button>
            </div>
        </form>

        <div class="inner-container" style="<?php echo $filter_route ? 'display: block;' : 'display: none;'; ?>">
            <!-- Container for the form -->
            <div class="form-container">
                <!-- Form for Crew Details -->
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="no_body">NO Body (Lambung) Bus</label>
                        <input type="text" id="no_body" name="no_body" class="form-control" value="<?php echo htmlspecialchars($no_body); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="nama_crew1">Nama Crew 1</label>
                        <input type="text" id="nama_crew1" name="nama_crew1" class="form-control" value="<?php echo htmlspecialchars($nama_crew1); ?>">
                    </div>
                    <div class="form-group">
                        <label for="nama_crew2">Nama Crew 2</label>
                        <input type="text" id="nama_crew2" name="nama_crew2" class="form-control" value="<?php echo htmlspecialchars($nama_crew2); ?>">
                    </div>
                    <div class="form-group">
                        <label for="no_telp_crew">NO Telp Crew 1</label>
                        <input type="text" id="no_telp_crew" name="no_telp_crew" class="form-control" value="<?php echo htmlspecialchars($no_telp_crew); ?>">
                    </div>
                    <div class="form-group">
                        <button type="submit" id="save-button" class="btn-primary">Masukan data</button>
                    </div>
                </form>
            </div>

            <div class="print-section">
                <div class="output">
                    <h2 class="title"><strong>Surat Perintah Jalan</strong></h2>
                    <div class="header-section">
                        <div class="info-card">
                            <div class="info-header">
                                <p><strong>Waktu :</strong> <?php echo $currentDateTime; ?></p>
                            </div>
                            <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                            <div class="info-body">
                                <div class="info-left">
                                    <div class="info-group">
                                        <label><strong>PO :</strong> <strong> LAJU PRIMA</strong></label>
                                    </div>
                                    <div class="info-group">
                                        <label><strong>BUS :</strong> <?php echo htmlspecialchars($filter_route); ?></label>
                                    </div>
                                </div>
                                <div class="info-right">
                                    <div class="info-group">
                                        <label><strong>NO Body (Lambung) Bus:</strong> <?php echo htmlspecialchars($no_body); ?></label>
                                    </div>
                                    <div class="info-group">
                                        <label><strong>Nama Crew 1:</strong> <?php echo htmlspecialchars($nama_crew1); ?></label>
                                    </div>
                                    <div class="info-group">
                                        <label><strong>Nama Crew 2:</strong> <?php echo htmlspecialchars($nama_crew2); ?></label>
                                    </div>
                                    <div class="info-group">
                                        <label><strong>NO Telp Crew 1 :</strong> <?php echo htmlspecialchars($no_telp_crew); ?></label>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="summary-section">
                        <div class="summary-item">
                            <label for="total_passenger">Total Penumpang</label>
                            <input type="text" id="total_passenger" value="<?php echo htmlspecialchars($total_passenger_count); ?>" readonly>
                        </div>
                        <div class="summary-item">
                            <label for="total_setoran">Total Setoran Crew</label>
                            <input type="text" id="total_setoran" value="<?php echo htmlspecialchars(formatRupiah($total_setoran)); ?>" readonly>
                        </div>
                    </div>

                    <div class="tables-section">
                        <div class="col-md-12 table-container">
                            <h3 class="table-title"><strong>Daftar Penumpang</strong></h3>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Kode Booking</th>
                                            <th>Nama Penumpang</th>
                                            <th>No telp penumpang</th>
                                            <th>Kursi</th>
                                            <th>Asal Keberangkatan</th>
                                            <th>Tujuan</th>
                                            <th>No Body</th>
                                            <th>Status check in</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php echo $passenger_rows; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="col-md-12 table-container2 ml-auto">
                            <h3 class="table-title"><strong>Setoran Crew</strong></h3>
                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Asal Keberangkatan</th>
                                            <th>Total penumpang</th>
                                            <th>Setoran satuan</th>
                                            <th>Total Setoran Crew</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($passenger_count as $departure => $count) : ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($departure); ?></td>
                                            <td><?php echo htmlspecialchars($count); ?></td>
                                            <td>
                                                <?php 
                                                if ($count > 0) {
                                                    echo htmlspecialchars(number_format($setoran[$departure] / $count, 0, ',', '.'));
                                                } else {
                                                    echo 'Rp 0'; // Atau nilai default lain
                                                }
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars(formatRupiah($setoran[$departure])); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                        
                                    </tbody>
                                </table>
                                      
                            </div>
                            <div class="admin-info">
                <p>Mengetahui</p>
                <div class="signature-box">
                    <p>____________________</p> <!-- Placeholder for signature -->
                </div>
                <p><strong>Petugas keberangkatan:</strong> <?php echo htmlspecialchars($username); ?></p>
                <p class="timestamp"><strong>Waktu Cetak:</strong> <span id="timestamp"></span></p>
            </div>
             </div>
                    </div>
         
                </div>
            </div>
            <div class="button-container">
            <a href="crud.php" class="back-link">Kembali</a>
            <button class="btn-print">Print</button>
                </div>

                </div>
    </div>
 
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
        document.addEventListener('DOMContentLoaded', function() {
            const routeSelect = document.getElementById('route');
            const innerContainer = document.getElementById('inner-container');
            const filterButton = document.getElementById('filter-button');
            
            function toggleInnerContainer() {
                // Show or hide the inner container based on the route selection
                if (routeSelect.value) {
                    innerContainer.style.display = 'block';
                } else {
                    innerContainer.style.display = 'none';
                }
            }

  // JavaScript to display the current timestamp
  function updateTimestamp() {
            const now = new Date();
            const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit', timeZone: 'Asia/Jakarta' };
            const formattedDate = now.toLocaleDateString('id-ID', options);
            document.getElementById('timestamp').textContent = formattedDate;
        }
        
        // Update timestamp on page load
        window.onload = updateTimestamp;

           function printDiv() {
  var divToPrint = document.querySelector('.print-section');
  var htmlToPrint = divToPrint.outerHTML;
  var printWindow = window.open('', '_self');
  printWindow.document.body.innerHTML = htmlToPrint;
  printWindow.print();
}

document.querySelector('.btn-print').addEventListener('click', function() {
  printDiv();
});
            // Initial state of the inner container based on current filter values
            toggleInnerContainer();
            
            // Handle filter button click
            filterButton.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default form submission
                
                // Manually show or hide the container based on current route selection
                toggleInnerContainer();
                
                // Uncomment the line below if you want to submit the form after toggle
                // document.querySelector('form').submit();
            });
        });

//         function toggleColumn(columnIndex) {
//     const table = document.querySelector('.table-responsive table');
//     const rows = table.rows;

//     for (let i = 0; i < rows.length; i++) {
//         const cells = rows[i].cells;
//         if (cells.length > columnIndex) {
//             cells[columnIndex].style.display = 
//                 cells[columnIndex].style.display === 'none' ? '' : 'none';
//         }
//     }
// }

// // Optionally, hide the column on load if needed
// toggleColumn(8);  // Hide Status Check-in column by default
     
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
    </script>
  </body>
</html>
