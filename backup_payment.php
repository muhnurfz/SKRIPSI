<?php
session_start(); // Ensure the session is started
include('conn.php');
$order_details = isset($_SESSION['order_details']) ? $_SESSION['order_details'] : null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Bukti Pembayaran</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .hidden {
            display: none;
        }

        .drop-area {
            border: 2px dashed #007bff;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            color: #007bff;
            margin-bottom: 20px;
            cursor: pointer;
        }

        .drop-area.hover {
            border-color: #0056b3;
            background-color: #e9f5ff;
        }
        
        #file-input {
            display: none;
        }

        .button, .submit-btn {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 10px;
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
        }

        .button:hover, .submit-btn:hover {
            background-color: #0056b3;
        }

        input[type="text"] {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        input[type="submit"] {
            width: 100%;
        }

        .search-form, .payment-form {
            display: flex;
            flex-direction: column;
        }

        .center-text {
            text-align: center;
        }

        .order-details {
            background-color: #f9f9f9;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 8px;
        }

        .order-details p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Bukti Pembayaran</h1>

        <?php if ($order_details): ?>
            <div class="order-details">
                <p><strong>Booking Code:</strong> <?php echo $order_details['booking_code']; ?></p>
                <p><strong>Departure:</strong> <?php echo $order_details['departure']; ?></p>
                <p><strong>Route:</strong> <?php echo $order_details['route']; ?></p>
                <p><strong>Destination:</strong> <?php echo $order_details['destination']; ?></p>
                <p><strong>Departure Date:</strong> <?php echo $order_details['departure_date']; ?></p>
                <p><strong>Passenger Name:</strong> <?php echo $order_details['passenger_name']; ?></p>
                <p><strong>Passenger Phone:</strong> <?php echo $order_details['passenger_phone']; ?></p>
                <p><strong>Total Tariff:</strong> <?php echo $order_details['total_tariff']; ?></p>
            </div>

            <form id="payment-form" action="process_payment.php" method="POST" enctype="multipart/form-data" class="payment-form">
                <div id="drop-area" class="drop-area">
                    <p>Drag & Drop your file here or click to select</p>
                    <input type="file" id="file-input" name="bukti_pembayaran" required>
                </div>
                <input type="submit" value="Upload Bukti Pembayaran" class="submit-btn">
            </form>
        <?php else: ?>
            <form id="search-form" action="process_payment.php" method="POST" class="search-form">
                <input type="text" id="search_booking_code" name="search_booking_code" placeholder="Enter Booking Code" required>
                <input type="submit" value="Search Booking Code" class="submit-btn">
            </form>
        <?php endif; ?>

        <div class="center-text">
            <a href="index.php" class="button">Kembali ke home</a>
        </div>
    </div>

    <script>
        // JavaScript for drag-and-drop functionality
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('file-input');

        // Prevent default drag behaviors
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        // Highlight the drop area when item is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.classList.add('hover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, () => {
                dropArea.classList.remove('hover');
            }, false);
        });

        // Handle dropped files
        dropArea.addEventListener('drop', (e) => {
            let files = e.dataTransfer.files;
            handleFiles(files);
        }, false);

        // Handle file input when clicking on the drop area
        dropArea.addEventListener('click', () => {
            fileInput.click();
        });

        // Handle file input change event
        fileInput.addEventListener('change', () => {
            handleFiles(fileInput.files);
        });

        function handleFiles(files) {
            if (files.length > 0) {
                // Optionally handle file previews here
                // For now, just trigger the form submission
                document.getElementById('payment-form').submit();
            }
        }
    </script>
</body>
</html>
