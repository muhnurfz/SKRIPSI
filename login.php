<?php
session_start();
include('conn.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = md5($_POST['password']); // Hash password with MD5

    // Prepare and execute SQL statement
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch user data
        $user = $result->fetch_assoc();

        // Set session variables
        $_SESSION['username'] = $username;
        $_SESSION['accessLevel'] = $user['accessLevel']; // Save accessLevel in session

        header("Location: crud.php");
        exit();
    } else {
        $error = "Username atau password salah!";
    }

    $stmt->close();
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('img/lp_bus.svg'); /* Specify your background image URL here */
            background-size: cover;
            background-position: center top 0%;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            background-size: 50% 50%; /* width height */
            height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 20px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 400px;
        }
        .error-message {
            color: #dc3545;
            font-size: 0.9em;
            margin-bottom: 15px;
        }
                .btn-primary, .btn-secondary {
            transition: background-color 0.3s, border-color 0.3s, box-shadow 0.3s;
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
            border-radius: 0.25rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-center">Login</h2>
        <?php if (isset($error)) echo "<p class='error-message text-center'>$error</p>"; ?>
        <form id="loginForm" method="post">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            <div class="form-group">
            <label for="password">Password</label>
            <div class="password-wrapper">
                <input type="password" class="form-control" id="password" name="password" required>
                <i id="togglePassword" class="fas fa-eye"></i>
            </div>
            </div>
            <div class="form-group row">
                <div class="col-md-6">
                    <a class="btn btn-secondary btn-block" href="index.php">Kembali</a>
                </div>
                <div class="col-md-6">
                    <button type="submit" class="btn btn-primary btn-block" id="loginButton">Login</button>
                </div>
            </div>
        </form>
        <div id="loading" class="loading">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Loading...</span>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script> <!-- Load FontAwesome -->
    <script>
       document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('loginForm').addEventListener('submit', function() {
        document.getElementById('loginButton').disabled = true;
        document.getElementById('loading').classList.add('active');
    });

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
});

    </script>
</body>
</html>
