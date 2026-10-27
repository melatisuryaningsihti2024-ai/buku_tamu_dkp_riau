
<?php

require_once "config/database.php";

header("Content-Type: application/json; charset=UTF-8");

// ========================================
// FUNGSI RESPONS JSON
// ========================================

function kirim_respons($status, $message)
{
    echo json_encode([
        "status" => $status,
        "message" => $message
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

// ========================================
// CEK METODE PERMINTAAN
// ========================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    kirim_respons("error", "Permintaan tidak valid.");
}

// ========================================
// AMBIL DATA FORM
// ========================================

$nama_lengkap = trim($_POST["nama_lengkap"] ?? "");
$instansi = trim($_POST["instansi"] ?? "");
$no_telepon = trim($_POST["no_telepon"] ?? "");
$bidang_tujuan = trim($_POST["bidang_tujuan"] ?? "");
$tujuan = trim($_POST["tujuan"] ?? "");
$jumlah_raw = trim((string) ($_POST["jumlah_rombongan"] ?? "1"));

// ========================================
// FUNGSI MENGHITUNG PANJANG TEKS
// ========================================

function panjang_teks($teks)
{
    if (function_exists("mb_strlen")) {
        return mb_strlen($teks, "UTF-8");
    }

    return strlen($teks);
}

// ========================================
// VALIDASI FIELD WAJIB
// ========================================

if (
    $nama_lengkap === "" ||
    $instansi === "" ||
    $bidang_tujuan === "" ||
    $tujuan === ""
) {
    kirim_respons(
        "error",
        "Nama lengkap, instansi, bidang tujuan, dan tujuan kunjungan wajib diisi."
    );
}

// ========================================
// VALIDASI NAMA LENGKAP
// Minimal 2 karakter, maksimal 100 karakter
// ========================================

if (
    panjang_teks($nama_lengkap) < 2 ||
    panjang_teks($nama_lengkap) > 100
) {
    kirim_respons(
        "error",
        "Nama lengkap harus terdiri dari 2 sampai 100 karakter."
    );
}

// ========================================
// VALIDASI INSTANSI
// Minimal 2 karakter, maksimal 150 karakter
// ========================================

if (
    panjang_teks($instansi) < 2 ||
    panjang_teks($instansi) > 150
) {
    kirim_respons(
        "error",
        "Nama instansi harus terdiri dari 2 sampai 150 karakter."
    );
}

// ========================================
// VALIDASI TUJUAN KUNJUNGAN
// Minimal 5 karakter, maksimal 1000 karakter
// ========================================

if (
    panjang_teks($tujuan) < 5 ||
    panjang_teks($tujuan) > 1000
) {
    kirim_respons(
        "error",
        "Tujuan kunjungan harus terdiri dari 5 sampai 1000 karakter."
    );
}

// ========================================
// VALIDASI NOMOR HP INDONESIA
// Nomor HP boleh kosong.
// Jika diisi, harus berupa nomor yang valid.
// ========================================

if ($no_telepon !== "") {

    // Izinkan angka dan pemisah nomor yang umum.
    // Huruf dan simbol lain akan ditolak.

    if (!preg_match('/^[0-9+\s().-]+$/', $no_telepon)) {
        kirim_respons(
            "error",
            "Nomor HP hanya boleh berisi angka dan pemisah nomor yang wajar. Huruf tidak diperbolehkan."
        );
    }

    // Hapus spasi, tanda kurung, titik, dan tanda hubung.
    $nomor_bersih = preg_replace('/[\s().-]/', '', $no_telepon);

    // Ubah awalan +62 atau 62 menjadi 0.
    if (strpos($nomor_bersih, "+62") === 0) {
        $nomor_bersih = "0" . substr($nomor_bersih, 3);
    } elseif (strpos($nomor_bersih, "62") === 0) {
        $nomor_bersih = "0" . substr($nomor_bersih, 2);
    } elseif (strpos($nomor_bersih, "8") === 0) {
        $nomor_bersih = "0" . $nomor_bersih;
    }

    // Nomor HP Indonesia: awalan 08, total 10-13 digit.
    if (!preg_match('/^08[0-9]{8,11}$/', $nomor_bersih)) {
        kirim_respons(
            "error",
            "Nomor HP tidak valid. Masukkan nomor HP Indonesia dengan awalan 08 dan panjang 10 sampai 13 digit."
        );
    }

    // Simpan dalam format lokal yang konsisten.
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
    kirim_respons(
        "error",
        "Jumlah rombongan harus berupa angka bulat antara 1 sampai 1000."
    );
}

// ========================================
// TANGGAL DAN WAKTU KUNJUNGAN
// ========================================

date_default_timezone_set("Asia/Jakarta");

$tanggal_kunjungan = date("Y-m-d");
$waktu_kunjungan = date("H:i:s");

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
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    kirim_respons(
        "error",
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

// ========================================
// HASIL SIMPAN
// ========================================

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    kirim_respons(
        "success",
        "Terima kasih, data kunjungan Anda berhasil disimpan."
    );

} else {

    mysqli_stmt_close($stmt);
    mysqli_close($conn);

    kirim_respons(
        "error",
        "Data gagal disimpan. Silakan coba kembali."
    );
}
