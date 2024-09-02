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

// Mendapatkan data penumpang berdasarkan email dari sesi login
$sql = "SELECT * FROM data_pnp WHERE email = '$logged_in_email'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "Data penumpang tidak ditemukan.";
    exit();
}

// Memproses form saat di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $passenger_name = $_POST['passenger_name'];
    $passenger_phone = $_POST['passenger_phone'];
    $email = $_POST['email'];
    $password = md5($_POST['password']); // Pastikan password di-hash dengan MD5
    
    // Update data penumpang berdasarkan email yang sedang login
    $update_sql = "UPDATE data_pnp SET 
                    passenger_name = '$passenger_name', 
                    passenger_phone = '$passenger_phone', 
                    email = '$email', 
                    password = '$password' 
                  WHERE email = '$logged_in_email'";

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
    <title>Dashboard Penumpang</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
       body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
        }
        .navbar-static-top {
    display: flex;
    justify-content: center; /* Menyelaraskan item secara horizontal ke tengah */
    align-items: center; /* Menyelaraskan item secara vertikal ke tengah */
    width: 100%;
    background-color: #f8f9fa; /* Contoh warna background */
}

.navbar-brand {
    font-weight: bold;
}

.profile-link {
    position: absolute;
    right: 20px; /* Sesuaikan dengan margin yang diinginkan */
    top: 50%;
    transform: translateY(-50%);
}

        .navbar .profile-link i {
            margin-right: 5px;
        }
        .sidebar {
            width: 250px;
            background-color: #343a40;
            padding-top: 20px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            transition: transform 0.3s ease, width 0.3s ease;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            border-right: 1px solid #495057;
            z-index: 1000; /* Ensure the sidebar is above the content */
        }
        .sidebar.collapsed {
            transform: translateX(-250px);
        }
        .sidebar a {
            padding: 15px 20px;
            display: flex;
            align-items: center;
            font-size: 1.1rem;
            color: #adb5bd;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .sidebar a i {
            margin-right: 10px;
        }
        .sidebar a:hover {
            background-color: #495057;
            color: #ffffff;
        }
        .sidebar .profile {
            text-align: center;
            margin-bottom: 20px;
            color: #ffffff;
        }
        .sidebar .profile h5 {
            margin: 0;
            font-size: 1.2rem;
        }
        .sidebar .toggle-btn {
            position: absolute;
            top: 20px;
            right: -40px;
            background-color: #28a745; /* Green color for toggle button */
            border: none;
            color: white;
            padding: 10px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            z-index: 1000; /* Ensure button is above the content */
            transition: background-color 0.3s ease, transform 0.3s ease;
        }
        .sidebar .toggle-btn:hover {
            background-color: #218838; /* Darker shade for hover effect */
            transform: scale(1.1); /* Slightly enlarge button on hover */
        }
        .content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
            transition: margin-left 0.3s ease;
        }
        .content.sidebar-collapsed {
            margin-left: 0;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            margin-bottom: 20px;
        }
        .card-title {
            margin-bottom: 15px;
        }
        .btn-primary, .btn-danger {
            border-radius: 30px;
        }
        .table-wrapper {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            overflow-x: auto;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        .table {
            margin-bottom: 0;
        }
        .table th, .table td {
            vertical-align: middle;
            white-space: nowrap;
        }
        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }
        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            text-align: center;
            color: #fff;
            font-weight: bold;
        }
        .verified {
            background-color: #28a745;
        }
        .paid {
            background-color: #4cbccc;
        }
        .pending {
            background-color: #ffc107;
            color: black;
        }
        .unpaid {
            background-color: #dc3545;
        }
        @media (max-width: 767.98px) {
            .table-responsive-sm {
                display: block;
                width: 100%;
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            .table-wrapper {
                padding: 10px;
            }
            .status {
                font-size: 10px;
                padding: 3px 6px;
            }
        }
    </style>
</head>
<body>

    <!-- Top Navbar -->
    <nav class="navbar navbar-static-top">
        <span class="navbar-brand">Dashboard Penumpang</span>
        <a href="profile_pnp.php" class="profile-link">
            <i class="fas fa-user"></i> Profil
        </a>
    </nav>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="btn btn-primary toggle-btn" id="toggleSidebar">☰</button>
        <div class="profile">
            <h5>Hallo, <?php echo htmlspecialchars($passenger['passenger_name']); ?>!</h5>
        </div>
        <a href="dashboard_pnp.php"><i class="fas fa-home"></i> Home</a>
        <a href="pesan_tiket_pnp.php"><i class="fas fa-ticket-alt"></i> Pesan Tiket</a>
        <a href="riwayat_pnp.php"><i class="fas fa-history"></i> Riwayat Transaksi</a>
        <a href="logout_penumpang.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    
    <div class="container">
        <h2>Edit Profil Penumpang</h2>
        <form method="POST">
            <div class="form-group">
                <label for="kode_penumpang">Kode Penumpang:</label>
                <input type="text" id="kode_penumpang" name="kode_penumpang" class="form-control non-editable" value="<?= htmlspecialchars($row['kode_penumpang']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="passenger_name">Nama Penumpang:</label>
                <input type="text" id="passenger_name" name="passenger_name" class="form-control" value="<?= htmlspecialchars($row['passenger_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="passenger_phone">No. Telepon:</label>
                <input type="text" id="passenger_phone" name="passenger_phone" class="form-control" value="<?= htmlspecialchars($row['passenger_phone']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Simpan Perubahan</button>
            <a href="dashboard_pnp.php" class="btn btn-secondary btn-block">Kembali ke Dashboard</a>
        </form>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('toggleSidebar').addEventListener('click', function() {
            var sidebar = document.getElementById('sidebar');
            var content = document.getElementById('content');
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('sidebar-collapsed');
        });
    </script>
</body>
</html>
