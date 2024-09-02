<?php
session_start();
include('conn.php');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login_penumpang.php");
    exit();
}

// Get the email of the logged-in user
$logged_in_email = $_SESSION['email'];

// Mendapatkan data penumpang berdasarkan ID
$id = $_GET['id'];
$sql = "SELECT * FROM data_pnp WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "Penumpang tidak ditemukan.";
    exit();
}

// Memproses form saat di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $passenger_name = $_POST['passenger_name'];
    $passenger_phone = $_POST['passenger_phone'];
    $email = $_POST['email'];
    $password = md5($_POST['password']); // Pastikan password di-hash dengan MD5
    
    $update_sql = "UPDATE data_pnp SET 
                    passenger_name = '$passenger_name', 
                    passenger_phone = '$passenger_phone', 
                    email = '$email', 
                    password = '$password' 
                  WHERE id = $id";

    if ($conn->query($update_sql) === TRUE) {
        echo "Data berhasil diperbarui!";
    } else {
        echo "Error: " . $conn->error;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Penumpang</title>
    <style>
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }
        .non-editable {
            pointer-events: none;
            background-color: #f0f0f0;
        }
        button {
            padding: 10px 15px;
            background-color: #4CAF50;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h2>Edit Profil Penumpang</h2>
    <form method="POST">
        <label for="kode_penumpang">Kode Penumpang:</label>
        <input type="text" id="kode_penumpang" name="kode_penumpang" class="non-editable" value="<?= htmlspecialchars($row['kode_penumpang']); ?>" readonly>

        <label for="passenger_name">Nama Penumpang:</label>
        <input type="text" id="passenger_name" name="passenger_name" value="<?= htmlspecialchars($row['passenger_name']); ?>" required>

        <label for="passenger_phone">No. Telepon:</label>
        <input type="text" id="passenger_phone" name="passenger_phone" value="<?= htmlspecialchars($row['passenger_phone']); ?>" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($row['email']); ?>" required>

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Simpan Perubahan</button>
    </form>
</body>
</html>
