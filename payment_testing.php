<?php
session_start(); // Ensure the session is started

include('conn.php');

// Handle search booking code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_booking_code'])) {
    $search_booking_code = $_POST['search_booking_code'];
    $query = "SELECT booking_code FROM orders WHERE booking_code = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $search_booking_code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['booking_code'] = $search_booking_code;
        echo "<script>
                document.getElementById('search-form').classList.add('hidden');
                document.getElementById('payment-form').classList.remove('hidden');
                document.getElementById('booking_code').value = '$search_booking_code';
              </script>";
    } else {
        echo "<script>alert('Invalid booking code. Please try again.');</script>";
    }

    $stmt->close();
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_pembayaran'])) {
    if (isset($_SESSION['booking_code'])) {
        $booking_code = $_SESSION['booking_code'];
        $file_error = $_FILES['bukti_pembayaran']['error'];
        $file_type = strtolower(pathinfo($_FILES['bukti_pembayaran']['name'], PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'pdf'];

        if ($file_error === UPLOAD_ERR_OK) {
            if (in_array($file_type, $allowed_types)) {
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
                echo "<script>alert('Invalid file type. Please upload a JPG, JPEG, PNG, or PDF file.');</script>";
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

<!DOCTYPE html>
<html>
<head>
    <title>Upload Bukti Pembayaran</title>
    <style>
        body {
            font-family: sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .button-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .button {
            width: 48%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #0062cc;
        }

        .drop-area {
            border: 2px dashed #ccc;
            border-radius: 4px;
            padding: 20px;
            text-align: center;
            color: #333;
            margin-bottom: 20px;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .drop-area.hover {
            border-color: #007bff;
        }

        #file-input {
            display: none;
        }

        .instructions {
            font-size: 0.9em;
            color: #666;
            margin-top: -15px;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Bukti Pembayaran</h1>
        <?php if (isset($_SESSION['booking_code'])): ?>
        <form id="payment-form" action="" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="booking_code">Kode Booking</label>
                <input type="text" id="booking_code" name="booking_code" value="<?= $_SESSION['booking_code'] ?>" readonly>
            </div>
            <div class="form-group">
                <label for="bukti_pembayaran">Upload Bukti Pembayaran</label>
                <div id="drop-area" class="drop-area">
                    <p>Drag & Drop your file here or click to select</p>
                    <input type="file" id="file-input" name="bukti_pembayaran" accept=".jpg, .jpeg, .png, .pdf" required>
                </div>
                <p class="instructions">Accepted file types: JPG, JPEG, PNG, PDF</p>
            </div>
            <div class="button-group">
                <a href="process_ticket.php" class="button">Kembali</a>
                <button type="submit" class="button">Upload</button>
            </div>
        </form>
        <?php else: ?>
        <form id="search-form" action="" method="POST">
            <div class="form-group">
                <label for="search_booking_code">Kode Booking</label>
                <input type="text" id="search_booking_code" name="search_booking_code" required>
            </div>
            <div class="button-group">
                <button type="submit" class="button">Cari</button>
            </div>
        </form>
        <?php endif; ?>
    </div>

    <script>
        const dropArea = document.getElementById('drop-area');
        const fileInput = document.getElementById('file-input');

        // Prevent default drag behavior
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropArea.addEventListener(eventName, (event) => {
                event.preventDefault();
            });
        });

        // Add hover effect to drop area
        dropArea.addEventListener('dragover', () => {
            dropArea.classList.add('hover');
        });

        dropArea.addEventListener('dragleave', () => {
            dropArea.classList.remove('hover');
        });

        // Handle file selection
        dropArea.addEventListener('drop', (event) => {
            const files = event.dataTransfer.files;
            fileInput.files = files;
            dropArea.classList.remove('hover');
            updateDropArea(files);
        });

        dropArea.addEventListener('click', () => {
            fileInput.click();
        });

        fileInput.addEventListener('change', () => {
            const files = fileInput.files;
            updateDropArea(files);
        });

        function updateDropArea(files) {
            if (files.length > 0) {
                dropArea.innerHTML = `
                    <p>${files[0].name}</p>
                    <p>Size: ${Math.round(files[0].size / 1024)} KB</p>
                `;
            } else {
                dropArea.innerHTML = `<p>Drag & Drop your file here or click to select</p>`;
            }
        }
    </script>
</body>
</html>
