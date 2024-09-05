<?php
include('conn.php');
session_start();


// Initialize variables
$tariffPerSeat = 0;
$seatCount = 0;
$total_tariff = 0;
$status_pembayaran = '';
$deadline = '';
$search_booking_code = ''; // Ensure this variable is defined

// Handle search booking code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_booking_code'])) {
    $search_booking_code = htmlspecialchars($_POST['search_booking_code']);
    $query = "SELECT departure, route, destination, departure_date, passenger_name, passenger_phone, selected_seats, total_tariff, status_pembayaran, purchase_date FROM orders WHERE booking_code = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $search_booking_code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($departure, $route, $destination, $departure_date, $passenger_name, $passenger_phone, $selected_seats, $total_tariff, $status_pembayaran, $purchase_date);
        $stmt->fetch();

        // Display notification based on payment status
        if ($status_pembayaran === 'verified') {
            echo "
            <div id='notification' style='
                position: fixed;
                top: 10px;
                right: 10px;
                background-color: #f8d7da;
                color: #721c24;
                border: 1px solid #f5c6cb;
                border-radius: 5px;
                padding: 15px;
                z-index: 1000;
                font-family: Arial, sans-serif;
                width: 300px;
                display: none;
                opacity: 1;
                transition: opacity 0.5s ease-out;
            '>
                <strong>Info:</strong> Kode booking ini sudah dibayarkan.
            </div>
            <script>
                function showNotification() {
                    var notification = document.getElementById('notification');
                    notification.style.display = 'block';
                    setTimeout(function() {
                        notification.style.opacity = '0';
                        setTimeout(function() {
                            notification.style.display = 'none';
                        }, 500);
                    }, 3000); // Display for 3 seconds
                }
                showNotification();
            </script>
            ";
            // Stop further processing if payment is already made
         
        } else {
            // Calculate the number of seats and the tariff per seat
            $seatCount = count(explode(',', $selected_seats));
            $tariffPerSeat = $total_tariff / $seatCount;

            // Calculate payment deadline (2 hours from purchase date)
            try {
                $deadline_datetime = new DateTime($purchase_date, new DateTimeZone('Asia/Jakarta'));
                $deadline_datetime->add(new DateInterval('PT2H'));
                $deadline = $deadline_datetime->format('d/m/Y H:i:s');
            } catch (Exception $e) {
                $deadline = 'Invalid date/time';
            }

            $_SESSION['booking_code'] = $search_booking_code;
        }
    } else {
        echo "
        <div id='notification' style='
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: #f8d7da; /* Light red background */
            color: #721c24; /* Dark red text for contrast */
            border: 1px solid #f5c6cb; /* Slightly darker red border */
            border-radius: 5px;
            padding: 15px;
            z-index: 1000;
            font-family: Arial, sans-serif;
            width: 300px;
            display: none;
            opacity: 1;
            transition: opacity 0.5s ease-out;
        '>
            <strong>Info:</strong> Maaf KODE BOOKING tidak ditemukan.
        </div>
        <script>
            function showNotification() {
                var notification = document.getElementById('notification');
                notification.style.display = 'block';
                setTimeout(function() {
                    notification.style.opacity = '0';
                    setTimeout(function() {
                        notification.style.display = 'none';
                    }, 500);
                }, 3000); // Display for 3 seconds
            }
            showNotification();
        </script>
        ";
    }

    $stmt->close();
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['bukti_pembayaran'])) {
    if (isset($_SESSION['booking_code'])) {
        $booking_code = $_SESSION['booking_code'];
        $file_error = $_FILES['bukti_pembayaran']['error'];
        $file_size = $_FILES['bukti_pembayaran']['size'];

        // Set maximum file size to 768KB
        $max_file_size = 768 * 1024; // 768KB in bytes

        if ($file_error === UPLOAD_ERR_OK) {
            if ($file_size > $max_file_size) {
                echo "<script>showError('File terlalu besar. Maksimal ukuran file yang diperbolehkan adalah 768KB.');</script>";
                exit;
            }

            // Get the file contents
            $file_contents = file_get_contents($_FILES['bukti_pembayaran']['tmp_name']);

              // Prepare the SQL query
              $query = "UPDATE orders SET bukti_pembayaran = ?, status_pembayaran = 'paid', tanggal_pembayaran = NOW() WHERE booking_code = ?";
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
                    echo "<script>showError('SQL Error: " . $stmt->error . "');</script>";
                }

                // Close the statement
                $stmt->close();
            } else {
                echo "<script>showError('Failed to prepare the SQL statement.');</script>";
            }
        } else {
            echo "<script>showError('Error uploading file. File terlalu besar. Maksimal ukuran file yang diperbolehkan adalah 768KB.');</script>";
        }
    } else {
        echo "<script>showError('No booking code found. Please start the booking process again.');</script>";
    }

    // Close the database connection
    $conn->close();
}
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
            background-color: #f0f4f8;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            
        }

        .container {
            background-color: #fff;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
            text-align: center;
            box-sizing: border-box; /* Include padding and border in the element's total width and height */
        }

        h1 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333;
        }

        .drop-area {
            border: 2px dashed #007BFF;
            padding: 40px;
            border-radius: 10px;
            transition: background-color 0.3s ease;
            background-color: #f9f9f9;
            cursor: pointer;
        }

        .drop-area:hover {
            background-color: #e0eaff;
        }

        .drop-area p {
            margin: 10px 0;
            font-size: 16px;
            color: #666;
        }

        .hidden {
            display: none;
        }

        .button, .submit-btn {
            display: block;
            padding: 12px 20px;
            margin: 10px auto;
            color: #fff;
            background-color: #007bff;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            text-align: center; /* Ensure text is centered */
            transition: background-color 0.3s, transform 0.2s;
            width: calc(100% - 40px); /* Adjust width to fit inside the container with padding */
            max-width: 250px;
            box-sizing: border-box; /* Include padding and border in the element's total width and height */
        }

        .button:hover, .submit-btn:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        
        .button2 {
            display: block;
            padding: 12px 20px;
            margin: 10px auto;
            color: #fff;
            background-color: #aaa;
            text-decoration: none;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s, transform 0.2s;
            width: 100%;
            max-width: 200px;
        }

        .button2:hover {
            background-color: #6e6e6e;
            transform: scale(1.05);
        }

       
        input[type="text"], input[type="file"] {
            width: calc(100% - 20px); /* Adjust width for input fields */
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; /* Include padding and border in the element's total width and height */
        }

        .no-rek {
            display: flex; /* Use flexbox to align items horizontally */
            align-items: center; /* Vertically center items */
            gap: 10px; /* Space between items */
            justify-content: center; /* Center items horizontally */
            margin: 10px auto; /* Center the container horizontally */
            max-width: 100%; /* Ensure container doesn't exceed screen width */
            overflow: hidden; /* Prevent overflow if content is too large */
        }

        .no-rek img {
            height: 2rem; /* Adjust height as needed using rem for responsive sizing */
            max-height: 100%; /* Ensure image scales correctly within the container */
        }

        .no-rek svg {
            width: 2rem; /* Adjust width as needed using rem */
            height: 2rem; /* Adjust height as needed using rem */
            cursor: pointer;
            transition: transform 0.2s;
        }

        @media (max-width: 600px) {
            .no-rek img {
                height: 1.5rem; /* Smaller size for mobile devices */
            }
            .no-rek svg {
                width: 1.5rem; /* Smaller size for mobile devices */
                height: 1.5rem; /* Smaller size for mobile devices */
            }
        }

        .label-value {
            display: flex;
            justify-content: space-between;
            font-family: Arial, sans-serif;
            margin-bottom: 10px;
        }

        .label {
            text-align: left;
            font-weight: bold;
            color: #333;
        }

        .value {
            flex: 1;
            text-align: right;
            font-family: Consolas, monospace;
            color: #666;
        }

        .divider {
            border-bottom: 1px solid #ccc;
            margin: 10px 0;
        }

        .center-text {
            text-align: center;
        }

        .error {
            color: red;
            font-size: 0.9em;
            margin-top: 10px;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            width: 300px;
            text-align: center;
        }

        .close-btn {
            float: right;
            font-size: 20px;
            cursor: pointer;
        }
         .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px;
        border-radius: 5px;
        color: white;
        z-index: 1000;
        transition: opacity 0.5s ease-in-out;
    }

    .notification.success {
        background-color: #28a745;
    }

    .notification.error {
        background-color: #dc3545;
    }

    .notification.fade-out {
        opacity: 0;
    }
    .toast {
    position: fixed;
    top: 20px; /* Position at the top */
    right: 20px; /* Position at the right */
    background-color: #28a745; /* Green background */
    color: #fff; /* White text */
    border-radius: 5px;
    padding: 15px 25px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    font-size: 16px;
    opacity: 0; /* Start hidden */
    visibility: hidden; /* Ensure it's not focusable */
    z-index: 1000;
    transition: opacity 0.5s ease, visibility 0.5s ease;
  }

  .toast.show {
    opacity: 1;
    visibility: visible;
    top: 20px; /* Adjust to control final position */
  }

  .toast.fade-out {
    opacity: 0;
    visibility: hidden;
  }
    </style>
</head>

<body>
<div id="errorModal" class="modal">
    <div class="modal-content">
        <span class="close-btn" onclick="closeErrorModal()">&times;</span>
        <p id="errorMessage"></p>
        <button class="button2" onclick="closeErrorModal()">Close</button>
    </div>
</div>

        
    <div class="container">
        <h1>Upload Bukti Pembayaran</h1>
        <form id="search-form" action="payment_form.php" method="POST" class="search-form">
            <input type="text" id="search_booking_code" name="search_booking_code" placeholder="Enter Booking Code" required>
            <input type="submit" value="Cari kode booking" class="button">
        </form>
        <?php if (isset($_SESSION['booking_code']) && $_SESSION['booking_code'] === $search_booking_code) { ?>
            <form id="payment-form" action="payment_form.php" method="POST" enctype="multipart/form-data" class="payment-form">
                <input type="hidden" id="booking_code" name="booking_code" value="<?php echo $search_booking_code; ?>">
       
                <p>Silahkan transfer ke rekening</p>
                <div class="no-rek">
        <img src="img/BANK.svg" alt="BRI">
        <!-- SVG button for copying the number -->
        <svg onclick="copyToClipboard()" alt="Copy Icon" id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 33.250000000002728 44.920000000000073">
            <rect x="8.810000000003129" y=".5" width="23.9399999999996" height="36.000000000001819" rx="3.869999999998981" ry="3.869999999998981" style="fill: #e5e5e5; stroke: #231f20; stroke-miterlimit: 10;"/>
            <rect x=".5" y="8.420000000000073" width="23.9399999999996" height="36" rx="3.869999999998981" ry="3.869999999998981" style="fill: #fff; stroke: #231f20; stroke-miterlimit: 10;"/>
        </svg>
    </div>
    
    
    <div class="payment-details">
                    <div class="label-value">
                        <span class="label">Nama penumpang</span>
                        <span class="value"><?php echo htmlspecialchars($passenger_name); ?></span>
                    </div>
                    <div class="label-value">
                        <span class="label">Tanggal keberangkatan</span>
                        <span class="value"><?php echo date('d/m/Y', strtotime($departure_date)); ?></span>
                    </div>
                    <div class="label-value">
                        <span class="label">Kota tujuan</span>
                        <span class="value"><?php echo htmlspecialchars($destination); ?></span>
                    </div>
                    <div class="label-value">
                        <span class="label">NO Kursi</span>
                        <span class="value"><?php echo htmlspecialchars($selected_seats); ?></span>
                    </div>
                    <div class="label-value">
                        <span class="label">Tarif per tiket</span>
                        <span class="value">Rp <?php echo number_format($tariffPerSeat, 0, ',', '.'); ?></span>
                    </div>
                    <div class="label-value">
                        <span class="label">Total tiket dibeli</span>
                        <span class="value"><?php echo htmlspecialchars($seatCount); ?></span>
                    </div>
                    <div class="label-value">
                        <span class="label">Total pembayaran</span>
                        <span class="value">Rp <?php echo number_format($total_tariff, 0, ',', '.'); ?></span>
                    </div>
                    <div class="divider"></div>
                    <div class="coupon-code">
                        <span class="label" style="color:red;">Lakukan Pembayaran Sebelum:</span>
                        <span class="value" style="color:red;"><?php echo $deadline; ?></span>
                    </div>
                </div>
                <div id="drop-area" class="drop-area">
                    <p>Klik di sini atau Drag & Drop file Anda</p>
                    <p>Hanya JPG, JPEG, PNG</p>
                    <div id="error-message" class="error hidden">Hanya JPG, JPEG, PNG files yang diperbolehkan.</div>
                </div>
                    <input type="file" id="file-input" name="bukti_pembayaran" accept=".jpg, .jpeg, .png" required>
               
                <input type="submit" value="Upload Bukti Pembayaran" class="submit-btn">
            </form>
        <?php } ?>
        <a href="index.php" class="button2">Kembali ke home</a>
    </div>

    <script>
    const dropArea = document.getElementById('drop-area');
const fileInput = document.getElementById('file-input');
const errorMessage = document.getElementById('error-message');

['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
    dropArea.addEventListener(eventName, preventDefaults, false);
});

function preventDefaults(e) {
    e.preventDefault();
    e.stopPropagation();
}

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

dropArea.addEventListener('drop', handleDrop, false);

function handleDrop(e) {
    let dt = e.dataTransfer;
    let files = dt.files;
    fileInput.files = files;
    handleFiles(files); // Ensure to handle files after drop
}


function copyToClipboard() {
    // Create a temporary input element
    const tempInput = document.createElement('input');
    tempInput.value = '082901023484536'; // The number to be copied
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand('copy');
    document.body.removeChild(tempInput);

    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = 'Nomor telah disalin ke clipboard!';
    document.body.appendChild(toast);

    // Use requestAnimationFrame to ensure styles are applied
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        toast.classList.add('show');
      });
    });

    // Add fade-out effect after 2 seconds
    setTimeout(() => {
      toast.classList.add('fade-out');
    }, 2000);

    // Remove toast from the DOM after fade-out
    setTimeout(() => {
      document.body.removeChild(toast);
    }, 2500);
  }



dropArea.addEventListener('click', () => {
    fileInput.click();
});

fileInput.addEventListener('change', () => {
    let files = fileInput.files;
    handleFiles(files);
    checkFileSize(); // Check file size on change
});

function handleFiles(files) {
    fileInput.files = files;
    validateFile(files[0]);
}

function validateFile(file) {
    const allowedTypes = ['image/jpeg', 'image/png'];
    if (file && !allowedTypes.includes(file.type)) {
        errorMessage.classList.remove('hidden');
        fileInput.value = ''; // Clear the input
    } else {
        errorMessage.classList.add('hidden');
    }
}

function showError(message) {
    document.getElementById('errorMessage').textContent = message;
    document.getElementById('errorModal').style.display = 'flex';
}

function closeErrorModal() {
    document.getElementById('errorModal').style.display = 'none';
}

function checkFileSize() {
    var file = fileInput.files[0];
    if (file && file.size > 768 * 1024) { // 768KB in bytes
        showError('File terlalu besar. Maksimal ukuran file yang diperbolehkan adalah 768KB.');
        fileInput.value = ''; // Clear the file input
    }
}

    </script>
</body>

</html>
