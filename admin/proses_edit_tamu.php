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
// CEK METHOD
// =========================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: data_tamu.php");
    exit;
}


// =========================
// AMBIL DATA FORM
// =========================

$id = (int) ($_POST["id"] ?? 0);

$nama_lengkap = trim(
    $_POST["nama_lengkap"] ?? ""
);

$instansi = trim(
    $_POST["instansi"] ?? ""
);

$no_telepon = trim(
    $_POST["no_telepon"] ?? ""
);

$bidang_tujuan = trim(
    $_POST["bidang_tujuan"] ?? ""
);

$tujuan = trim(
    $_POST["tujuan"] ?? ""
);

$jumlah_rombongan = (int) (
    $_POST["jumlah_rombongan"] ?? 1
);


// =========================
// VALIDASI
// =========================

if (
    $id <= 0 ||
    $nama_lengkap === "" ||
    $instansi === "" ||
    $bidang_tujuan === "" ||
    $tujuan === ""
) {
    header(
        "Location: edit_tamu.php?id="
        . $id
        . "&error=empty"
    );

    exit;
}


if ($jumlah_rombongan < 1) {
    $jumlah_rombongan = 1;
}


// =========================
// UPDATE DATA
// =========================
//
// Tanggal dan waktu sengaja
// TIDAK diubah.
//

$sql = "UPDATE tamu
        SET
            nama_lengkap = ?,
            instansi = ?,
            no_telepon = ?,
            bidang_tujuan = ?,
            tujuan = ?,
            jumlah_rombongan = ?
        WHERE id = ?";


$stmt = mysqli_prepare(
    $conn,
    $sql
);


if (!$stmt) {
    header(
        "Location: edit_tamu.php?id="
        . $id
        . "&error=system"
    );

    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "sssssii",
    $nama_lengkap,
    $instansi,
    $no_telepon,
    $bidang_tujuan,
    $tujuan,
    $jumlah_rombongan,
    $id
);


// =========================
// EKSEKUSI
// =========================

if (mysqli_stmt_execute($stmt)) {

    header(
        "Location: data_tamu.php?edit=success"
    );

    exit;

}


header(
    "Location: edit_tamu.php?id="
    . $id
    . "&error=system"
);

exit;

?>