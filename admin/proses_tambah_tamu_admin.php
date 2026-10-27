<?php
session_start();

date_default_timezone_set("Asia/Jakarta");

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

// ========================================
// FUNGSI REDIRECT
// ========================================

function kembali_tambah_tamu($status, $pesan = "")
{
    $url = "tambah_tamu.php?status=" . urlencode($status);

    if ($pesan !== "") {
        $url .= "&pesan=" . urlencode($pesan);
    }

    header("Location: " . $url);
    exit;
}

// ========================================
// CEK METODE REQUEST
// ========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    kembali_tambah_tamu(
        "gagal",
        "Permintaan tidak valid."
    );
}

// ========================================
// AMBIL DATA FORM
// ========================================

$nama_lengkap = trim($_POST["nama_lengkap"] ?? "");
$instansi = trim($_POST["instansi"] ?? "");
$no_telepon = trim($_POST["no_telepon"] ?? "");
$bidang_tujuan = trim($_POST["bidang_tujuan"] ?? "");
$tujuan = trim($_POST["tujuan"] ?? "");
$jumlah_raw = trim((string) ($_POST["jumlah_rombongan"] ?? ""));
$tanggal_kunjungan = trim($_POST["tanggal_kunjungan"] ?? "");
$waktu_kunjungan = trim($_POST["waktu_kunjungan"] ?? "");

// ========================================
// DAFTAR BIDANG YANG DIIZINKAN
// ========================================

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

// ========================================
// FUNGSI PANJANG TEKS
// ========================================

function panjang_teks($teks)
{
    return function_exists("mb_strlen")
        ? mb_strlen($teks, "UTF-8")
        : strlen($teks);
}

// ========================================
// VALIDASI FIELD WAJIB
// ========================================

if (
    $nama_lengkap === "" ||
    $instansi === "" ||
    $bidang_tujuan === "" ||
    $tujuan === "" ||
    $tanggal_kunjungan === "" ||
    $waktu_kunjungan === "" ||
    $jumlah_raw === ""
) {
    kembali_tambah_tamu(
        "gagal",
        "Semua kolom wajib harus diisi."
    );
}

// ========================================
// VALIDASI NAMA DAN INSTANSI
// Mengikuti batas pada proses pengunjung
// ========================================

if (
    panjang_teks($nama_lengkap) < 2 ||
    panjang_teks($nama_lengkap) > 100
) {
    kembali_tambah_tamu(
        "gagal",
        "Nama lengkap harus terdiri dari 2 sampai 100 karakter."
    );
}

if (
    panjang_teks($instansi) < 2 ||
    panjang_teks($instansi) > 150
) {
    kembali_tambah_tamu(
        "gagal",
        "Nama instansi harus terdiri dari 2 sampai 150 karakter."
    );
}

// ========================================
// VALIDASI TUJUAN KUNJUNGAN
// ========================================

if (
    panjang_teks($tujuan) < 5 ||
    panjang_teks($tujuan) > 1000
) {
    kembali_tambah_tamu(
        "gagal",
        "Tujuan kunjungan harus terdiri dari 5 sampai 1000 karakter."
    );
}

// ========================================
// VALIDASI BIDANG
// ========================================

if (!in_array($bidang_tujuan, $daftar_bidang, true)) {
    kembali_tambah_tamu(
        "gagal",
        "Bidang atau unit tujuan tidak valid."
    );
}

// ========================================
// VALIDASI NOMOR TELEPON
// Boleh kosong
// ========================================

if ($no_telepon !== "") {

    if (!preg_match('/^[0-9+\s().-]+$/', $no_telepon)) {
        kembali_tambah_tamu(
            "gagal",
            "Nomor telepon hanya boleh berisi angka dan pemisah nomor."
        );
    }

    $nomor_bersih = preg_replace(
        '/[\s().-]/',
        '',
        $no_telepon
    );

    if (strpos($nomor_bersih, "+62") === 0) {
        $nomor_bersih = "0" . substr($nomor_bersih, 3);
    } elseif (strpos($nomor_bersih, "62") === 0) {
        $nomor_bersih = "0" . substr($nomor_bersih, 2);
    } elseif (strpos($nomor_bersih, "8") === 0) {
        $nomor_bersih = "0" . $nomor_bersih;
    }

    if (!preg_match('/^08[0-9]{8,11}$/', $nomor_bersih)) {
        kembali_tambah_tamu(
            "gagal",
            "Nomor telepon tidak valid. Gunakan nomor HP Indonesia dengan awalan 08."
        );
    }

    $no_telepon = $nomor_bersih;
}

// ========================================
// VALIDASI JUMLAH ROMBONGAN
// ========================================

$jumlah_rombongan = filter_var(
    $jumlah_raw,
    FILTER_VALIDATE_INT
);

if (
    $jumlah_rombongan === false ||
    $jumlah_rombongan < 1 ||
    $jumlah_rombongan > 1000
) {
    kembali_tambah_tamu(
        "gagal",
        "Jumlah rombongan harus berupa angka bulat antara 1 sampai 1000."
    );
}

// ========================================
// VALIDASI TANGGAL
// Format dari input type=date: YYYY-MM-DD
// ========================================

$tanggal_obj = DateTime::createFromFormat(
    "!Y-m-d",
    $tanggal_kunjungan
);

if (
    !$tanggal_obj ||
    $tanggal_obj->format("Y-m-d") !== $tanggal_kunjungan
) {
    kembali_tambah_tamu(
        "gagal",
        "Tanggal kunjungan tidak valid."
    );
}

// ========================================
// VALIDASI WAKTU
// Format input type=time: HH:MM
// ========================================

$waktu_obj = DateTime::createFromFormat(
    "!H:i",
    $waktu_kunjungan
);

if (
    !$waktu_obj ||
    $waktu_obj->format("H:i") !== $waktu_kunjungan
) {
    kembali_tambah_tamu(
        "gagal",
        "Waktu kunjungan tidak valid."
    );
}

// Database menyimpan waktu dalam format HH:MM:SS
$waktu_kunjungan = $waktu_obj->format("H:i:s");

// ========================================
// SIMPAN KE DATABASE
// ========================================

$sql = "INSERT INTO tamu (
            nama_lengkap,
            instansi,
            no_telepon,
            tujuan,
            bidang_tujuan,
            jumlah_rombongan,
            tanggal_kunjungan,
            waktu_kunjungan
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    error_log("Prepare tambah tamu gagal: " . mysqli_error($conn));

    kembali_tambah_tamu(
        "gagal",
        "Terjadi kesalahan pada sistem. Silakan coba kembali."
    );
}

mysqli_stmt_bind_param(
    $stmt,
    "sssssiss",
    $nama_lengkap,
    $instansi,
    $no_telepon,
    $tujuan,
    $bidang_tujuan,
    $jumlah_rombongan,
    $tanggal_kunjungan,
    $waktu_kunjungan
);

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    kembali_tambah_tamu("berhasil");

} else {

    error_log("Insert tambah tamu gagal: " . mysqli_stmt_error($stmt));

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    kembali_tambah_tamu(
        "gagal",
        "Data gagal disimpan. Silakan coba kembali."
    );
}
