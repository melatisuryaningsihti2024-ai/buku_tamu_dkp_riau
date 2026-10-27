<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}


// =========================
// CEK LEVEL
// =========================

if (($_SESSION["admin_level"] ?? "") !== "master") {
    header("Location: data_tamu.php?error=permission");
    exit;
}


require_once "../config/database.php";

$id = (int) ($_GET["id"] ?? 0);

if ($id <= 0) {
    header("Location: data_tamu.php");
    exit;
}


// =========================
// HAPUS DATA
// =========================

$sql = "DELETE FROM tamu WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);


// =========================
// KEMBALI
// =========================

header("Location: data_tamu.php?deleted=1");
exit;

?>