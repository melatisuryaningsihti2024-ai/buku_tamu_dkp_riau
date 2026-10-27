
<?php
require_once "../config/database.php";

// ==============================
// FILTER TANGGAL DAN BIDANG
// ==============================
$tanggal_awal  = $_GET['tanggal_dari'] ?? '';
$tanggal_akhir = $_GET['tanggal_sampai'] ?? '';
$bidang        = $_GET['bidang'] ?? '';

if (empty($tanggal_awal) || empty($tanggal_akhir)) {
    die("Tanggal awal dan tanggal akhir harus dipilih terlebih dahulu.");
}

if (
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_awal) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal_akhir) ||
    !checkdate(
        (int) substr($tanggal_awal, 5, 2),
        (int) substr($tanggal_awal, 8, 2),
        (int) substr($tanggal_awal, 0, 4)
    ) ||
    !checkdate(
        (int) substr($tanggal_akhir, 5, 2),
        (int) substr($tanggal_akhir, 8, 2),
        (int) substr($tanggal_akhir, 0, 4)
    ) ||
    $tanggal_awal > $tanggal_akhir
) {
    die("Rentang tanggal tidak valid.");
}

// ==============================
// QUERY DATA TAMU
// ==============================
$sql = "SELECT
            id,
            nama_lengkap,
            instansi,
            no_telepon,
            tujuan,
            bidang_tujuan,
            jumlah_rombongan,
            tanggal_kunjungan,
            waktu_kunjungan
        FROM tamu
        WHERE tanggal_kunjungan BETWEEN ? AND ?";

if ($bidang !== '') {
    $sql .= " AND bidang_tujuan = ?";
}

$sql .= " ORDER BY tanggal_kunjungan ASC, waktu_kunjungan ASC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Gagal menyiapkan query database.");
}

if ($bidang !== '') {
    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $tanggal_awal,
        $tanggal_akhir,
        $bidang
    );
} else {
    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $tanggal_awal,
        $tanggal_akhir
    );
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// ==============================
// FUNGSI FORMAT TANGGAL
// ==============================
function tanggalIndonesia($tanggal)
{
    $bulan = [
        1 => 'Januari',
        'Februari',
        'Maret',
        'April',
        'Mei',
        'Juni',
        'Juli',
        'Agustus',
        'September',
        'Oktober',
        'November',
        'Desember'
    ];

    $pecah = explode('-', $tanggal);

    return (int) $pecah[2] . ' ' .
           $bulan[(int) $pecah[1]] . ' ' .
           $pecah[0];
}

function aman($nilai)
{
    return htmlspecialchars(
        (string) ($nilai ?? ''),
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

// ==============================
// LOGO DINAS
// ==============================
$path_logo = realpath(__DIR__ . "/../assets/img/logo.png");
$logo_src = '';

if ($path_logo && is_file($path_logo)) {
    $isi_logo = file_get_contents($path_logo);

    if ($isi_logo !== false) {
        $logo_src = 'data:image/png;base64,' .
                    base64_encode($isi_logo);
    }
}

// ==============================
// HEADER DOWNLOAD WORD
// ==============================
$nama_file = "Daftar_Tamu_" .
    $tanggal_awal . "_sampai_" .
    $tanggal_akhir . ".doc";

header("Content-Type: application/msword; charset=UTF-8");
header(
    "Content-Disposition: attachment; filename=\"" .
    $nama_file . "\""
);
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Daftar Tamu SAPA</title>

<style>
@page Section1 {
    size: 841.9pt 595.3pt;
    mso-page-orientation: landscape;
    margin: 28pt 28pt 28pt 28pt;
}

div.Section1 {
    page: Section1;
}

body {
    font-family: Arial, sans-serif;
    font-size: 9pt;
    color: #000000;
    margin: 0;
}

.kop {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin-bottom: 12pt;
}

.kop td {
    border: none;
    vertical-align: middle;
}
.kop .logo {
    width: 20pt;
    text-align: left;
    vertical-align: middle;
}

.kop .logo img {
    width: 35pt;
    height: auto;
}


.kop-text {
    text-align: center;
    padding-right: 75pt;
}

.kop-text h2 {
    font-size: 15pt;
    margin: 0 0 4pt;
    font-weight: bold;
}

.kop-text h3 {
    font-size: 12pt;
    margin: 0 0 4pt;
    font-weight: bold;
}

.kop-text p {
    font-size: 9pt;
    margin: 0;
}

.garis-kop {
    border: none;
    border-top: 1.5pt solid #000000;
    margin: 8pt 0 15pt;
}

.judul {
    text-align: center;
    font-size: 12pt;
    font-weight: bold;
    margin: 0 0 7pt;
}

.periode {
    text-align: center;
    font-size: 10pt;
    margin: 0 0 14pt;
}

.data-tamu {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    mso-table-layout-alt: fixed;
}

.data-tamu th,
.data-tamu td {
    border: 0.7pt solid #000000;
    padding: 4pt 3pt;
    font-size: 8pt;
    vertical-align: top;
    word-wrap: break-word;
}

.data-tamu th {
    text-align: center;
    vertical-align: middle;
    font-weight: bold;
    background-color: #E8EEF5;
}

.data-tamu .c-no {
    width: 3%;
    text-align: center;
}

.data-tamu .c-nama {
    width: 12%;
}

.data-tamu .c-instansi {
    width: 11%;
}

.data-tamu .c-telepon {
    width: 10%;
}

.data-tamu .c-tujuan {
    width: 19%;
}

.data-tamu .c-bidang {
    width: 11%;
}

.data-tamu .c-jumlah {
    width: 9%;
    text-align: center;
}

.data-tamu .c-tanggal {
    width: 13%;
}

.data-tamu .c-waktu {
    width: 12%;
}

.kosong {
    text-align: center;
    padding: 12pt;
}
</style>
</head>

<body>
<div class="Section1">

    <!-- KOP DINAS -->
    <table class="kop">
        <tr>
            <td class="logo">
                <?php if ($logo_src !== ''): ?>
                    <img
                        src="<?= $logo_src ?>"
                        alt="Logo Provinsi Riau"
                    >
                <?php endif; ?>
            </td>

            <td class="kop-text">
                <h2>PEMERINTAH PROVINSI RIAU</h2>
                <h3>DINAS KELAUTAN DAN PERIKANAN</h3>
                <p>Pekanbaru, Provinsi Riau</p>
            </td>
        </tr>
    </table>

    <hr class="garis-kop">

    <!-- JUDUL DAN PERIODE -->
    <p class="judul">
        DAFTAR TAMU DINAS KELAUTAN DAN PERIKANAN PROVINSI RIAU
    </p>

    <p class="periode">
        Dari <?= aman(tanggalIndonesia($tanggal_awal)) ?>
        sampai <?= aman(tanggalIndonesia($tanggal_akhir)) ?>
    </p>

    <!-- TABEL DATA TAMU -->
    <table class="data-tamu">
        <thead>
            <tr>
                <th class="c-no">No.</th>
                <th class="c-nama">Nama Lengkap</th>
                <th class="c-instansi">Instansi</th>
                <th class="c-telepon">No. Telepon</th>
                <th class="c-tujuan">Tujuan Kunjungan</th>
                <th class="c-bidang">Bidang Tujuan</th>
                <th class="c-jumlah">Jumlah Rombongan</th>
                <th class="c-tanggal">Tanggal Kunjungan</th>
                <th class="c-waktu">Waktu Kunjungan</th>
            </tr>
        </thead>

        <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php $no = 1; ?>

            <?php while ($tamu = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td class="c-no"><?= $no++ ?></td>
                    <td class="c-nama"><?= aman($tamu['nama_lengkap']) ?></td>
                    <td class="c-instansi"><?= aman($tamu['instansi']) ?></td>
                    <td class="c-telepon"><?= aman($tamu['no_telepon']) ?></td>
                    <td class="c-tujuan"><?= aman($tamu['tujuan']) ?></td>
                    <td class="c-bidang"><?= aman($tamu['bidang_tujuan']) ?></td>
                    <td class="c-jumlah"><?= aman($tamu['jumlah_rombongan']) ?></td>
                    <td class="c-tanggal">
                        <?= aman(tanggalIndonesia($tamu['tanggal_kunjungan'])) ?>
                    </td>
                    <td class="c-waktu"><?= aman($tamu['waktu_kunjungan']) ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="9" class="kosong">
                    Tidak ada data tamu pada periode tersebut.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>

</div>
</body>
</html>
<?php
mysqli_stmt_close($stmt);
?>
