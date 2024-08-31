<?php
include('conn.php');

// Handle cancellation request
if (isset($_GET['cancel'])) {
    $id = intval($_GET['cancel']);
    $sql = "UPDATE orders SET comments = 'MENGAJUKAN PEMBATALAN!!!' WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        echo "<script>window.addEventListener('load', function() { $('#successModal').modal('show'); });</script>";
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
// $current_date = date('Y-m-d'); // Today's date in Y-m-d format

// // Display the orders
// while ($row = $result->fetch_assoc()) {
//     $departure_date = $row['departure_date'];
//     $check_in_status = $row['check_in_status'];
//     $id = $row['id'];
// }
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
        .action-buttons a {
            margin-right: 5px;
        }
        .btn-sm {
            padding: 5px 10px;
        }
    </style>
</head>
<body>

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
                                
                                if ($departure_date === $current_date): ?>
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
                            <td colspan="12" class="text-center">No orders found</td>
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

    function confirmDelete(id) {
        deleteId = id;
        $('#deleteModal').modal('show');
    }

    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (deleteId) {
            window.location.href = 'batal_penumpang.php?cancel=' + deleteId;
        }
    });
</script>

</body>
</html>
