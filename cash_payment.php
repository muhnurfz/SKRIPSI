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

$notification_message = '';
$notification_type = '';

// Cek jika form sudah disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_code = $_POST['booking_code'];
    
    // Hapus format "Rp" dan pemisah ribuan sebelum menyimpan atau menggunakan nilai
    $uang_muka = isset($_POST['uang_muka']) ? $_POST['uang_muka'] : '';
    $uang_muka = str_replace(['Rp. ', '.'], '', $uang_muka);
    $uang_muka = (int)$uang_muka;

    if (isset($_POST['update'])) {
        // Ambil data dari database untuk memastikan kita mendapatkan total_tariff yang benar
        $stmt = $conn->prepare('SELECT total_tariff FROM orders WHERE booking_code = ?');
        $stmt->bind_param('s', $booking_code);
        $stmt->execute();
        $stmt->bind_result($total_tariff);
        $stmt->fetch();
        $stmt->close();

        // Simpan uang muka ke dalam database
        $stmt = $conn->prepare('UPDATE orders SET uang_muka = ? WHERE booking_code = ?');
        $stmt->bind_param('is', $uang_muka, $booking_code);
        if ($stmt->execute()) {
            $notification_message = 'Uang muka untuk ' . htmlspecialchars($booking_code, ENT_QUOTES, 'UTF-8') . ' berhasil diupdate.';
            $notification_type = 'success';

            // Periksa apakah uang muka sama dengan total tarif
            if ($uang_muka == $total_tariff) {
                // Update status_pembayaran menjadi verified
                $stmt = $conn->prepare('UPDATE orders SET status_pembayaran = ? WHERE booking_code = ?');
                $status_pembayaran = 'verified';
                $stmt->bind_param('ss', $status_pembayaran, $booking_code);
                if ($stmt->execute()) {
                    $notification_message .= ' Status pembayaran diubah menjadi LUNAS.';
                } else {
                    $notification_message .= ' Gagal memperbarui status pembayaran.';
                    $notification_type = 'warning';
                }
            }
        } else {
            $notification_message = 'Gagal memperbarui uang muka.';
            $notification_type = 'danger';
        }
        $stmt->close();
    }

    if (isset($_POST['submit'])) {
        $stmt = $conn->prepare("SELECT * FROM orders WHERE booking_code = ?");
        $stmt->bind_param("s", $booking_code);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        } else {
            $notification_message = 'Kode booking tidak ditemukan.';
            $notification_type = 'error';
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir Pembayaran Cash</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
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

        .payment-summary {
            margin-top: 20px;
        }
        .payment-summary table {
            width: 100%;
        }
        .payment-summary td {
            padding: 10px;
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
        .php-output {
                font-family: monospace, 'Courier New', Courier, monospace;
                white-space: pre-wrap; /* Agar spasi dan baris baru dipertahankan */
            }
    </style>
</head>
<body>

<div id="notification-container" class="notification-container"></div>

    <div class="container mt-5">
        <h2>Proses pembayaran tunai</h2>
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
                    <a class="nav-link" href="cash_payment.php"><i class="bi bi-wallet2"></i> Proses Pembayaran</a>
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

<div class="container">
  <!-- Form untuk mencari data penumpang berdasarkan booking_code -->
  <div class="card">
    <div class="card-header">
      <h5>Cari Penumpang</h5>
    </div>
    <div class="card-body">
      <form method="post">
        <div class="form-group">
          <label for="booking_code">Kode Booking:</label>
          <input type="text" class="form-control" id="booking_code" name="booking_code" required>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Cari Penumpang</button>
      </form>
    </div>
  </div>

  <?php if (isset($row)): ?>
<div class="container">
  <div class="row">
    <div class="col-md-8 offset-md-2">
      <table class="table table-bordered shadow-sm">
        <thead class="thead-dark">
          <tr>
            <th colspan="2">Detail Penumpang</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <th>Nama Penumpang</th>
            <td><?php echo $row['passenger_name']; ?></td>
          </tr>
          <tr>
            <th>Nomor Telepon</th>
            <td><?php echo $row['passenger_phone']; ?></td>
          </tr>
          <tr>
            <th>Email </th>
            <td><?php echo $row['email']; ?></td>
          </tr>
          <tr>
            <th>BUS </th>
            <td><?php echo $row['route']; ?></td>
          </tr>
          <tr>
            <th>Asal keberangkatan </th>
            <td><?php echo $row['departure']; ?></td>
          </tr>
          <tr>
            <th>Kota tujuan </th>
            <td><?php echo $row['destination']; ?></td>
          </tr>
          <tr>
            <th>Total Tarif</th>
            <td><?php echo $row['total_tariff']; ?></td>
          </tr>
          <tr>
            <th>Kursi</th>
            <td><?php echo $row['selected_seats']; ?></td>
          </tr>
        </tbody>
      </table>

      <form action="" method="post">
        <input type="hidden" name="booking_code" value="<?php echo isset($row['booking_code']) ? $row['booking_code'] : ''; ?>">
        
        <div class="form-group">
          <label for="uang_muka">Uang Muka:</label>
          <input type="text" class="form-control" id="uang_muka" name="uang_muka" value="<?php echo isset($row['uang_muka']) ? number_format($row['uang_muka'], 0, ',', '.') : ''; ?>" required>
        </div>
        
        <button type="submit" name="update" class="btn btn-primary">Update</button>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>


</div>

    <script>
           function showNotification(message, type = 'success') {
            var container = document.getElementById('notification-container');
            
            var notification = document.createElement('div');
            notification.className = 'notification ' + (type === 'error' ? 'error' : 'success');
            notification.innerText = message;

            container.appendChild(notification);

            setTimeout(function() {
                notification.classList.add('show');
            }, 10);

            setTimeout(function() {
                notification.classList.remove('show');
                setTimeout(function() {
                    container.removeChild(notification);
                }, 300);
            }, 3000);
        }

        // Cek apakah ada notifikasi dari PHP
        <?php if (!empty($notification_message)): ?>
            showNotification('<?php echo htmlspecialchars($notification_message); ?>', '<?php echo htmlspecialchars($notification_type); ?>');
        <?php endif; ?>

  function formatRupiah(angka) {
            var number_string = angka.replace(/[^,\d]/g, '').toString(),
                split = number_string.split(','),
                sisa = split[0].length % 3,
                rupiah = split[0].substr(0, sisa),
                ribuan = split[0].substr(sisa).match(/\d{3}/gi);
            
            if (ribuan) {
                separator = sisa ? '.' : '';
                rupiah += separator + ribuan.join('.');
            }
            
            rupiah = split[1] != undefined ? rupiah + ',' + split[1] : rupiah;
            return 'Rp. ' + rupiah;
        }

        function unformatRupiah(rupiah) {
            return rupiah.replace(/[^,\d]/g, '').toString();
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            const input = document.getElementById('uang_muka');
            input.addEventListener('keyup', function(e) {
                e.target.value = formatRupiah(unformatRupiah(e.target.value));
            });

            // Set initial value with proper format
            input.value = formatRupiah(input.value);
        });

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
