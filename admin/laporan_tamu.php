<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

$active_page = "laporan";


// =========================
// FILTER
// =========================

$tanggal_dari = $_GET["tanggal_dari"] ?? "";
$tanggal_sampai = $_GET["tanggal_sampai"] ?? "";
$bidang = $_GET["bidang"] ?? "";

// Jika hanya satu tanggal diisi, jadikan tanggal awal dan akhir sama.
if ($tanggal_dari !== "" && $tanggal_sampai === "") {
    $tanggal_sampai = $tanggal_dari;
} elseif ($tanggal_dari === "" && $tanggal_sampai !== "") {
    $tanggal_dari = $tanggal_sampai;
}


// =========================
// QUERY DATA
// =========================

$sql = "SELECT
            id,
            nama_lengkap,
            instansi,
            no_telepon,
            bidang_tujuan,
            jumlah_rombongan,
            tanggal_kunjungan,
            waktu_kunjungan
        FROM tamu
        WHERE 1=1";

$params = [];
$types = "";


// Filter tanggal dari
if ($tanggal_dari !== "") {
    $sql .= " AND tanggal_kunjungan >= ?";
    $params[] = $tanggal_dari;
    $types .= "s";
}


// Filter tanggal sampai
if ($tanggal_sampai !== "") {
    $sql .= " AND tanggal_kunjungan <= ?";
    $params[] = $tanggal_sampai;
    $types .= "s";
}


// Filter bidang
if ($bidang !== "") {
    $sql .= " AND bidang_tujuan = ?";
    $params[] = $bidang;
    $types .= "s";
}


$sql .= " ORDER BY tanggal_kunjungan DESC, waktu_kunjungan DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$data_laporan = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data_laporan[] = $row;
}


// =========================
// RINGKASAN
// =========================

$total_kunjungan = count($data_laporan);

$total_orang = 0;

$jumlah_bidang = [];

foreach ($data_laporan as $row) {

    $total_orang += (int) $row["jumlah_rombongan"];

    $nama_bidang = $row["bidang_tujuan"];

    if (!isset($jumlah_bidang[$nama_bidang])) {
        $jumlah_bidang[$nama_bidang] = 0;
    }

    $jumlah_bidang[$nama_bidang]++;
}


// Cari bidang terbanyak
$bidang_terbanyak = "-";

if (!empty($jumlah_bidang)) {

    arsort($jumlah_bidang);

    $bidang_terbanyak = array_key_first($jumlah_bidang);
}


// =========================
// DAFTAR BIDANG
// =========================

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

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Laporan Tamu - Buku Tamu Digital</title>

    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap">

    <link rel="stylesheet" href="../assets/css/style.css">
<link rel="icon" type="image/png" href="../assets/img/SAPA_icon.png">


</head>

<body>


<?php include "sidebar.php"; ?>


<main class="admin-content">

    <div class="admin-content-inner">


        <!-- =========================
             HEADER
        ========================== -->

        <div class="page-header">

            <div>
                <h1>Laporan Kunjungan Tamu</h1>

                <p>
                    Rekap data kunjungan tamu ke Dinas Kelautan dan Perikanan Provinsi Riau.
                </p>
            </div>

        </div>


        <!-- =========================
             FILTER LAPORAN
        ========================== -->

        <div class="laporan-filter">

            <div class="laporan-filter-header">

                <div>
                    <h2>Filter Laporan</h2>

                    <p>
                        Pilih periode dan bidang tujuan untuk menampilkan data laporan.
                    </p>
                </div>

            </div>


            <form method="GET" action="laporan_tamu.php" class="laporan-filter-form">


                <div class="laporan-form-group">

                    <label for="tanggal_dari">
                        Dari Tanggal
                    </label>

                    <input
                        type="date"
                        id="tanggal_dari"
                        name="tanggal_dari"
                        value="<?= htmlspecialchars($tanggal_dari); ?>"
                    >

                </div>


                <div class="laporan-form-group">

                    <label for="tanggal_sampai">
                        Sampai Tanggal
                    </label>

                    <input
                        type="date"
                        id="tanggal_sampai"
                        name="tanggal_sampai"
                        value="<?= htmlspecialchars($tanggal_sampai); ?>"
                    >

                </div>


                <div class="laporan-form-group">

                    <label for="bidang">
                        Bidang Tujuan
                    </label>

                    <select id="bidang" name="bidang">

                        <option value="">
                            Semua Bidang
                        </option>

                        <?php foreach ($daftar_bidang as $item): ?>

                            <option
                                value="<?= htmlspecialchars($item); ?>"
                                <?= $bidang === $item ? "selected" : ""; ?>
                            >
                                <?= htmlspecialchars($item); ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div class="laporan-filter-actions">

                    <button type="submit" class="btn-laporan-tampilkan">
                        🔍 Tampilkan
                    </button>

                    <a href="laporan_tamu.php" class="btn-laporan-reset">
                        ↻ Reset
                    </a>

                </div>


            </form>

        </div>



        <!-- =========================
             RINGKASAN
        ========================== -->

        <div class="laporan-summary">


            <div class="laporan-summary-card">

                <div class="laporan-summary-icon">
                    👥
                </div>

                <div>

                    <span>Total Kunjungan</span>

                    <strong>
                        <?= $total_kunjungan; ?>
                    </strong>

                </div>

            </div>


            <div class="laporan-summary-card">

                <div class="laporan-summary-icon">
                    👤
                </div>

                <div>

                    <span>Total Orang</span>

                    <strong>
                        <?= $total_orang; ?>
                    </strong>

                </div>

            </div>


            <div class="laporan-summary-card">

                <div class="laporan-summary-icon">
                    🏢
                </div>

                <div>

                    <span>Bidang Terbanyak</span>

                    <strong class="summary-bidang">
                        <?= htmlspecialchars($bidang_terbanyak); ?>
                    </strong>

                </div>

            </div>


        </div>



        <!-- =========================
             DATA LAPORAN
        ========================== -->

        <div class="laporan-card">


            <div class="laporan-card-header">

                <div>

                    <h2>Data Kunjungan</h2>

                    <p>
                        <?= $total_kunjungan; ?> data ditemukan.
                    </p>

                </div>

                <!-- Tombol export -->
                <div class="laporan-export-buttons">

                <button type="button" class="btn-export word" onclick="exportWord()">
                    📝 Word
                </button>

                <button type="button" class="btn-export excel" onclick="exportExcel()">
                    📊 Excel
                </button>

                <button type="button" class="btn-export pdf" onclick="exportPDF()">
                    📄 PDF
                </button>

            </div>

            </div>

            <div class="laporan-table-wrapper">

                <table class="laporan-table">

                    <thead>

                        <tr>

                            <th>No</th>

                            <th>Nama Lengkap</th>

                            <th>Instansi</th>

                            <th>Bidang Tujuan</th>

                            <th>Jumlah</th>

                            <th>Tanggal</th>

                            <th>Waktu</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (!empty($data_laporan)): ?>

                        <?php $no = 1; ?>

                        <?php foreach ($data_laporan as $row): ?>

                            <tr>

                                <td>
                                    <?= $no++; ?>
                                </td>

                                <td>
                                    <span class="laporan-nama">
                                        <?= htmlspecialchars($row["nama_lengkap"]); ?>
                                    </span>
                                </td>

                                <td>
                                    <?= htmlspecialchars($row["instansi"]); ?>
                                </td>

                                <td>

                                    <span class="laporan-bidang">

                                        <?= htmlspecialchars($row["bidang_tujuan"]); ?>

                                    </span>

                                </td>

                                <td>
                                    <?= htmlspecialchars($row["jumlah_rombongan"]); ?> orang
                                </td>

                                <td>
                                    <?= date("d-m-Y", strtotime($row["tanggal_kunjungan"])); ?>
                                </td>

                                <td>
                                    <?= date("H:i", strtotime($row["waktu_kunjungan"])); ?>
                                </td>

                            </tr>

                        <?php endforeach; ?>


                    <?php else: ?>

                        <tr>

                            <td colspan="7" class="laporan-empty">

                                <div>
                                    📋
                                </div>

                                <strong>
                                    Tidak ada data laporan
                                </strong>

                                <span>
                                    Tidak ditemukan data kunjungan pada periode atau bidang yang dipilih.
                                </span>

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>


        </div>


    </div>

</main>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function validasiTanggalExport() {
    const dari = document.getElementById('tanggal_dari').value;
    const sampai = document.getElementById('tanggal_sampai').value;

    if (!dari && !sampai) {
        Swal.fire({
            icon: 'warning',
            title: 'Tanggal belum dipilih',
            text: 'Isi minimal satu tanggal untuk export.'
        });
        return false;
    }

    if (dari && sampai && dari > sampai) {
        Swal.fire({
            icon: 'error',
            title: 'Rentang tanggal tidak valid',
            text: 'Tanggal awal tidak boleh melewati tanggal akhir.'
        });
        return false;
    }

    return true;
}

function exportData(namaFile) {
    if (!validasiTanggalExport()) return;

    const dari = document.getElementById('tanggal_dari').value;
    const sampai = document.getElementById('tanggal_sampai').value;
    const bidang = document.getElementById('bidang').value;

    // Jika hanya satu tanggal diisi, gunakan tanggal itu untuk kedua batas.
    const tanggalAwal = dari || sampai;
    const tanggalAkhir = sampai || dari;

    const params = new URLSearchParams();
    params.set('tanggal_dari', tanggalAwal);
    params.set('tanggal_sampai', tanggalAkhir);

    if (bidang) params.set('bidang', bidang);

    window.location.href = namaFile + '?' + params.toString();
}

function exportWord() {
    exportData('export_word.php');
}

function exportExcel() {
    exportData('export_excel.php');
}

function exportPDF() {
    exportData('export_pdf.php');
}
</script>
<?php include "../footer.php"; ?>
</body>
</html>