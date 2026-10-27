
<?php
session_start();
require_once "../config/database.php";

// Cek login
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Hanya master yang boleh mengelola admin
if (($_SESSION["admin_level"] ?? "") !== "master") {
    header("Location: dashboard.php");
    exit;
}

$active_page = "admins";

// Ambil data admin
$sql = "SELECT id, nama, username, level, created_at
        FROM admins
        ORDER BY id DESC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Gagal mengambil data admin.");
}

$data_admin = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data_admin[] = $row;
}

$total_admin = count($data_admin);

// Notifikasi
$status = $_GET["status"] ?? "";
$notifikasi = "";
$jenis_notifikasi = "info";

switch ($status) {
    case "berhasil":
        $notifikasi = "Akun admin berhasil ditambahkan.";
        $jenis_notifikasi = "success";
        break;

    case "duplikat":
        $notifikasi = "Username sudah digunakan. Silakan gunakan username lain.";
        $jenis_notifikasi = "warning";
        break;

    case "gagal":
        $notifikasi = "Akun admin gagal diproses. Silakan coba kembali.";
        $jenis_notifikasi = "error";
        break;

    case "edit_berhasil":
        $notifikasi = "Data admin berhasil diperbarui.";
        $jenis_notifikasi = "success";
        break;

    case "hapus_berhasil":
        $notifikasi = "Akun admin berhasil dihapus.";
        $jenis_notifikasi = "success";
        break;

    case "tidak_bisa_hapus":
        $notifikasi = "Akun yang sedang kamu gunakan tidak bisa dihapus.";
        $jenis_notifikasi = "warning";
        break;

    case "admin_tidak_ditemukan":
        $notifikasi = "Data admin tidak ditemukan.";
        $jenis_notifikasi = "error";
        break;

    case "username_tidak_valid":
        $notifikasi = "Format username tidak valid.";
        $jenis_notifikasi = "warning";
        break;

    case "password_tidak_valid":
        $notifikasi = "Password baru minimal 8 karakter.";
        $jenis_notifikasi = "warning";
        break;

    case "input_tidak_valid":
        $notifikasi = "Nama atau username melebihi batas karakter.";
        $jenis_notifikasi = "warning";
        break;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manajemen Admin | Buku Tamu Digital DKP Riau</title>

    <link
        rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
    >

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" type="image/png" href="../assets/img/SAPA_icon.png">
    

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<?php require_once "sidebar.php"; ?>

<main class="admin-content">
    <div class="admin-content-inner">

        <div class="page-header">
            <div>
                <h1>Manajemen Admin</h1>
                <p>
                    Kelola akun pengguna Buku Tamu Digital
                    Dinas Kelautan dan Perikanan Provinsi Riau.
                </p>
            </div>
        </div>

        <!-- FORM TAMBAH ADMIN -->
        <section class="admins-card">

            <div class="admins-card-header">
                <div>
                    <h2>Tambah Admin</h2>
                    <p>Isi informasi berikut untuk membuat akun admin baru.</p>
                </div>
            </div>

            <form action="proses_tambah_admin.php" method="POST">

                <div class="admins-form-grid">

                    <div class="admins-form-group">
                        <label for="nama">Nama Lengkap</label>
                        <input
                            type="text"
                            id="nama"
                            name="nama"
                            maxlength="100"
                            placeholder="Masukkan nama lengkap"
                            autocomplete="name"
                            required
                        >
                    </div>

                    <div class="admins-form-group">
                        <label for="username">Username</label>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            maxlength="50"
                            placeholder="Masukkan username"
                            autocomplete="username"
                            required
                        >
                    </div>

                    <div class="admins-form-group admins-password-group">
                        <label for="password">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            minlength="8"
                            placeholder="Minimal 8 karakter"
                            autocomplete="new-password"
                            required
                        >
                        <small>Password minimal 8 karakter.</small>
                    </div>

                </div>

                <div class="admins-form-actions">
                    <button type="submit" class="admins-submit-button">
                        + Tambah Admin
                    </button>
                </div>

            </form>
        </section>

        <!-- DAFTAR ADMIN -->
        <section class="admins-card">

            <div class="admins-card-header admins-list-header">
                <div>
                    <h2>Daftar Admin</h2>
                    <p>Seluruh akun yang terdaftar di sistem.</p>
                </div>

                <div class="admins-total">
                    Total akun:
                    <strong><?= $total_admin; ?></strong>
                </div>
            </div>

            <div class="admins-table-wrapper">
                <table class="admins-table">

                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Nama Lengkap</th>
                            <th>Username</th>
                            <th>Level</th>
                            <th>Tanggal Dibuat</th>
                            <th class="admins-action-heading">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (!empty($data_admin)): ?>

                        <?php $no = 1; ?>

                        <?php foreach ($data_admin as $admin): ?>
                            <?php
                            $adminId = (int) $admin["id"];
                            $akunSendiri = (
                                $adminId === (int) $_SESSION["admin_id"]
                            );
                            ?>

                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <?= htmlspecialchars(
                                        $admin["nama"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $admin["username"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ); ?>
                                </td>

                                <td>
                                    <span class="admins-level <?= $admin["level"] === "master"
                                        ? "level-master"
                                        : "level-admin"; ?>">
                                        <?= htmlspecialchars(
                                            strtoupper($admin["level"]),
                                            ENT_QUOTES,
                                            "UTF-8"
                                        ); ?>
                                    </span>
                                </td>

                                <td>
                                    <?php
                                    if (!empty($admin["created_at"])) {
                                        echo htmlspecialchars(
                                            date(
                                                "d-m-Y H:i",
                                                strtotime($admin["created_at"])
                                            ),
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );
                                    } else {
                                        echo "-";
                                    }
                                    ?>
                                </td>

                                <!-- AKSI ADMIN -->
                                <td class="admins-action-cell">
                                    <div class="admins-action-buttons">

                                        <!-- Edit -->
                                        <a
                                            href="edit_admin.php?id=<?= $adminId; ?>"
                                            class="btn-edit"
                                            title="Edit admin"
                                            aria-label="Edit admin"
                                        >✏️</a>

                                        <!-- Hapus -->
                                        <?php if (!$akunSendiri): ?>
                                            <button
                                                type="button"
                                                class="btn-delete"
                                                data-form-id="hapus-admin-<?= $adminId; ?>"
                                                title="Hapus admin"
                                                aria-label="Hapus admin"
                                            >🗑️</button>
                                        <?php else: ?>
                                            <button
                                                type="button"
                                                class="btn-delete"
                                                title="Akun sendiri tidak dapat dihapus"
                                                aria-label="Akun sendiri tidak dapat dihapus"
                                                disabled
                                            >🗑️</button>
                                        <?php endif; ?>

                                    </div>

                                    <!-- Form hapus -->
                                    <?php if (!$akunSendiri): ?>
                                        <form
                                            id="hapus-admin-<?= $adminId; ?>"
                                            action="hapus_admin.php"
                                            method="POST"
                                            hidden
                                        >
                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?= $adminId; ?>"
                                            >
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="admins-empty">
                                Belum ada akun admin.
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>

                </table>
            </div>

        </section>

    </div>
</main>

<script>
document.addEventListener("DOMContentLoaded", function () {

    // ========================================
    // 1. POPUP NOTIFIKASI DARI PHP
    // ========================================

    const status = <?= json_encode($status); ?>;
    const pesan = <?= json_encode($notifikasi); ?>;
    const jenis = <?= json_encode($jenis_notifikasi); ?>;

    if (status && pesan) {

        // Hapus parameter status dari URL supaya popup
        // tidak muncul lagi ketika halaman direfresh.
        const url = new URL(window.location.href);
        url.searchParams.delete("status");

        window.history.replaceState(
            {},
            document.title,
            url.pathname + url.search + url.hash
        );

        if (typeof Swal !== "undefined") {
            Swal.fire({
                icon: jenis,
                title: jenis === "success"
                    ? "Berhasil!"
                    : jenis === "warning"
                        ? "Perhatian!"
                        : "Gagal!",
                text: pesan,
                showConfirmButton: false,
                timer: 1500,
                timerProgressBar: true,
                heightAuto: false
            });
        } else {
            alert(pesan);
        }
    }

    // ========================================
    // 2. KONFIRMASI SEBELUM HAPUS ADMIN
    // ========================================

    document.querySelectorAll(
        ".admins-action-buttons .btn-delete:not(:disabled)"
    ).forEach(function (tombol) {

        tombol.addEventListener("click", function () {

            const formId = tombol.dataset.formId;
            const formHapus = document.getElementById(formId);

            if (!formHapus) {
                return;
            }

            if (typeof Swal === "undefined") {
                if (confirm("Yakin ingin menghapus akun admin ini?")) {
                    formHapus.submit();
                }
                return;
            }

            Swal.fire({
                title: "Yakin ingin menghapus?",
                text: "Akun admin yang dihapus tidak dapat dipulihkan.",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ya, Hapus",
                cancelButtonText: "Batal",
                confirmButtonColor: "#dc3545",
                cancelButtonColor: "#6c757d",
                reverseButtons: true,
                focusCancel: true
            }).then(function (hasil) {
                if (hasil.isConfirmed) {
                    formHapus.submit();
                }
            });

        });

    });

});
</script>
<?php include "../footer.php"; ?>
</body>
</html>
