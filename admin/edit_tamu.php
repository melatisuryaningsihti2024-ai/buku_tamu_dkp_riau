<?php

session_start();


// =========================
// CEK LOGIN
// =========================

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}


// =========================
// CEK LEVEL MASTER
// =========================

if (($_SESSION["admin_level"] ?? "") !== "master") {
    header("Location: data_tamu.php");
    exit;
}


// =========================
// DATABASE
// =========================

require_once "../config/database.php";


// =========================
// CEK ID
// =========================

$id = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($id <= 0) {
    header("Location: data_tamu.php");
    exit;
}


// =========================
// AMBIL DATA TAMU
// =========================

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

if (!$stmt) {
    die("Terjadi kesalahan pada sistem.");
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$tamu = mysqli_fetch_assoc($result);


// =========================
// CEK DATA
// =========================

if (!$tamu) {
    header("Location: data_tamu.php");
    exit;
}


// =========================
// DAFTAR BIDANG
// =========================

$daftar_bidang = [
    "Sekretariat",
    "Bidang Perikanan Tangkap",
    "Bidang Perikanan Budidaya",
    "Bidang Kelautan",
    "Bidang Pengolahan dan Pemasaran Hasil Kelautan dan Perikanan",
    "UPT Penerapan Mutu Hasil",
    "UPT Pelabuhan Perikanan",
    "UPT Budidaya Perikanan",
    "UPT PSDKP Wilayah I,II,III"
];


$nama_admin = $_SESSION["admin_nama"] ?? "Admin";

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
        Edit Data Tamu | Buku Tamu Digital
    </title>


    <!-- FONT -->

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


    <!-- CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >
<link rel="icon" type="image/png" href="../assets/img/SAPA_icon.png">


</head>


<body>


    <!-- SIDEBAR -->

    <?php include "sidebar.php"; ?>


    <!-- CONTENT -->

    <main class="admin-content">

        <div class="admin-content-inner">


            <!-- =========================
                 HEADER
            ========================== -->

            <div class="admin-header">

                <span class="form-badge">
                    EDIT DATA TAMU
                </span>

                <h1>
                    Edit Data Tamu
                </h1>

                <p>
                    Perbarui data kunjungan tamu yang tersimpan
                    di dalam sistem.
                </p>

            </div>


            <!-- =========================
                 FORM EDIT
            ========================== -->

            <div class="edit-tamu-card">


                <form
                    action="proses_edit_tamu.php"
                    method="POST"
                    class="edit-tamu-form"
                >


                    <!-- ID -->

                    <input
                        type="hidden"
                        name="id"
                        value="<?= (int) $tamu["id"]; ?>"
                    >


                    <!-- =========================
                         NAMA LENGKAP
                    ========================== -->

                    <div class="edit-form-group">

                        <label for="nama_lengkap">
                            Nama Lengkap
                        </label>

                        <input
                            type="text"
                            id="nama_lengkap"
                            name="nama_lengkap"
                            value="<?= htmlspecialchars($tamu["nama_lengkap"]); ?>"
                            required
                        >

                    </div>


                    <!-- =========================
                         INSTANSI
                    ========================== -->

                    <div class="edit-form-group">

                        <label for="instansi">
                            Instansi
                        </label>

                        <input
                            type="text"
                            id="instansi"
                            name="instansi"
                            value="<?= htmlspecialchars($tamu["instansi"]); ?>"
                            required
                        >

                    </div>


                    <!-- =========================
                         NO TELEPON
                    ========================== -->

                    <div class="edit-form-group">

                        <label for="no_telepon">
                            No. Telepon
                        </label>

                        <input
                            type="text"
                            id="no_telepon"
                            name="no_telepon"
                            value="<?= htmlspecialchars($tamu["no_telepon"]); ?>"
                        >

                    </div>


                    <!-- =========================
                         BIDANG TUJUAN
                    ========================== -->

                    <div class="edit-form-group">

                        <label for="bidang_tujuan">
                            Bidang Tujuan
                        </label>

                        <select
                            id="bidang_tujuan"
                            name="bidang_tujuan"
                            required
                        >

                            <option value="">
                                Pilih Bidang Tujuan
                            </option>

                            <?php foreach ($daftar_bidang as $item): ?>

                                <option
                                    value="<?= htmlspecialchars($item); ?>"
                                    <?= $tamu["bidang_tujuan"] === $item ? "selected" : ""; ?>
                                >
                                    <?= htmlspecialchars($item); ?>
                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- =========================
                         TUJUAN
                    ========================== -->

                    <div class="edit-form-group">

                        <label for="tujuan">
                            Tujuan Kunjungan
                        </label>

                        <textarea
                            id="tujuan"
                            name="tujuan"
                            rows="4"
                            required
                        ><?= htmlspecialchars($tamu["tujuan"]); ?></textarea>

                    </div>


                    <!-- =========================
                         JUMLAH ROMBONGAN
                    ========================== -->

                    <div class="edit-form-group">

                        <label for="jumlah_rombongan">
                            Jumlah Rombongan
                        </label>

                        <input
                            type="number"
                            id="jumlah_rombongan"
                            name="jumlah_rombongan"
                            min="1"
                            value="<?= (int) $tamu["jumlah_rombongan"]; ?>"
                            required
                        >

                    </div>


                    <!-- =========================
                         INFO TANGGAL & WAKTU
                    ========================== -->

                    <div class="edit-info-box">

                        <div class="edit-info-item">

                            <span>
                                Tanggal Kunjungan
                            </span>

                            <strong>
                                <?= date(
                                    "d/m/Y",
                                    strtotime($tamu["tanggal_kunjungan"])
                                ); ?>
                            </strong>

                        </div>


                        <div class="edit-info-item">

                            <span>
                                Waktu Kunjungan
                            </span>

                            <strong>
                                <?= date(
                                    "H:i",
                                    strtotime($tamu["waktu_kunjungan"])
                                ); ?>
                                WIB
                            </strong>

                        </div>

                    </div>


                    <!-- =========================
                         BUTTON
                    ========================== -->

                    <div class="edit-form-actions">

                        <a
                            href="data_tamu.php"
                            class="btn-edit-cancel"
                        >
                            Batal
                        </a>

                        <button
                            type="submit"
                            class="btn-edit-save"
                        >
                            Simpan Perubahan
                        </button>

                    </div>


                </form>

            </div>

        </div>

    </main>
<?php include "../footer.php"; ?>
</body>

</html>