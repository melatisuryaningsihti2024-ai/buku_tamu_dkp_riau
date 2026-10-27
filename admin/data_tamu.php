<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

$nama_admin = $_SESSION["admin_nama"] ?? "Admin";
$level_admin = $_SESSION["admin_level"] ?? "admin";
$active_page = "data_tamu";

// ==================================================
// FILTER PENCARIAN
// ==================================================

$keyword = trim($_GET["keyword"] ?? "");
$bidang  = trim($_GET["bidang"] ?? "");

$tanggal_dari   = trim($_GET["tanggal_dari"] ?? "");
$tanggal_sampai = trim($_GET["tanggal_sampai"] ?? "");

// ==================================================
// DAFTAR BIDANG TUJUAN
// ==================================================

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

if ($bidang !== "" &&
    !in_array($bidang, $daftar_bidang, true)) {
    $bidang = "";
}

// ==================================================
// VALIDASI TANGGAL
// ==================================================

function validasiTanggal($tanggal)
{
    if ($tanggal === "") {
        return "";
    }

    $date = DateTime::createFromFormat("!Y-m-d", $tanggal);

    if (!$date || $date->format("Y-m-d") !== $tanggal) {
        return "";
    }

    return $tanggal;
}

$tanggal_dari = validasiTanggal($tanggal_dari);
$tanggal_sampai = validasiTanggal($tanggal_sampai);

// Jika hanya satu tanggal diisi, cari tanggal tersebut saja.
// Nilai input yang ditampilkan tetap sesuai pilihan pengguna.
$tanggal_filter_dari = $tanggal_dari;
$tanggal_filter_sampai = $tanggal_sampai;

if ($tanggal_filter_dari !== "" &&
    $tanggal_filter_sampai === "") {
    $tanggal_filter_sampai = $tanggal_filter_dari;
}

if ($tanggal_filter_sampai !== "" &&
    $tanggal_filter_dari === "") {
    $tanggal_filter_dari = $tanggal_filter_sampai;
}

// Jika tanggal awal lebih besar daripada tanggal akhir,
// tukar agar rentang pencarian tetap benar.
if (
    $tanggal_filter_dari !== "" &&
    $tanggal_filter_sampai !== "" &&
    $tanggal_filter_dari > $tanggal_filter_sampai
) {
    $sementara = $tanggal_filter_dari;
    $tanggal_filter_dari = $tanggal_filter_sampai;
    $tanggal_filter_sampai = $sementara;
}

// ==================================================
// QUERY DATA TAMU
// ==================================================

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
        WHERE 1=1";

$params = [];
$types = "";

// Pencarian nama, instansi, telepon, dan bidang
if ($keyword !== "") {
    $sql .= " AND (
        nama_lengkap LIKE ?
        OR instansi LIKE ?
        OR no_telepon LIKE ?
        OR bidang_tujuan LIKE ?
    )";

    $search = "%" . $keyword . "%";

    $params[] = $search;
    $params[] = $search;
    $params[] = $search;
    $params[] = $search;

    $types .= "ssss";
}

// Filter bidang
if ($bidang !== "") {
    $sql .= " AND bidang_tujuan = ?";

    $params[] = $bidang;
    $types .= "s";
}

// Filter tanggal awal
if ($tanggal_filter_dari !== "") {
    $sql .= " AND tanggal_kunjungan >= ?";

    $params[] = $tanggal_filter_dari;
    $types .= "s";
}

// Filter tanggal akhir
if ($tanggal_filter_sampai !== "") {
    $sql .= " AND tanggal_kunjungan <= ?";

    $params[] = $tanggal_filter_sampai;
    $types .= "s";
}

// Urutkan kunjungan terbaru terlebih dahulu
$sql .= " ORDER BY
            tanggal_kunjungan DESC,
            waktu_kunjungan DESC,
            id DESC";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Gagal menyiapkan pencarian data tamu.");
}

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

if (!mysqli_stmt_execute($stmt)) {
    die("Gagal mengambil data tamu.");
}

$result = mysqli_stmt_get_result($stmt);

$data_tamu = [];

while ($row = mysqli_fetch_assoc($result)) {
    $data_tamu[] = $row;
}

$total_tamu = count($data_tamu);

mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Tamu | Buku Tamu Digital DKP Riau</title>

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >
<link rel="icon" type="image/png" href="../assets/img/SAPA_icon.png">


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    

    <!-- CSS khusus halaman Data Tamu -->
    <style>
        .guest-page,
        .guest-page * {
            box-sizing: border-box;
        }

        .guest-page {
            width: 100%;
            min-width: 0;
        }

        .guest-page .admin-header {
            margin-bottom: 24px;
        }

        /* Kartu filter */
        .guest-filter-card {
            width: 100%;
            padding: 24px;
            margin-bottom: 24px;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.025);
        }

        .guest-filter-form {
            display: grid;
            grid-template-columns:
                minmax(180px, 1.15fr)
                minmax(200px, 1.15fr)
                minmax(280px, 1.5fr)
                auto;
            align-items: end;
            gap: 18px;
        }

        .guest-filter-group {
            min-width: 0;
        }

        .guest-filter-group label {
            display: block;
            margin-bottom: 9px;
            color: #26364b;
            font-size: 13px;
            font-weight: 600;
        }

        .guest-filter-group input,
        .guest-filter-group select {
            display: block;
            width: 100%;
            height: 46px;
            min-width: 0;
            padding: 0 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            background: #ffffff;
            color: #334155;
            font-family: "Poppins", sans-serif;
            font-size: 12px;
            outline: none;
            transition: border-color .2s, box-shadow .2s;
        }

        .guest-filter-group input:focus,
        .guest-filter-group select:focus {
            border-color: #1769aa;
            box-shadow: 0 0 0 3px rgba(23, 105, 170, .10);
        }

        .guest-date-range {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
        }

        .guest-date-range input {
            width: 100%;
        }

        .guest-filter-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .guest-filter-actions .btn-filter,
        .guest-filter-actions .btn-reset {
            height: 46px;
            padding: 0 17px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            font-family: "Poppins", sans-serif;
            font-size: 12px;
            font-weight: 500;
            white-space: nowrap;
            text-decoration: none;
            cursor: pointer;
            transition: background .2s, border-color .2s;
        }

        .guest-filter-actions .btn-filter {
            border: 1px solid #1769aa;
            background: #1769aa;
            color: #ffffff;
        }

        .guest-filter-actions .btn-filter:hover {
            background: #12598f;
        }

        .guest-filter-actions .btn-reset {
            border: 1px solid #d1d5db;
            background: #f8fafc;
            color: #475569;
        }

        .guest-filter-actions .btn-reset:hover {
            background: #e5e7eb;
        }

        /* Kartu tabel */
        .guest-page .data-table-card {
            width: 100%;
            min-width: 0;
            overflow: hidden;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.025);
        }

        .guest-page .data-table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 22px 24px;
        }

        .guest-page .data-table-header h2 {
            margin: 0 0 5px;
            color: #1f2937;
            font-size: 18px;
            font-weight: 600;
        }

        .guest-page .data-table-header p {
            margin: 0;
            color: #64748b;
            font-size: 12px;
        }

        .guest-page .data-total {
            color: #64748b;
            font-size: 13px;
        }

        .guest-page .data-total strong {
            color: #1769aa;
        }

        .guest-page .table-wrapper {
            width: 100%;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .guest-page .data-table {
            width: 100%;
            min-width: 850px;
            border-collapse: collapse;
        }

        .guest-page .data-table th {
            padding: 14px 16px;
            background: #f8fafc;
            color: #475569;
            font-size: 11px;
            font-weight: 600;
            text-align: left;
            white-space: nowrap;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .guest-page .data-table td {
            padding: 14px 16px;
            color: #475569;
            font-size: 12px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .guest-page .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .guest-page .data-table tbody tr:hover {
            background: #fafcff;
        }

        .guest-page .guest-name {
            color: #1f2937;
            font-weight: 500;
        }

        .guest-page .bidang-badge {
            display: inline-block;
            max-width: 240px;
            padding: 5px 9px;
            border-radius: 6px;
            background: #eff6ff;
            color: #1d5f96;
            font-size: 11px;
            line-height: 1.5;
        }

        .guest-page .table-actions {
            display: flex;
            align-items: center;
            gap: 7px;
        }

        .guest-page .table-actions a,
        .guest-page .table-actions button {
            width: 33px;
            height: 33px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #e5e7eb;
            border-radius: 7px;
            background: #ffffff;
            text-decoration: none;
            font-size: 14px;
            cursor: pointer;
        }

        .guest-page .table-actions a:hover,
        .guest-page .table-actions button:hover {
            background: #f1f5f9;
        }

        .guest-page .empty-data {
            padding: 45px 20px;
            text-align: center;
        }

        .guest-page .empty-data-icon {
            margin-bottom: 10px;
            font-size: 30px;
        }

        .guest-page .empty-data strong {
            color: #334155;
            font-size: 14px;
        }

        .guest-page .empty-data p {
            margin: 7px 0 0;
            color: #64748b;
            font-size: 12px;
        }

        /* Tablet */
        @media (max-width: 1200px) {
            .guest-filter-form {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .guest-filter-actions {
                align-self: end;
            }
        }

        /* HP */
        @media (max-width: 600px) {
            .guest-filter-card {
                padding: 17px;
                border-radius: 11px;
            }

            .guest-filter-form {
                grid-template-columns: minmax(0, 1fr);
                gap: 17px;
            }

            .guest-filter-actions {
                width: 100%;
            }

            .guest-filter-actions .btn-filter,
            .guest-filter-actions .btn-reset {
                flex: 1;
            }

            .guest-page .data-table-header {
                padding: 18px;
            }
        }
    </style>
    
</head>

<body>

<?php include "sidebar.php"; ?>

<main class="admin-content">
    <div class="admin-content-inner guest-page">

        <!-- HEADER -->
        <div class="admin-header">
            <span class="form-badge">DATA TAMU</span>

            <h1>Data Tamu</h1>

            <p>
                Daftar seluruh data kunjungan tamu
                Dinas Kelautan dan Perikanan Provinsi Riau.
            </p>
        </div>

        <!-- FILTER PENCARIAN -->
        <section class="guest-filter-card">

            <form
                method="GET"
                action="data_tamu.php"
                class="guest-filter-form"
            >

                <div class="guest-filter-group">
                    <label for="keyword">Cari Tamu</label>

                    <input
                        type="text"
                        id="keyword"
                        name="keyword"
                        value="<?= htmlspecialchars(
                            $keyword,
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>"
                        placeholder="Nama, instansi, telepon..."
                    >
                </div>

                <div class="guest-filter-group">
                    <label for="bidang">Bidang Tujuan</label>

                    <select id="bidang" name="bidang">
                        <option value="">Semua Bidang</option>

                        <?php foreach ($daftar_bidang as $item): ?>
                            <option
                                value="<?= htmlspecialchars(
                                    $item,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>"
                                <?= $bidang === $item ? "selected" : ""; ?>
                            >
                                <?= htmlspecialchars(
                                    $item,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="guest-filter-group">
                    <label for="tanggal_dari">Tanggal Kunjungan</label>

                    <div class="guest-date-range">
                        <input
                            type="date"
                            id="tanggal_dari"
                            name="tanggal_dari"
                            value="<?= htmlspecialchars(
                                $tanggal_dari,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                            aria-label="Dari tanggal"
                        >

                        <input
                            type="date"
                            id="tanggal_sampai"
                            name="tanggal_sampai"
                            value="<?= htmlspecialchars(
                                $tanggal_sampai,
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>"
                            aria-label="Sampai tanggal"
                        >
                    </div>
                </div>

                <div class="guest-filter-actions">
                    <button type="submit" class="btn-filter">
                        🔍 Cari
                    </button>

                    <a href="data_tamu.php" class="btn-reset">
                        Reset
                    </a>
                </div>

            </form>
        </section>

        <!-- TABEL DATA TAMU -->
        <section class="data-table-card">

            <div class="data-table-header">
                <div>
                    <h2>Daftar Tamu</h2>
                    <p>Data kunjungan yang tersimpan di sistem.</p>
                </div>

                <div class="data-total">
                    Total: <strong><?= $total_tamu; ?></strong> data
                </div>
            </div>

            <div class="table-wrapper">
                <table class="data-table">

                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Lengkap</th>
                            <th>Instansi</th>
                            <th>No. Telepon</th>
                            <th>Bidang Tujuan</th>
                            <th>Jumlah</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if (!empty($data_tamu)): ?>

                        <?php $no = 1; ?>

                        <?php foreach ($data_tamu as $tamu): ?>
                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <div class="guest-name">
                                        <?= htmlspecialchars(
                                            $tamu["nama_lengkap"] ?? "",
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </div>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $tamu["instansi"] ?? "",
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $tamu["no_telepon"] ?: "-",
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <span class="bidang-badge">
                                        <?= htmlspecialchars(
                                            $tamu["bidang_tujuan"] ?? "",
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ); ?>
                                    </span>
                                </td>

                                <td>
                                    <?= (int) $tamu["jumlah_rombongan"]; ?>
                                    orang
                                </td>

                                <td>
                                    <?php
                                    if (!empty($tamu["tanggal_kunjungan"])) {
                                        echo htmlspecialchars(
                                            date(
                                                "d/m/Y",
                                                strtotime(
                                                    $tamu["tanggal_kunjungan"]
                                                )
                                            ),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        );
                                    } else {
                                        echo "-";
                                    }
                                    ?>
                                </td>

                                <td>
                                    <div class="table-actions">

                                        <a
                                            href="detail_tamu.php?id=<?= (int) $tamu["id"]; ?>"
                                            class="btn-detail"
                                            title="Lihat Detail"
                                            aria-label="Lihat detail tamu"
                                        >
                                            👁
                                        </a>

                                        <?php if ($level_admin === "master"): ?>

                                            <a
                                                href="edit_tamu.php?id=<?= (int) $tamu["id"]; ?>"
                                                class="btn-edit"
                                                title="Edit Data"
                                                aria-label="Edit data tamu"
                                            >
                                                ✏️
                                            </a>

                                            <button
                                                type="button"
                                                class="btn-delete"
                                                title="Hapus Data"
                                                aria-label="Hapus data tamu"
                                                onclick='hapusTamu(
                                                    <?= (int) $tamu["id"]; ?>,
                                                    <?= json_encode(
                                                        $tamu["nama_lengkap"],
                                                        JSON_HEX_TAG |
                                                        JSON_HEX_APOS |
                                                        JSON_HEX_QUOT |
                                                        JSON_HEX_AMP
                                                    ); ?>
                                                )'
                                            >
                                                🗑
                                            </button>

                                        <?php endif; ?>

                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="8" class="empty-data">
                                <div class="empty-data-icon">📭</div>

                                <strong>Data tamu belum ditemukan</strong>

                                <p>
                                    Tidak ada data yang sesuai dengan
                                    pencarian atau filter yang dipilih.
                                </p>
                            </td>
                        </tr>

                    <?php endif; ?>
                    </tbody>

                </table>
            </div>
        </section>

    </div>
</main>

<script>
function hapusTamu(id, nama) {
    if (typeof Swal === "undefined") {
        if (confirm("Hapus data tamu " + nama + "?")) {
            window.location.href =
                "hapus_tamu.php?id=" + encodeURIComponent(id);
        }
        return;
    }

    Swal.fire({
        title: "Hapus Data Tamu?",
        html: "Data tamu <strong>" + escapeHtml(nama) +
              "</strong> akan dihapus secara permanen.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, Hapus",
        cancelButtonText: "Batal",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6b7280",
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href =
                "hapus_tamu.php?id=" + encodeURIComponent(id);
        }
    });
}

function escapeHtml(text) {
    const element = document.createElement("div");
    element.textContent = text;
    return element.innerHTML;
}
</script>
<?php include "../footer.php"; ?>
</body>
</html>
