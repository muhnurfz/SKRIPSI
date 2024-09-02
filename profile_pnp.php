<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Penumpang</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f7f9fc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-top: 50px;
        }
        h2 {
            font-weight: 600;
            margin-bottom: 20px;
            color: #333;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            font-weight: 500;
            color: #555;
        }
        .form-control {
            border-radius: 4px;
            padding: 10px;
        }
        .form-control.non-editable {
            background-color: #e9ecef;
        }
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .password-wrapper input {
            flex-grow: 1;
        }
        .password-wrapper i {
            position: absolute;
            right: 10px;
            cursor: pointer;
            color: #aaa;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
            padding: 10px 20px;
            font-weight: 500;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="content">
        <h2>Edit Profil Penumpang</h2>
        <form method="POST">
            <div class="form-group">
                <label for="kode_penumpang">Kode Penumpang:</label>
                <input type="text" id="kode_penumpang" name="kode_penumpang" class="form-control non-editable" value="<?= htmlspecialchars($row['kode_penumpang']); ?>" readonly>
            </div>
            <div class="form-group">
                <label for="passenger_name">Nama Penumpang:</label>
                <input type="text" id="passenger_name" name="passenger_name" class="form-control" value="<?= htmlspecialchars($row['passenger_name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="passenger_phone">No. Telepon:</label>
                <input type="text" id="passenger_phone" name="passenger_phone" class="form-control" value="<?= htmlspecialchars($row['passenger_phone']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']); ?>" required>
            </div>
            <div class="form-group" style="position: relative;">
                <label for="password">Password:</label>
                <div class="password-wrapper">
                    <input type="password" class="form-control" id="password" name="password" required>
                    <i id="togglePassword" class="fas fa-eye"></i>
                </div>
            </div>
            <div class="form-group">
                <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('togglePassword').addEventListener('click', function () {
        const passwordField = document.getElementById('password');
        const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordField.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
</script>

</body>
</html>
