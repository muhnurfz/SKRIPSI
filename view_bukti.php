<?php
include('conn.php');
session_start();

// Get order ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$imageData = '';

if ($id > 0) {
    $sql = "SELECT bukti_pembayaran FROM orders WHERE id=$id";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $imageData = base64_encode($row['bukti_pembayaran']); // Encode to base64 for displaying
    } else {
        $error = "Tidak ada file gambar.";
    }
} else {
    $error = "Order ID tidak ditemukan.";
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payment Proof</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* body {
            background-color: #f8f9fa;
        }
        .container {
            margin-top: 50px;
        } */
        .image-container {
            display: flex;
            justify-content: center;
            align-items: center;
            overflow: hidden;
            border: 2px solid #ddd;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            background-color: #fff;
            position: relative;
        }
        .image-container img {
            max-width: 100%;
            max-height: 100%;
            transition: transform 0.3s ease;
            transform-origin: center center;
        }
        /* .alert {
            margin-top: 20px;
        }
        .back-button {
            margin-top: 30px;
        }
        .zoom-buttons {
            position: absolute;
            top: 10px;
            right: 10px;
            display: flex;
            flex-direction: column;
        }
        .zoom-buttons button {
            margin-bottom: 10px;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: none;
            background-color: #007bff;
            color: #fff;
            font-size: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .zoom-buttons button:hover {
            background-color: #0056b3;
        } */
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-center">Bukti pembayaran</h1>
    
    <?php if (isset($error)): ?>
        <div class="alert alert-danger text-center">
            <?php echo $error; ?>
        </div>
    <?php else: ?>
        <div class="image-container">
            <img src="data:image/jpeg;base64,<?php echo $imageData; ?>" alt="Payment Proof" id="zoom-image">
           
        </div>
    <?php endif; ?>
    
    <div class="text-center back-button" style="margin : 10px;">
        <a href="crud.php" class="btn btn-primary btn-lg">Kembali</a>
    </div>
</div>

<script>
    // document.addEventListener("DOMContentLoaded", function() {
    //     const img = document.getElementById('zoom-image');
    //     const zoomInBtn = document.getElementById('zoom-in');
    //     const zoomOutBtn = document.getElementById('zoom-out');
    //     let scale = 1;

    //     function updateZoom() {
    //         img.style.transform = `scale(${scale})`;
    //     }

    //     zoomInBtn.addEventListener('click', function() {
    //         scale += 0.1; // Zoom in
    //         scale = Math.min(scale, 3); // Limit max zoom to 3x
    //         updateZoom();
    //     });

    //     zoomOutBtn.addEventListener('click', function() {
    //         scale -= 0.1; // Zoom out
    //         scale = Math.max(scale, 0.5); // Limit min zoom to 0.5x
    //         updateZoom();
    //     });
    // });
</script>

</body>
</html>
