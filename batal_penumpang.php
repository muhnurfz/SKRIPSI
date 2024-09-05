<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Menyertakan file PHPMailer secara manual
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include('conn.php');
// Handle cancellation request
if (isset($_GET['cancel'])) {
    $id = intval($_GET['cancel']);
    $inputNumber = isset($_GET['inputNumber']) ? $conn->real_escape_string($_GET['inputNumber']) : '';

    // Update both comments and pengajuan_batal
    $sql = "UPDATE orders SET comments = CONCAT('MENGAJUKAN PEMBATALAN!!! informasi transfer ke =', '$inputNumber'), pengajuan_batal = 'ya' WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        // Retrieve the updated order details
        $sql = "SELECT * FROM orders WHERE id = $id";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
            $email = $order['email'];
            $booking_code = $order['booking_code'];
            $tanggal_pembatalan = date('d/m/Y'); // Format tanggal sesuai kebutuhan

            // Send confirmation email
            if (!empty($email)) {
                $mail = new PHPMailer(true);
                try {
                    // Server settings
                    $mail->isSMTP();
                    $mail->Host = 'smtp.hostinger.com'; // Host SMTP Anda
                    $mail->SMTPAuth = true;
                    $mail->Username = 'customer.service@tiket.agungindahtrav.com'; // Alamat email pengirim
                    $mail->Password = '1Customer.service'; // Password email pengirim
                    $mail->SMTPSecure = 'ssl'; // Atau 'tls' jika menggunakan port 587
                    $mail->Port = 465; // Gunakan port 465 untuk 'ssl' atau port 587 untuk 'tls'

                    // Penerima dan pengirim
                    $mail->setFrom('customer.service@tiket.agungindahtrav.com', 'Tiket Agung Indah Travel');
                    $mail->addAddress($email);

                    // Konten email
                    $mail->isHTML(true);
                    $mail->Subject = 'Konfirmasi Pembatalan Tiket';
                    $mail->Body    = '
                        <!DOCTYPE html>
                        <html lang="en">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>Ticket Cancellation Confirmation</title>
                            <style>
                                body {
                                    font-family: \'Arial\', sans-serif;
                                    background-color: #f4f4f4;
                                    margin: 0;
                                    padding: 0;
                                    display: flex;
                                    justify-content: center;
                                    align-items: center;
                                    height: 100vh;
                                }
                                .container {
                                    background-color: #fff;
                                    padding: 30px;
                                    max-width: 500px;
                                    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                                    border-radius: 10px;
                                    text-align: center;
                                }
                                h1 {
                                    color: #e74c3c;
                                    font-size: 24px;
                                    margin-bottom: 20px;
                                }
                                p {
                                    color: #333;
                                    font-size: 16px;
                                    line-height: 1.5;
                                }
                                .highlight {
                                    color: #e74c3c;
                                    font-weight: bold;
                                }
                                .btn {
                                    display: inline-block;
                                    padding: 10px 20px;
                                    margin-top: 20px;
                                    background-color: #e74c3c;
                                    color: #fff;
                                    text-decoration: none;
                                    border-radius: 5px;
                                    font-size: 16px;
                                }
                                .btn:hover {
                                    background-color: #c0392b;
                                }
                            </style>
                        </head>
                        <body>
                            <div class="container">
                                <h1>Ticket Cancellation Confirmation</h1>
                                <p>
                                    Pada tanggal <span class="highlight">' . $tanggal_pembatalan . '</span>, Anda telah mengajukan pembatalan tiket dengan kode booking <span class="highlight">' . $booking_code . '</span>.
                                </p>
                                <p>
                                  Pengembalian dana akan ditransfer dalam 2x24 jam melalui nomor rekening yang telah Anda kirim.
                                </p>
                              <p>Untuk informasi lebih lanjut silahkan akses halaman cetak tiket di <a href="https://tiket.agungindahtrav.com/cari_tiket.php" target="https://tiket.agungindahtrav.com/cari_tiket.php" style="color: #007bff; text-decoration: none;">tiket.agungindahtrav.com</a></p>
                            </div>
                        </body>
                        </html>';

                    $mail->send();
                    echo "<script>window.addEventListener('load', function() { $('#successModal').modal('show'); });</script>";
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>Error sending email: " . $mail->ErrorInfo . "</div>";
                }
            }
        }
    } else {
        echo "<div class='alert alert-danger'>Error updating record: " . $conn->error . "</div>";
    }
}

// Handle search
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$search_condition = '';
if (!empty($search)) {
    if (is_numeric($search)) {
        $search_condition = " WHERE id = $search";
    } else {
        $search_condition = " WHERE booking_code LIKE '%$search%'";
    }
}

// Retrieve orders with optional search filter
$sql = "SELECT * FROM orders" . $search_condition;
$result = $conn->query($sql);

if ($result === FALSE) {
    die("<div class='alert alert-danger'>Error: " . $conn->error . "</div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan pembatalan</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 30px;
        }
        .search-card {
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }
        .search-input {
            width: 100%;
        }
        .search-button {
            margin-left: 10px;
        }
        .table {
            width: 100%; /* Ensure the table takes the full width */
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
        overflow-x: auto; /* Allows horizontal scrolling if necessary */
        }

        .table-responsive table {
            width: 200%; /* Make the table take up 100% of the container's width */
            border-collapse: collapse; /* Ensures borders are collapsed into a single line */
        }

        .table-responsive th, .table-responsive td {
            padding: 8px; /* Add padding for better spacing */
            text-align: left; /* Align text to the left */
            border-bottom: 1px solid #dee2e6; /* Light gray border for rows */
        }
        .form-text {
            font-size: 0.875rem; /* Slightly smaller text */
            color: #6c757d; /* Bootstrap's secondary text color */
            margin-top: 0.25rem; /* Space between label and text */
            margin-bottom: 1rem; /* Space between text and input field */
        }
        .action-buttons a {
            margin-right: 5px;
        }
        .btn-sm {
            padding: 5px 10px;
        }
    </style>
</head>
<body>
<!-- Number Input Modal -->
<div class="modal fade" id="inputNumberModal" tabindex="-1" aria-labelledby="inputNumberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="inputNumberModalLabel">Masukkan Nomor</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="inputNumber">Nomor Rekening</label>
                    <div class="form-text">Masukkan dengan format : bank - no rek</div>
                    <input type="text" class="form-control" id="inputNumber" placeholder="Masukkan nomor rekening">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" id="submitNumberBtn">Kirim</button>
            </div>
        </div>
    </div>
</div>


<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Konfirmasi Pembatalan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin mengajukan pembatalan untuk pesanan ini?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Ajukan Pembatalan</button>
            </div>
        </div>
    </div>
</div>

<!-- Success Confirmation Modal -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Pengajuan Berhasil</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
    Pengajuan pembatalan Anda telah berhasil dikirim.
    <br><small>
        <?php
            // Menghitung tanggal 2 hari ke depan
            $today = new DateTime();
            $today->modify('+2 days');
            
            // Format tanggal menjadi format tertulis
            $formatter = new IntlDateFormatter('id_ID', IntlDateFormatter::LONG, IntlDateFormatter::NONE);
            $formattedDate = $formatter->format($today);
            
            echo "Pengembalian dana akan ditransfer pada " . $formattedDate . " melalui nomor rekening yang telah Anda kirim";
        ?>
    </small>
</div>

            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>


<div class="container">
    <h2 class="mb-4">Ajukan pembatalan</h2>
    
    <!-- Search form with card -->
    <div class="search-card">
        <form class="search-form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="GET">
            <div class="form-row align-items-center">
                <div class="col-auto">
                    <input class="form-control search-input" type="text" name="search" placeholder="Masukan kode booking" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary search-button" type="submit">Cari</button>
                </div>
            </div>
        </form>
    </div>
    
    <?php if (!empty($search)): ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Kode booking</th>
                        <th>Tanggal pembelian</th>
                        <th>Nama penumpang</th>
                        <th>No Telepon</th>
                        <th>Asal keberangkatan</th>
                        <th>BUS</th>
                        <th>Kota tujuan</th>
                        <th>Tanggal keberangkatan</th>
                        <th>Kursi dipilih</th>
                        <th>Total pembayaran</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['route'] . '-' . $row['id']; ?></td>
                                <td><?php echo $row['booking_code']; ?></td>
                                <td><?php echo (new DateTime($row['purchase_date']))->format('d/m/Y'); ?></td>
                                <td><?php echo $row['passenger_name']; ?></td>
                                <td><?php echo $row['passenger_phone']; ?></td>
                                <td><?php echo $row['departure']; ?></td>
                                <td><?php echo $row['route']; ?></td>
                                <td><?php echo $row['destination']; ?></td>
                                <td><?php echo (new DateTime($row['departure_date']))->format('d/m/Y'); ?></td>
                                <td><?php echo $row['selected_seats']; ?></td>
                                <td><?php echo number_format($row['total_tariff'], 0, ',', '.'); ?></td>
                                <td class="action-buttons">
                                <?php
                                $current_date = date('Y-m-d'); // Today's date in Y-m-d format
                                $departure_date = $row['departure_date'];
                                
                              // Check if the departure date is today or has already passed
                                    if ($departure_date <= $current_date): ?>
                                        <span class="btn btn-secondary btn-sm">Sudah hari keberangkatan tidak dapat dibatalkan</span>
                                    <?php else: ?>
                                        <?php if ($row['check_in_status'] === '1'): ?>
                                            <span class="btn btn-secondary btn-sm">Telah melakukan check-in</span>
                                        <?php else: ?>
                                            <button class="btn btn-danger btn-sm" onclick="confirmDelete(<?php echo $row['id']; ?>)">Ajukan Pembatalan</button>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="12" class="text-center">Tidak ada data</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
    
    <a class="btn btn-secondary mt-3" href="index.php">Kembali</a>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
   let deleteId = null;
let inputNumber = '';

function confirmDelete(id) {
    deleteId = id;
    $('#inputNumberModal').modal('show');
}

document.getElementById('submitNumberBtn').addEventListener('click', function() {
    inputNumber = document.getElementById('inputNumber').value.trim();
    if (inputNumber && deleteId) {
        $('#inputNumberModal').modal('hide');
        $('#deleteModal').modal('show');
    } else {
        alert('Please enter a valid number.');
    }
});

document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
    if (deleteId && inputNumber) {
        window.location.href = `batal_penumpang.php?cancel=${deleteId}&inputNumber=${encodeURIComponent(inputNumber)}`;
    }
});

</script>

</body>
</html>
