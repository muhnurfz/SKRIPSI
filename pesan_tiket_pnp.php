<?php
include('conn.php'); // Menghubungkan ke database
include('navbar.php');
session_start(); // Memulai sesi


// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login_penumpang.php");
    exit();
}

// Get the email of the logged-in user
$logged_in_email = $_SESSION['email'];

// Fetch passenger data from `data_pnp` based on the logged-in user's email
$sql_pnp = "SELECT * FROM data_pnp WHERE email = ?";
$stmt_pnp = $conn->prepare($sql_pnp);
$stmt_pnp->bind_param("s", $logged_in_email);
$stmt_pnp->execute();
$result_pnp = $stmt_pnp->get_result();
$passenger = $result_pnp->fetch_assoc();


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $kode_penumpang = $_POST['kode_penumpang'];
    $password = md5($_POST['password']); // Menggunakan MD5 untuk hashing password

    // Query untuk memeriksa kode penumpang dan password
    $query = "SELECT * FROM data_pnp WHERE kode_penumpang = '$kode_penumpang' AND password = '$password'";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        // Debugging: Lihat apakah passenger_phone ada di array $user
        echo "Query berhasil, berikut hasilnya: ";
        var_dump($user);
        exit(); // Hentikan eksekusi untuk melihat hasil debug

        // Menyimpan data pengguna ke dalam sesi
        $_SESSION['kode_penumpang'] = $user['kode_penumpang'];
        $_SESSION['passenger_name'] = $user['passenger_name'];
        $_SESSION['passenger_phone'] = $user['passenger_phone'];
        $_SESSION['email'] = $user['email'];

        header('Location: pesan_tiket_pnp.php'); // Redirect ke halaman form pemesanan
        exit();
    } else {
        echo "Login gagal! Kode penumpang atau password salah.";
    }
}


// Determine the CSS class for the status
$status_class = [
    'verified' => 'verified', 
    'paid' => 'pending',
    'cancelled' => 'unpaid',
    'unknown' => 'unpaid'
][$status_pembayaran] ?? 'unpaid';

// Initialize message content
$message = "";

// Mengambil data dari sesi untuk mengisi form secara otomatis
$passenger_name = isset($_SESSION['passenger_name']) ? $_SESSION['passenger_name'] : '';
$passenger_phone = isset($_SESSION['passenger_phone']) ? $_SESSION['passenger_phone'] : '';
$email = isset($_SESSION['email']) ? $_SESSION['email'] : '';

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pemesanan Tiket Bus</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://unpkg.com/react@17/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@17/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
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
            width: 90%; /* Adjust width for better mobile responsiveness */
            max-width: 800px; /* Set a maximum width */
            margin: auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        input[type="text"],
        input[type="tel"],
        input[type="email"] {
            width: 100%;
            padding: 12px; /* Increased padding for better touch interaction */
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 16px; /* Larger text for readability */
        }

        button {
            padding: 12px 20px; /* Larger buttons for better touch interaction */
            font-size: 16px; /* Larger text for readability */
        }

        .divider {
            height: 1px;
            background-color: #eee;
            margin-bottom: 5px;
            border-bottom: 5px solid #eee;
        }

        .ticket-container {
            border: 2px solid #000;
            border-radius: 10px;
            background-color: #fff;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: 10px 0px 10px 0px;
        }

        .ticket {
            display: flex;
            flex-direction: column;
        }

        .route {
            display: flex;
            flex-direction: column;
            gap: 15px; /* Reduced gap for smaller screens */
            position: relative;
        }

        .route-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-left: 30px;
        }

        .circle {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin-right: 10px;
            z-index: 1;
        }

        .circle.blue {
            background-color: #0000ff; /* Blue color */
        }

        .circle.grey {
            background-color: #808080; /* Grey color */
        }

        .timeline-connector {
            width: 2px;
            position: absolute;
            left: 39px; /* Sesuaikan posisi horizontal */
            top: 20px; /* Sesuaikan posisi vertikal sesuai dengan elemen di atas */
            bottom: 20px; /* Pastikan elemen ini memanjang dari atas ke bawah */
            background: repeating-linear-gradient(
                to bottom,
                #ccc,
                #ccc 5px,
                transparent 5px,
                transparent 10px
            );
            z-index: 0;
        }

        .route-info {
            flex: 1;
        }

        .label {
            color: #000;
            font-size: 14px;
            font-weight: bold;
        }

        .location {
            color: #333; /* Slightly lighter for better readability */
            font-size: 16px; /* Consistent size for readability */
            margin-bottom: 8px; /* Increased margin for spacing */
            line-height: 1.4; /* Improved readability with better line spacing */
            transition: color 0.3s ease, background-color 0.3s ease, transform 0.3s ease; /* Smooth transition effects */
        }

        .time {
            color: #000;
            font-size: 16px; /* Consistent size */
            font-weight: 600; /* Slightly lighter than bold for a modern look */
            padding: 4px 8px; /* Padding for better spacing around text */
            border-radius: 4px; /* Rounded corners for a soft look */
            display: inline-block; /* Ensures background and padding apply correctly */
            transition: background-color 0.3s ease, color 0.3s ease, transform 0.3s ease; /* Smooth transition effects */
        }

        .time:hover {
            color: #000; /* Darker text color for contrast */
            transform: scale(1.05); /* Slightly increase size on hover */
        }

        .seat-selector {
            display: none; /* Initially hidden */
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            visibility: hidden;
        }

        .seat-selector.show {
            display: block; /* Show when class "show" is added */
            opacity: 1;
            visibility: visible;
        }

        .legend {
            display: flex;
            justify-content: space-around;
        }

        .legend-item {
            display: flex;
            align-items: center;
        }

        .legend-color {
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }

        .legend-empty {
            background-color: #f7f7f7;
        }

        .legend-selected {
            background-color: #4caf50;
            color: white;
        }

        .legend-reserved {
            background-color: #f44336;
            color: white;
        }

        .seat-container {
            grid-template-columns: repeat(auto-fill, minmax(40px, 1fr)); /* Responsive grid layout */
            gap: 5px;
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .seat {
            width: 40px;
            height: 40px;
            background-color: #f7f7f7;
            cursor: pointer;
            border: 1px solid #ddd;
            border-radius: 5px;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 1px;
        }

        .seat.selected {
            background-color: #4caf50;
            color: white;
        }

        .seat.reserved {
            background-color: #f44336;
            color: white;
            cursor: not-allowed;
        }

        .seat-icon {
            font-size: 18px;
            font-weight: bold;
        }

        .seat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .seat-separator {
            width: 20px;
            height: 40px;
            background-color: white;
            margin: 0 10px;
        }

        .seat-placeholder {
            width: 40px; /* same width as a regular seat */
            height: 40px; /* same height as a regular seat */
            background-color: transparent; /* make it invisible */
            border: none; /* remove border */
            display: flex;
            margin: 0 1px; /* same margin as a regular seat */
            pointer-events: none;
        }

        .tariff {
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-bottom: 20px;
            margin-top: 20px;
            font-weight: bold;
            font-size: 1.2em;
        }

        .tariff ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .tariff li {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .tariff li:last-child {
            border-bottom: none;
        }

        #total-tariff-amount {
            font-weight: bold;
            font-size: 18px;
            color: #337ab7;
        }

        #update-tariff-btn {
            background-color: #337ab7;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }

        #update-tariff-btn:hover {
            background-color: #23527c;
        }

        .crud-link {
            display: block;
            margin-top: 20px;
            text-align: center;
            text-decoration: none;
            background-color: #8a8a8a;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease; /* Smooth transition */
        }

        .crud-link:hover {
            background-color: #525252; /* Darker background color on hover */
            color: white;
        }

        #notification-container {
            position: fixed;
            top: 10px; /* Adjust position to fit smaller screens */
            right: 10px;
            width: auto; /* Allow notifications to be responsive */
            z-index: 1000;
        }

        .notification {
            background-color: #28a745; /* Default green color */
            color: white;
            padding: 15px;
            border-radius: 5px;
            font-size: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            opacity: 1;
            transition: opacity 0.5s ease, transform 0.5s ease;
            margin-bottom: 10px;
        }

        .notification.success {
            background-color: #28a745; /* Green */
        }

        .notification.error {
            background-color: #dc3545; /* Red */
        }

        .notification.fade-out {
            opacity: 0;
            transform: translateY(-20px);
        }
    </style>
    
    <script>
 document.addEventListener('DOMContentLoaded', function() {
    var today = new Date();
    var minDate = today.toISOString().split('T')[0];
    var maxDate = new Date();
    maxDate.setDate(maxDate.getDate() + 45);
    var maxDateString = maxDate.toISOString().split('T')[0];
    document.getElementById("departure_date").setAttribute("min", minDate);
    document.getElementById("departure_date").setAttribute("max", maxDateString);
    
    document.getElementById("route").addEventListener("change", function() {
        updateDestinations();
        updateTariff();
        checkReservedSeats();
        updateScheduleInfo();
        updateTotalTariff(); 
    });
    
    document.getElementById("departure_date").addEventListener("change", checkReservedSeats);
    document.getElementById("passenger_phone").addEventListener("input", formatPhoneNumber);

   // Function to show notification
function showNotification(message, type) {
    var container = document.getElementById('notification-container');
    var notification = document.createElement('div');
    notification.className = 'notification ' + type;
    notification.textContent = message;
    
    container.appendChild(notification);
    
    setTimeout(function() {
        notification.classList.add('fade-out');
        setTimeout(function() {
            container.removeChild(notification);
        }, 500); // Match this with CSS transition duration
    }, 3000); // Notification will disappear after 3 seconds
}

var seats = document.querySelectorAll('.seat');
seats.forEach(function(seat) {
    seat.addEventListener('click', function() {
        var selectedSeats = document.querySelectorAll('.seat.selected');
        var seatNumber = seat.getAttribute('data-seat');
        if (!seat.classList.contains('reserved')) {
            if (seat.classList.contains('selected')) {
                seat.classList.remove('selected');
            } else if (selectedSeats.length < 4) {
                seat.classList.add('selected');
            } else {
                showNotification('Maksimal 4 kursi yang dapat dipilih.', 'error');
            }
            updateTotalTariff();
        }
    });
});


    function updateDestinations() {
        var route = document.getElementById("route").value;
        var destination = document.getElementById("destination");
        var options = {
            ponorogo: ["Sragen", "Ngawi", "Madiun", "Ponorogo"],
            solo: ["Semarang", "Salatiga", "Boyolali", "Solo", "Matesih"],
            bojonegoro: ["Wirosari", "Blora", "Cepu", "Bojonegoro"],
            gemolong: ["Gubug", "Godong", "Purwodadi", "Sumberlawang", "Gemolong"]
        };
        destination.innerHTML = "";
        if (route && options[route]) {
            options[route].forEach(function(city) {
                var option = document.createElement("option");
                option.value = city;
                option.text = city;
                destination.appendChild(option);
            });
        } else {
            var option = document.createElement("option");
            option.value = "0";
            option.text = "Pilih rute terlebih dahulu";
            destination.appendChild(option);
        }
    }

    function updateTariff() {
    var route = document.getElementById("route").value;
    var tariffInfo = document.getElementById("tariff-info");
    
    // Fetch tariff data from PHP script
    fetch('get_tariffs.php')
        .then(response => response.json())
        .then(data => {
            var tariffPerSeat = data[route.toUpperCase()] || 0; // Default to 0 if route not found
            
            // Update the tariff info on the page
            tariffInfo.textContent = "Tarif per kursi: Rp " + formatCurrency(tariffPerSeat);
            
            // Update total tariff based on the new tariff per seat
            updateTotalTariff();
        })
        .catch(error => console.error('Error fetching tariff data:', error));
}


    const departureDateInput = document.getElementById('route');
    const seatContainer = document.querySelector('.seat-selector');

    departureDateInput.addEventListener('change', () => {
        if (departureDateInput.value !== '') {
            seatContainer.classList.add('show');
        } else {
            seatContainer.classList.remove('show');
        }
    });
    const routeSelect = document.getElementById('route');
const seatRowPOG = document.querySelector('.seat-rowPOG');
const seatRowBJO = document.querySelector('.seat-rowBJO');

routeSelect.addEventListener('change', () => {
    const selectedRoute = routeSelect.value;

    // Sembunyikan semua elemen seat-row terlebih dahulu
    seatRowPOG.style.display = 'none';
    seatRowBJO.style.display = 'none';

    // Tampilkan elemen yang sesuai dengan rute yang dipilih
    if (selectedRoute === 'ponorogo' || selectedRoute === 'solo') {
        seatRowPOG.style.display = 'block';
    } else if (selectedRoute === 'bojonegoro' || selectedRoute === 'gemolong') {
        seatRowBJO.style.display = 'block';
    }
});


    function updateTotalTariff() {
    var selectedSeats = document.querySelectorAll('.seat.selected').length;
    var route = document.getElementById("route").value;

    // Fetch tariff data from PHP script
    fetch('get_tariffs.php')
        .then(response => response.json())
        .then(data => {
            var tariffPerSeat = data[route.toUpperCase()] || 0; // Default to 0 if route not found
            
            // Calculate total tariff
            var totalTariff = selectedSeats * tariffPerSeat;
            
            // Update the total tariff on the page
            document.getElementById('total-tariff').textContent = "Total Tarif tiket: Rp " + formatCurrency(totalTariff);
        })
        .catch(error => console.error('Error fetching tariff data:', error));
}


    function formatCurrency(value) {
        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }
   
    function formatPhoneNumber(event) {
        var input = event.target;
        var value = input.value.replace(/\D/g, '');
        if (value.length > 13) {
            value = value.substring(0, 13);
        }
        var formattedValue = '';
        for (var i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) {
                formattedValue += '-';
            }
            formattedValue += value[i];
        }
        input.value = formattedValue;
    }
  
// Fungsi untuk format tanggal dalam format DD-MM-YYYY
function formatDate(date) {
    const bulanIndo = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    var year = date.getFullYear();
    var month = bulanIndo[date.getMonth()];
    var day = ('0' + date.getDate()).slice(-2);

    return day + '/' + month;
}


// // Fungsi untuk menghitung waktu kedatangan berdasarkan waktu keberangkatan dan jam tambahan
// function calculateArrivalTime(departureDateTime, hours, minutes) {
//     var arrivalDateTime = new Date(departureDateTime);
//     arrivalDateTime.setHours(arrivalDateTime.getHours() + hours);
//     arrivalDateTime.setMinutes(arrivalDateTime.getMinutes() + minutes);
//     return formatDate(arrivalDateTime) + ' ' + arrivalDateTime.toTimeString().split(' ')[0].substring(0, 5) + ' WIB';
// }

// Fungsi untuk menambahkan hari ke tanggal
function addDays(date, days) {
    var result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
}

// Event Listener untuk keberangkatan
document.getElementById('departure').addEventListener('change', function() {
    var departure = document.getElementById('departure').value;
    document.getElementById('departure-location').textContent = departure;

    var departureTime;
    var departureDate = document.getElementById('departure_date').value; // Ambil tanggal dari input
    var departureDateTime = new Date(departureDate);

    switch (departure) {
        case 'Balaraja':
            departureTime = formatDate(departureDateTime) + ' | jam 12.00 WIB';
            break;
        case 'BSD Serpong':
            departureTime = formatDate(departureDateTime) + ' | jam 13.30 WIB';
            break;
        case 'Samsat BSD':
            departureTime = formatDate(departureDateTime) + ' | jam 14.00 WIB';
            break;
        case 'Cilenggang':
            departureTime = formatDate(departureDateTime) + ' | jam 14.30 WIB';
            break;
        default:
            departureTime = '';
    }
    document.getElementById('departure-time').textContent = departureTime;
});

// Event Listener untuk tujuan
document.getElementById('destination').addEventListener('change', function() {
    var destination = document.getElementById('destination').value;
    document.getElementById('destination-location').textContent = destination;

    var departureDate = document.getElementById('departure_date').value; // Ambil tanggal dari input
    var departureDateTime = new Date(departureDate);

    var arrivalDateTime;
    var arrivalTime;

    switch (destination) {
        case 'Sragen':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba Jam 23.00 WIB';
            break;
        case 'Ngawi':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba Jam 00.00 WIB';
            break;
        case 'Madiun':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba Jam 01.30 WIB';
            break;
        case 'Ponorogo':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba Jam 03.00 WIB';
            break;
        case 'Semarang':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba jam 22.00 WIB';
            break;
        case 'Salatiga':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba jam 00.00 WIB';
            break;
        case 'Boyolali':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba jam 01.00 WIB';
            break;
        case 'Solo':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba jam 02.00 WIB';
            break;
        case 'Matesih':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba jam 04.00 WIB';
            break;
        case 'Wirosari':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba jam 01.00 WIB';
            break;
        case 'Blora':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba jam 02.00 WIB';
            break;
        case 'Cepu':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba jam 03.00 WIB';
            break;
        case 'Bojonegoro':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba jam 04.00 WIB';
            break;
        case 'Gubug':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba jam 23.30 WIB';
            break;
        case 'Godong':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba jam 00.00 WIB';
            break;
        case 'Purwodadi':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba jam 00.30 WIB';
            break;
        case 'Sumberlawang':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba jam 01.00 WIB';
            break;
        case 'Gemolong':
            arrivalDateTime = addDays(departureDateTime, 1); // Tambahkan 1 hari
            arrivalTime = formatDate(arrivalDateTime) + ' | Est tiba jam 02.00 WIB';
            break;
        default:
            arrivalTime = '';
    }

    document.getElementById('arrival-time').textContent = arrivalTime;
});


    function checkReservedSeats() {
        var route = document.getElementById("route").value;
        var departureDate = document.getElementById("departure_date").value;

        if (route !== "0" && departureDate) {
            fetch(`get_reserved_seats.php?route=${route}&departure_date=${departureDate}`)
                .then(response => response.json())
                .then(data => {
                    seats.forEach(function(seat) {
                        var seatNumber = seat.getAttribute('data-seat');
                        if (data.includes(seatNumber)) {
                            seat.classList.add('reserved');
                        } else {
                            seat.classList.remove('reserved');
                        }
                    });
                })
                .catch(error => console.error('Error fetching reserved seats:', error));
        } else {
            // Reset all seats to default state if route or departure date is not selected
            seats.forEach(function(seat) {
                seat.classList.remove('reserved');
            });
        }
    }

    // Trigger initial check when route or departure date changes
    document.getElementById("route").addEventListener("change", checkReservedSeats);
    document.getElementById("departure_date").addEventListener("change", checkReservedSeats);

    // Handle form submission to store selected seats
    document.querySelector('form').addEventListener('submit', function(event) {
        var selectedSeats = [];
        document.querySelectorAll('.seat.selected').forEach(function(seat) {
            selectedSeats.push(seat.getAttribute('data-seat'));
        });
        document.getElementById('selected_seats').value = selectedSeats.join(',');
    });
});

    </script>
</head>
<body>

<div id="notification-container"></div>

    <div class="container">
        <h2>Form Pemesanan Tiket Bus</h2>
        <form action="process_ticket.php" method="POST">
        <label for="passenger_name">Nama Penumpang:</label>
        <input type="text" id="passenger_name" name="passenger_name" value="<?php echo htmlspecialchars($passenger_name); ?>" required>

        <label for="passenger_phone">No Telepon Penumpang:</label>
        <input type="tel" id="passenger_phone" name="passenger_phone" value="<?php echo htmlspecialchars($passenger_phone); ?>" required>

        <label for="email">Email Penumpang:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="example@gmail.com" pattern="^[a-zA-Z0-9._%+-]+@gmail\.com$" title="Hanya alamat email dengan domain @gmail.com yang diterima" required>
      
            <div>
            <label for="departure_date"><strong>Tanggal Keberangkatan:</strong></label>
            <input type="date" id="departure_date" name="departure_date" required>
        </div> 

            <label for="departure">Asal Keberangkatan:</label>
            <select id="departure" name="departure" required>
            <option value="0">Pilih asal keberangkatan</option>
                <option value="Balaraja">Balaraja</option>
                <option value="BSD Serpong">BSD Serpong</option>
                <option value="Samsat BSD">Samsat BSD</option>
                <option value="Cilenggang">Cilenggang</option>
            </select>

            <label for="route">BUS : </label>
            <select id="route" name="route" required>
                <option value="0">Pilih rute BUS</option>
                <option value="ponorogo">Ponorogo</option>
                <option value="solo">Solo</option>
                <option value="bojonegoro">Bojonegoro</option>
                <option value="gemolong">Gemolong</option>
            </select>

            <label for="destination">Tujuan:</label>
            <select id="destination" name="destination" required>
            <option value="0">Pilih kota tujuan</option>
                <!-- Options will be populated based on the selected route -->
            </select>
            
            <!-- <div id="schedule-info" class="schedule-info"></div>
            -->
         
     
            <div class="seat-selector">
            <div class="ticket-container">
            <strong style="text-align:center; display:flex;">Jadwal BUS</strong>
            <div class="divider"></div>
        <div class="ticket">
            <div class="route"> 
                <div class="route-item">  
                <div class="circle grey"></div>
                    <div class="route-info">
                        <div class="label">Asal keberangkatan</div>
                        <div class="location" id="departure-location"></div>
                    </div>
                    <div class="time" id="departure-time"></div>
                </div>
                <div class="timeline-connector"></div>
                <div class="route-item">   
                <div class="circle blue"></div>
                    <div class="route-info">
                        <div class="label">Kota tujuan</div>
                        <div class="location" id="destination-location"></div>
                    </div>
                    <div class="time" id="arrival-time"></div>
                </div>
            </div>
        </div>
    </div>

            <h2>Mohon pilih seat</h2>
           <div class="legend">
               <div class="legend-item">
                   <div class="legend-color legend-empty"></div>
                   <span>Kursi Kosong</span>
               </div>
               <div class="legend-item">
                   <div class="legend-color legend-selected"></div>
                   <span>Kursi Dipilih</span>
               </div>
               <div class="legend-item">
                   <div class="legend-color legend-reserved"></div>
                   <span>Kursi Terisi</span>
               </div>
           </div>
           
            <div class="seat-container">
           
            <div class="seat-row">
            <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 47.95 44.72" width="30" height="30">
  <defs>
    <clipPath id="clippath">
      <path d="m39.21,35.18h-2.61c-2.45,1.7-7.16,2.87-12.64,2.87s-10.19-1.17-12.64-2.87h-2.61c-1.79,0-3.25,1.45-3.25,3.25v2.2c0,1.79,1.45,3.25,3.25,3.25h30.51c1.79,0,3.25-1.45,3.25-3.25v-2.2c0-1.79-1.45-3.25-3.25-3.25Z" style="fill: none; stroke-width: 0px;"/>
    </clipPath>
  </defs>
  <g>
    <rect x="5.45" y=".43" width="38.14" height="39.09" rx="2.13" ry="2.13" style="fill: #fff; stroke: #010101; stroke-miterlimit: 10; stroke-width: .86px;"/>
    <rect x="-9.36" y="21.86" width="27.45" height="7.87" rx="3.42" ry="3.42" transform="translate(-21.43 30.16) rotate(-90)" style="fill: #fff; stroke: #010101; stroke-miterlimit: 10; stroke-width: .86px;"/>
    <rect x="29.86" y="21.86" width="27.45" height="7.87" rx="3.42" ry="3.42" transform="translate(17.79 69.38) rotate(-90)" style="fill: #fff; stroke: #010101; stroke-miterlimit: 10; stroke-width: .86px;"/>
    <g>
      <g>
        <path d="m8.7,44.29c-2.03,0-3.67-1.65-3.67-3.67v-2.2c0-2.03,1.65-3.67,3.67-3.67h2.75l.11.08c2.48,1.72,7.24,2.79,12.4,2.79s9.92-1.07,12.4-2.79l.11-.08h2.75c2.03,0,3.67,1.65,3.67,3.67v2.2c0,2.03-1.65,3.67-3.67,3.67H8.7Z" style="fill: #fff; stroke-width: 0px;"/>
        <path d="m39.21,35.18c1.79,0,3.25,1.45,3.25,3.25v2.2c0,1.79-1.45,3.25-3.25,3.25H8.7c-1.79,0-3.25-1.45-3.25-3.25v-2.2c0-1.79,1.45-3.25,3.25-3.25h2.61c2.45,1.7,7.16,2.87,12.64,2.87s10.19-1.17,12.64-2.87h2.61m0-.86h-2.88l-.22.15c-2.42,1.67-7.07,2.71-12.16,2.71s-9.74-1.04-12.16-2.71l-.22-.15h-2.88c-2.26,0-4.1,1.84-4.1,4.1v2.2c0,2.26,1.84,4.1,4.1,4.1h30.51c2.26,0,4.1-1.84,4.1-4.1v-2.2c0-2.26-1.84-4.1-4.1-4.1h0Z" style="fill: #010101; stroke-width: 0px;"/>
      </g>
      <g style="opacity: .2;">
        <g style="clip-path: url(#clippath);">
          <g>
            <rect x="16.51" y="33.79" width="14.9" height="10.5" style="fill: #010101; stroke-width: 0px;"/>
            <path d="m30.97,34.22v9.64h-14.04v-9.64h14.04m.86-.86h-15.75v11.36h15.75v-11.36h0Z" style="fill: #010101; stroke-width: 0px;"/>
          </g>
        </g>
      </g>
    </g>
  </g>
  <text transform="translate(6.81 9.74)" style="fill: #010101; font-family: Bahnschrift-Bold, Bahnschrift; font-size: 6.36px; font-variation-settings: &apos;wght&apos; 700, &apos;wdth&apos; 100; font-weight: 700;"><tspan x="0" y="0" style="letter-spacing: .03em;">C</tspan><tspan x="4.12" y="0" style="letter-spacing: .05em;">O-</tspan><tspan x="11.91" y="0" style="letter-spacing: .04em;">D</tspan><tspan x="16.34" y="0" style="letter-spacing: .05em;">R</tspan><tspan x="20.8" y="0" style="letter-spacing: .07em;">I</tspan><tspan x="22.99" y="0" style="letter-spacing: .05em;">V</tspan><tspan x="27.07" y="0" style="letter-spacing: .05em;">ER</tspan></text>
</svg>
    <div class="seat seat-placeholder"></div>
    <div class="seat-separator"></div>
    <div class="seat seat-placeholder"></div>
    <svg id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 49.02 85.43" width="50" height="50">
  <defs>
    <clipPath id="clippath">
      <path d="m39.21,75.88h-2.61c-2.45,1.7-7.16,2.87-12.64,2.87s-10.19-1.17-12.64-2.87h-2.61c-1.79,0-3.25,1.45-3.25,3.25v2.2c0,1.79,1.45,3.25,3.25,3.25h30.51c1.79,0,3.25-1.45,3.25-3.25v-2.2c0-1.79-1.45-3.25-3.25-3.25Z" style="fill: none; stroke-width: 0px;"/>
    </clipPath>
  </defs>
  <g>
    <rect x="5.45" y="41.14" width="38.14" height="39.09" rx="2.13" ry="2.13" style="fill: #fff; stroke: #010101; stroke-miterlimit: 10; stroke-width: .86px;"/>
    <rect x="-9.36" y="62.56" width="27.45" height="7.87" rx="3.42" ry="3.42" transform="translate(-62.14 70.87) rotate(-90)" style="fill: #fff; stroke: #010101; stroke-miterlimit: 10; stroke-width: .86px;"/>
    <rect x="29.86" y="62.56" width="27.45" height="7.87" rx="3.42" ry="3.42" transform="translate(-22.91 110.09) rotate(-90)" style="fill: #fff; stroke: #010101; stroke-miterlimit: 10; stroke-width: .86px;"/>
    <g>
      <g>
        <path d="m8.7,85c-2.03,0-3.67-1.65-3.67-3.67v-2.2c0-2.03,1.65-3.67,3.67-3.67h2.75l.11.08c2.48,1.72,7.24,2.79,12.4,2.79s9.92-1.07,12.4-2.79l.11-.08h2.75c2.03,0,3.67,1.65,3.67,3.67v2.2c0,2.03-1.65,3.67-3.67,3.67H8.7Z" style="fill: #fff; stroke-width: 0px;"/>
        <path d="m39.21,75.88c1.79,0,3.25,1.45,3.25,3.25v2.2c0,1.79-1.45,3.25-3.25,3.25H8.7c-1.79,0-3.25-1.45-3.25-3.25v-2.2c0-1.79,1.45-3.25,3.25-3.25h2.61c2.45,1.7,7.16,2.87,12.64,2.87s10.19-1.17,12.64-2.87h2.61m0-.86h-2.88l-.22.15c-2.42,1.67-7.07,2.71-12.16,2.71s-9.74-1.04-12.16-2.71l-.22-.15h-2.88c-2.26,0-4.1,1.84-4.1,4.1v2.2c0,2.26,1.84,4.1,4.1,4.1h30.51c2.26,0,4.1-1.84,4.1-4.1v-2.2c0-2.26-1.84-4.1-4.1-4.1h0Z" style="fill: #010101; stroke-width: 0px;"/>
      </g>
      <g style="opacity: .2;">
        <g style="clip-path: url(#clippath);">
          <g>
            <rect x="16.51" y="74.5" width="14.9" height="10.5" style="fill: #010101; stroke-width: 0px;"/>
            <path d="m30.97,74.93v9.64h-14.04v-9.64h14.04m.86-.86h-15.75v11.36h15.75v-11.36h0Z" style="fill: #010101; stroke-width: 0px;"/>
          </g>
        </g>
      </g>
    </g>
  </g>
  <g>
    <path d="m9.28,23.69c7.09,0,12.84,5.75,12.84,12.84,0,3.64-1.51,6.92-3.94,9.25h12.8c-2.43-2.34-3.94-5.62-3.94-9.25,0-7.09,5.75-12.84,12.84-12.84,2.68,0,5.17.82,7.23,2.23v-5.49H1.94v5.57c2.08-1.45,4.61-2.31,7.34-2.31Z" style="fill: #010101; stroke-width: 0px;"/>
    <circle cx="24.52" cy="24.5" r="22.58" style="fill: none; stroke: #171717; stroke-miterlimit: 10; stroke-width: 3.84px;"/>
  </g>
  <text transform="translate(10.17 63.75)" style="fill: #010101; font-family: Bahnschrift-Bold, Bahnschrift; font-size: 7.74px; font-variation-settings: &apos;wght&apos; 700, &apos;wdth&apos; 100; font-weight: 700;"><tspan x="0" y="0" style="letter-spacing: .04em;">D</tspan><tspan x="5.39" y="0" style="letter-spacing: .05em;">R</tspan><tspan x="10.82" y="0" style="letter-spacing: .07em;">I</tspan><tspan x="13.49" y="0" style="letter-spacing: .05em;">V</tspan><tspan x="18.45" y="0" style="letter-spacing: .05em;">ER</tspan></text>
</svg>
</div>
  <div class="seat-row">
    <div class="seat" data-seat="1A"><span class="seat-icon">1A</span></div>
    <div class="seat" data-seat="1B"><span class="seat-icon">1B</span></div>
    <div class="seat-separator"></div>
    <div class="seat" data-seat="1C"><span class="seat-icon">1C</span></div>
    <div class="seat" data-seat="1D"><span class="seat-icon">1D</span></div>
  </div>
  <div class="seat-row">
    <div class="seat" data-seat="2A"><span class="seat-icon">2A</span></div>
    <div class="seat" data-seat="2B"><span class="seat-icon">2B</span></div>
    <div class="seat-separator"></div>
    <div class="seat" data-seat="2C"><span class="seat-icon">2C</span></div>
    <div class="seat" data-seat="2D"><span class="seat-icon">2D</span></div>
  </div>
  <div class="seat-row">
    <div class="seat" data-seat="3A"><span class="seat-icon">3A</span></div>
    <div class="seat" data-seat="3B"><span class="seat-icon">3B</span></div>
    <div class="seat-separator"></div>
    <div class="seat" data-seat="3C"><span class="seat-icon">3C</span></div>
    <div class="seat" data-seat="3D"><span class="seat-icon">3D</span></div>
  </div>
  <div class="seat-row">
    <div class="seat" data-seat="4A"><span class="seat-icon">4A</span></div>
    <div class="seat" data-seat="4B"><span class="seat-icon">4B</span></div>
    <div class="seat-separator"></div>
    <div class="seat" data-seat="4C"><span class="seat-icon">4C</span></div>
    <div class="seat" data-seat="4D"><span class="seat-icon">4D</span></div>
  </div><div class="seat-row">
    <div class="seat" data-seat="5A"><span class="seat-icon">5A</span></div>
    <div class="seat" data-seat="5B"><span class="seat-icon">5B</span></div>
    <div class="seat-separator"></div>
    <div class="seat" data-seat="5C"><span class="seat-icon">5C</span></div>
    <div class="seat" data-seat="5D"><span class="seat-icon">5D</span></div>
  </div>
  <div class="seat-row">
    <div class="seat" data-seat="6A"><span class="seat-icon">6A</span></div>
    <div class="seat" data-seat="6B"><span class="seat-icon">6B</span></div>
    <div class="seat-separator"></div>
    <div class="seat" data-seat="6C"><span class="seat-icon">6C</span></div>
    <div class="seat" data-seat="6D"><span class="seat-icon">6D</span></div>
  </div><div class="seat-row">
    <div class="seat" data-seat="7A"><span class="seat-icon">7A</span></div>
    <div class="seat" data-seat="7B"><span class="seat-icon">7B</span></div>
    <div class="seat-separator"></div>
    <div class="seat" data-seat="7C "><span class="seat-icon">7C</span></div>
    <div class="seat" data-seat="7D"><span class="seat-icon">7D</span></div>
  </div>

<div class="seat-rowPOG">
  <div class="seat-row">
    <div class="seat seat-placeholder"></div>
    <div class="seat seat-placeholder"></div>
    <div class="seat-separator"></div>
    <div class="seat" data-seat="8C"><span class="seat-icon">8C</span></div>
    <div class="seat" data-seat="8D"><span class="seat-icon">8D</span></div>
  </div>

<div class="seat-row">
<img src="img/toilet.png" alt="Co-Driver Seat" style="width: 45px; height: 40px; object-fit: contain;">
    <div class="seat seat-placeholder"></div>
    <div class="seat-separator"></div>
    <div class="seat" data-seat="9C"><span class="seat-icon">9C</span></div>
    <div class="seat" data-seat="9D"><span class="seat-icon">9D</span></div>
  </div>
</div>

<div class="seat-rowBJO">
  <div class="seat-row">
    <div class="seat" data-seat="8A"><span class="seat-icon">8A</span></div>
    <div class="seat" data-seat="8B"><span class="seat-icon">8B</span></div>
    <div class="seat-separator"></div>
    <div class="seat" data-seat="8C"><span class="seat-icon">8C</span></div>
    <div class="seat" data-seat="8D"><span class="seat-icon">8D</span></div>
  </div>

<div class="seat-row">
    <div class="seat" data-seat="9A"><span class="seat-icon">9A</span></div>
    <div class="seat" data-seat="9B"><span class="seat-icon">9B</span></div>
    <div class="seat-separator"></div>
    <div class="seat" data-seat="9C"><span class="seat-icon">9C</span></div>
    <div class="seat" data-seat="9D"><span class="seat-icon">9D</span></div>
  </div>

  <div class="seat-row">
<img src="img/toilet.png" alt="Co-Driver Seat" style="width: 45px; height: 40px; object-fit: contain;">
    <div class="seat seat-placeholder"></div>
    <div class="seat-separator"></div>
    <div class="seat" data-seat="10C"><span class="seat-icon">10C</span></div>
    <div class="seat" data-seat="10D"><span class="seat-icon">10D</span></div>
  </div>
</div>

  <input type="hidden" id="selected_seats" name="selected_seats">
  <div id="tariff-info" class="tariff"></div>
  <div id="total-tariff" class="tariff"></div>

</div>
    </div>
    <label style="display: flex; align-items: center; font-size: 14px; margin-bottom: 20px; line-height: 1.5;">
        <input type="checkbox" name="terms" required style="
          margin-right: 15px;
          accent-color: #0079e0; /* Primary blue color for checkbox */
          transform: scale(1.2); /* Increase the size of the checkbox */
          cursor: pointer; /* Pointer cursor on hover */
          /* Additional styling for better appearance */
        ">
        <span style="
          color: #333; /* Dark text color for better readability */
          line-height: 1.5; /* Line height for readability */
        ">
          Dengan melanjutkan pemesanan Anda menyetujui syarat dan ketentuan yang berlaku. Serta data pesanan adalah data penumpang.
        </span>
      </label>
      <br> <input type="submit" value="Pesan Tiket" class="inline-submit-btn"
    style="
        background-color: #0079e0; /* Primary blue color */
        color: #fff; /* White text color */
        border: none; /* No border */
        padding: 10px 20px; /* Padding */
        font-size: 16px; /* Font size */
        cursor: pointer; /* Pointer cursor on hover */
        border-radius: 5px; /* Rounded corners */
        margin-top: 10px; /* Top margin */
        transition: background-color 0.3s ease; /* Smooth transition for hover effect */
        "
    onmouseover="this.style.backgroundColor='#0261b3';"
    onmouseout="this.style.backgroundColor='#0079e0';">
    <a class="crud-link" href="index.php" style="text-decoration: none;">Kembali</a>
</div>
</form>

</body>
</html>
