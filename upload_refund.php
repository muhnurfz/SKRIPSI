<?php
// Include the database connection
include('conn.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the order ID and booking code from the form
    $order_id = $_POST['id'];
    $booking_code = $_POST['booking_code'];

    // Check if a file is uploaded
    if (isset($_FILES['fileUpload']) && $_FILES['fileUpload']['error'] === UPLOAD_ERR_OK) {
        // Get the file content
        $file = $_FILES['fileUpload']['tmp_name'];
        $fileContent = file_get_contents($file);

        // Prepare the SQL query to insert into refund_history table
        $sql = "INSERT INTO refund_history (order_id, refund_status, bukti_batal) VALUES (?, 'pending', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $order_id, $fileContent);

        // Execute the query
        if ($stmt->execute()) {
            echo "File uploaded successfully.";
        } else {
            echo "Error uploading file.";
        }
        
        $stmt->close();
    } else {
        echo "No file uploaded or there was an upload error.";
    }
    
    $conn->close();
}
?>
