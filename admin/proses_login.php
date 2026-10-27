
<?php

session_start();

require_once "../config/database.php";

// ========================================
// CEK REQUEST
// ========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: login.php");
    exit;
}

// ========================================
// AMBIL DATA LOGIN
// ========================================

$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";

// ========================================
// CEK DATA KOSONG
// ========================================

if ($username === "" || $password === "") {
    header("Location: login.php?error=empty");
    exit;
}

// ========================================
// CARI DATA ADMIN
// ========================================

$sql = "SELECT id, nama, username, password, level
        FROM admins
        WHERE username = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    header("Location: login.php?error=system");
    exit;
}

mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$admin = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

// ========================================
// VALIDASI USERNAME
// ========================================

if (!$admin) {
    mysqli_close($conn);

    header("Location: login.php?error=username");
    exit;
}

// ========================================
// VALIDASI PASSWORD
// ========================================

if (!password_verify($password, $admin["password"])) {
    mysqli_close($conn);

    header("Location: login.php?error=password");
    exit;
}

// ========================================
// LOGIN BERHASIL
// ========================================

session_regenerate_id(true);

$_SESSION["admin_id"] = $admin["id"];
$_SESSION["admin_nama"] = $admin["nama"];
$_SESSION["admin_username"] = $admin["username"];
$_SESSION["admin_level"] = $admin["level"];

mysqli_close($conn);

header("Location: dashboard.php");
exit;

?>
