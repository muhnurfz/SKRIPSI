<?php
include('conn.php');

if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];

    $query = "SELECT rp.id, rp.image, rp.upload_date 
              FROM refund_payment rp 
              JOIN orders o ON rp.order_id = o.id 
              WHERE o.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imageData = base64_encode($row['image']);
        $imageSrc = 'data:image/jpeg;base64,' . $imageData;
        echo json_encode(['status' => 'success', 'image' => $imageSrc, 'upload_date' => $row['upload_date']]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No image found for this order.']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Order ID is missing.']);
}
?>
