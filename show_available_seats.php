<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bangku yang Tersedia</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 50%;
            margin: auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        .available-seats {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Bangku yang Tersedia untuk Tanggal</h2>
        
        <?php
        // Check if departure_date is set and valid
        if (isset($_POST['departure_date']) && !empty($_POST['departure_date'])) {
            $departure_date = $_POST['departure_date'];
            $formatted_date = date('d/m/Y', strtotime($departure_date));
            echo '<p>Pilih Tanggal Keberangkatan: ' . htmlspecialchars($formatted_date) . '</p>';

            // Query to get reserved seats for the selected departure date
            $query = "SELECT seat_number FROM orders WHERE departure_date = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$departure_date]);
            $reserved_seats = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Create array of all seats
            $all_seats = range('1A', '7D');

            // Find available seats
            $available_seats = array_diff($all_seats, $reserved_seats);

            if (empty($available_seats)) {
                echo '<p>Semua bangku sudah dipesan untuk tanggal ini.</p>';
            } else {
                echo '<p>Bangku yang tersedia:</p>';
                echo '<div class="available-seats">';
                foreach ($available_seats as $seat) {
                    echo '<span>' . htmlspecialchars($seat) . '</span>';
                }
                echo '</div>';
            }
        } else {
            echo '<p>Tanggal tidak valid atau sudah berlalu.</p>';
        }
        ?>
        
        <a href="index.php">Kembali ke Form Pemesanan Tiket</a>
    </div>
</body>
</html>
