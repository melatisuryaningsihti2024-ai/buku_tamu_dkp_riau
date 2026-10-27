<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

$id = (int) ($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: data_tamu.php");
    exit;
}

$sql = "SELECT
            id,
            nama_lengkap,
            instansi,
            no_telepon,
            tujuan,
            bidang_tujuan,
            jumlah_rombongan,
            tanggal_kunjungan,
            waktu_kunjungan
        FROM tamu
        WHERE id = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$tamu = mysqli_fetch_assoc($result);

if (!$tamu) {
    header("Location: data_tamu.php");
    exit;
}

$active_page = "data_tamu";

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Detail Tamu | Buku Tamu Digital
    </title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >
<link rel="icon" type="image/png" href="../assets/img/SAPA_icon.png">


</head>

<body>

    <?php include "sidebar.php"; ?>


    <main class="admin-content">

        <div class="admin-content-inner">


            <div class="admin-header">

                <span class="form-badge">
                    DATA TAMU
                </span>

                <h1>
                    Detail Tamu
                </h1>

                <p>
                    Informasi lengkap data kunjungan tamu.
                </p>

            </div>


            <div class="detail-card">


                <div class="detail-card-header">

                    <div>

                        <h2>
                            Informasi Kunjungan
                        </h2>

                        <p>
                            Data yang tercatat pada sistem.
                        </p>

                    </div>

                    <a
                        href="data_tamu.php"
                        class="btn-back"
                    >
                        ← Kembali
                    </a>

                </div>


                <div class="detail-grid">

                    <div class="detail-item">
                        <span>Nama Lengkap</span>
                        <strong><?= htmlspecialchars($tamu["nama_lengkap"]); ?></strong>
                    </div>

                    <div class="detail-item">
                        <span>Instansi</span>
                        <strong><?= htmlspecialchars($tamu["instansi"]); ?></strong>
                    </div>

                    <div class="detail-item">
                        <span>No. Telepon</span>
                        <strong><?= htmlspecialchars($tamu["no_telepon"]); ?></strong>
                    </div>

                    <div class="detail-item">
                        <span>Bidang Tujuan</span>
                        <strong><?= htmlspecialchars($tamu["bidang_tujuan"]); ?></strong>
                    </div>

                    <div class="detail-item">
                        <span>Jumlah Rombongan</span>
                        <strong><?= htmlspecialchars($tamu["jumlah_rombongan"]); ?> orang</strong>
                    </div>

                    <div class="detail-item">
                        <span>Tanggal Kunjungan</span>
                        <strong>
                            <?= date("d F Y", strtotime($tamu["tanggal_kunjungan"])); ?>
                        </strong>
                    </div>

                    <!-- WAKTU DI KIRI -->
                    <div class="detail-item">
                        <span>Waktu Kunjungan</span>
                        <strong>
                            <?= date("H:i", strtotime($tamu["waktu_kunjungan"])); ?> WIB
                        </strong>
                    </div>

                    <!-- TUJUAN DI KANAN -->
                    <div class="detail-item">
                        <span>Tujuan Kunjungan</span>
                        <strong>
                            <?= nl2br(htmlspecialchars($tamu["tujuan"])); ?>
                        </strong>
                    </div>

                </div>

            </div>

        </div>

    </main>
<?php include "../footer.php"; ?>
</body>

</html>