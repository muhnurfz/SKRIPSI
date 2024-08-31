<?php
include('conn.php');

// Initialize the order variable and update message variable
$order = null;
$update_msg = '';

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Load the existing order data
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM orders WHERE id=?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
        } else {
            die("Order not found.");
        }
        $stmt->close();
    } else {
        die("Failed to prepare statement: " . $conn->error);
    }
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $id = $_POST['id'];
    $departure = $_POST['departure'];
    $email = $_POST['email'];
    $route = $_POST['route'];
    $destination = $_POST['destination'];
    $departure_date = $_POST['departure_date'];
    $passenger_name = $_POST['passenger_name'];
    $passenger_phone = $_POST['passenger_phone'];
    $selected_seats = isset($_POST['selected_seats']) ? $_POST['selected_seats'] : '';
    $selectedSeatsCount = count(explode(',', $selected_seats)); // Count number of selected seats

    // Determine the tariff per seat based on the route
    $tariffPerSeat = 0;
    if ($route === "ponorogo" || $route === "solo") {
        $tariffPerSeat = 250000;
    } else if ($route === "bojonegoro" || $route === "gemolong") {
        $tariffPerSeat = 210000;
    }

    // Calculate the total tariff
    $totalTariff = $selectedSeatsCount * $tariffPerSeat;

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

    // Get current date and time
    $current_datetime = new DateTime();

    // Format the current date and time for the comment
    $day_index = $current_datetime->format('w'); // Day of the week (0 for Sunday, 6 for Saturday)
    $day_name = $days_in_indonesian[$day_index];
    $month_index = $current_datetime->format('n') - 1; // Month index (1-12)
    $month_name = $months_in_indonesian[$month_index];
    $formatted_date = $day_name . ' ' . $current_datetime->format('d') . ' ' . $month_name . ' ' . $current_datetime->format('Y');
    $formatted_time = $current_datetime->format('H:i:s');

    // Prepare the comment with current date and time
    $comment = "PADA TANGGAL " . $formatted_date . ' ' . $formatted_time . " TELAH UPDATE DATA";

    // Prepared statement to update order details
    if ($stmt = $conn->prepare("UPDATE orders SET departure=?, email=?, route=?, destination=?, departure_date=?, passenger_name=?, passenger_phone=?, selected_seats=?, total_tariff=?, comments=? WHERE id=?")) {
        $stmt->bind_param("ssssssssssi", $departure, $email, $route, $destination, $departure_date, $passenger_name, $passenger_phone, $selected_seats, $totalTariff, $comment, $id);
        if ($stmt->execute()) {
            $update_msg = "Data berhasil diubah!";

            // Insert edit log entry
            if ($stmt_log = $conn->prepare("INSERT INTO edit_logs (order_id) VALUES (?)")) {
                $stmt_log->bind_param("i", $id);
                $stmt_log->execute();
                $stmt_log->close();
            } else {
                $update_msg = "Failed to prepare log statement: " . $conn->error;
            }
        } else {
            $update_msg = "Error updating record: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $update_msg = "Failed to prepare update statement: " . $conn->error;
    }

    // Reload the updated order data
    if ($stmt = $conn->prepare("SELECT * FROM orders WHERE id=?")) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $order = $result->fetch_assoc();
        } else {
            die("Order not found.");
        }
        $stmt->close();
    } else {
        die("Failed to prepare select statement: " . $conn->error);
    }
}

$route = isset($order['route']) ? trim($order['route']) : '';

function renderSeats($route) {
    if ($route === 'ponorogo' || $route === 'solo') {
        echo ' <div class="seat-rowPOG">
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
            </div>';
    } 
    if ($route === 'bojonegoro' || $route === 'gemolong') {
        echo ' <div class="seat-rowBJO">
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
                    <div class="seat" data-seat="10C"><span class="seat-icon">10D</span></div>
                    <div class="seat" data-seat="10D"><span class="seat-icon">10D</span></div>
                </div>
                </div>
            </div>';
      
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
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
        /* .schedule-info {
            margin-top: 20px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1.1em;
            color: #333;
        } */
        .divider {
                    height: 1px;
                    background-color: #eee;
                    margin-bottom : 5px;
                    
                   border-bottom: 5px solid #eee;
                }
   
.ticket-container {
    border: 2px solid #000;
    border-radius: 10px;
    background-color: #fff;
    padding: 20px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    margin-top: 10px;
}

.ticket {
    display: flex;
    flex-direction: column;
    
}

        .readonly-select {
            background-color: #f0f0f0; /* Light gray background to indicate it's read-only */
            color: #a0a0a0; /* Gray text color */
            border: 1px solid #ccc; /* Light border to show it's not interactive */
            pointer-events: none; /* Prevent user interactions */
        }


        .seat-selector {
            display: none;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            visibility: hidden;
        }
        .seat-selector.show {
            display: block;
            opacity: 1;
            visibility: visible;
        }
        .legend {
            display: flex;
            justify-content: space-around;
            margin-bottom: 20px;
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
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 20px;
        }
        .seat-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
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
        .seat-placeholder {
            width: 40px;
            height: 40px;
            background-color: transparent;
            border: none;
            display: flex;
            margin: 0 1px;
            pointer-events: none;
        }
        .seat-separator {
            width: 20px;
            height: 40px;
            background-color: white;
            margin: 0 10px;
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
            grid-template-columns: repeat(4, 45px);
            gap: 5px;
            margin-top: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .tariff {
            background-color: #f9f9f9;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
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
        }
        .crud-link:hover {
            background-color: #6c6c6c;
        }
        /* CSS to visually disable select elements */
        .disabled-select {
            background-color: #f0f0f0; /* Light gray background */
            color: #888; /* Gray text color */
            cursor: not-allowed; /* Show cursor as not-allowed */
            pointer-events: none; /* Disable pointer events */
        }
        .seat-selector {
            display: none; /* Awalnya tersembunyi */
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
            visibility: hidden;
        }
        .seat-selector.show {
            display: block; /* Tampilkan ketika kelas "show" ditambahkan */
            opacity: 1;
            visibility: visible;
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
    // Set minimum and maximum date for the departure date input
    var today = new Date();
    var minDate = today.toISOString().split('T')[0];
    var maxDate = new Date();
    maxDate.setDate(maxDate.getDate() + 45);
    var maxDateString = maxDate.toISOString().split('T')[0];

    var departureDateInput = document.getElementById("departure_date");
    var seatContainer = document.querySelector('.seat-selector');
    var selectedSeatsInput = document.getElementById('selected_seats');
    document.getElementById("passenger_phone").addEventListener("input", formatPhoneNumber);
    if (departureDateInput) {
        departureDateInput.setAttribute("min", minDate);
        departureDateInput.setAttribute("max", maxDateString);

        departureDateInput.addEventListener('change', () => {
            if (departureDateInput.value !== '') {
                seatContainer.classList.add('show');
                checkReservedSeats(); // Trigger seat check when date changes
            } else {
                seatContainer.classList.remove('show');
            }
        });
    } else {
        console.error('Element with ID "departure_date" not found.');
    }
    
    document.addEventListener('DOMContentLoaded', function() {
    // Ambil elemen yang menyimpan nilai route
    const routeInput = document.getElementById('selected-route');
    if (!routeInput) {
        console.error('Element with ID "selected-route" not found.');
        return;
    }
});

    // Initialize the destinations dropdown based on the existing route
    updateDestinations();

    // Add event listeners
    var routeElement = document.getElementById("route");
    if (routeElement) {
        routeElement.addEventListener("change", function() {
            updateDestinations();
            checkReservedSeats(); // Check reserved seats when the route changes
        });
    } else {
        console.error('Element with ID "route" not found.');
    }

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


    // Format phone number on input
    function formatPhoneNumber(event) {
        var input = event.target;
        var value = input.value.replace(/\D/g, '');
        if (value.length > 15) {
            value = value.substring(0, 15);
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

    // Update destinations based on the selected route
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

        if (route in options) {
            options[route].forEach(function(city) {
                var option = document.createElement("option");
                option.value = city;
                option.textContent = city;
                if ('<?php echo isset($order["destination"]) ? $order["destination"] : ''; ?>' === city) {
                    option.selected = true;
                }
                destination.appendChild(option);
            });
        }
    }

    // Format currency with thousands separator
    function formatCurrency(value) {
        return value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Check reserved seats from the server
    function checkReservedSeats() {
        var route = document.getElementById("route").value;
        var departureDate = document.getElementById("departure_date").value;
        if (route !== "0" && departureDate) {
            fetch(`get_reserved_seats.php?route=${route}&departure_date=${departureDate}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    console.log("Reserved seats data:", data); // Debugging line
                    if (Array.isArray(data)) {
                        seats.forEach(function(seat) {
                            var seatNumber = seat.getAttribute('data-seat');
                            if (data.includes(seatNumber)) {
                                seat.classList.add('reserved');
                            } else {
                                seat.classList.remove('reserved');
                            }
                        });
                    } else {
                        console.error('Data received is not an array:', data);
                    }
                })
                .catch(error => console.error('Error fetching reserved seats:', error));
        } else {
            console.log("Route or Departure Date is not selected, clearing reserved seats.");
            seats.forEach(function(seat) {
                seat.classList.remove('reserved');
            });
        }
    }

    // Handle form submission to set selected seats in hidden field
    document.querySelector('form').addEventListener('submit', function(event) {
        var selectedSeats = [];
        document.querySelectorAll('.seat.selected').forEach(function(seat) {
            selectedSeats.push(seat.getAttribute('data-seat'));
        });
        selectedSeatsInput.value = selectedSeats.join(',');
    });
});
    </script>
</head>
<body>
<div id="notification-container"></div>
    <div class="container">
        <h2>Edit data penumpang</h2>
        <?php if (!empty($update_msg)): ?>
            <div class="alert alert-success text-center" role="alert">
                <?php echo htmlspecialchars($update_msg); ?>
               <p> kode booking :  <?php echo htmlspecialchars(isset($order['booking_code']) ? $order['booking_code'] : ''); ?> </p>
            </div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars(isset($order['id']) ? $order['id'] : ''); ?>">
            <div class="form-group">
                <label for="passenger_name">Nama Penumpang:</label>
                <input type="text" class="form-control" id="passenger_name" name="passenger_name" value="<?php echo htmlspecialchars(isset($order['passenger_name']) ? $order['passenger_name'] : ''); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="passenger_phone">Nomor Telepon Penumpang:</label>
                <input type="text" class="form-control" id="passenger_phone" name="passenger_phone" value="<?php echo htmlspecialchars(isset($order['passenger_phone']) ? $order['passenger_phone'] : ''); ?>" required oninput="formatPhoneNumber(event)">
            </div>

            <div class="form-group">
                <label for="email">Email penumpang : </label>
                <input type="text" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars(isset($order['email']) ? $order['email'] : ''); ?>" >
            </div>
            
            <div class="form-group">
                <label for="departure_date">Tanggal Keberangkatan:</label>
                <input type="date" class="form-control" id="departure_date" name="departure_date" value="<?php echo htmlspecialchars(isset($order['departure_date']) ? $order['departure_date'] : ''); ?>" required>
            </div>
            
            <div class="form-group">
    <label for="departure">Asal Keberangkatan:</label>
    <select class="form-control readonly-select" id="departure" name="departure" required onchange="markAsChanged(this);" onfocus="this.blur();">
        <option value="Balaraja" <?php echo isset($order['departure']) && $order['departure'] == "Balaraja" ? "selected" : ""; ?>>Balaraja</option>
        <option value="BSD Serpong" <?php echo isset($order['departure']) && $order['departure'] == "BSD Serpong" ? "selected" : ""; ?>>BSD Serpong</option>
        <option value="Samsat BSD" <?php echo isset($order['departure']) && $order['departure'] == "Samsat BSD" ? "selected" : ""; ?>>Samsat BSD</option>
        <option value="Cilenggang" <?php echo isset($order['departure']) && $order['departure'] == "Cilenggang" ? "selected" : ""; ?>>Cilenggang</option>
    </select>
</div>
        
                
            <label for="route">BUS :</label>
<select class="form-control <?php echo !empty($order['route']) ? 'readonly-select' : ''; ?>" id="route" name="route" required>
    <option value="ponorogo" <?php echo isset($order['route']) && $order['route'] == "ponorogo" ? "selected" : ""; ?>>Ponorogo</option>
    <option value="solo" <?php echo isset($order['route']) && $order['route'] == "solo" ? "selected" : ""; ?>>Solo</option>
    <option value="bojonegoro" <?php echo isset($order['route']) && $order['route'] == "bojonegoro" ? "selected" : ""; ?>>Bojonegoro</option>
    <option value="gemolong" <?php echo isset($order['route']) && $order['route'] == "gemolong" ? "selected" : ""; ?>>Gemolong</option>
</select>
<!-- Elemen dengan atribut data untuk menyimpan nilai route -->
<input type="hidden" id="selected-route" value="<?php echo isset($order['route']) ? $order['route'] : ''; ?>">

     
            <div class="form-group">
                <label for="destination">Tujuan:</label>
                <select class="form-control" id="destination" name="destination" required>
                    <!-- Options will be populated based on the selected route -->
                </select>
            </div>
            
            <div class="seat-selector">
        <form id="booking-form">
            <div class="form-group">
                <label for="selected_seats">Kursi yang Dipilih:</label>
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
            </div>

            
        
            <div class="seat-container">
                <!-- Seats layout -->
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
                <!-- Add more seat rows here -->
                <input type="hidden" id="selected_seats" name="selected_seats" value="">
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
                </div>
                <div class="seat-row">
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
                </div>
                <div class="seat-row">
                    <div class="seat" data-seat="7A"><span class="seat-icon">7A</span></div>
                    <div class="seat" data-seat="7B"><span class="seat-icon">7B</span></div>
                    <div class="seat-separator"></div>
                    <div class="seat" data-seat="7C"><span class="seat-icon">7C</span></div>
                    <div class="seat" data-seat="7D"><span class="seat-icon">7D</span></div>
                </div>
                                
                <?php renderSeats($route); ?>
                </div>

                <button type="submit" name="update" class="btn btn-primary btn-block">Update</button>
                <a href="index.php" class="btn btn-secondary btn-block">Kembali</a>
        </form>
    </div>
    
    <!-- Bootstrap JS and dependencies (optional if not already included) -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  
</body>
</html>

   
