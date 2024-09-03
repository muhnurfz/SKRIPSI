<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Menyertakan file PHPMailer secara manual
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

include('conn.php');


date_default_timezone_set('Asia/Jakarta'); // Ensure the time zone is set
// Get the inserted ID from the GET parameter
$inserted_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($inserted_id > 0) {
    // Retrieve booking details from the database
    $query = "SELECT * FROM orders WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $inserted_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $passenger_name = $row['passenger_name'];
        $passenger_phone = isset($row['passenger_phone']) ? $row['passenger_phone'] : 'Not Provided';
        $email = $row['email'];
        $departure = $row['departure'];
        $destination = $row['destination'];
        $departure_date = $row['departure_date'];
        $selected_seats = $row['selected_seats'];
        $seatCount = count(explode(',', $selected_seats));
        $booking_code = $row['booking_code'];
        $bus_code = isset($row['bus_code']) ? $row['bus_code'] : ''; // Add this line
        $total_tariff = $row['total_tariff'];
        $status_pembayaran = $row['status_pembayaran'];
        $pnp_dewasa = isset($row['pnp_dewasa']) ? intval($row['pnp_dewasa']) : 0;
        $pnp_balita = isset($row['pnp_balita']) ? intval($row['pnp_balita']) : 0;
    } else {
        $booking_code = "Not Found";
        $bus_code = ''; // Add this line
        $status_pembayaran = 'unknown'; // Default value for status if no booking found
        $seatCount = 0;
        $tariffPerSeat = 0;
        $total_tariff = 0;
        $pnp_dewasa = 0; // Default value for pnp_dewasa if no booking found
        $pnp_balita = 0; // Default value for pnp_balita if no booking found
    }

    $stmt->close();
} else {
    $booking_code = "Invalid ID";
    $status_pembayaran = 'unknown'; // Default value for status if invalid ID
    $seatCount = 0;
    $tariffPerSeat = 0;
    $total_tariff = 0;
}

$departure = htmlspecialchars($departure);

$phone_number = "";
$address = "";

switch ($departure) {
    case "Balaraja":
        $phone_number = "0857 8463 128";
        $address = "Jl. Raya Serang No.KM 25,8 Kec. Balaraja, Kabupaten Tangerang, Banten 15610";
        break;
    case "BSD Serpong":
        $phone_number = "0813 1808 8343 | 0813 8696 5060 | 0877 8300 8222";
        $address = "JL Raya Serpong KM 7 NO 60 Tangerang Selatan 15320";
        break;
    case "Samsat BSD":
        $phone_number = "0811 1000 434";
        $address = "JL Raya Pahlawan Seribu (Dekat SPBU Shell) Tangerang selatan 15321";
        break;
    case "Cilenggang":
        $phone_number = "0852 8761 6267";
        $address = "JL Raya Serpong KM 21 Kel Cilenggang Tangerang Selatan";
        break;
    default:
        $phone_number = "Tidak tersedia";
        $address = "Alamat tidak tersedia";
        break;
}


// Status messages
$status_messages = [
    'verified' => 'LUNAS', 
    'paid' => 'Menunggu verfikasi',
    'pending' => 'Menunggu Pembayaran',
    'cancelled' => 'Batal',
    'unknown' => 'Unknown Status'
];

// Get the status message
$status_message = isset($status_messages[$status_pembayaran]) ? $status_messages[$status_pembayaran] : $status_messages['unknown'];

// Determine the CSS class for the status
$status_class = [
    'verified' => 'verified', 
    'paid' => 'pending',
    'cancelled' => 'unpaid',
    'unknown' => 'unpaid'
][$status_pembayaran] ?? 'unpaid';

// Initialize message content
$message = "";

// Format passenger phone number
$passenger_phone = htmlspecialchars($passenger_phone); // Extract from the span

// Step 1: Remove non-numeric characters
$clean_phone = preg_replace('/[^0-9]/', '', $passenger_phone);

// Step 2: Remove leading zero and add +62
if (substr($clean_phone, 0, 1) === '0') {
    $formatted_phone = '+62' . substr($clean_phone, 1);
} else {
    $formatted_phone = '+62' . $clean_phone;
}

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
  $mail->isHTML(false);
  $mail->Subject = 'Konfirmasi Pemesanan Tiket';
  $mail->isHTML(true); // Set email format to HTML

  $mail->Body = '
  <!DOCTYPE html>
  <html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <style>
          body {
              font-family: Arial, sans-serif;
              background-color: #f2f2f2;
              margin: 0;
              padding: 0;
          }
          .container {
              background-color: white;
              padding: 20px;
              border-radius: 10px;
              box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
              max-width: 600px;
              margin: 20px auto;
              text-align: center;
          }
          h2 {
              color: #333;
              margin-bottom: 20px;
          }
          .success-message {
              font-size: 18px;
              color: #4CAF50;
              margin-bottom: 20px;
          }
          .ticket {
              border: 1px solid #ddd;
              padding: 20px;
              background-color: white;
              border-radius: 10px;
              margin-top: 20px;
          }
          .ticket-header {
              display: flex;
              justify-content: space-between;
              align-items: center;
              border-bottom: 1px solid #ddd;
              padding-bottom: 10px;
              margin-bottom: 20px;
          }
          .ticket-header img {
              width: 100px;
          }
          .ticket-header h2 {
              margin: 0;
              font-size: 24px;
          }
          .ticket-content {
              text-align: left;
          }
          .ticket-content p {
              margin: 5px 0;
          }
          .ticket-table {
              width: 100%;
              border-collapse: collapse;
              margin-top: 20px;
          }
          .ticket-table th, .ticket-table td {
              border: 1px solid #ddd;
              padding: 10px;
              text-align: left;
          }
          .ticket-table th {
              background-color: #f9f9f9;
          }
          .status {
              display: inline-block;
              padding: 5px 10px;
              border-radius: 4px;
              font-size: 12px;
              text-align: center;
              color: #fff;
          }
          .verified {
              background-color: #28a745; /* Green */
              color: white;
          }
          .paid {
              background-color: #4cbccc; /* Light Blue */
              color: white;
          }
          .unpaid {
              background-color: #dc3545; /* Red */
              color: white;
          }
          .pending {
              background-color: #ffc107; /* Yellow */
              color: black;
          }
          .contact-info {
              margin-top: 20px;
              padding: 10px;
              background-color: #f9f9f9;
              border: 1px solid #ddd;
              border-radius: 12px;
              text-align: center;
          }
          .contact-info p {
              margin: 0;
              font-size: 12px;
              color: #333;
          }
          .phone-number {
              color: #007bff;
              font-weight: bold;
              font-size: 18px;
          }
          .divider {
              border: 1px solid #ddd;
              margin: 20px 0;
              background-color: #fff;
              height: 1px;
              box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
          }
          .voucher-eat, .boarding-pass {
              padding: 10px;
              margin: 10px 0;
              border-radius: 8px;
              border-collapse: collapse;
          }
          table {
              width: 100%;
              border-collapse: collapse;
          }
          th, td {
              border: 1px solid #ddd;
              padding: 8px;
          }
          th {
              text-align: left;
              background-color: #f4f4f4;
          }
      </style>
  </head>
  <body>
      <div class="container">
          <h2>Pembayaran berhasil!</h2>
          <div class="success-message">
              Terima kasih atas pembayarannya. Pesanan anda dengan kode booking <strong>' . htmlspecialchars($booking_code) . '</strong> telah terkonfirmasi.
          </div>
          <p><strong>Tanggal pembelian :</strong> <span>' . date('d/m/Y') . '</span></p>
          <div class="ticket">
              <div class="ticket-header">
                  <h2>TIKET PENUMPANG</h2>
                  <strong><p style="color:#00008B;">PO LAJU PRIMA</p></strong>
              </div>
              <div class="ticket-content">
                <div><strong>Nama penumpang:</strong> ' . htmlspecialchars($passenger_name) . '</div>
                <div><strong>Tanggal keberangkatan:</strong> ' . date('d/m/Y', strtotime($departure_date)) . '</div>
                <div><strong>NO telepon:</strong> ' . htmlspecialchars($formatted_phone) . '</div>
                <div><strong>Email penumpang :</strong> ' . htmlspecialchars($email) . '</div>
                <div><strong>Penumpang Dewasa:</strong> <?php echo intval($pnp_dewasa); ?></div>
                <div><strong>Penumpang Balita/Anak 0-2 tahun:</strong> <?php echo intval($pnp_balita); ?></div>
                  <table class="ticket-table">
                      <tr>
                          <th>Asal keberangkatan</th>
                          <th>Kota tujuan</th>
                          <th>NO Kursi</th>
                          <th>NO Body BUS</th>
                      </tr>
                      <tr>
                          <td>' . htmlspecialchars($departure) . '</td>
                          <td>' . htmlspecialchars($destination) . '</td>
                          <td>' . htmlspecialchars($selected_seats) . '</td>
                          <td>' . (isset($bus_code) ? htmlspecialchars($bus_code) : '') . '</td>
                      </tr>
                  </table>
                  <strong><p style="color:#d1191c;">UNTUK MENDAPAT NO BODY BUS HARAP CHECK-IN TIKET DI AGEN</p></strong>
                  <table class="ticket-table">
                      <tr>
                          <th>Kode booking</th>
                          <td>' . htmlspecialchars($booking_code) . '</td>
                      </tr>
                      <tr>
                          <th>Status Pembayaran</th>
                          <td><div class="status ' . htmlspecialchars($status_class) . '">' . htmlspecialchars($status_message) . '</div></td>
                      </tr>
                      <tr>
                          <th>Total tiket dibeli</th>
                          <td>' . htmlspecialchars($seatCount) . '</td>
                      </tr>
                      <tr>
                          <th>Total pembayaran</th>
                          <td>Rp ' . number_format($total_tariff, 0, ',', '.') . '</td>
                      </tr>
                  </table>
                  <p>Untuk informasi lebih lanjut silahkan akses halaman cetak tiket di <a href="https://tiket.agungindahtrav.com/cari_tiket.php" target="https://tiket.agungindahtrav.com/cari_tiket.php" style="color: #007bff; text-decoration: none;">tiket.agungindahtrav.com</a></p>
              </div>
          </div>
         
      </div>
  </body>
  </html>
  ';
  $mail->send();
}
catch (Exception $e) {
   
}

// Prepare message content for WhatsApp
$message .= "Tanggal pembelian: " . date('d/m/Y') . "\n";
$message .= "Nama penumpang: $passenger_name\n";
$message .= "Tanggal keberangkatan: " . date('d/m/Y', strtotime($departure_date)) . "\n";
$message .= "NO telepon: $formatted_phone\n";
$message .= "Asal keberangkatan: $departure\n";
$message .= "Kota tujuan: $destination\n";
$message .= "NO Kursi: $selected_seats\n";
$message .= "Kode booking: $booking_code\n";
$message .= "Status Pembayaran: $status_message\n";
$message .= "Total tiket dibeli: $seatCount\n";
$message .= "Total pembayaran: Rp " . number_format($total_tariff, 0, ',', '.') . "\n";
$message .= "UNTUK MENDAPAT NO BODY BUS HARAP CHECK-IN TIKET DI AGEN";

// Encode message for URL
$encoded_message = urlencode($message);

// Close connection
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;

        }
        .container {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 600px;
            text-align: center;
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .success-message {
            font-size: 18px;
            color: #4CAF50;
            margin-bottom: 20px;
        }
                /* General container styling */
        .action-buttons {
            display: flex; /* Use flexbox for alignment */
            justify-content: center; /* Center align the buttons */
            gap: 10px; /* Add spacing between buttons */
            margin-top: 20px;
        }

        /* Back link styling */
        .back-link, .whatsapp-button, .reprint-button {
            display: inline-flex; /* Use inline-flex to align items */
            align-items: center; /* Center align items vertically */
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 5px;
            text-decoration: none; /* Remove underline for links */
            transition: all 0.3s ease;
        }

        .back-link {
            color: #007bff;
            border: 2px solid #007bff;
        }

        .back-link:hover {
            background-color: #007bff;
            color: #fff;
        }

        /* WhatsApp button styling */
        .whatsapp-button {
            color: #fff;
            background-color: #25D366; /* WhatsApp green */
            border: none;
        }

        .whatsapp-button:hover {
            background-color: #1EBEA5; /* Darker green */
        }

        .whatsapp-button i {
            margin-right: 8px;
        }

        /* Re-print button styling */
        .reprint-button {
            color: #fff;
            background-color: #007bff; /* Primary color */
            border: none;
            cursor: pointer;
        }

        .reprint-button:hover {
            background-color: #0056b3; /* Darker blue */
        }


        .ticket {
            border: 1px solid black;
            padding: 20px;
            background-color: white;
            border-radius: 10px;
            margin-top: 20px;
        }
        .ticket-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid black;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .ticket-header img {
            width: 100px;
        }
        .ticket-header h2 {
            margin: 0;
            font-size: 24px;
        }
        .ticket-content {
            text-align: left;
        }
        .ticket-content p {
            margin: 5px 0;
        }
        .ticket-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .ticket-table th, .ticket-table td {
            border: 1px solid black;
            padding: 10px;
            text-align: left;
        }
        .ticket-table th {
            background-color: #f9f9f9;
        }
        .label-value {
            display: flex;
            justify-content: space-between;
            font-family: monospace;
        }
        .label {
            width: 180px; /* Adjust based on your needs */
            text-align: left;
            font-family: Arial, sans-serif;
        }
        .value {
            flex: 1;
            text-align: left;
            font-family: Consolas, monospace;
        }
        /* .divider {
            border-top: 5px dotted black; /* Creates a dotted line at the top of the element */
           /* width: 100%; /* Full width or adjust as needed */
           /* margin: 20px 0; /* Spacing above and below the line */
      /*  } */
              .voucher-eat, .boarding-pass {
            padding: 10px;
            margin: 10px 0;
            border-radius: 8px;
            width: 100%;
            border-collapse: collapse;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
        }

        th {
            text-align: left;
            background-color: #f4f4f4;
        }

        p {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
            color: #d1191c; /* Warna judul */
        }
        .departure-info {
        font-weight: bold;
        font-size: 18px;
        margin-bottom: 10px;
        }

        .phone-number {
        color: #337ab7;
        font-size: 16px;
        margin-bottom: 10px;
        }

        .address-info {
        font-size: 16px;
        color: #666;
        }

        i.fas {
        margin-right: 10px;
        }
        
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            text-align: center;
            color: #fff;
        }
        
        .verified {
            background-color: #28a745; /* Green */
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            text-align: center;
        }
        .paid {
            background-color:  #4cbccc; 
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
            text-align: center.
        }
        .pending {
            background-color: #ffc107; /* Yellow */
            color: black;   
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            text-align: center.
        }
          .contact-info {
            max-width: 600px; 
            margin: 10px 0px 10px 0px;
            padding: 15px; /* Reduced padding */
            border: 1px solid black;
            border-radius: 8px;
            background-color: #f9f9f9;
            text-align: center; /* Center align text */
        }
        .contact-info p {
            margin: 5px 0; /* Reduced margins */
            font-size: 14px; /* Smaller font size */
            color: #333;
            line-height: 1.4;
        }
        .contact-info i {
            margin-right: 5px; /* Reduced icon margin */
            color: #007bff;
        }
        .contact-info .departure-info strong {
            display: block; /* Make the label block-level for better alignment */
            font-size: 16px; /* Slightly larger font size for labels */
            color: #333;
            margin-bottom: 5px; /* Space between label and value */
        }
        .phone-number a, .address-info a {
            color: #007bff;
            text-decoration: none;
        }
        .phone-number a:hover, .address-info a:hover {
            text-decoration: underline;
        }

        .php-output {
            font-family: Consolas, monospace;
        }
@media print {
    @page {
        size: A4;
        margin: 0;
    }

    body {
        visibility: hidden;
    }
    .ticket {
    border: 1px solid black; /* Mengatur ketebalan border menjadi 5px dan warna border hitam */
    visibility: visible;
    max-width: 60%; /* Maksimal lebar elemen adalah 60% dari halaman */
    height: auto;
    box-sizing: border-box;
    margin: -220px 0px 0px -80px;
    position: absolute;
    left: 0;
    top: 0;
    overflow: hidden;
    transform: scale(0.6); /* Menyesuaikan skala agar muat dalam satu halaman */
    page-break-inside: avoid; /* Mencegah pemotongan konten */ 
}
}
   </style>
</head>
<body>
    <div class="container">
        <h2>Pembayaran berhasil!</h2>
        <div class="success-message">
            Terima kasih atas pembayarannya. Pesanan anda dengan kode booking <strong><span class="php-output"><?php echo htmlspecialchars($booking_code); ?></span></strong> telah terkonfirmasi.
        </div>
        <p><strong>Tanggal pembelian :</strong> <span class="php-output"><?php echo date('d/m/Y'); ?></span></p>
        <div class="ticket">
            <div class="ticket-header">
                <h2>TIKET PENUMPANG</h2>
                <strong><p style="color:#00008B;">PO LAJU PRIMA</p></strong>
            </div>
            <div class="ticket-content">
                <div class="label-value">
                    <span class="label"><strong>Nama penumpang</strong></span>
                    <span class="value">: <?php echo htmlspecialchars($passenger_name); ?></span>
                </div>
                <div class="label-value">
                    <span class="label"><strong>Tanggal keberangkatan</strong></span>
                    <span class="value">: <?php echo $formatted_date; ?></span>
                </div>
                <div class="label-value">
                    <span class="label"><strong>NO telepon</strong></span>
                    <span class="value">: <?php echo htmlspecialchars($passenger_phone); ?></span>
                </div>
                <div class="label-value">
                    <span class="label"><strong>Email penumpang</strong></span>
                    <span class="value">: <?php echo htmlspecialchars($email); ?></span>
                </div>
                <table class="ticket-table">
                <tr>
                        <th>Jam keberangkatan</th>
                        <td id="departure-cell"><span id="departure-time" class="php-output"><?php echo htmlspecialchars($departure); ?></span></td>
                    </tr>
                    <tr>
                        <th>Estimasi Jam kedatangan</th>
                        <td id="destination-cell"><span id="arrival-time" class="php-output"><?php echo htmlspecialchars($destination); ?></span></td>
                    </tr>                    
                </tr>
                </table>
                        
                <table class="ticket-table">
                    <tr>
                        <th>Asal keberangkatan</th>
                        <th>Kota tujuan</th>
                        <th>NO Kursi</th>
                        <th>NO Body BUS</th>
                    </tr>
                    <tr>
                        <td><span class="php-output"><?php echo htmlspecialchars($departure); ?></span></td>
                        <td><span class="php-output"><?php echo htmlspecialchars($destination); ?></span></td>
                        <td><span class="php-output"><?php echo htmlspecialchars($selected_seats); ?></span></td>
                        <td><span class="php-output"><?php echo htmlspecialchars($bus_code); ?></span></td>
                    </tr>
                </table>
                <strong><p style="color:#d1191c;">UNTUK MENDAPAT NO BODY BUS HARAP CHECK-IN TIKET DI AGEN</p></strong>
                <table class="ticket-table">
                    <th>Kode booking</th>
                        <td><span class="php-output"><?php echo htmlspecialchars($booking_code); ?></span></td>
                    </tr>
                    <tr>
                        <th>Status Pembayaran</th>
                        <td><div class="status <?php echo htmlspecialchars($status_class); ?>"><?php echo htmlspecialchars($status_message); ?></div></td>
                    </tr>
                    <tr>
                        <th>Total tiket dibeli</th>
                        <td><span class="php-output"><?php echo htmlspecialchars($seatCount); ?></span></td>
                    </tr>
                    <tr>
                        <th>Penumpang Dewasa</th>
                        <td><span class="php-output"><?php echo htmlspecialchars($pnp_dewasa); ?></span></td>
                    </tr>
                    <tr>
                        <th>Penumpang Balita/Anak 0-2 tahun</th>
                        <td><span class="php-output"><?php echo htmlspecialchars($pnp_balita); ?></span></td>
                    </tr>
                    <tr>
                        <th>Total pembayaran</th>
                        <td><span class="php-output">Rp <?php echo number_format($total_tariff, 0, ',', '.'); ?></span></td>
                    </tr>
                    
                </table>
                
                <div class="contact-info">
        <p class="departure-info"><strong>Kontak Agen:</strong> <?php echo htmlspecialchars($departure); ?></p>
        <p class="phone-number">
            <i class="fas fa-phone"></i> 
            <a <?php echo htmlspecialchars($phone_number); ?>><?php echo htmlspecialchars($phone_number); ?></a>
        </p>
        <p class="address-info">
            <i class="fas fa-map-marker-alt"></i> 
            <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($address); ?>" target="_blank"><?php echo htmlspecialchars($address); ?></a>
        </p>
    </div>

<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 347.2 12.8" style="enable-background:new 0 0 347.2 12.8;" xml:space="preserve">
<style type="text/css">
	.st0{fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;}
	.st1{fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;stroke-dasharray:2.9873,2.9873;}
</style>
<g>
	<g>
		<line class="st0" x1="6.6" y1="6.7" x2="8.1" y2="6.7"/>
		<line class="st1" x1="11.1" y1="6.7" x2="344.2" y2="6.7"/>
		<line class="st0" x1="345.7" y1="6.7" x2="347.2" y2="6.7"/>
	</g>
</g>
</svg>

 <table class="voucher-eat">
 <strong><p style="color:#d1191c;">VOUCHER MAKAN PENUMPANG</p></strong>
                 <th>Nama penumpang</th>
                 <td><span class="php-output"><?php echo htmlspecialchars($passenger_name); ?></span></td>
             </tr>
            
             <tr>
                 <th>Tanggal keberangkatan</th>
                 <td><span class="php-output"><?php echo $formatted_date; ?></span></td>
             </tr>  
             <tr>
                 <th>Total penumpang</th>
                 <td><span class="php-output"><?php echo htmlspecialchars($seatCount); ?></span></td>
             </tr>
         </table>
         <table class="boarding-pass">
            
         
<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 347.2 12.8" style="enable-background:new 0 0 347.2 12.8;" xml:space="preserve">
<style type="text/css">
	.st0{fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;}
	.st1{fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;stroke-dasharray:2.9873,2.9873;}
</style>
<g>
	<g>
		<line class="st0" x1="6.6" y1="6.7" x2="8.1" y2="6.7"/>
		<line class="st1" x1="11.1" y1="6.7" x2="344.2" y2="6.7"/>
		<line class="st0" x1="345.7" y1="6.7" x2="347.2" y2="6.7"/>
	</g>
</g>
</svg>

 <strong><p style="color:#d1191c;">BOARDING PASS PENUMPANG</p></strong>
                 <th>Nama penumpang</th>
                 <td><span class="php-output"><?php echo htmlspecialchars($passenger_name); ?></span></td>
             </tr>
             <tr>
                 <th>Nomor telepon</th>
                 <td><span class="php-output"><?php echo htmlspecialchars($passenger_phone); ?></span></td>
             </tr>
             <tr>
                 <th>Tanggal keberangkatan</th>
                 <td><span class="php-output"><?php echo $formatted_date; ?></span></td>
             </tr>
             <tr>
                 <th>Total penumpang</th>
                 <td><span class="php-output"><?php echo htmlspecialchars($seatCount); ?></span></td>
             </tr>
         </table>
         
<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
	 viewBox="0 0 347.2 12.8" style="enable-background:new 0 0 347.2 12.8;" xml:space="preserve">
<style type="text/css">
	.st0{fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;}
	.st1{fill:none;stroke:#000000;stroke-width:2;stroke-miterlimit:10;stroke-dasharray:2.9873,2.9873;}
</style>
<g>
	<g>
		<line class="st0" x1="6.6" y1="6.7" x2="8.1" y2="6.7"/>
		<line class="st1" x1="11.1" y1="6.7" x2="344.2" y2="6.7"/>
		<line class="st0" x1="345.7" y1="6.7" x2="347.2" y2="6.7"/>
	</g>
</g>
</svg>

            </div>
        </div>
   
<div class="action-buttons">
    <a href="index.php" class="back-link">Kembali ke homepage</a>
    <a class="whatsapp-button" href="https://wa.me/<?php echo $formatted_phone; ?>?text=<?php echo $encoded_message; ?>" target="_blank">
        <i class="bi bi-whatsapp"></i> Kirim ke WhatsApp
    </a>
    <button class="reprint-button" onclick="printTicket()">Print tiket</button>
</div>

         
             
 
    <script>
      // Function to set departure time based on departure location
      document.addEventListener("DOMContentLoaded", function() {
            var departure = document.getElementById('departure-time').textContent.trim();
            var departureTime = '';

            switch (departure) {
                case 'Balaraja':
                    departureTime = '12.00 WIB';
                    break;
                case 'BSD Serpong':
                    departureTime = '13.30 WIB';
                    break;
                case 'Samsat BSD':
                    departureTime = '14.00 WIB';
                    break;
                case 'Cilenggang':
                    departureTime = '14.30 WIB';
                    break;
                default:
                    departureTime = 'Waktu tidak tersedia';
            }
            document.getElementById('departure-time').textContent = departureTime;

            // Function to set arrival time based on destination location
            var destination = document.getElementById('arrival-time').textContent.trim();
            var arrivalTime = '';

            switch (destination) {
                case 'Sragen':
                    arrivalTime = 'Jam 23.00 WIB';
                    break;
                case 'Ngawi':
                    arrivalTime = 'Jam 00.00 WIB';
                    break;
                case 'Madiun':
                    arrivalTime = 'Jam 01.30 WIB';
                    break;
                case 'Ponorogo':
                    arrivalTime = 'Jam 03.00 WIB';
                    break;
                case 'Semarang':
                    arrivalTime = 'jam 22.00 WIB';
                    break;
                case 'Salatiga':
                    arrivalTime = 'jam 00.00 WIB';
                    break;
                case 'Boyolali':
                    arrivalTime = 'jam 01.00 WIB';
                    break;
                case 'Solo':
                    arrivalTime = 'jam 02.00 WIB';
                    break;
                case 'Matesih':
                    arrivalTime = 'jam 04.00 WIB';
                    break;
                case 'Wirosari':
                    arrivalTime = 'jam 01.00 WIB';
                    break;
                case 'Blora':
                    arrivalTime = 'jam 02.00 WIB';
                    break;
                case 'Cepu':
                    arrivalTime = 'jam 03.00 WIB';
                    break;
                case 'Bojonegoro':
                    arrivalTime = 'jam 04.00 WIB';
                    break;
                case 'Gubug':
                    arrivalTime = 'jam 23.30 WIB';
                    break;
                case 'Godong':
                    arrivalTime = 'jam 00.00 WIB';
                    break;
                case 'Purwodadi':
                    arrivalTime = 'jam 00.30 WIB';
                    break;
                case 'Sumberlawang':
                    arrivalTime = 'jam 01.00 WIB';
                    break;
                case 'Gemolong':
                    arrivalTime = 'jam 02.00 WIB';
                    break;
                default:
                    arrivalTime = 'Jam kedatangan tidak tersedia';
            }

            document.getElementById('arrival-time').textContent = arrivalTime;
        });

        function printTicket() {
            window.print();
        }

        // // Automatically call the print function on page load
        // window.onload = function() {
        //     printTicket();
        // };
    </script>
</body>
</html>
