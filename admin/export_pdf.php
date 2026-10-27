
<?php
require_once "../config/database.php";
require_once "../vendor/autoload.php";

use Dompdf\Dompdf;
use Dompdf\Options;

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
// AMBIL DATA TAMU
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

$data_tamu = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data_tamu[] = $row;
}

mysqli_stmt_close($stmt);

// ==============================
// FORMAT TANGGAL INDONESIA
// ==============================
function tanggalIndonesiaPdf($tanggal)
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

    return (int)$pecah[2] . ' ' .
           $bulan[(int)$pecah[1]] . ' ' .
           $pecah[0];
}

function amanPdf($nilai)
{
    return htmlspecialchars(
        (string)($nilai ?? ''),
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
// SUSUN HTML PDF
// ==============================
ob_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">

<style>
@page {
    margin: 25px 28px;
}

body {
    font-family: DejaVu Sans, sans-serif;
    font-size: 9px;
    color: #000;
}

.kop {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 12px;
}

.kop td {
    border: none;
    vertical-align: middle;
}

.logo {
    width: 65px;
}

.logo img {
    width: 45px;
    height: auto;
}

.kop-text {
    text-align: center;
    padding-right: 65px;
}

.kop-text h2 {
    font-size: 15px;
    margin: 0 0 4px;
}

.kop-text h3 {
    font-size: 12px;
    margin: 0 0 4px;
}

.kop-text p {
    font-size: 9px;
    margin: 0;
}

.garis-kop {
    border-top: 2px solid #000;
    margin: 8px 0 14px;
}

.judul {
    text-align: center;
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 7px;
}

.periode {
    text-align: center;
    font-size: 10px;
    margin-bottom: 14px;
}

.data-tamu {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

.data-tamu th,
.data-tamu td {
    border: 1px solid #000;
    padding: 5px 3px;
    font-size: 8px;
    word-wrap: break-word;
    vertical-align: top;
}

.data-tamu th {
    background: #e8eef5;
    text-align: center;
    vertical-align: middle;
}

.center {
    text-align: center;
}

.no { width: 3%; }
.nama { width: 12%; }
.instansi { width: 11%; }
.telepon { width: 10%; }
.tujuan { width: 19%; }
.bidang { width: 11%; }
.jumlah { width: 9%; }
.tanggal { width: 13%; }
.waktu { width: 12%; }
</style>
</head>

<body>

<table class="kop">
    <tr>
        <td class="logo">
            <?php if ($logo_src !== ''): ?>
                <img src="<?= $logo_src ?>" alt="Logo Riau">
            <?php endif; ?>
        </td>

        <td class="kop-text">
            <h2>PEMERINTAH PROVINSI RIAU</h2>
            <h3>DINAS KELAUTAN DAN PERIKANAN</h3>
            <p>Pekanbaru, Provinsi Riau</p>
        </td>
    </tr>
</table>

<div class="garis-kop"></div>

<div class="judul">
    DAFTAR TAMU DINAS KELAUTAN DAN PERIKANAN PROVINSI RIAU
</div>

<div class="periode">
    Dari <?= amanPdf(tanggalIndonesiaPdf($tanggal_awal)) ?>
    sampai <?= amanPdf(tanggalIndonesiaPdf($tanggal_akhir)) ?>
</div>

<table class="data-tamu">
    <thead>
        <tr>
            <th class="no">No.</th>
            <th class="nama">Nama Lengkap</th>
            <th class="instansi">Instansi</th>
            <th class="telepon">No. Telepon</th>
            <th class="tujuan">Tujuan Kunjungan</th>
            <th class="bidang">Bidang Tujuan</th>
            <th class="jumlah">Jumlah Rombongan</th>
            <th class="tanggal">Tanggal Kunjungan</th>
            <th class="waktu">Waktu Kunjungan</th>
        </tr>
    </thead>

    <tbody>
    <?php if (count($data_tamu) > 0): ?>
        <?php $no = 1; ?>

        <?php foreach ($data_tamu as $tamu): ?>
            <tr>
                <td class="center"><?= $no++ ?></td>
                <td><?= amanPdf($tamu['nama_lengkap']) ?></td>
                <td><?= amanPdf($tamu['instansi']) ?></td>
                <td><?= amanPdf($tamu['no_telepon']) ?></td>
                <td><?= amanPdf($tamu['tujuan']) ?></td>
                <td><?= amanPdf($tamu['bidang_tujuan']) ?></td>
                <td class="center">
                    <?= amanPdf($tamu['jumlah_rombongan']) ?>
                </td>
                <td>
                    <?= amanPdf(tanggalIndonesiaPdf(
                        $tamu['tanggal_kunjungan']
                    )) ?>
                </td>
                <td><?= amanPdf($tamu['waktu_kunjungan']) ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="9" class="center">
                Tidak ada data tamu pada periode tersebut.
            </td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

</body>
</html>
<?php
$html = ob_get_clean();

// ==============================
// GENERATE PDF
// ==============================
$options = new Options();
$options->set('isRemoteEnabled', false);

$pdf = new Dompdf($options);
$pdf->loadHtml($html, 'UTF-8');
$pdf->setPaper('A4', 'landscape');
$pdf->render();

$nama_file = "Daftar_Tamu_" .
    $tanggal_awal . "_sampai_" .
    $tanggal_akhir . ".pdf";

$pdf->stream($nama_file, ['Attachment' => true]);
exit;
?>
