<?php
session_start();
include('conn.php');
include('navbar.php');

// Check if the user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: login_penumpang.php");
    exit();
}

// Get the email of the logged-in user
$logged_in_email = $_SESSION['email'];

// Fetch passenger data
$sql_pnp = "SELECT * FROM data_pnp WHERE email = ?";
$stmt_pnp = $conn->prepare($sql_pnp);
$stmt_pnp->bind_param("s", $logged_in_email);
$stmt_pnp->execute();
$result_pnp = $stmt_pnp->get_result();
$passenger = $result_pnp->fetch_assoc();

// Check if passenger data exists
if (!$passenger) {
    echo "<script>alert('Data penumpang tidak ditemukan.'); window.location.href = 'login_penumpang.php';</script>";
    exit();
}

$message = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        // Handle account deletion
        $delete_sql = "DELETE FROM data_pnp WHERE email = ?";
        $stmt_delete = $conn->prepare($delete_sql);
        $stmt_delete->bind_param("s", $logged_in_email);
        if ($stmt_delete->execute()) {
            // Log out user and redirect to login page
            session_destroy();
            header("Location: login_penumpang.php");
            exit();
        } else {
            $message = 'Error: ' . $stmt_delete->error;
            $message_type = 'error';
        }
    } else {
        // Handle profile update
        $passenger_name = $_POST['passenger_name'];
        $passenger_phone = $_POST['passenger_phone'];
        $email = $_POST['email'];
        $password = md5($_POST['password']); // Ensure password is hashed with MD5
        
        $update_sql = "UPDATE data_pnp SET 
                        passenger_name = ?, 
                        passenger_phone = ?, 
                        email = ?, 
                        password = ? 
                      WHERE email = ?";
        $stmt_update = $conn->prepare($update_sql);
        $stmt_update->bind_param("sssss", $passenger_name, $passenger_phone, $email, $password, $logged_in_email);
        
        if ($stmt_update->execute()) {
            $message = 'Data berhasil diperbarui!';
            $message_type = 'success';
        } else {
            $message = 'Error: ' . $stmt_update->error;
            $message_type = 'error';
        }
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
        .notification {
            position: fixed;
            top: 10px;
            right: 10px;
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            display: none;
            z-index: 1000;
            transition: opacity 0.5s ease-in-out;
        }
        .notification.error {
            background-color: #f44336;
        }
        .notification.show {
            display: block;
            opacity: 1;
        }
        .notification.fade {
            opacity: 0;
            transition: opacity 0.5s ease-out;
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
  <!-- Notification div -->
  <?php if (!empty($message)): ?>
        <div class="notification <?php echo $message_type; ?> show" id="notification">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>    

 
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

<form method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun ini?');">
        <input type="hidden" name="action" value="delete">
        <button type="submit" class="btn btn-danger">Hapus Akun</button>
    </form>

    <!-- JavaScript -->
    <script>
        document.getElementById('togglePassword').addEventListener('click', function () {
            var passwordInput = document.getElementById('password');
            var type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
        document.addEventListener('DOMContentLoaded', function() {
            var notification = document.getElementById('notification');
            if (notification) {
                // Hide the notification after 5 seconds
                setTimeout(function() {
                    notification.classList.add('fade');
                    setTimeout(function() {
                        notification.classList.remove('show', 'fade');
                    }, 500); // Match with transition duration
                }, 5000);
            }
        });
    </script>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
