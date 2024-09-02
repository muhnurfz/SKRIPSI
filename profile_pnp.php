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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }

        .form-group {
            margin-bottom: 15px;
            position: relative;
        }

        .form-control {
            padding: 12px;
        height: auto;
        width: 100%;
        box-sizing: border-box;
        margin-bottom: 10px;
        border: 1px solid #ced4da;
        border-radius: 5px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        transition: border-color 0.3s, box-shadow 0.3s;
        }

        .form-control:focus {
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            border-color: #80bdff;   
        }

        .form-group .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
            font-size: 18px; /* Adjust size if needed */
        }

        .btn-primary {
            background: linear-gradient(45deg, #4CAF50, #45a049);
            border: none;
            color: white;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: background 0.3s, transform 0.2s;
        }

        .btn-primary:hover {
            background: linear-gradient(45deg, #45a049, #3e8e41);
            transform: translateY(-2px);
        }

        h2 {
        margin-bottom: 20px;
        text-align: center;
        color: #343a40;
     }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
            padding: 10px 15px;
            margin-top: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }

        h2 {
            margin-bottom: 20px;
            text-align: center;
            color: #343a40;
        }

        .navbar-static-top {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            background-color: #f8f9fa;
            padding: 10px 0;
        }

        .navbar-brand {
            font-weight: bold;
        }

        .profile-link {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
        }

        .profile-link i {
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
            z-index: 1000;
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
            background-color: #28a745;
            border: none;
            color: white;
            padding: 10px;
            border-radius: 0 4px 4px 0;
            cursor: pointer;
            z-index: 1000;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .sidebar .toggle-btn:hover {
            background-color: #218838;
            transform: scale(1.1);
        }

        .content {
            margin-left: 250px;
            padding: 20px;
            flex-grow: 1;
            transition: margin-left 0.3s ease;
            max-width: 800px; /* set a maximum width for the content area */
            margin: 0 auto; /* center the content area horizontally */
        }

        .content.sidebar-collapsed {
            margin-left: 0;
        }

        .password-wrapper {
            position: relative;
        }
        .password-wrapper .form-control {
            padding-right: 40px; /* Space for the icon */
        }
        .password-wrapper #togglePassword {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #007bff;
            font-size: 1.2em;
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

            .sidebar {
                transform: translateX(-250px);
            }

            .content {
                margin-left: 0;
            }

            .sidebar.collapsed {
                transform: translateX(0);
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

<!-- Content -->
<div class="container content" id="content">
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
        <div class="form-group" style="position: relative;">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <i id="togglePassword" class="fas fa-eye"></i>
                </div>
            </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
    </form>
</div>

    <!-- JavaScript -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            var passwordInput = document.getElementById('password');
            var type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });

        document.getElementById('toggleSidebar').addEventListener('click', function () {
            var sidebar = document.getElementById('sidebar');
            var content = document.getElementById('content');
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('sidebar-collapsed');
        });
    </script>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
