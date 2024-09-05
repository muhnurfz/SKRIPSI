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

if (isset($_POST['search'])) {
    $departure_date = $_POST['departure_date'];
    $route = $_POST['route'];
    $booking_code = $_POST['booking_code'];

    if (!empty($booking_code)) {
        // Query jika kode booking dimasukkan
        $sql = "SELECT * FROM orders WHERE booking_code = ? AND (comments LIKE '%MENGAJUKAN PEMBATALAN!!!%' OR pengajuan_batal = 'ya')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $booking_code);
    } elseif (!empty($departure_date) && $route !== "0") {
        // Query jika tanggal dan rute dimasukkan
        $sql = "SELECT * FROM orders WHERE departure_date = ? AND route = ? AND (comments LIKE '%MENGAJUKAN PEMBATALAN!!!%' OR pengajuan_batal = 'ya')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $departure_date, $route);
    } else {
        // Tidak ada input yang valid
        $result = null;
    }

    if (isset($stmt)) {
        $stmt->execute();
        $result = $stmt->get_result();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_seats'])) {
    $orderId = $_POST['order_id'];
    $selectedSeats = $_POST['selected_seats'];

    // Validate the selected seats input (optional)
    if (!empty($selectedSeats)) {
        $sqlUpdateSeats = "UPDATE orders SET selected_seats = ? WHERE id = ?";
        $stmtUpdateSeats = $conn->prepare($sqlUpdateSeats);
        $stmtUpdateSeats->bind_param("si", $selectedSeats, $orderId);

        if ($stmtUpdateSeats->execute()) {
            echo "<div class='alert alert-success'>Seats updated successfully for order ID: $orderId.</div>";
        } else {
            echo "<div class='alert alert-danger'>Failed to update seats: " . $stmtUpdateSeats->error . "</div>";
        }

        $stmtUpdateSeats->close();
    } else {
        echo "<div class='alert alert-warning'>Please enter valid seats.</div>";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileUpload'])) {
    $orderId = $_POST['id']; // ID dari tabel orders

    // Cek apakah ada file yang di-upload
    if ($_FILES['fileUpload']['error'] === UPLOAD_ERR_NO_FILE) {
        $response = ['status' => 'error', 'message' => 'No file uploaded.'];
        echo json_encode($response);
        exit;
    }

    $image = file_get_contents($_FILES['fileUpload']['tmp_name']); // Membaca file yang di-upload

    // Validasi tipe file
    $fileType = mime_content_type($_FILES['fileUpload']['tmp_name']);
    if ($fileType !== 'image/png' && $fileType !== 'image/jpeg') {
        $response = ['status' => 'error', 'message' => 'Only PNG and JPEG files are allowed.'];
        echo json_encode($response);
        exit;
    }

    // Pertama, unggah file ke kolom img_batal di tabel orders
    $sqlUpdateOrders = "UPDATE orders SET img_batal = ? WHERE id = ?";
    $stmtOrders = $conn->prepare($sqlUpdateOrders);
    $stmtOrders->bind_param("bi", $null, $orderId);
    $stmtOrders->send_long_data(0, $image); // Kirim data gambar ke kolom img_batal

    if (!$stmtOrders->execute()) {
        $response = ['status' => 'error', 'message' => 'An error occurred while uploading to orders: ' . $stmtOrders->error];
        echo json_encode($response);
        $stmtOrders->close();
        exit;
    }
    $stmtOrders->close();

    // Kedua, unggah file ke tabel refund_payment
    $sqlSelectRefund = "SELECT id FROM refund_payment WHERE order_id = ?";
    $stmtRefundSelect = $conn->prepare($sqlSelectRefund);
    $stmtRefundSelect->bind_param("i", $orderId);
    $stmtRefundSelect->execute();
    $stmtRefundSelect->bind_result($refundId);
    $stmtRefundSelect->fetch();
    $stmtRefundSelect->close();

    if ($refundId) {
        // Jika gambar sebelumnya sudah ada, lakukan update
        $sqlUpdateRefund = "UPDATE refund_payment SET image = ? WHERE id = ?";
        $stmtRefundUpdate = $conn->prepare($sqlUpdateRefund);
        $stmtRefundUpdate->bind_param("bi", $null, $refundId);
        $stmtRefundUpdate->send_long_data(0, $image); // Kirim data gambar yang baru
    } else {
        // Jika belum ada, insert gambar baru
        $sqlInsertRefund = "INSERT INTO refund_payment (order_id, image) VALUES (?, ?)";
        $stmtRefundInsert = $conn->prepare($sqlInsertRefund);
        $stmtRefundInsert->bind_param("ib", $orderId, $null);
        $stmtRefundInsert->send_long_data(1, $image); // Kirim data gambar yang baru
    }

    if (($refundId && $stmtRefundUpdate->execute()) || (!$refundId && $stmtRefundInsert->execute())) {
        // Update status pembayaran menjadi 'cancelled'
        $sqlUpdateStatus = "UPDATE orders SET status_pembayaran = 'cancelled' WHERE id = ?";
        $stmtUpdateStatus = $conn->prepare($sqlUpdateStatus);
        $stmtUpdateStatus->bind_param("i", $orderId);

        if ($stmtUpdateStatus->execute()) {
            $response = ['status' => 'success', 'message' => 'File berhasil di upload dan status pembayaran diubah menjadi cancelled.'];
        } else {
            $response = ['status' => 'error', 'message' => 'File berhasil di upload, namun gagal mengubah status pembayaran: ' . $stmtUpdateStatus->error];
        }

        $stmtUpdateStatus->close();
    } else {
        $response = ['status' => 'error', 'message' => 'An error occurred while uploading to refund_payment: ' . ($refundId ? $stmtRefundUpdate->error : $stmtRefundInsert->error)];
    }

    // Tutup semua statement dan koneksi
    if (isset($stmtRefundUpdate)) {
        $stmtRefundUpdate->close();
    }
    if (isset($stmtRefundInsert)) {
        $stmtRefundInsert->close();
    }
    $conn->close();

    echo json_encode($response);
    exit;
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Penumpang yang Mengajukan Pembatalan</title>
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
            margin-left: 0;
            margin-top: 50px;
            padding : 10px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .container.shifted {
            margin-left: 270px;
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

        /* Success notification */
        .notification.success {
            background-color: #4CAF50; /* Green */
        }

        /* Error notification */
        .notification.error {
            background-color: #f44336; /* Red */
        }

        /* Show notification */
        .notification.show {
            display: block;
            opacity: 1;
        }

        /* Hide notification after 5 seconds */
        .notification.show {
            animation: fadeOut 5s forwards;
        }

        @keyframes fadeOut {
            0% {
                opacity: 1;
            }
            100% {
                opacity: 0;
                display: none;
            }
        }
</style>
</head>
<body>

<!-- Upload Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="uploadModalLabel"><i class="bi bi-upload"></i> Upload gambar</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="uploadForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="modalId" name="id">
                    <div class="form-group">
                        <label for="fileUpload" class="font-weight-bold">Pilih file <small class="text-muted"> (Hanya PNG or JPEG)</small>:</label>
                        <div class="custom-file">
                            <input type="file" class="custom-file-input" id="fileUpload" name="fileUpload" accept=".png, .jpeg, .jpg" required>
                            <label class="custom-file-label" for="fileUpload">Pilih file...</label>
                        </div>

                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Kembali</button>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-cloud-arrow-up"></i> Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

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


<div class="container mt-5">
    <h2 class="mb-4">Cari Penumpang yang Mengajukan Pembatalan</h2>

    <!-- Search Form -->
    <form method="post" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label for="departure_date">Tanggal keberangkatan :</label>
                    <input type="date" class="form-control" id="departure_date" name="departure_date">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label for="route">BUS: </label>
                    <select class="form-control" id="route" name="route">
                        <option value="0">Pilih rute BUS</option>
                        <option value="ponorogo">Ponorogo</option>
                        <option value="solo">Solo</option>
                        <option value="bojonegoro">Bojonegoro</option>
                        <option value="gemolong">Gemolong</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group mb-3">
                    <label for="booking_code">Kode Booking:</label>
                    <input type="text" class="form-control" id="booking_code" name="booking_code" placeholder="Masukkan kode booking">
                </div>
            </div>
        </div>
        <button type="submit" name="search" class="btn btn-primary">Cari</button>
    </form>
   
    <!-- Results Section -->
    <?php if (isset($result) && $result->num_rows > 0): ?>
    <h4 class="mb-3">Daftar Penumpang yang Mengajukan Pembatalan</h4>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Kode Booking</th>
                    <th>Nama Penumpang</th>
                    <th>Tanggal Keberangkatan</th>
                    <th>Kursi</th>
                    <th>Asal Keberangkatan</th>
                    <th>Kota Tujuan</th>
                    <th>Keterangan</th>
                    <th>Upload</th>
                    <th>Lihat gambar</th>
                    <th>Status pengembalian</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['booking_code']) ?></td>
                    <td><?= htmlspecialchars($row['passenger_name']) ?></td>
                    <td><?= (new DateTime($row['departure_date']))->format('d/m/Y') ?></td>
                    <td>
            <form method="post" action="">
                <input type="hidden" name="order_id" value="<?= $row['id'] ?>">
                <input type="text" name="selected_seats" value="<?= htmlspecialchars($row['selected_seats']) ?>">
                <button type="submit" class="btn btn-sm btn-success">Update</button>
            </form>
        </td>
                    <td><?= htmlspecialchars($row['departure']) ?></td>
                    <td><?= htmlspecialchars($row['destination']) ?></td>
                    <td><?= htmlspecialchars($row['comments']) ?></td>
                    <td>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#uploadModal" data-id="<?= htmlspecialchars($row['id']) ?>" data-booking-code="<?= htmlspecialchars($row['booking_code']) ?>">Upload</button>
                    </td>
                    <td>
                    <button class="btn btn-info btn-sm" onclick="viewBukti(<?php echo $row['id']; ?>)">View</button>
</td>
<td>
    <?php if (!empty($row['img_batal'])): ?>
        <span class="badge badge-success">SUDAH</span>
    <?php else: ?>
        <span class="badge badge-danger">BELUM</span>
    <?php endif; ?>
</td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php elseif (isset($result)): ?>
        <div class="alert alert-info">Tidak ada penumpang yang mengajukan pembatalan.</div>
    <?php endif; ?>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->
<script>
document.getElementById('fileUpload').addEventListener('change', function(){
        var fileName = this.files[0].name;
        var label = this.nextElementSibling;
        label.textContent = fileName;
    });

// When the modal is shown, set the form ID to the modal's hidden input field
$('#uploadModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Button that triggered the modal
    var id = button.data('id'); // Extract info from data-* attributes
    var modal = $(this);
    modal.find('.modal-body #modalId').val(id); // Update the modal's content
});

// Handle the form submission and show notifications
$('#uploadForm').on('submit', function (e) {
    e.preventDefault(); // Prevent the default form submission
    var formData = new FormData(this); // Create a FormData object with the form's data

    $.ajax({
        url: '', // Use the current URL
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            var data = JSON.parse(response); // Parse the JSON response
            var notificationContainer = $('#notification-container');

            // Create a new notification element
            var notification = $('<div class="notification"></div>').addClass(data.status);

            // Set the notification message
            notification.text(data.message);

            // Append the notification to the container
            notificationContainer.html(notification);

            // Show the notification
            notification.addClass('show');

            // Hide the modal
            $('#uploadModal').modal('hide'); // This should close the modal
        },
        error: function () {
            var notificationContainer = $('#notification-container');

            // Create a new notification element
            var notification = $('<div class="notification error"></div>').text('An error occurred while uploading the file.');

            // Append the notification to the container
            notificationContainer.html(notification);

            // Show the notification
            notification.addClass('show');

            // Hide the modal
            $('#uploadModal').modal('hide');

            // Auto-reload the page after 5 seconds if an error occurred
            setTimeout(function() {
                location.reload(); // Reload the page
            }, 5000); // 5000 milliseconds = 5 seconds
        }
    });
});


$(document).ready(function() {
    // Menangani klik tombol "View Image"
    $('.view-image-btn').on('click', function() {
        var id = $(this).data('id'); // Mengambil ID dari atribut data-id
        loadImage(id); // Memanggil fungsi AJAX untuk memuat gambar
    });
});

// fungsi untuk view bukti pembayaran
function viewBukti(id) {
    // Show the modal
    var viewBuktiModal = new bootstrap.Modal(document.getElementById('viewBuktiModal'));
    viewBuktiModal.show();

    // Load content via AJAX
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'view_batal.php?id=' + id, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            document.getElementById('buktiPembayaranContent').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}
//  // Event listener for when the modal is completely hidden
//  $('#viewBuktiModal').on('hidden.bs.modal', function () {
//         location.reload(); // Refresh the page
//     });


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
