<?php
// Include koneksi database
include('conn.php');

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

if (isset($_POST['process_refund'])) {
    $booking_code = $_POST['booking_code'];
    $refund_amount = $_POST['refund_amount'];
    $comments = $_POST['comments'];

    // Simpan riwayat pembatalan ke tabel refund_history
    $sql = "INSERT INTO refund_history (booking_code, refund_amount, refund_status, comments) VALUES (?, ?, 'processed', ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sis", $booking_code, $refund_amount, $comments);
    $stmt->execute();

    // Update status pembayaran di tabel orders
    $sql = "UPDATE orders SET status_pembayaran = 'cancelled', comments = ? WHERE booking_code = ?";
    $new_comments = $comments . ' - Refund diproses';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $new_comments, $booking_code);
    $stmt->execute();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Penumpang yang Mengajukan Pembatalan</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
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
    <h4 class="mb-3">BLM READY TO DEPLOY</h4>

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
                    <th>Komentar</th>
                    <th>Proses Refund</th>
                </tr>
            </thead>
            <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['booking_code']) ?></td>
                    <td><?= htmlspecialchars($row['passenger_name']) ?></td>
                    <td><?= (new DateTime($row['departure_date']))->format('d/m/Y') ?></td>
                    <td><?= htmlspecialchars($row['selected_seats']) ?></td>
                    <td><?= htmlspecialchars($row['departure']) ?></td>
                    <td><?= htmlspecialchars($row['destination']) ?></td>
                    <td><?= htmlspecialchars($row['comments']) ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="booking_code" value="<?= htmlspecialchars($row['booking_code']) ?>">
                            <div class="form-group">
                                <label for="refund_amount_<?= htmlspecialchars($row['id']) ?>">Jumlah Refund:</label>
                                <input type="number" class="form-control" id="refund_amount_<?= htmlspecialchars($row['id']) ?>" name="refund_amount" required>
                            </div>
                            <div class="form-group">
                                <label for="comments_<?= htmlspecialchars($row['id']) ?>">Komentar:</label>
                                <textarea class="form-control" id="comments_<?= htmlspecialchars($row['id']) ?>" name="comments"></textarea>
                            </div>
                            <button type="submit" name="process_refund" class="btn btn-danger">Proses Refund</button>
                        </form>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Sample data
  

    // Initialize the data in the page
    document.getElementById('passengerName').innerText = refundData.passengerName;
    document.getElementById('bookingCode').innerText = refundData.bookingCode;
    document.getElementById('refundAmount').innerText = refundData.refundAmount + ' IDR';
    document.getElementById('refundStatus').innerText = refundData.refundStatus;

    // Populate the hidden fields in the modal
    document.getElementById('hiddenBookingCode').value = refundData.bookingCode;
    document.getElementById('hiddenRefundAmount').value = refundData.refundAmount;

    // Function to submit the refund process
    function submitRefund() {
        const form = document.getElementById('refundForm');
        const formData = new FormData(form);

        fetch('process_refund.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the refund status on the page
                document.getElementById('refundStatus').innerText = formData.get('refund_status');
                document.getElementById('refundStatus').className = 'badge bg-success';
                
                // Close the modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('processRefundModal'));
                modal.hide();
            } else {
                alert('Refund processing failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>

</body>
</html>
