<?php
include('conn.php');
session_start(); // Start the session to use session variables

// Fetch logs from the database
$query = "SELECT ol.*, o.booking_code, u.username 
          FROM order_logs ol
          JOIN orders o ON ol.order_id = o.id
          JOIN users u ON ol.changed_by = u.id
          ORDER BY ol.log_date DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Function to log changes
function logOrderChanges($order_id, $operation, $changes, $changed_by, $db) {
    $sql = "INSERT INTO order_logs (order_id, operation, changes, changed_by) 
            VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($sql);
    $stmt->execute([$order_id, $operation, $changes, $changed_by]);
}

// Example of capturing an UPDATE
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['order_id']) && isset($_SESSION['user_id'])) {
        $order_id = $_POST['order_id'];
        $changed_by = $_SESSION['user_id'];

        // Fetch old data (before update) for comparison
        $oldQuery = "SELECT * FROM orders WHERE id = ?";
        $stmt = $db->prepare($oldQuery);
        $stmt->execute([$order_id]);
        $oldData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Here you will apply your update logic (e.g., updating fields based on POST data)
        // Example: assuming you're updating `total_tariff`
        $new_total_tariff = $_POST['total_tariff'];
        $sql = "UPDATE orders SET total_tariff = ? WHERE id = ?";
        $stmt = $db->prepare($sql);

        if ($stmt->execute([$new_total_tariff, $order_id])) {
            // Capture old vs new values
            $changes = "Old total_tariff: " . $oldData['total_tariff'] . " | New total_tariff: " . $new_total_tariff;

            // Log the changes
            logOrderChanges($order_id, 'Update', $changes, $changed_by, $db);

            echo "Order updated and changes logged.";
        } else {
            echo "Failed to update order.";
        }
    } else {
        echo "Order ID or User ID is missing.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .log-table {
            margin-top: 20px;
        }
        .log-date {
            font-size: 0.9em;
            color: #888;
        }
    </style>
</head>
<body>

<div class="container">
    <h2 class="my-4">Order Change Logs</h2>

    <table class="table table-striped log-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Booking Code</th>
                <th>Operation</th>
                <th>Changes</th>
                <th>Changed By</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?php echo htmlspecialchars($log['log_id']); ?></td>
                    <td><?php echo htmlspecialchars($log['booking_code']); ?></td>
                    <td><?php echo htmlspecialchars($log['operation']); ?></td>
                    <td><?php echo htmlspecialchars($log['changes']); ?></td>
                    <td><?php echo htmlspecialchars($log['username']); ?></td>
                    <td><span class="log-date"><?php echo htmlspecialchars($log['log_date']); ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
