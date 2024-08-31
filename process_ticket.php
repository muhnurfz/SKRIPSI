<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Menyertakan file PHPMailer secara manual
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include('conn.php');

session_start(); // Start the session to use $_SESSION

date_default_timezone_set('Asia/Jakarta'); // Ensure the time zone is set

// Function to generate a unique booking code with route prefix
function generateUniqueBookingCode($conn, $route, $length = 8) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $unique = false;

    // Define route prefixes
    $prefixes = [
        'ponorogo' => 'POG-',
        'solo' => 'SSO-',
        'bojonegoro' => 'BJO-',
        'gemolong' => 'GML-'
    ];

    // Get the route prefix
    $prefix = isset($prefixes[$route]) ? $prefixes[$route] : '';

    while (!$unique) {
        $bookingCode = $prefix;
        for ($i = 0; $i < $length - strlen($prefix); $i++) {
            $bookingCode .= $characters[rand(0, $charactersLength - 1)];
        }

        // Check if the booking code already exists in the database
        $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE booking_code = ?");
        $stmt->bind_param("s", $bookingCode);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count == 0) {
            $unique = true;
        }
    }

    return $bookingCode;
}

// Function to check if selected seats are available
function areSeatsAvailable($conn, $selected_seats) {
    // Split the selected seats by comma
    $seats_array = explode(',', $selected_seats);

    // Prepare a SQL statement with placeholders
    $placeholders = implode(',', array_fill(0, count($seats_array), '?'));
    $sql = "SELECT COUNT(*) FROM orders WHERE status_pembayaran = 'pending' AND FIND_IN_SET(?, selected_seats) > 0";

    $stmt = $conn->prepare($sql);

    // Bind each seat to the query
    foreach ($seats_array as $seat) {
        $stmt->bind_param('s', $seat);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();

        // If any of the seats are already taken, return false
        if ($count > 0) {
            $stmt->close();
            return false;
        }
    }

    $stmt->close();
    return true;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form data
    $passenger_name = htmlspecialchars($_POST['passenger_name']);
    $passenger_phone = htmlspecialchars($_POST['passenger_phone']);
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; // Make email optional
    $departure = htmlspecialchars($_POST['departure']);
    $route = htmlspecialchars($_POST['route']);
    $destination = htmlspecialchars($_POST['destination']);
    $departure_date = htmlspecialchars($_POST['departure_date']);
    $selected_seats = htmlspecialchars($_POST['selected_seats']);

    // Generate a new unique booking code with route prefix
    $booking_code = generateUniqueBookingCode($conn, $route);

    // Calculate total tariff based on the selected route
    $tariffPerSeat = ($route === "ponorogo" || $route === "solo") ? 250000 : 210000;
    $seatCount = count(explode(',', $selected_seats));
    $total_tariff = $tariffPerSeat * $seatCount;

    // Create a DateTime object with the current date and time
    $now = new DateTime();
    // Get current date and time as purchase date
    $purchase_date = date('Y-m-d H:i:s');
    
    // Locale set to Indonesian
    setlocale(LC_TIME, 'id_ID.UTF-8');

    // Array of Indonesian day names
    $days_in_indonesian = [
        'Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'
    ];

    // Array of Indonesian month names
    $months_in_indonesian = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    // Get Indonesian day name
    $day_index = date('w', strtotime($departure_date));
    $day_name = $days_in_indonesian[$day_index];

    // Get Indonesian month name
    $month_index = date('n', strtotime($departure_date)) - 1;
    $month_name = $months_in_indonesian[$month_index];

    // Format date in Indonesian
    $formatted_date = $day_name . ', ' . date('d', strtotime($departure_date)) . ' ' . $month_name . ' ' . date('Y', strtotime($departure_date));
    // Format the purchase date in Indonesian
    $formatted_purchase_date = date('d', strtotime($purchase_date)) . ' ' . $month_name . ' ' . date('Y', strtotime($purchase_date));

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Check if seats are available
        if (!areSeatsAvailable($conn, $selected_seats)) {
            throw new Exception("Maaf ada yang lebih dahulu memesan kursi.");
        }

        // Proceed with the insertion
        $stmt = $conn->prepare("INSERT INTO orders (
            departure, route, destination, departure_date, passenger_name, passenger_phone, booking_code, selected_seats, total_tariff, purchase_date, status_pembayaran, email
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
        $stmt->bind_param("ssssssssiss", $departure, $route, $destination, $departure_date, $passenger_name, $passenger_phone, $booking_code, $selected_seats, $total_tariff, $purchase_date, $email);
        $stmt->execute();
        $stmt->close();

        // Commit transaction
        $conn->commit();

        // Calculate payment deadline (2 hours from purchase date)
        $deadline_datetime = new DateTime($purchase_date, new DateTimeZone('Asia/Jakarta'));
        $deadline_datetime->add(new DateInterval('PT2H'));
        $deadline = $deadline_datetime->format('d/m/Y H:i:s');

        $status_pembayaran = 'pending'; // Based on the status you are inserting
        // Map status to custom messages
        $status_messages = [
            'verified' => 'LUNAS', 
            'paid' => 'Menunggu verifikasi',
            'pending' => 'Menunggu Pembayaran',
            'cancelled' => 'Batal',
            'unknown' => 'Unknown Status'
        ];
        
        // Get the status message
        $status_message = isset($status_messages[$status_pembayaran]) ? $status_messages[$status_pembayaran] : 'Unknown Status';
        
        // Store data in session
        $_SESSION['booking_code'] = $booking_code;
        $_SESSION['passenger_name'] = $passenger_name;
        $_SESSION['passenger_phone'] = $passenger_phone;
        $_SESSION['email'] = $email; // Add email to session
        $_SESSION['departure'] = $departure;
        $_SESSION['formatted_date'] = $formatted_date;
        $_SESSION['route'] = $route;
        $_SESSION['destination'] = $destination;
        $_SESSION['departure_date'] = $departure_date;
        $_SESSION['selected_seats'] = $selected_seats;
        $_SESSION['tariff_per_seat'] = $tariffPerSeat;
        $_SESSION['seat_count'] = $seatCount;
        $_SESSION['total_tariff'] = $total_tariff;
        $_SESSION['deadline'] = $deadline;
        $_SESSION['status_message'] = $status_message;
        $_SESSION['purchase_date'] = $purchase_date;

        // Send confirmation email if email is provided
        if (!empty($email)) {
            $mail = new PHPMailer(true);
            try {
                // Server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com'; // Host SMTP Anda
                $mail->SMTPAuth = true;
                $mail->Username = 'customer.service@tiket.agungindahtrav.com'; // Alamat email pengirim
                $mail->Password = '1Customer.service'; // Password email pengirim
                $mail->SMTPSecure = 'ssl'; // Atau 'tls' jika menggunakan port 587
                $mail->Port = 465; // Gunakan port 465 untuk 'ssl' atau port 587 untuk 'tls'

                // Penerima dan pengirim
                $mail->setFrom('customer.service@tiket.agungindahtrav.com', 'Tiket Agung Indah Travel');
                $mail->addAddress($email);

                // Konten email
                $mail->isHTML(true);
                $mail->Subject = 'Konfirmasi Pemesanan Tiket';
                $mail->Body = '
                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Konfirmasi Pemesanan Tiket</title>
                    <style>
                        body {
                            font-family: sans-serif;
                            margin: 0;
                            padding: 0;
                            background-color: #f8f8f8;
                        }
                        .container {
                            width: 500px;
                            margin: 50px auto;
                            background-color: #fff;
                            border-radius: 10px;
                            padding: 20px;
                            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                        }
                        .divider {
                            height: 1px;
                            background-color: #eee;
                            margin-bottom: 5px;
                            border-bottom: 5px solid #eee;
                        }
                        h2 {
                            text-align: center;
                            margin-bottom: 20px;
                        }
                        .booking-details {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 10px;
                            padding: 10px;
                        }
                        .booking-details label {
                            font-weight: bold;
                            margin-right: 10px;
                        }
                        .booking-details span {
                            font-size: 14px;
                        }
                        .payment-details {
                            margin-bottom: 20px;
                        }
                        .payment-details .price,
                        .payment-details .total {
                            display: flex;
                            justify-content: space-between;
                            margin-bottom: 5px;
                        }
                        .payment-details .price {
                            font-weight: bold;
                            font-size: 16px;
                        }
                        .payment-details .total {
                            font-size: 16px;
                        }
                        .status {
                            padding: 5px 10px;
                            border-radius: 5px;
                            font-size: 12px;
                            text-align: center;
                            margin-top: 10px;
                        }
                        .paid {
                            background-color: #28a745;
                            color: white;
                        }
                        .unpaid {
                            background-color: #dc3545;
                            color: white;
                        }
                        .pending {
                            background-color: #ffc107;
                            color: black;
                        }
                    </style>
                </head>
                <body>
                    <div class="container">
                      <p><strong>Tanggal pembelian :</strong> <span>' . date('d/m/Y') . '</span></p>
                        <h2>Konfirmasi Pemesanan Tiket</h2>
                        <div class="booking-details">
                            <label>Kode Booking:</label>
                            <strong>' . htmlspecialchars($_SESSION['booking_code']) . '</strong>
                        </div>
                        <div class="divider"></div>
                        <div class="booking-details">
                            <label>Nama Penumpang:</label>
                            <span>' . htmlspecialchars($_SESSION['passenger_name']) . '</span>
                        </div>
                        <div class="booking-details">
                            <label>No Telepon:</label>
                            <span>' . htmlspecialchars($_SESSION['passenger_phone']) . '</span>
                        </div>
                        <div class="booking-details">
                            <label>Email:</label>
                            <span>' . htmlspecialchars($_SESSION['email']) . '</span>
                        </div>
                        <div class="booking-details">
                            <label>Tanggal Keberangkatan:</label>
                            <span>' . htmlspecialchars($_SESSION['formatted_date']) . '</span>
                        </div>
                        
                        <div class="booking-details">
                            <label>No Kursi:</label>
                            <span>' . htmlspecialchars($_SESSION['selected_seats']) . '</span>
                        </div>
                        <div class="divider"></div>
                        <div class="booking-details">
                            <label>Asal Keberangkatan:</label>
                            <span>' . htmlspecialchars($_SESSION['departure']) . '</span>
                        </div>
                        
                        <div class="booking-details">
                            <label>BUS:</label>
                            <span>' . htmlspecialchars($_SESSION['route']) . '</span>
                        </div>
                        <div class="booking-details">
                            <label>Kota Tujuan:</label>
                            <span>' . htmlspecialchars($_SESSION['destination']) . '</span>
                        </div>
                        <div class="divider"></div>
                        <div class="payment-details">
                            <div class="total">
                                <label>Tarif per Tiket:</label>
                                <span>Rp ' . number_format($_SESSION['tariff_per_seat'], 0, ',', '.') . '</span>
                            </div>
                            <div class="total">
                                <label>Total Tiket Dibeli:</label>
                                <span>' . htmlspecialchars($_SESSION['seat_count']) . '</span>
                            </div>
                            <div class="price">
                                <label>Total Pembayaran:</label>
                                <span>Rp ' . number_format($_SESSION['total_tariff'], 0, ',', '.') . '</span>
                            </div>
                            <div class="divider"></div>
                            <div>
                                <label>Lakukan Pembayaran Sebelum : </label>
                              <span style="color: red;">' . htmlspecialchars($_SESSION['deadline']) . '</span>
                              </div>
                              <div>
                              <span style="color: red;">Apabila anda tidak melakukan pembayaran sebelum waktu yang ditentukan, data anda akan terhapus otomatis</span>
                              </div>
                              <div>
                                <label>Status pembayaran anda :</label>
                               <span style="color: red;">' . htmlspecialchars($_SESSION['status_message']) . '</span>
                            </div>
                        </div>
                    </div>
                </body>
                </html>';

                $mail->send();
            } catch (Exception $e) {
                // Log or handle email sending errors if necessary
                error_log("Email could not be sent. Mailer Error: " . $mail->ErrorInfo);
            }
        }

        // Redirect to tanda tiket regardless of email status
        header('Location: tanda_tiket.php');
        exit(); // Ensure no further code is executed
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}

$conn->close();
?>
