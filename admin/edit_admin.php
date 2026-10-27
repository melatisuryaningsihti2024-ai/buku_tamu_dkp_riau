
<?php

session_start();
require_once "../config/database.php";

// Cek login
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Hanya master yang boleh mengedit admin
if (($_SESSION["admin_level"] ?? "") !== "master") {
    header("Location: dashboard.php");
    exit;
}

// Validasi ID admin
$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: admins.php?status=admin_tidak_ditemukan");
    exit;
}

// Ambil data admin
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, nama, username, level
     FROM admins
     WHERE id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Gagal menyiapkan data admin.");
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// Jika admin tidak ditemukan
if (!$admin) {
    header("Location: admins.php?status=admin_tidak_ditemukan");
    exit;
}

$active_page = "admins";

// Notifikasi error
$status = $_GET["status"] ?? "";

$pesan_error = [
    "duplikat" => "Username sudah digunakan. Silakan gunakan username lain.",
    "username_tidak_valid" => "Format username tidak valid. Gunakan huruf, angka, titik, garis bawah, atau tanda hubung.",
    "password_tidak_valid" => "Password baru minimal 8 karakter.",
    "input_tidak_valid" => "Nama atau username melebihi batas karakter.",
    "gagal" => "Data admin gagal diperbarui. Silakan coba kembali."
];

$pesan = $pesan_error[$status] ?? "";

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Admin | Buku Tamu Digital DKP Riau</title>

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
    >

    <link rel="stylesheet" href="../assets/css/style.css">
<link rel="icon" type="image/png" href="../assets/img/SAPA_icon.png">

</head>

<body>

<?php require_once "sidebar.php"; ?>

<main class="admin-content">
    <div class="admin-content-inner">

        <!-- HEADER HALAMAN -->
        <div class="page-header">
            <div>
                <h1>Edit Admin</h1>
                <p>
                    Perbarui informasi akun pengguna Buku Tamu Digital
                    Dinas Kelautan dan Perikanan Provinsi Riau.
                </p>
            </div>
        </div>

        <!-- NOTIFIKASI ERROR -->
        <?php if ($pesan !== ""): ?>
            <div class="admins-alert admins-warning" role="alert">
                <?= htmlspecialchars($pesan, ENT_QUOTES, "UTF-8"); ?>
            </div>
        <?php endif; ?>

        <!-- FORM EDIT ADMIN -->
        <section class="admins-card">

            <div class="admins-card-header">
                <div>
                    <h2>Form Edit Admin</h2>
                    <p>Ubah informasi akun yang diperlukan.</p>
                </div>
            </div>

            <form action="proses_edit_admin.php" method="POST">

                <input
                    type="hidden"
                    name="id"
                    value="<?= (int) $admin["id"]; ?>"
                >

                <div class="admins-form-grid">

                    <!-- NAMA LENGKAP -->
                    <div class="admins-form-group">
                        <label for="nama">Nama Lengkap</label>

                        <input
                            type="text"
                            id="nama"
                            name="nama"
                            maxlength="100"
                            value="<?= htmlspecialchars(
                                $admin["nama"],
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>"
                            placeholder="Masukkan nama lengkap"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <!-- USERNAME -->
                    <div class="admins-form-group">
                        <label for="username">Username</label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            maxlength="50"
                            value="<?= htmlspecialchars(
                                $admin["username"],
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>"
                            placeholder="Masukkan username"
                            autocomplete="username"
                            required
                        >
                    </div>

                    <!-- PASSWORD BARU -->
                    <div class="admins-form-group">
                        <label for="password">Password Baru</label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            minlength="8"
                            placeholder="Kosongkan jika tidak diubah"
                            autocomplete="new-password"
                        >

                        <small>
                            Isi minimal 8 karakter jika ingin mengganti password.
                        </small>
                    </div>

                    <!-- LEVEL AKUN -->
                    <div class="admins-form-group">
                        <label for="level">Level Akun</label>

                        <input
                            type="text"
                            id="level"
                            value="<?= htmlspecialchars(
                                strtoupper($admin["level"]),
                                ENT_QUOTES,
                                "UTF-8"
                            ); ?>"
                            disabled
                        >

                        <small>
                            Level akun tidak dapat diubah melalui halaman ini.
                        </small>
                    </div>

                </div>

                <!-- TOMBOL -->
                <div class="admins-form-actions">

                    <button
                        type="submit"
                        class="admins-submit-button"
                    >
                        Simpan Perubahan
                    </button>

                    <a
                        href="admins.php"
                        class="admins-cancel-button"
                    >
                        Batal
                    </a>

                </div>

            </form>

        </section>

    </div>
</main>
<?php include "../footer.php"; ?>
</body>
</html>
