<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        .register-container {
            background-color: rgba(255, 255, 255, 0.85);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.25);
            width: 100%;
            max-width: 400px;
            backdrop-filter: blur(10px);
        }
        .error-message, .success-message {
            font-size: 0.9em;
            margin-bottom: 15px;
            text-align: center;
        }
        .error-message {
            color: #dc3545;
        }
        .success-message {
            color: #28a745;
        }
        .btn-primary, .btn-secondary {
            transition: background-color 0.3s, border-color 0.3s, box-shadow 0.3s;
            border-radius: 30px;
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

/* Adjust button font size for mobile */
@media (max-width: 576px) {
    .btn {
        font-size: 0.875rem; /* Slightly smaller font size on mobile */
    }
}
    </style>
</head>
<body>
<div class="register-container">
    <h2 class="text-center">Register</h2>
    <?php if (isset($_GET['error'])) echo "<p class='error-message'><i class='fas fa-exclamation-circle'></i> " . htmlspecialchars($_GET['error']) . "</p>"; ?>
    <?php if (isset($_GET['success'])) echo "<p class='success-message'><i class='fas fa-check-circle'></i> " . htmlspecialchars($_GET['success']) . "</p>"; ?>
    <form id="registerForm" method="post" action="register_process.php">
        <div class="form-group">
            <label for="username">Nama</label>
            <input type="text" class="form-control" id="username" name="username" required>
        </div>
        <div class="form-group">
            <label for="passenger_phone">Nomor Telepon</label>
            <input type="text" class="form-control" id="passenger_phone" name="passenger_phone"  required>
            <small id="phoneError" class="form-text text-danger" style="display: none;">Nomor telepon tidak valid.</small>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" class="form-control" id="email" name="email" placeholder="contoh@gmail.com"required>
        </div>
        <div class="form-group" style="position: relative;">
                <label for="password">Password</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <i id="togglePassword" class="fas fa-eye"></i>
                </div>
        </div>
        <div class="form-group">
            <label for="confirm_password">Konfirmasi password</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
            <small id="passwordError" class="form-text text-danger" style="display: none;">Password tidak sama.</small>
        </div>

        <div class="form-group row">
        <div class="col-12 col-md-6 mb-2 mb-md-0">
        <a class="btn btn-secondary btn-block" href="login_penumpang.php">Kembali</a>
    </div>
    <div class="col-12 col-md-6">
        <button type="submit" class="btn btn-primary btn-block" id="registerButton">Registrasi</button>
    </div>
        </div>
    </form>

    <div id="loading" class="loading">
        <div class="spinner-border text-primary" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div>
    <div class="form-footer">
        <p>Sudah punya akun?<a href="login_penumpang.php"> Login</a></p>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- Load FontAwesome -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle Password Visibility
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('togglePassword');

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

 // Form Submission and Validation
document.getElementById('registerForm').addEventListener('submit', function(event) {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    const passwordError = document.getElementById('passwordError');

    // Reset error messages
    passwordError.style.display = 'none';

    if (password !== confirmPassword) {
        event.preventDefault(); // Prevent form submission
        passwordError.style.display = 'block'; // Show error message
    } else {
        document.getElementById('registerButton').disabled = true;
        document.getElementById('loading').classList.add('active');
    }
});
});
</script>
</body>
</html>
