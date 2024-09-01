<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PO. Laju Prima - Pemesanan Tiket</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f9;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .hero {
            position: relative;
            background: url('img/AGEN.svg') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 100px 20px;
            text-align: center;
        }
        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1;
        }
        .hero .content {
            position: relative;
            z-index: 2;
        }
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
        }
        .hero p {
            font-size: 1.25rem;
            margin-bottom: 30px;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
        }
        .hero .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 50px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.2);
        }
        .features {
            padding: 60px 20px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .feature-item:hover {
            transform: translateY(-5px); /* Lift effect */
            box-shadow: 0px 10px 20px rgba(0, 0, 0, 0.15); /* Stronger shadow */
        }
        .features .feature-item {
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            margin: 20px 0;
            background-color: white;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .features .feature-item h3 {
            font-size: 1.75rem;
            margin-bottom: 15px;
        }
        .features .feature-item p {
            font-size: 1rem;
            color: #666;
        }

        .terms, .container-details {
    padding: 60px 20px;
    background-color: white;
    margin-bottom: 40px;
    border-radius: 8px;
    box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.1);
}

.terms h2, .container-details h3 {
    font-size: 2rem;
    margin-bottom: 20px;
}

.terms ul, .container-details ul {
    list-style-type: disc;
    padding-left: 20px;
    margin-bottom: 20px;
}

.detail-item {
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    margin-bottom: 20px;
    background-color: white;
    box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.1);
}

.detail-item h3 {
    font-size: 1.75rem;
    margin-bottom: 15px;
}

.detail-item p {
    font-size: 1rem;
    color: #666;
}


        .dropdown {
            position: relative;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .dropdown-menu {
            max-height: 300px; /* Increased max-height for more items */
            opacity: 0;
            visibility: hidden;
            overflow: hidden;
            transition: max-height 0.3s ease, opacity 0.3s ease, visibility 0.3s ease;
            position: absolute;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0px 5px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            top: 100%;
            left: 0;
            min-width: 160px;
            margin-top: 10px;
        }
        .dropdown-menu.show {
            max-height: 200px;
            opacity: 1;
            visibility: visible;
        }
        .dropdown-menu a {
            color: #333;
            padding: 10px 20px;
            display: block;
            text-decoration: none;
        }
        .dropdown-menu a:hover {
            background-color: #f4f4f9;
        }
        .dropdown-toggle {
            cursor: pointer;
        }
        footer {
            background: linear-gradient(135deg, #343a40, #1d2124);
            color: white;
            padding: 60px 20px; /* Increased padding for better spacing */
            text-align: center;
            margin-top: 50px;
            position: relative;
            border-top: 2px solid #00aaff; /* Add a border-top */
        }
        footer .footer-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        footer p {
            margin: 0;
            font-size: 1rem;
            color: #dcdcdc;
            text-decoration: none;
        }
        footer a {
            color: #00aaff;
            text-decoration: none;
            font-weight: bold;
        }
        footer a:hover {
            text-decoration: none;
        }
        footer .social-icons {
            margin: 20px 0;
        }
        footer .social-icons a {
            color: #ffffff;
            margin: 0 10px;
            font-size: 1.5rem;
            text-decoration: none;
        }
        footer .social-icons a:hover {
            color: #00aaff;
        }
        footer .footer-bottom {
            margin-top: 20px;
            font-size: 0.875rem;
            color: #b0b0b0;
        }
    </style>
</head>
<body>

<div class="hero">
    <div class="content">
        <h1>Selamat datang di Biro perjalanan Agung indah</h1>
        <p>
    Pesan tiket 
    <span 
        style="
            font-family: Arial, sans-serif; 
            font-weight: bold; 
            color: white; 
            cursor: pointer; 
            transition: text-shadow 0.3s ease;"
        onmouseover="
            this.style.backgroundColor = 'transparent'; 
            this.style.color = 'white'; 
            this.style.textShadow = '0 0 10px white';"
        onmouseout="
            this.style.backgroundColor = 'transparent'; 
            this.style.color = 'white'; 
            this.style.textShadow = 'none';"
    >
        PO.LAJU PRIMA
    </span> 
    Anda dari sini mulai langkah pesan - bayar - berangkat
</p>


    </div>
</div>

<div class="container">
      <!-- Tombol Login di kanan atas -->
      <div class="d-flex justify-content-end mb-3">
          </div>
          <div class="features">
              <a href="login_penumpang.php" class="btn btn-outline-primary">Login</a>
        <div class="row">
            <div class="col-md-4">
                <div class="feature-item">
                    <h3>Pesan tiket Anda</h3>
                    <p>Segera amankan kursi dan perjalanan Anda</p>
                    <a href="pesan_tiket.php" class="btn btn-primary">Pesan tiket</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-item">
                    <h3>Pembayaran</h3>
                    <p>Langkah cepat untuk membayar tiket Anda</p>
                    <a href="payment_form.php" class="btn btn-primary">Bayar tiket</a>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-item">
                    <h3>Lihat tiket Anda</h3>
                    <p>Buat perubahan tiket atau cetak tiket anda dengan mudah</p>
                    <div class="dropdown">
                        <button class="btn btn-primary dropdown-toggle" type="button" id="dropdownMenuButton">
                            Lihat tiket
                        </button>
                        <div class="dropdown-menu" id="dropdownMenu">
                            <a class="dropdown-item" href="edit_penumpang.php">Edit Penumpang</a>
                            <a class="dropdown-item" href="batal_penumpang.php">Ajukan Pembatalan</a>
                            <a class="dropdown-item" href="cari_tiket.php">Cetak Tiket</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="terms">
        <h2>Ketentuan Perjalanan</h2>
        <p>Berikut adalah ketentuan yang berlaku untuk perjalanan Anda:</p>
        <ul>
            <li>Harap tiba di boarding point setidaknya 30 menit sebelum keberangkatan.</li>
            <li>Pastikan membawa tiket dan identitas diri saat boarding.</li>
            <li>Untuk anak usia 3 tahun atau dengan tinggi 90cm maka WAJIB DIBELIKAN 1 TIKET.</li>
            <li>Apabila bus mengalami masalah dan keberangkatan dibatalkan maka uang akan dikembalikan 100%</li>
            <li>Kami tidak bertanggung jawab atas barang hilang atau rusak selama perjalanan.</li>
        </ul>
    </div>
    <div class="container-details">
        <!-- <div class="row">
            <div class="col-md-6"> -->
                <div class="detail-item">
                    <h3>Perubahan dan Pembatalan Tiket</h3>
                    <p>Informasi mengenai perubahan atau pembatalan tiket Anda:</p>
                    <ul>
                        <li>Perubahan jadwal tiket dapat dilakukan jika dilakukan paling lambat 24 jam sebelum keberangkatan.</li>
                        <li>Perubahan jadwal tiket dapat dilakukan maksimal 2x.</li>
                        <li>Pembatalan tiket dikenakan biaya administrasi sebesar Rp 30.000 dari tarif tiket dan dilakukan paling lambat 24 jam sebelum keberangkatan.</li>
                        <li>Pembatalan tiket yang di setujui, maka dana akan dilakukan maksimal 2x24 jam.</li>
                        <li>Tiket tidak dapat diubah atau dibatalkan setelah melewati batas waktu yang ditentukan.</li>
                    </ul>
                </div>
            </div>
            <!-- <div class="col-md-6">
                <div class="detail-item">
                    <h3>Kelas dan Jadwal Bus</h3>
                    <p>Berikut adalah informasi tentang kelas dan jadwal bus:</p>
                    <ul>
                        <li><strong>Kelas Ekonomi:</strong> Fasilitas standar, kursi yang nyaman, dan layanan dasar.</li>
                        <li><strong>Kelas Bisnis:</strong> Fasilitas lebih lengkap, kursi lebih nyaman, dan layanan prioritas.</li>
                        <li><strong>Kelas VIP:</strong> Fasilitas premium, kursi recliner, dan layanan khusus.</li>
                    </ul>
                    <a href="jadwal_bus.php" class="btn btn-primary">Lihat Jadwal Bus</a>
                </div>
            </div> -->
        </div>
    </div>
</div>

<footer>
    <p>Masuk sebagai <a href="crud.php">Admin</a></p>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var dropdownToggle = document.getElementById('dropdownMenuButton');
        var dropdownMenu = document.getElementById('dropdownMenu');

        dropdownToggle.addEventListener('click', function () {
            if (dropdownMenu.classList.contains('show')) {
                dropdownMenu.classList.remove('show');
                dropdownMenu.style.maxHeight = '0';
            } else {
                dropdownMenu.classList.add('show');
                dropdownMenu.style.maxHeight = dropdownMenu.scrollHeight + 'px';
            }
        });

        window.addEventListener('click', function (event) {
            if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.remove('show');
                dropdownMenu.style.maxHeight = '0';
            }
        });
    });
</script>

</body>
</html>
