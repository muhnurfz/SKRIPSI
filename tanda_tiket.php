<?php
session_start();

// Check if session variables are set
if (!isset($_SESSION['booking_code'])) {
    // Output the HTML structure for the error message with inline CSS
    echo '<div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; text-align: center; margin: 20px; font-family: Arial, sans-serif;">
            <p><strong>SESSION TELAH BERAKHIR</strong></p>
            <p>Untuk melihat pesanan sebelumnya silahkan cek email</p>
            <p>atau buat pesanan baru untuk melanjutkan.</p>
          </div>
          <div style="
        display: flex;
        justify-content: center;
        align-items: center;
        height: 10vh; /* Full viewport height */
        background-color: #f8f8f8; /* Optional background color */
    ">
        <a href="index.php" style="text-decoration: none;">
            <button style="
                background-color: #aaa; /* Grey */
                color: #fff; /* White text */
                border: none;
                border-radius: 5px;
                padding: 10px 20px;
                cursor: pointer;
                font-size: 16px;
                transition: background-color 0.3s;
            " 
            onmouseover="this.style.backgroundColor=\'#6e6e6e\'" 
            onmouseout="this.style.backgroundColor=\'#aaa\'">
                Kembali
            </button>
        </a>
      </div>';
    exit();
}

// Retrieve session variables
$purchase_date = $_SESSION['purchase_date'];
$booking_code = $_SESSION['booking_code'];
$passenger_name = $_SESSION['passenger_name'];
$passenger_phone = $_SESSION['passenger_phone'];
$email = $_SESSION['email'];
$departure = $_SESSION['departure'];
$route = $_SESSION['route'];
$destination = $_SESSION['destination'];
$departure_date = $_SESSION['departure_date'];
$selected_seats = $_SESSION['selected_seats'];
$pnp_dewasa = $_SESSION['pnp_dewasa'];   
$pnp_balita = $_SESSION['pnp_balita'];
$tariff_per_seat = $_SESSION['tariff_per_seat'];
$seat_count = $_SESSION['seat_count'];
$total_tariff = $_SESSION['total_tariff'];
$deadline = $_SESSION['deadline'];

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
$purchase_day_name = $days_in_indonesian[$day_index];

// Get Indonesian month name
$month_index = date('n', strtotime($departure_date)) - 1;
$month_name = $months_in_indonesian[$month_index];

// Format date in Indonesian
$formatted_date = $day_name . ', ' . date('d', strtotime($departure_date)) . ' ' . $month_name . ' ' . date('Y', strtotime($departure_date));
// Format the purchase date in Indonesian
$formatted_purchase_date = date('d', strtotime($purchase_date)) . ' ' . $month_name . ' ' . date('Y', strtotime($purchase_date));

// Set the payment status
$status_pembayaran = 'pending'; // Based on the status you are inserting
$passenger_phone = htmlspecialchars($passenger_phone); // Extract from the span
// Step 1: Remove non-numeric characters
$clean_phone = preg_replace('/[^0-9]/', '', $passenger_phone);

// Step 2: Remove leading zero and add +62
if (substr($clean_phone, 0, 1) === '0') {
    $formatted_phone = '+62' . substr($clean_phone, 1);
} else {
    $formatted_phone = '+62' . $clean_phone;
}


// Map status to custom messages
$status_messages = [
    'pending' => 'Menunggu Pembayaran',
    'paid' => 'Lunas',
    'cancelled' => 'Batal'
];

// Get the status message
$status_message = isset($status_messages[$status_pembayaran]) ? $status_messages[$status_pembayaran] : 'Unknown Status';

// Clear session variables
session_unset();
?>
<!DOCTYPE html>
        <html lang="en">
        <head>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Tanda terima pesanan</title>
            <style>
                body {
                    font-family: sans-serif;
                    margin: 0;
                    padding: 0;
                    background-color: #f8f8f8;
                }
                                /* Style for the session expired message */
                .session-expired-message {
                    background-color: #f8d7da; /* Light red background */
                    color: #721c24; /* Dark red text */
                    border: 1px solid #f5c6cb; /* Red border */
                    padding: 15px;
                    border-radius: 5px;
                    text-align: center;
                    margin: 20px;
                    font-family: Arial, sans-serif; /* Optional: Change to your preferred font */
                }

                .session-expired-message p {
                    margin: 10px 0;
                }

                .session-expired-message strong {
                    display: block;
                    font-size: 18px; /* Slightly larger font size for emphasis */
                }
                @media (max-width: 600px) {
                    .session-expired-message {
                        font-size: 14px;
                        padding: 10px;
                        margin: 10px;
                    }
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
                    margin-bottom : 5px;
                    
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
                .booking-details span {
                font-family: Consolas, 'Courier New', monospace;
               
                }
                .booking-details label {
                    font-weight: bold;
                    margin-right: 10px;
                }

                .booking-details span {
                    font-size: 14px;
                }

                .route {
                    margin-bottom: 10px;
                     justify-content: space-between;
                }

                .route .departure-time,
                .route .arrival-time {
                    display: flex;
                    align-items: center;
                    margin-bottom: 5px;
                     justify-content: space-between;
                }

                .route .departure-time .icon,
                .route .arrival-time .icon {
                    margin-right: 10px;
                     justify-content: space-between;
                }

                .route .departure-time .icon {
                    width: 10px;
                    height: 10px;
                    border-radius: 50%;
                    background-color: #ccc;
                    margin-right: 10px;
                     justify-content: space-between;
                }

                .route .arrival-time .icon {
                    width: 10px;
                    height: 10px;
                    border-radius: 50%;
                    background-color: gold;
                    margin-right: 10px;
                     justify-content: space-between;
                }

                .passenger {
                    margin-bottom: 10px;
                     justify-content: space-between;
                    
                }

                .passenger .passenger-name {
                    font-weight: bold;
                    margin-bottom: 5px;
                     justify-content: space-between;
                }

                .passenger .phone-number {
                    margin-bottom: 5px;
                     justify-content: space-between;
                }

                .payment-details {
                    margin-bottom: 20px;
                     justify-content: space-between;
                }

                .payment-details .price {
                    font-weight: bold;
                    font-size: 16px;
                     justify-content: space-between;
                }

                .payment-details .total {
                    font-size: 16px;
                }

                .payment-details .price,
                .payment-details .total {
                display: flex;
                justify-content: space-between;
                margin-bottom: 5px;
                }

                .buttons {
    display: flex;
    gap: 10px; /* Adjust the space between buttons */
}

.whatsapp-button {
    display: inline-flex;
    align-items: center;
    background-color: #25D366; /* WhatsApp green color */
    color: white;
    text-decoration: none;
    border-radius: 5px;
    padding: 10px 20px;
    font-size: 16px;
    border: none;
    transition: background-color 0.3s;
}

.whatsapp-button:hover {
    background-color: #1ebe5f; /* Darker green on hover */
}

.whatsapp-button .bi-whatsapp {
    font-size: 20px; /* Adjust icon size */
    margin-right: 8px; /* Space between icon and text */
}

.button {
    text-decoration: none; /* Remove underline from links */
}

.btn {
    padding: 10px 20px;
    font-size: 16px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s, color 0.3s;
}

.btn-secondary {
    background-color: #6c757d; /* Bootstrap secondary color */
    color: white;
    border: none;
}

.btn-secondary:hover {
    background-color: #5a6268; /* Darker gray on hover */
}

.btn-primary {
    background-color: #007bff; /* Bootstrap primary color */
    color: white;
    border: none;
}

.btn-primary:hover {
    background-color: #0056b3; /* Darker blue on hover */
}

            
            .paid {
            background-color: #28a745; /* Green */
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            text-align: center;
            }
            .unpaid {
                background-color: #dc3545; /* Red */
                color: white;
                padding: 5px 10px;
                border-radius: 5px;
                font-size: 12px;
                text-align: center;
            }
            .pending {
                background-color: #ffc107; /* Yellow */
                color: black;
                padding: 5px 10px;
                border-radius: 5px;
                font-size: 12px;
                text-align: center;
            }
            .notification {
    position: fixed;
    top: 20px;
    right: 20px;
    background-color: #28a745; /* Green */
    color: white;
    padding: 15px;
    border-radius: 5px;
    font-size: 16px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    opacity: 1;
    transition: opacity 0.5s ease;
}

.notification.success {
    background-color: #28a745; /* Green */
}

            </style>

            <script>
           function copyBookingCode() {
    var bookingCode = document.getElementById("booking-number").innerText;
    navigator.clipboard.writeText(bookingCode).then(function() {
        showSuccessMessage("Berhasil copy KODE BOOKING: " + bookingCode);
    }, function(err) {
        console.error("Gagal copy BOOKING CODE: ", err);
    });
}

function showSuccessMessage(message) {
    var notification = document.createElement('div');
    notification.className = 'notification success';
    notification.innerText = message;

    document.body.appendChild(notification);

    setTimeout(function() {
        notification.style.opacity = 0;
        setTimeout(function() {
            notification.remove();
        }, 500);
    }, 3000);
}

            </script>

        </head>
        <body>
            <div class="container">
            <span class="data-label">Tanggal Pembelian :</span> <?php echo $formatted_purchase_date; ?>
                <h2>Tanda terima pesanan</h2>
                  <div class="booking-details">
                  
                    <label for="booking-number">Booking Number:</label>
                    <strong><span id="booking-number"><?php echo htmlspecialchars($booking_code); ?></span></strong>
                   
                    <svg onclick="copyBookingCode()" style="width: 20px; height: 20px; cursor: pointer; transition: transform 0.2s;" alt="Copy Icon" id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 33.250000000002728 44.920000000000073">
                    <rect x="8.810000000003129" y=".5" width="23.9399999999996" height="36.000000000001819" rx="3.869999999998981" ry="3.869999999998981" style="fill: #e5e5e5; stroke: #231f20; stroke-miterlimit: 10;"/>
                    <rect x=".5" y="8.420000000000073" width="23.9399999999996" height="36" rx="3.869999999998981" ry="3.869999999998981" style="fill: #fff; stroke: #231f20; stroke-miterlimit: 10;"/>
                    </svg>
            </button>
                </div>

                <div class="divider"></div>

                  <div class="booking-details">
                    <label for="passenger">Nama penumpang :</label>
                    <span id="passenger-name"><?php echo htmlspecialchars($passenger_name); ?></span>
                </div>

                  <div class="booking-details">
                  <label for="phone-number">No telepon penumpang :</label>
                  <span id="phone-number"><?php echo htmlspecialchars($passenger_phone); ?></span>
                </div>

                <div class="booking-details">
                  <label for="phone-number">E-mail penumpang :</label>
                  <span id="phone-number"><?php echo htmlspecialchars($email); ?></span>
                </div>

                <div class="booking-details">
                <label for="pnp-dewasa">Jumlah Penumpang Dewasa:</label>
                <span id="pnp-dewasa"><?php echo htmlspecialchars($pnp_dewasa); ?></span>
                </div>

                <div class="booking-details">
                <label for="pnp-balita">Jumlah Penumpang Balita:</label>
                <span id="pnp-balita"><?php echo htmlspecialchars($pnp_balita); ?></span>
                </div>
                
                <div class="booking-details">
                  <label for="phone-number">Tanggal keberangkatan :</label>
                  <span id="departure"><?php echo $formatted_date; ?></span>
                </div>

                <div class="booking-details">
                   <label for="seat">No kursi :</label>
                   <span id="selected-seats"><?php echo htmlspecialchars($selected_seats); ?></span>
                </div>
                 <div class="divider"></div>
                 <div class="booking-details">
                   <label for="seat">Asal Keberangkatan :</label>
                   <span id="departure"><?php echo htmlspecialchars($departure); ?></span>
                </div>
                
                 <div class="booking-details">
                   <label for="seat">BUS :</label>
                   <span id="route"><?php echo htmlspecialchars($route); ?></span>
                </div>

                <div class="booking-details">
                   <label for="seat">Kota tujuan : </label>
                   <span id="destination"><?php echo htmlspecialchars($destination); ?></span>
                </div>                
                 <div class="divider"></div>
                <div class="payment-details">
                    <div class="total">
                        <label for="price-per-ticket">Tarif per tiket :</label>
                        <span id="price-per-ticket">Rp <?php echo number_format($tariff_per_seat, 0, ',', '.'); ?></span>
                    </div>
                    
                    <div class="total">
                        <label for="total-tickets">Total tiket dibeli :  </label>
                        <span id="total-tickets"><?php echo htmlspecialchars($seat_count); ?></span>
                    </div>
                   
                    <div class="price">
                        <label for="total-payment">Total pembayaran : </label>
                        <span id="total-payment">Rp <?php echo number_format($total_tariff, 0, ',', '.'); ?></span>
                    </div>
                      <div class="divider"></div>
                      <div class="<?php echo $status_pembayaran; ?>"><?php echo $status_message; ?></div>
                      <div class="coupon-code" style="color: red; padding: 10px 0px 10px 0px"><span class="data-label">Lakukan Pembayaran Sebelum:</span>    <?php echo htmlspecialchars($deadline); ?></div>
                      <span style="color: red; padding: 10px 0px 10px 0px">Apabila anda tidak melakukan pembayaran sebelum waktu yang ditentukan, data anda akan terhapus otomatis</span>
                </div>
               
                <div class="buttons">
                    <a class="button" href="index.php">
                        <button id="cancel-button" class="btn btn-secondary">Kembali</button>
                    </a>
                    <a class="whatsapp-button" href="https://wa.me/<?php echo $formatted_phone; ?>?text=Booking%20Code:%20<?php echo urlencode($booking_code); ?>%0ANama%20penumpang:%20<?php echo urlencode($passenger_name); ?>%0ANo%20telepon%20penumpang:%20<?php echo urlencode($passenger_phone); ?>%0ANo%20kursi:%20<?php echo urlencode($selected_seats); ?>%0AAsal%20Keberangkatan:%20<?php echo urlencode($departure); ?>%0ABUS:%20<?php echo urlencode($route); ?>%0AKota%20tujuan:%20<?php echo urlencode($destination); ?>%0ATarif%20per%20tiket:%20Rp%20<?php echo urlencode(number_format($tariff_per_seat, 0, ',', '.')); ?>%0ATotal%20tiket%20dibeli:%20<?php echo urlencode($seat_count); ?>%0ATotal%20pembayaran:%20Rp%20<?php echo urlencode(number_format($total_tariff, 0, ',', '.')); ?>%0ALakukan%20Pembayaran%20Sebelum:%20<?php echo urlencode($deadline); ?>" target="_blank">
                    <i class="bi bi-whatsapp"></i> Kirim ke WhatsApp
                </a>

    <a class="payment-link" href="payment_form.php?booking_code=<?php echo urlencode($booking_code); ?>">
        <button id="payment-button" class="btn btn-primary">Lanjut ke pembayaran</button>
    </a>
</div>

        </body>
        </html>