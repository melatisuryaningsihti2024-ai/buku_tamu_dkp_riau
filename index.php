<?php
require_once "config/database.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SAPA | Dinas Kelautan dan Perikanan Provinsi Riau</title>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="assets/img/SAPA_icon.png">
</head>

<body>

<!-- Navbar -->
<nav class="navbar">

    <!-- Logo SAPA di kiri -->
    <div class="logo-area">
        <img src="assets/img/SAPA_icon.png" alt="Logo SAPA">

        <div class="logo-text">
            <h3>SAPA</h3>
            <p>Sistem Administrasi Penerimaan Tamu</p>
        </div>
    </div>


    <!-- Identitas Dinas di kanan -->
    <div class="dinas-area">
        <div class="dinas-text">
            <h3>Dinas Kelautan dan Perikanan</h3>
            <p>Provinsi Riau</p>
        </div>

        <img src="assets/img/air.jpg"
            alt="Logo Provinsi Riau">
    </div>

</nav>


    <!-- Hero -->
    <section class="hero">
        <div class="hero-content">

            <div class="hero-badge">
                SAPA DIGITAL
            </div>

            <h1>
                Selamat Datang di
                <span>SAPA</span>
            </h1>

            <p>
                Sistem Administrasi Penerimaan Tamu
                untuk pencatatan kunjungan di
                Dinas Kelautan dan Perikanan Provinsi Riau.
            </p>

            <a href="buku_tamu.php" class="btn-tamu">
                Isi Buku Tamu
            </a>

        </div>
    </section>

    <!-- Footer -->
    <?php include "footer.php"; ?>

</body>
</html>
