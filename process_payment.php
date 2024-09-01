<?php
session_start(); // Ensure the session is started
include('conn.php');

// Handle search booking code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_booking_code'])) {
    $search_booking_code = $_POST['search_booking_code'];
    $query = "SELECT id, booking_code, departure, route, destination, departure_date, passenger_name, passenger_phone, total_tariff FROM orders WHERE booking_code = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $search_booking_code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $booking_code, $departure, $route, $destination, $departure_date, $passenger_name, $passenger_phone, $total_tariff);
        $stmt->fetch();
        
        $_SESSION['booking_code'] = $search_booking_code;
        $_SESSION['order_details'] = [
            'id' => $id,
            'booking_code' => $booking_code,
            'departure' => $departure,
            'route' => $route,
            'destination' => $destination,
            'departure_date' => $departure_date,
            'passenger_name' => $passenger_name,
            'passenger_phone' => $passenger_phone,
            'total_tariff' => $total_tariff
        ];
        
        header("Location: upload_bukti_pembayaran.php");
        exit;
    } else {
        echo "<script>alert('Invalid booking code. Please try again.'); window.location.href='upload_bukti_pembayaran.php';</script>";
    }

    $stmt->close();
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_pembayaran'])) {
    if (isset($_SESSION['booking_code'])) {
        $booking_code = $_SESSION['booking_code'];
        $file_error = $_FILES['bukti_pembayaran']['error'];

        if ($file_error === UPLOAD_ERR_OK) {
            // Get the file contents
            $file_contents = file_get_contents($_FILES['bukti_pembayaran']['tmp_name']);

            // Prepare the SQL query
            $query = "UPDATE orders SET bukti_pembayaran = ?, status_pembayaran = 'paid' WHERE booking_code = ?";
            $stmt = $conn->prepare($query);

            if ($stmt) {
                // Bind the parameters and execute the statement
                $stmt->bind_param("ss", $file_contents, $booking_code);
                $result = $stmt->execute();

                if ($result) {
                    // Retrieve the order ID for redirection
                    $stmt = $conn->prepare("SELECT id FROM orders WHERE booking_code = ?");
                    $stmt->bind_param("s", $booking_code);
                    $stmt->execute();
                    $stmt->bind_result($order_id);
                    $stmt->fetch();

                    // Redirect to the success page with order ID
                    header("Location: payment_success.php?id=" . $order_id);
                    exit;
                } else {
                    echo "SQL Error: " . $stmt->error;
                }

                // Close the statement
                $stmt->close();
            } else {
                echo "Failed to prepare the SQL statement.";
            }
        } else {
            echo "Error uploading file.";
        }
    } else {
        echo "No booking code found. Please start the booking process again.";
    }

    // Close the database connection
    $conn->close();
}
?>
