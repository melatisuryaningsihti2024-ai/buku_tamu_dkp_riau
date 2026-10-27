
<?php

session_start();

require_once "../config/database.php";

// Hanya menerima POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admins.php");
    exit;
}

// Pastikan sudah login sebagai master
if (
    !isset($_SESSION["admin_id"]) ||
    ($_SESSION["admin_level"] ?? "") !== "master"
) {
    http_response_code(403);
    exit("Akses ditolak.");
}

// Ambil dan bersihkan input
$nama = trim($_POST["nama"] ?? "");
$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

// Validasi input
if (
    $nama === "" ||
    $username === "" ||
    $password === "" ||
    strlen($password) < 8 ||
    strlen($nama) > 100 ||
    strlen($username) > 50
) {
    header("Location: admins.php?status=gagal");
    exit;
}

// Validasi karakter username
if (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
    header("Location: admins.php?status=gagal");
    exit;
}

// Cek apakah username sudah digunakan
$sql = "SELECT id FROM admins WHERE username = ? LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    header("Location: admins.php?status=gagal");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_fetch_assoc($result)) {
    mysqli_stmt_close($stmt);

    header("Location: admins.php?status=duplikat");
    exit;
}

mysqli_stmt_close($stmt);

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Akun yang dibuat dari halaman ini selalu berlevel admin
$level = "admin";

$sql = "INSERT INTO admins (nama, username, password, level)
        VALUES (?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    header("Location: admins.php?status=gagal");
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "ssss",
    $nama,
    $username,
    $password_hash,
    $level
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    header("Location: admins.php?status=berhasil");
    exit;
}

$error_number = mysqli_stmt_errno($stmt);
mysqli_stmt_close($stmt);

// Menangani username duplikat jika terjadi bentrok saat insert
if ($error_number === 1062) {
    header("Location: admins.php?status=duplikat");
    exit;
}

header("Location: admins.php?status=gagal");
exit;
