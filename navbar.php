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

// Fetch passenger data from `data_pnp` based on the logged-in user's email
$sql_pnp = "SELECT * FROM data_pnp WHERE email = ?";
$stmt_pnp = $conn->prepare($sql_pnp);
$stmt_pnp->bind_param("s", $logged_in_email);
$stmt_pnp->execute();
$result_pnp = $stmt_pnp->get_result();
$passenger = $result_pnp->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Dashboard Penumpang'; ?></title>
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
    justify-content: space-between;
    align-items: center;
    width: 50%;
    background-color: #f8f9fa;
    padding: 10px;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.navbar-brand {
    font-weight: bold;
}

.profile-link {
    display: flex;
    align-items: center;
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
    z-index: 999;
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
    top: 10px;
    right: -40px;
    background-color: #28a745;
    border: none;
    color: white;
    padding: 10px;
    border-radius: 0 4px 4px 0;
    cursor: pointer;
    z-index: 1001;
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
}

.content.sidebar-collapsed {
    margin-left: 0;
}

@media (max-width: 767.98px) {
    .content {
        margin-left: 0;
    }

    .sidebar {
        transform: translateX(-250px);
    }

    .sidebar.collapsed {
        transform: translateX(0);
    }

    .content.sidebar-collapsed {
        margin-left: 0;
    }

    .toggle-btn {
        right: 10px;
    }
}

    </style>
</head>
<body>
   <!-- Top Navbar -->
<nav class="navbar navbar-static-top bg-light">
    <div class="container-fluid">
        <div class="d-flex justify-content-between w-100">
            <span class="navbar-brand mb-0 h1">Dashboard Penumpang</span>
            <a href="profile_pnp.php" class="profile-link d-flex align-items-center">
                <i class="fas fa-user"></i> <span class="ml-2">Profil</span>
            </a>
        </div>
    </div>
</nav>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <button class="btn btn-primary toggle-btn" id="toggleSidebar">☰</button>
    <div class="profile text-center mt-3">
        <h5>Hallo, <?php echo htmlspecialchars($passenger['passenger_name']); ?>!</h5>
    </div>
    <a href="dashboard_pnp.php"><i class="fas fa-home"></i> Home</a>
    <a href="pesan_tiket_pnp.php"><i class="fas fa-ticket-alt"></i> Pesan Tiket</a>
    <a href="riwayat_pnp.php"><i class="fas fa-history"></i> Riwayat Transaksi</a>
    <a href="logout_penumpang.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
      document.getElementById('toggleSidebar').addEventListener('click', function() {
    var sidebar = document.getElementById('sidebar');
    var content = document.querySelector('.content');
    sidebar.classList.toggle('collapsed');
    content.classList.toggle('sidebar-collapsed');
});

    </script>
</body>
</html>
