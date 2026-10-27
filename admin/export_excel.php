
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
// FORMAT TANGGAL INDONESIA
// ==============================
function tanggalIndonesiaExcel($tanggal)
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

// ==============================
// NAMA FILE EXCEL
// ==============================
$nama_file = "Daftar_Tamu_" .
    $tanggal_awal . "_sampai_" .
    $tanggal_akhir . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header(
    "Content-Disposition: attachment; filename=\"" .
    $nama_file . "\""
);
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }

        th, td {
            border: 1px solid #000000;
            padding: 6px;
            vertical-align: top;
        }

        th {
            background-color: #D9EAF7;
            font-weight: bold;
            text-align: center;
        }

        .judul {
            font-size: 16px;
            font-weight: bold;
            text-align: center;
        }

        .periode {
            text-align: center;
        }

        .tengah {
            text-align: center;
        }
    </style>
</head>
<body>

<table>
    <tr>
        <td colspan="9" class="judul">
            DAFTAR TAMU DINAS KELAUTAN DAN PERIKANAN PROVINSI RIAU
        </td>
    </tr>
    <tr>
        <td colspan="9" class="periode">
            Periode <?= tanggalIndonesiaExcel($tanggal_awal) ?>
            sampai <?= tanggalIndonesiaExcel($tanggal_akhir) ?>
        </td>
    </tr>
    <tr>
        <td colspan="9"></td>
    </tr>

    <tr>
        <th>No.</th>
        <th>Nama Lengkap</th>
        <th>Instansi</th>
        <th>No. Telepon</th>
        <th>Tujuan Kunjungan</th>
        <th>Bidang Tujuan</th>
        <th>Jumlah Rombongan</th>
        <th>Tanggal Kunjungan</th>
        <th>Waktu Kunjungan</th>
    </tr>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php $no = 1; ?>

        <?php while ($tamu = mysqli_fetch_assoc($result)): ?>
            <tr>
                <td class="tengah"><?= $no++ ?></td>
                <td><?= htmlspecialchars($tamu['nama_lengkap'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($tamu['instansi'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td style="mso-number-format:'\@';">
                    <?= htmlspecialchars($tamu['no_telepon'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td><?= htmlspecialchars($tamu['tujuan'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($tamu['bidang_tujuan'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="tengah"><?= htmlspecialchars($tamu['jumlah_rombongan'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= tanggalIndonesiaExcel($tamu['tanggal_kunjungan']) ?></td>
                <td style="mso-number-format:'\@';">
                    <?= htmlspecialchars($tamu['waktu_kunjungan'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                </td>
            </tr>
        <?php endwhile; ?>

    <?php else: ?>
        <tr>
            <td colspan="9" class="tengah">
                Tidak ada data tamu pada periode tersebut.
            </td>
        </tr>
    <?php endif; ?>
</table>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
?>
