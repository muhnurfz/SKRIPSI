<?php
include('conn.php');

if (isset($_GET['email']) && isset($_GET['code'])) {
    $email = $_GET['email'];
    $code = $_GET['code'];

    // Verify the confirmation code
    $sql = "SELECT id FROM data_pnp WHERE email = ? AND kode_penumpang = ? AND status_konfirmasi = 0";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Update the confirmation status
        $sql = "UPDATE data_pnp SET status_konfirmasi = 1 WHERE email = ? AND kode_penumpang = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $email, $code);
        if ($stmt->execute()) {
            // Success message and redirect
            $message = "Akun Anda telah dikonfirmasi. Anda dapat login sekarang.";
        } else {
            $message = "Terjadi kesalahan saat mengkonfirmasi akun.";
        }
    } else {
        $message = "Link konfirmasi tidak valid atau akun sudah dikonfirmasi.";
    }
    $stmt->close();
} else {
    $message = "Parameter tidak lengkap.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Konfirmasi Akun</title>
    <script>
        setTimeout(function() {
            window.location.href = 'login_penumpang.php';
        }, 3000); // Redirect after 3 seconds
    </script>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 50px;
        }
        .message {
            font-size: 18px;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="message">
        <?php echo htmlspecialchars($message); ?>
    </div>
</body>
</html>
