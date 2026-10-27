<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

$active_page = "tambah_tamu";
$level_admin = $_SESSION["admin_level"] ?? "admin";

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

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Tambah Tamu | Buku Tamu Digital DKP Riau</title>

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
    >

    <link rel="stylesheet" href="../assets/css/style.css">
<link rel="icon" type="image/png" href="../assets/img/SAPA_icon.png">


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<?php include "sidebar.php"; ?>

<main class="admin-content">
    <div class="admin-content-inner">

        <div class="page-header">
            <div>
                <h1>Tambah Data Tamu</h1>
                <p>
                    Input data kunjungan secara manual melalui halaman admin
                    Buku Tamu Digital Dinas Kelautan dan Perikanan Provinsi Riau.
                </p>
            </div>
        </div>

        <section class="admins-card">

            <div class="admins-card-header">
                <div>
                    <h2>Formulir Kunjungan</h2>
                    <p>Lengkapi data tamu dengan benar.</p>
                </div>
            </div>

            <form
                id="formTambahTamu"
                action="proses_tambah_tamu_admin.php"
                method="POST"
            >

                <div class="admins-form-grid">

                    <div class="admins-form-group">
                        <label for="nama_lengkap">Nama Lengkap *</label>
                        <input
                            type="text"
                            id="nama_lengkap"
                            name="nama_lengkap"
                            maxlength="150"
                            placeholder="Masukkan nama lengkap"
                            required
                        >
                    </div>

                    <div class="admins-form-group">
                        <label for="instansi">Asal Instansi / Organisasi *</label>
                        <input
                            type="text"
                            id="instansi"
                            name="instansi"
                            maxlength="200"
                            placeholder="Masukkan nama instansi"
                            required
                        >
                    </div>

                    <div class="admins-form-group">
                        <label for="no_telepon">Nomor Telepon</label>
                        <input
                            type="tel"
                            id="no_telepon"
                            name="no_telepon"
                            maxlength="25"
                            placeholder="Contoh: 081234567890"
                        >
                    </div>

                    <div class="admins-form-group">
                        <label for="bidang_tujuan">Bidang / Unit Tujuan *</label>
                        <select
                            id="bidang_tujuan"
                            name="bidang_tujuan"
                            required
                        >
                            <option value="">-- Pilih Bidang / Unit --</option>

                            <?php foreach ($daftar_bidang as $item): ?>
                                <option value="<?= htmlspecialchars($item, ENT_QUOTES, "UTF-8"); ?>">
                                    <?= htmlspecialchars($item, ENT_QUOTES, "UTF-8"); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="admins-form-group">
                        <label for="jumlah_rombongan">Jumlah Rombongan *</label>
                        <input
                            type="number"
                            id="jumlah_rombongan"
                            name="jumlah_rombongan"
                            value="1"
                            min="1"
                            max="10000"
                            required
                        >
                    </div>

                    <div class="admins-form-group">
                        <label for="tanggal_kunjungan">Tanggal Kunjungan *</label>
                        <input
                            type="date"
                            id="tanggal_kunjungan"
                            name="tanggal_kunjungan"
                            value="<?= date("Y-m-d"); ?>"
                            required
                        >
                    </div>

                    <div class="admins-form-group">
                        <label for="waktu_kunjungan">Waktu Kunjungan *</label>
                        <input
                            type="time"
                            id="waktu_kunjungan"
                            name="waktu_kunjungan"
                            value="<?= date("H:i"); ?>"
                            required
                        >
                    </div>

                    <div class="admins-form-group">
                        <label for="tujuan">Tujuan Kunjungan *</label>
                        <textarea
                            id="tujuan"
                            name="tujuan"
                            rows="4"
                            maxlength="2000"
                            placeholder="Tuliskan tujuan kunjungan"
                            required
                        ></textarea>
                    </div>

                </div>

                <div class="admins-form-actions">
                    <button
                        type="submit"
                        class="admins-submit-button"
                        id="btnSimpanTamu"
                    >
                        Simpan Data Tamu
                    </button>

                    <a href="data_tamu.php" class="btn-reset">
                        Kembali ke Data Tamu
                    </a>
                </div>

            </form>

        </section>

    </div>
</main>

<?php include "../footer.php"; ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("formTambahTamu");
    const tombol = document.getElementById("btnSimpanTamu");

    // Kirim form langsung tanpa notifikasi konfirmasi.
    form.addEventListener("submit", function () {
        tombol.disabled = true;
        tombol.textContent = "Menyimpan...";
    });

    const params = new URLSearchParams(window.location.search);
    const status = params.get("status");

    if (status === "berhasil") {
        Swal.fire({
            title: "Berhasil!",
            text: "Data tamu berhasil ditambahkan",
            icon: "success",
            showConfirmButton: true,
            confirmButtonText: "Oke",
            confirmButtonColor: "#1769aa",
            showCloseButton: true,
            timer: 3000,
            timerProgressBar: true,
            allowOutsideClick: false,
            allowEscapeKey: false
        }).then(function (result) {
            if (
                result.isConfirmed ||
                result.dismiss === Swal.DismissReason.timer
            ) {
                window.location.href = "data_tamu.php";
            } else if (result.dismiss === Swal.DismissReason.close) {
                // Jika X ditekan, tetap di halaman tambah tamu.
                window.history.replaceState(
                    {},
                    document.title,
                    window.location.pathname
                );
            }
        });
    }

    if (status === "gagal") {
        Swal.fire({
            title: "Gagal!",
            text: params.get("pesan") ||
                "Data tamu gagal ditambahkan.",
            icon: "error",
            confirmButtonText: "Oke",
            confirmButtonColor: "#1769aa",
            allowOutsideClick: false
        }).then(function () {
            // Tetap di halaman tambah tamu.
            window.history.replaceState(
                {},
                document.title,
                window.location.pathname
            );
        });
    }
});
</script>


</body>
</html>