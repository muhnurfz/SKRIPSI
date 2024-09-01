<?php
include('conn.php');

// Handle delete request
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM orders WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<div class='alert alert-danger'>Error deleting record: " . $conn->error . "</div>";
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
    <title>Edit Penumpang</title>
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
    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this record?")) {
                window.location.href = 'edit_penumpang.php?delete=' + id;
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h2 class="mb-4">Cari dan cetak tiket</h2>
        
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
                            <th>Tanggak keberangkatan</th>
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
                                    <td><?php echo $row['departure']; ?></td>
                                    <td><?php echo $row['route']; ?></td>
                                    <td><?php echo $row['destination']; ?></td>
                                    <td><?php echo (new DateTime($row['departure_date']))->format('d/m/Y'); ?></td>
                                    <td><?php echo $row['passenger_phone']; ?></td>
                                    <td><?php echo $row['selected_seats']; ?></td>
                                    <td><?php echo number_format($row['total_tariff'], 0, ',', '.'); ?></td>
                                    <td class="action-buttons">
                                    <?php if ($row['status_pembayaran'] === 'paid' || $row['status_pembayaran'] === 'verified'): ?>
                                        <a class="btn btn-success btn-sm" href="payment_success.php?id=<?php echo $row['id']; ?>">Cetak tiket</a>
                                    <?php else: ?>
                                        <span class="btn btn-secondary btn-sm">Harap lunasi pembayaran</span>
                                    <?php endif; ?>
                                    </td>
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
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to delete this record?")) {
                window.location.href = 'edit_penumpang.php?delete=' + id;
            }
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>
