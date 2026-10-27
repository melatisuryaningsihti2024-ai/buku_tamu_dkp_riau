
<?php
session_start();
require_once "../config/database.php";

// Pastikan pengguna sudah login
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Hanya master yang boleh menghapus akun admin
if (($_SESSION["admin_level"] ?? "") !== "master") {
    header("Location: dashboard.php");
    exit;
}

// Penghapusan hanya boleh melalui POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admins.php");
    exit;
}

// Validasi ID admin
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    header("Location: admins.php?status=admin_tidak_ditemukan");
    exit;
}

// Larang menghapus akun yang sedang digunakan
if ((int) $id === (int) $_SESSION["admin_id"]) {
    header("Location: admins.php?status=tidak_bisa_hapus");
    exit;
}

// Pastikan akun yang akan dihapus memang ada
$stmt = mysqli_prepare(
    $conn,
    "SELECT id FROM admins WHERE id = ?"
);

if (!$stmt) {
    header("Location: admins.php?status=gagal");
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$admin) {
    header("Location: admins.php?status=admin_tidak_ditemukan");
    exit;
}

// Hapus akun admin
$stmt = mysqli_prepare(
    $conn,
    "DELETE FROM admins WHERE id = ?"
);

if (!$stmt) {
    header("Location: admins.php?status=gagal");
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
$berhasil = mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

// Kembali ke daftar admin dengan status hasil proses
if ($berhasil) {
    header("Location: admins.php?status=hapus_berhasil");
} else {
    header("Location: admins.php?status=gagal");
}

exit;
?>
