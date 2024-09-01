<?php
include('conn.php');

// Generate kode penumpang
function generateKodePenumpang($name) {
    // Ambil 2 digit inisial dari nama
    $nameParts = explode(' ', $name);
    $initials = strtoupper(substr($nameParts[0], 0, 2));
    
    // Ambil bulan dan tahun saat ini
    $monthYear = date('mY');
    
    // Generate 2 digit angka acak
    $randomDigits = sprintf("%02d", mt_rand(0, 99));
    
    // Gabungkan semua bagian
    $kode_penumpang = $initials . $monthYear . $randomDigits;
    
    return $kode_penumpang;
}

// Check if kode_penumpang exists
function getUniqueKodePenumpang($conn, $name) {
    do {
        $kode_penumpang = generateKodePenumpang($name);

        // Cek apakah kode_penumpang sudah ada
        $sql = "SELECT id FROM data_pnp WHERE kode_penumpang = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $kode_penumpang);
        $stmt->execute();
        $stmt->store_result();
    } while ($stmt->num_rows > 0);

    $stmt->close();
    return $kode_penumpang;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $passenger_name = $_POST['passenger_name'];
    $passenger_phone = $_POST['passenger_phone'];
    $email = $_POST['email'];

    if (!empty($passenger_name) && !empty($passenger_phone) && !empty($email)) {
        $kode_penumpang = getUniqueKodePenumpang($conn, $passenger_name);

        // Masukkan data ke dalam tabel
        $sql = "INSERT INTO data_pnp (kode_penumpang, passenger_name, passenger_phone, email) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $kode_penumpang, $passenger_name, $passenger_phone, $email);

        if ($stmt->execute()) {
            header("Location: register.php?success=1");
            exit();
        } else {
            header("Location: register.php?error=" . urlencode("Terjadi kesalahan: " . $stmt->error));
            exit();
        }

        $stmt->close();
    } else {
        header("Location: register.php?error=" . urlencode("Semua field harus diisi!"));
        exit();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            box-sizing: border-box;
        }
        h2 {
            margin-top: 0;
            color: #333;
            font-size: 1.5em;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-group input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
            font-size: 1em;
        }
        .form-group input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .message {
            margin-top: 15px;
            color: #d9534f;
            font-size: 0.9em;
        }
        .message.success {
            color: #5bc0de;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form action="register_process.php" method="post">
            <div class="form-group">
                <label for="passenger_name">Nama Penumpang</label>
                <input type="text" id="passenger_name" name="passenger_name" required>
            </div>
            <div class="form-group">
                <label for="passenger_phone">Telepon</label>
                <input type="text" id="passenger_phone" name="passenger_phone" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <input type="submit" value="Register">
            </div>
            <div class="message">
                <?php
                if (isset($_GET['error'])) {
                    echo htmlspecialchars($_GET['error']);
                }
                if (isset($_GET['success']) && $_GET['success'] == 1) {
                    echo "<p class='success'>Pendaftaran berhasil!</p>";
                }
                ?>
            </div>
        </form>
    </div>
</body>
</html>
