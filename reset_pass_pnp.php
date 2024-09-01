<?php
include('conn.php');

$message = '';
$alertClass = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $kode_penumpang = $_POST['kode_penumpang'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi input password
    if ($password !== $confirm_password) {
        $message = "Password dan konfirmasi password tidak sesuai.";
        $alertClass = 'alert-danger';
    } else {
        // Hash password
        $hashed_password = md5($password);

        // Update password di database
        $query = "UPDATE data_pnp SET password = ? WHERE kode_penumpang = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $hashed_password, $kode_penumpang);
        
        if ($stmt->execute()) {
            $message = "Password Anda telah berhasil direset.";
            $alertClass = 'alert-success';
        } else {
            $message = "Terjadi kesalahan saat mereset password Anda. Silakan coba lagi.";
            $alertClass = 'alert-danger';
        }
    }
} else if (isset($_GET['kode_penumpang'])) {
    $kode_penumpang = $_GET['kode_penumpang'];
} else {
    die("Kode penumpang tidak valid.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('your-background-image.jpg'); /* Specify your background image URL here */
            background-size: cover;
            background-position: center;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .reset-password-container {
            background-color: rgba(255, 255, 255, 0.85);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(10px);
        }
        .btn-primary, .btn-secondary {
            transition: background-color 0.3s, border-color 0.3s, box-shadow 0.3s;
            border-radius: 30px;
        }
        .btn-primary:hover, .btn-primary:focus {
            background-color: #004494; /* Darker shade */
            border-color: #003a75;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        .btn-secondary:hover, .btn-secondary:focus {
            background-color: #5a6268; /* Darker shade */
            border-color: #4e555b;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        .loading {
            display: none;
            text-align: center;
            margin-top: 10px;
        }
        .loading.active {
            display: block;
        }
        .form-control {
            border-radius: 0.5rem;
        }
        h2 {
            font-size: 1.8rem;
            margin-bottom: 20px;
            color: #333;
        }
        .form-footer {
            margin-top: 20px;
            text-align: center;
        }
        .form-footer a {
            color: #007bff;
            text-decoration: none;
        }
        .form-footer a:hover {
            text-decoration: underline;
        }
        .alert {
            text-align: center;
        }
    </style>
</head>
<body>
<div class="reset-password-container">
    <h2 class="text-center">Reset Password</h2>
    <?php if (!empty($message)): ?>
        <div class="alert <?= $alertClass; ?>">
            <?= $message; ?>
        </div>
    <?php endif; ?>
    <form action="" method="POST">
        <input type="hidden" name="kode_penumpang" value="<?= htmlspecialchars($kode_penumpang); ?>">
        <div class="form-group">
            <label for="password">Password Baru</label>
            <div class="input-group">
                <input type="password" name="password" class="form-control" id="password" required>
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fa fa-eye" id="togglePasswordIcon"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="confirm_password">Konfirmasi Password</label>
            <div class="input-group">
                <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                        <i class="fa fa-eye" id="toggleConfirmPasswordIcon"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <div class="col-md-6">
                <a class="btn btn-secondary btn-block" href="login_penumpang.php">Kembali</a>
            </div>
            <div class="col-md-6">
                <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
            </div>
        </div>
    </form>
    <div id="loading" class="loading">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <div class="form-footer">
        <p>Sudah ingat password? <a href="login_penumpang.php">Login</a></p>
    </div>
</div>

<!-- Script to toggle password visibility -->
<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordField = document.getElementById('password');
    const toggleIcon = document.getElementById('togglePasswordIcon');

    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        passwordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
});

document.getElementById('toggleConfirmPassword').addEventListener('click', function () {
    const confirmPasswordField = document.getElementById('confirm_password');
    const toggleIcon = document.getElementById('toggleConfirmPasswordIcon');

    if (confirmPasswordField.type === 'password') {
        confirmPasswordField.type = 'text';
        toggleIcon.classList.remove('fa-eye');
        toggleIcon.classList.add('fa-eye-slash');
    } else {
        confirmPasswordField.type = 'password';
        toggleIcon.classList.remove('fa-eye-slash');
        toggleIcon.classList.add('fa-eye');
    }
});
</script>
</body>
</html>
