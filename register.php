<?php
include('conn.php');


// Ambil data dari form
$passenger_name = $_POST['passenger_name'];
$passenger_phone = $_POST['passenger_phone'];
$email = $_POST['email'];

// Validasi input
if (empty($passenger_name)) {
    header("Location: register.php?error=Nama Penumpang tidak boleh kosong");
    exit();
}

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

$kode_penumpang = generateKodePenumpang($passenger_name);

// Cek apakah kode_penumpang sudah ada
$sql = "SELECT id FROM data_pnp WHERE kode_penumpang = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $kode_penumpang);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Jika kode sudah ada, generate ulang
    $kode_penumpang = generateKodePenumpang($passenger_name);
}

$stmt->close();

// Masukkan data ke dalam tabel
$sql = "INSERT INTO data_pnp (kode_penumpang, passenger_name, passenger_phone, email) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssss", $kode_penumpang, $passenger_name, $passenger_phone, $email);

if ($stmt->execute()) {
    echo "Pendaftaran berhasil! Kode Penumpang: " . htmlspecialchars($kode_penumpang);
} else {
    echo "Terjadi kesalahan: " . $stmt->error;
}

$stmt->close();
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
            width: 300px;
        }
        h2 {
            margin-top: 0;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }
        .form-group input[type="submit"] {
            background-color: #007bff;
            color: #fff;
            border: none;
            cursor: pointer;
        }
        .form-group input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .message {
            margin-top: 15px;
            color: red;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form action="register_process.php" method="post">
            <div class="form-group">
                <label for="kode_penumpang">Kode Penumpang</label>
                <input type="text" id="kode_penumpang" name="kode_penumpang" required>
            </div>
            <div class="form-group">
                <label for="passenger_name">Nama Penumpang</label>
                <input type="text" id="passenger_name" name="passenger_name">
            </div>
            <div class="form-group">
                <label for="passenger_phone">Telepon</label>
                <input type="text" id="passenger_phone" name="passenger_phone">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email">
            </div>
            <div class="form-group">
                <input type="submit" value="Register">
            </div>
        </form>
        <div class="message">
            <?php
            if (isset($_GET['error'])) {
                echo htmlspecialchars($_GET['error']);
            }
            ?>
        </div>
    </div>
</body>
</html>
