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
            echo "Akun Anda telah dikonfirmasi. Anda dapat login sekarang.";
        } else {
            echo "Terjadi kesalahan saat mengkonfirmasi akun.";
        }
    } else {
        echo "Link konfirmasi tidak valid atau akun sudah dikonfirmasi.";
    }
    $stmt->close();
} else {
    echo "Parameter tidak lengkap.";
}

$conn->close();
?>
