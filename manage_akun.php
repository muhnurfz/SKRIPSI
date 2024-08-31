<?php
include('conn.php');
session_start();

// Set session timeout period (in seconds)
$timeout_duration = 1800; // 30 minutes

// Check if the session is active and if the timeout has expired
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY']) > $timeout_duration) {
    session_unset();     // Unset $_SESSION variables
    session_destroy();   // Destroy session data in storage
    header("Location: login.php"); // Redirect to login page
    exit();
}

// Update the last activity time
$_SESSION['LAST_ACTIVITY'] = time();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Get the username from the session
$username = $_SESSION['username'];

// Query to get the access level of the logged-in user
$query = "SELECT accessLevel FROM users WHERE username = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($accessLevel);
$stmt->fetch();
$stmt->close();

// Check if the user has access level 1 (owner)
if ($accessLevel != 1) {
    // Redirect to a different page or show an access denied message
    header("Location: denied.php"); // You can create an access_denied.php page
    exit();
}

// Initialize message variables
$updateMessage = "";
$registrationMessage = "";
$deletionMessage = "";
$usernameUpdateMessage = "";
$passwordUpdateMessage = "";
$accessLevelUpdateMessage = "";

// Handle employee registration
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['register_employee'])) {
    $regUsername = trim($_POST['username']);
    $regPassword = $_POST['password'];
    $regConfirmPassword = $_POST['confirm_password'];

    if (!empty($regUsername) && !empty($regPassword) && !empty($regConfirmPassword)) {
        if ($regPassword === $regConfirmPassword) {
            $passwordHash = password_hash($regPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, password, accessLevel) VALUES (?, ?, 2)");
            $stmt->bind_param('ss', $regUsername, $passwordHash);
            if ($stmt->execute()) {
                $registrationMessage = "Berhasil registrasi pegawai";
            } else {
                $registrationMessage = "Gagal registrasi pegawai: " . $stmt->error;
            }
            $stmt->close();
        } else {
            $registrationMessage = "Password tidak sama";
        }
    } else {
        $registrationMessage = "Mohon isi semua data!";
    }
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_employee'])) {
    $userId = $_POST['user_id'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    $newUsername = $_POST['new_username'];
    $newAccessLevel = $_POST['access_level'];

    if (!empty($userId)) {
        // Update username if provided
        if (!empty($newUsername)) {
            $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->bind_param('si', $newUsername, $userId);
            if ($stmt->execute()) {
                $usernameUpdateMessage = "Data pegawai berhasil diubah!";
            }
            $stmt->close();
        }

        // Update password if provided and confirmed
        if (!empty($newPassword)) {
            if ($newPassword === $confirmPassword) {
                $newPasswordHash = md5($newPassword);
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param('si', $newPasswordHash, $userId);
                if ($stmt->execute()) {
                    $passwordUpdateMessage = "Password berhasil diubah!";
                }
                $stmt->close();
            } else {
                $updateMessage = "Password tidak sama";
            }
        }

        // Update access level if provided
        if (!empty($newAccessLevel)) {
            $stmt = $conn->prepare("UPDATE users SET accessLevel = ? WHERE id = ?");
            $stmt->bind_param('ii', $newAccessLevel, $userId);
            if ($stmt->execute()) {
                $accessLevelUpdateMessage = "Access level berhasil diubah!";
            }
            $stmt->close();
        }
    } else {
        $updateMessage = "Mohon isi semua data!";
    }
}

// Handle employee deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_employee'])) {
    $userId = intval($_POST['user_id']);

    if ($userId > 0) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $deletionMessage = "Pegawai berhasil dihapus";
            } else {
                $deletionMessage = "Tidak ada pegawai yang dihapus. Mungkin ID tidak ditemukan.";
            }
            $stmt->close();
        } else {
            $deletionMessage = "Gagal menyiapkan statement SQL: " . $conn->error;
        }
    } else {
        $deletionMessage = "User ID tidak valid";
    }
}

// Fetch user data
$sql = "SELECT id, username, accessLevel FROM users WHERE accessLevel != 1";
$result = $conn->query($sql);

// Close connection if it's still valid
if ($conn instanceof mysqli) {
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Accounts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.10.0/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        .container {
            margin-top: 20px;
        }
            
        #sidebar {
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            width: 250px;
            background: #f8f9fa;
            transition: transform 0.3s ease;
            padding-top: 10px;
            transform: translateX(-250px); /* Sidebar initially hidden */
        }

        #sidebar.hidden {
            transform: translateX(0); /* Show sidebar when the hidden class is removed */
        }

        #sidebar-toggler {
            position: absolute;
            top: 10px;
            right: -45px;
            z-index: 101;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 3px;
            padding: 5px 10px;
        }

        .sidebar-wrapper {
            height: calc(100vh - 50px); /* Adjust height to accommodate user info */
            overflow-y: auto; /* Enables vertical scrolling */
        }

        .sidebar-wrapper::-webkit-scrollbar {
            width: 8px; /* Custom scrollbar width */
        }

        .sidebar-wrapper::-webkit-scrollbar-thumb {
            background-color: rgba(0, 0, 0, 0.5); /* Custom scrollbar thumb color */
            border-radius: 4px;
        }

        .sidebar-wrapper::-webkit-scrollbar-track {
            background-color: #f1f1f1; /* Custom scrollbar track color */
        }

        .nav-section-header {
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 10px 0px 0px 0px; /* Removed margin to fit the layout */
            padding: 10px;
            border-bottom: 1px solid #444;
        }

        .nav-item .nav-link {
            padding: 10px 20px;
            color: #333;
            transition: color 0.3s ease-in-out, background-color 0.3s ease-in-out;
        }

        .nav-link:hover {
            background-color: #d1d1d1;
        }

        .nav-link.active {
            background-color: #337ab7;
            color: #fff;
        }
          
        .container {
            transition: margin-left 0.3s ease;
            margin-left: 10px; /* Margin when sidebar is hidden */
            margin-top: 50px;
        }
        .container.shifted {
            margin-left: 270px; /* Margin when sidebar is shown */
        }
        #user-info {
            border-bottom: 1px solid #495057;
            margin-bottom: 15px;
        }

        #user-info strong {
            font-size: 1.2em;
            display: block;
            margin-bottom: 5px;
        }

        #user-info p {
            font-size: 0.9em;
            color: #ced4da;
            margin-bottom: 0;
        }
        .card {
            margin-bottom: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .alert {
            margin-top: 20px;
        }
        .back-button {
            margin-top: 20px;
        }
        .password-toggle {
            cursor: pointer;
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
    </style>

</head>
<body>



<div class="modal fade" id="notificationModal" tabindex="-1" role="dialog" aria-labelledby="notificationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="notificationModalLabel">Notifikasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="notificationMessage">
                <!-- Notification message will be inserted here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Employee Modal -->
<div class="modal fade" id="editEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="editEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editEmployeeModalLabel">Edit Pegawai</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editEmployeeForm" method="post" onsubmit="return confirmPasswordChange(event)">
                    <input type="hidden" id="edit_user_id" name="user_id">
                    
                    <div class="form-group">
                        <label for="edit_username">Username:</label>
                        <input type="text" class="form-control" id="edit_username" name="new_username" required>
                    </div>

                    <div class="form-group">
                        <label for="edit_new_password">Password baru :</label>
                        <div class="password-wrapper">
                            <div class="input-group">
                                <input type="password" class="form-control" id="edit_new_password" name="new_password">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary password-toggle" data-target="#edit_new_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_confirm_password">Ketik ulang password:</label>
                        <div class="password-wrapper">
                            <div class="input-group">
                                <input type="password" class="form-control" id="edit_confirm_password" name="confirm_password">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary password-toggle" data-target="#edit_confirm_password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="edit_access_level">Access Level:</label>
                        <select class="form-control" id="edit_access_level" name="access_level">
                            <option value="2">Berangkat</option>
                            <option value="3">Pelayanan</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary" name="update_employee">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>


<!-- Delete Employee Modal -->
<div class="modal fade" id="deleteEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="deleteEmployeeModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteEmployeeModalLabel">Konfirmasi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
               Apakah Anda yakin ingin menghapus pegawai? <strong id="employeeName"></strong>
            </div>
            <div class="modal-footer">
                <form id="deleteEmployeeForm" method="post">
                    <input type="hidden" name="user_id" id="employeeId">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Kembali</button>
                    <button type="submit" class="btn btn-danger" name="delete_employee">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>



<nav id="sidebar" class="bg-light">
    <button id="sidebar-toggler" class="btn btn-primary">☰</button>
    <div class="sidebar-sticky">
        <div class="user-info text-center p-3 bg-dark text-white">
            <h5>Hallo, <?php echo htmlspecialchars($username); ?>!</h5>
        </div>
        <div class="sidebar-wrapper">
            <ul class="nav flex-column">
                <!-- Alokasi Data Penumpang -->
                <li class="nav-item">
                    <div class="nav-section-header bg-dark text-white p-2">PEMESANAN TIKET</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="index.php"><i class="bi bi-house-door"></i> Homepage</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="pesan_tiket.php"><i class="bi bi-ticket"></i> Pesan Tiket</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cari_tiket.php"><i class="bi bi-printer"></i> Cetak Tiket</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cash_payment.php"><i class="bi bi-wallet2"></i> Proses Pembayaran</a>
                </li>
                <!-- Alokasi Kepegawaian -->
                <li class="nav-item">
                    <div class="nav-section-header bg-dark text-white p-2">ALOKASI KEAGENAN</div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="crud.php"><i class="bi bi-gear-fill"></i> Lihat daftar penumpang</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="bus_code.php"><i class="bi bi-bus-front"></i> Alokasi No Body</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="refund_payment.php"><i class="bi bi-x-circle"></i> Alokasi penumpang batal</a>
                </li>
                <li class="nav-item">
                    <?php
                    // Cek apakah user memiliki hak akses yang diperlukan
                    if ($_SESSION['accessLevel'] == 1) {
                        echo ' <a class="nav-link" href="manage_akun.php"><i class="bi bi-person"></i>Kelola Pegawai</a>';
                    }
                    ?>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php"><i class="bi bi-door-closed"></i> Log out</a>
                </li>
            </ul>
        </div>
    </div>
</nav>



<div class="container">
    <!-- Registration Form -->
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h2>Registrasi pegawai</h2>
                </div>
                <div class="card-body">
                   
                    <form method="post">
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <div class="password-wrapper">
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary password-toggle" data-target="#password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Ketik ulang password:</label>
                            <div class="password-wrapper">
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary password-toggle" data-target="#confirm_password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary" name="register_employee">Registrasi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

   
    <!-- Employee Management -->
    <div class="row mt-5">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h2>Kelola Pegawai</h2>
                </div>
                <div class="card-body">
                    <!-- Employee Table -->
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Akses level</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                                    <td><?php echo ($row['accessLevel'] == 2) ? 'Berangkat' : 'Pelayanan'; ?></td>
                                    <td>
                                        <!-- Edit Button -->
                                        <button class="btn btn-warning btn-sm mr-2" data-toggle="modal" data-target="#editEmployeeModal" data-id="<?php echo $row['id']; ?>" data-username="<?php echo htmlspecialchars($row['username']); ?>" data-access="<?php echo $row['accessLevel']; ?>">
                                            Edit
                                        </button>
                                        
                                        <!-- Delete Button -->
                                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteEmployeeModal" data-id="<?php echo $row['id']; ?>" data-username="<?php echo htmlspecialchars($row['username']); ?>">
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
                        <div class="back-button">
                            <a href="crud.php" class="btn btn-secondary">Kembali</a>
                        </div>
</div>

                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script> <!-- Pilih salah satu jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script> <!-- Pilih salah satu Bootstrap JS -->
<script>
    
// JavaScript to handle showing the modal with the message
document.addEventListener('DOMContentLoaded', function () {
    // Get the messages from PHP
    const updateMessage = "<?php echo addslashes($updateMessage); ?>";
    const registrationMessage = "<?php echo addslashes($registrationMessage); ?>";
    const deletionMessage = "<?php echo addslashes($deletionMessage); ?>";
    const usernameUpdateMessage = "<?php echo addslashes($usernameUpdateMessage); ?>";
    const passwordUpdateMessage = "<?php echo addslashes($passwordUpdateMessage); ?>";
    const accessLevelUpdateMessage = "<?php echo addslashes($accessLevelUpdateMessage); ?>";

    let message = '';

    if (usernameUpdateMessage) {
        message = usernameUpdateMessage;
    } else if (passwordUpdateMessage) {
        message = passwordUpdateMessage;
    } else if (accessLevelUpdateMessage) {
        message = accessLevelUpdateMessage;
    } else if (updateMessage) {
        message = updateMessage;
    } else if (registrationMessage) {
        message = registrationMessage;
    } else if (deletionMessage) {
        message = deletionMessage;
    }

    // Show the modal if there is a message
    if (message) {
        document.getElementById('notificationMessage').innerText = message;
        $('#notificationModal').modal('show');
    }
});

$(document).ready(function() {
    // Populate edit modal
    $('#editEmployeeModal').on('show.bs.modal', function(event) {
        var button = $(event.relatedTarget); // Button that triggered the modal
        var userId = button.data('id'); // Extract info from data-* attributes
        var username = button.data('username');
        var accessLevel = button.data('access');

        var modal = $(this);
        modal.find('#edit_user_id').val(userId);
        modal.find('#edit_username').val(username);
        modal.find('#edit_access_level').val(accessLevel);
    });

   // Mengatur ID dan nama pegawai saat modal ditampilkan
$('#deleteEmployeeModal').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Tombol yang diklik
    var userId = button.data('id'); // Ambil ID pengguna dari data-id
    var username = button.data('username'); // Ambil nama pengguna dari data-username
    
    var modal = $(this);
    modal.find('#employeeId').val(userId); // Set ID pengguna di input tersembunyi
    modal.find('#employeeName').text(username); // Tampilkan nama pengguna di modal
});

});

// Sidebar collapse functionality
     $(document).ready(function() {
            $('#sidebar-toggler').click(function() {
                $('#sidebar').toggleClass('hidden');
                $('.container').toggleClass('shifted'); 
            });
        });

document.getElementById('sidebar-toggler').addEventListener('click', function() {
    document.getElementById('sidebar').classList.toggle('collapsed');
});

  document.querySelectorAll('.password-toggle').forEach(button => {
    button.addEventListener('click', () => {
        const input = document.querySelector(button.getAttribute('data-target'));
        const icon = button.querySelector('i');
        
        if (input.type === 'password') {
            // Show password
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            // Hide password
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });
});
function confirmPasswordChange(event) {
    const form = event.target;
    const newPassword = form.querySelector('input[name="new_password"]').value;
    const confirmPassword = form.querySelector('input[name="confirm_password"]').value;
    
    if (newPassword !== confirmPassword) {
        alert('Passwords do not match.');
        return false;
    }
    return true;
}

</script>
</body>
</html>