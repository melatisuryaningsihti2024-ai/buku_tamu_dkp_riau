
<?php

session_start();
require_once "../config/database.php";

// Pastikan pengguna sudah login
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

// Hanya master yang boleh mengedit akun admin
if (($_SESSION["admin_level"] ?? "") !== "master") {
    header("Location: dashboard.php");
    exit;
}

// Proses hanya melalui POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admins.php");
    exit;
}

// Ambil dan validasi input
$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);
$nama = trim($_POST["nama"] ?? "");
$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

if (!$id || $id < 1 || $nama === "" || $username === "") {
    header("Location: admins.php?status=gagal");
    exit;
}

// Validasi panjang nama dan username
if (mb_strlen($nama) > 100 || mb_strlen($username) > 50) {
    header("Location: edit_admin.php?id=" . (int) $id . "&status=input_tidak_valid");
    exit;
}

// Validasi format username
if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
    header("Location: edit_admin.php?id=" . (int) $id . "&status=username_tidak_valid");
    exit;
}

// Validasi password jika ingin menggantinya
if ($password !== "" && strlen($password) < 8) {
    header("Location: edit_admin.php?id=" . (int) $id . "&status=password_tidak_valid");
    exit;
}

// Pastikan akun yang diedit tersedia
$stmt = mysqli_prepare(
    $conn,
    "SELECT id FROM admins WHERE id = ?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$admin) {
    header("Location: admins.php?status=admin_tidak_ditemukan");
    exit;
}

// Periksa apakah username sudah digunakan akun lain
$stmt = mysqli_prepare(
    $conn,
    "SELECT id FROM admins WHERE username = ? AND id != ? LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "si", $username, $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$username_duplikat = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if ($username_duplikat) {
    header("Location: edit_admin.php?id=" . (int) $id . "&status=duplikat");
    exit;
}

// Perbarui data sesuai apakah password diganti atau tidak
if ($password !== "") {
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE admins
         SET nama = ?, username = ?, password = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssi",
        $nama,
        $username,
        $password_hash,
        $id
    );
} else {
    $stmt = mysqli_prepare(
        $conn,
        "UPDATE admins
         SET nama = ?, username = ?
         WHERE id = ?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssi",
        $nama,
        $username,
        $id
    );
}

// Jalankan pembaruan
if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    header("Location: admins.php?status=edit_berhasil");
    exit;
}

mysqli_stmt_close($stmt);

header("Location: edit_admin.php?id=" . (int) $id . "&status=gagal");
exit;
