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
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
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
                    <input type="password" name="password" class="form-control" id="password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
                </div>
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="showPassword">
                    <label class="form-check-label" for="showPassword">Tampilkan Password</label>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Script to toggle password visibility -->
<script>
document.getElementById('showPassword').addEventListener('click', function() {
    var passwordField = document.getElementById('password');
    var confirmPasswordField = document.getElementById('confirm_password');
    if (this.checked) {
        passwordField.type = 'text';
        confirmPasswordField.type = 'text';
    } else {
        passwordField.type = 'password';
        confirmPasswordField.type = 'password';
    }
});
</script>

</body>
</html>
