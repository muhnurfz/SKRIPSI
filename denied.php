<?php
include('conn.php');
session_start();// Memulai sesi untuk memastikan $_SESSION dapat diakses
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            text-align: center;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            margin: 0 auto;
        }
        .container h1 {
            color: #e74c3c;
        }
        .container p {
            font-size: 1.2em;
            margin: 20px 0;
        }
        .button {
            display: inline-block;
            font-size: 1em;
            padding: 10px 20px;
            color: #fff;
            background-color: #3498db;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Akses ditolak</h1>
        <p>Maaf anda tidak memiliki izin untuk akses halaman ini</p>
        <p>Anda membutuhkan akses level untuk melanjutkan, silahkan hubungi admin website anda</p>
        <p>Akses level anda saat ini : Level <?php echo $_SESSION['accessLevel']; ?></p>
        <a href="crud.php" class="button">Kembali ke lihat daftar penumpang</a>
    </div>
     <!-- Bootstrap JS and dependencies -->
     <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
