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
// DATABASE
// =========================

require_once "../config/database.php";


// =========================
// DATA ADMIN
// =========================

$nama_admin = $_SESSION["admin_nama"] ?? "Admin";


// =========================
// MENU AKTIF
// =========================

$active_page = "dashboard";


// =========================
// TOTAL SELURUH TAMU
// =========================

$query_total = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM tamu"
);

$data_total = mysqli_fetch_assoc($query_total);

$total_tamu = $data_total["total"] ?? 0;


// =========================
// TOTAL TAMU HARI INI
// =========================

$query_hari_ini = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM tamu
     WHERE tanggal_kunjungan = CURDATE()"
);

$data_hari_ini = mysqli_fetch_assoc($query_hari_ini);

$tamu_hari_ini = $data_hari_ini["total"] ?? 0;


// =========================
// TOTAL TAMU BULAN INI
// =========================

$query_bulan_ini = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM tamu
     WHERE MONTH(tanggal_kunjungan) = MONTH(CURDATE())
     AND YEAR(tanggal_kunjungan) = YEAR(CURDATE())"
);

$data_bulan_ini = mysqli_fetch_assoc($query_bulan_ini);

$tamu_bulan_ini = $data_bulan_ini["total"] ?? 0;


// =========================
// DATA GRAFIK 7 HARI TERAKHIR
// =========================

$label_grafik = [];

$data_grafik = [];


for ($i = 6; $i >= 0; $i--) {

    $tanggal = date(
        "Y-m-d",
        strtotime("-" . $i . " days")
    );


    // Label tanggal untuk grafik
    $label_grafik[] = date(
        "d M",
        strtotime($tanggal)
    );


    // Hitung jumlah tamu pada tanggal tersebut
    $query_grafik = mysqli_query(
        $conn,
        "SELECT COUNT(*) AS total
         FROM tamu
         WHERE tanggal_kunjungan = '$tanggal'"
    );


    $data = mysqli_fetch_assoc($query_grafik);


    $data_grafik[] = (int) ($data["total"] ?? 0);
}

?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">


    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >


    <title>
        Dashboard | Buku Tamu Digital
    </title>


    <!-- =========================
         GOOGLE FONT
    ========================== -->

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


    <!-- =========================
         CSS
    ========================== -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >
<link rel="icon" type="image/png" href="../assets/img/SAPA_icon.png">

    <!-- =========================
         CHART.JS
    ========================== -->

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>


<body>


    <!-- =========================
         SIDEBAR
    ========================== -->

    <?php include "sidebar.php"; ?>


    <!-- =========================
         KONTEN UTAMA
    ========================== -->

    <main class="admin-content">

        <div class="admin-content-inner">


            <!-- =========================
                 HEADER DASHBOARD
            ========================== -->

            <div class="admin-header">

                <span class="form-badge">
                    DASHBOARD
                </span>


                <h1>
                    Selamat Datang, <?= htmlspecialchars($nama_admin); ?>!
                </h1>


                <p>
                    Ringkasan data kunjungan Buku Tamu Digital
                    Dinas Kelautan dan Perikanan Provinsi Riau.
                </p>

            </div>


            <!-- =========================
                 STATISTIK
            ========================== -->

            <div class="dashboard-stats">


                <!-- =========================
                     TOTAL TAMU
                ========================== -->

                <div class="stat-card">

                    <div class="stat-card-icon">
                        👥
                    </div>


                    <div class="stat-card-info">

                        <span class="stat-card-label">
                            Total Tamu
                        </span>


                        <strong class="stat-card-number">
                            <?= number_format($total_tamu, 0, ",", "."); ?>
                        </strong>

                    </div>

                </div>


                <!-- =========================
                     TAMU HARI INI
                ========================== -->

                <div class="stat-card">

                    <div class="stat-card-icon">
                        📅
                    </div>


                    <div class="stat-card-info">

                        <span class="stat-card-label">
                            Tamu Hari Ini
                        </span>


                        <strong class="stat-card-number">
                            <?= number_format($tamu_hari_ini, 0, ",", "."); ?>
                        </strong>

                    </div>

                </div>


                <!-- =========================
                     TAMU BULAN INI
                ========================== -->

                <div class="stat-card">

                    <div class="stat-card-icon">
                        📊
                    </div>


                    <div class="stat-card-info">

                        <span class="stat-card-label">
                            Tamu Bulan Ini
                        </span>


                        <strong class="stat-card-number">
                            <?= number_format($tamu_bulan_ini, 0, ",", "."); ?>
                        </strong>

                    </div>

                </div>


            </div>


            <!-- =========================
                 GRAFIK KUNJUNGAN
            ========================== -->

            <div class="dashboard-chart">


                <div class="dashboard-section-header">

                    <div>

                        <h2>
                            Grafik Kunjungan
                        </h2>


                        <p>
                            Jumlah kunjungan tamu dalam 7 hari terakhir.
                        </p>

                    </div>

                </div>


                <div class="chart-container">

                    <canvas id="grafikKunjungan"></canvas>

                </div>


            </div>


        </div>

    </main>


    <!-- =========================
         JAVASCRIPT GRAFIK
    ========================== -->

    <script>

        const labelGrafik =
            <?= json_encode($label_grafik); ?>;


        const dataGrafik =
            <?= json_encode($data_grafik); ?>;


        const ctx =
            document.getElementById("grafikKunjungan");


        new Chart(ctx, {

            type: "line",


            data: {

                labels: labelGrafik,


                datasets: [

                    {

                        label: "Jumlah Tamu",

                        data: dataGrafik,

                        borderWidth: 3,

                        tension: 0.4,

                        fill: false,

                        pointRadius: 4,

                        pointHoverRadius: 6

                    }

                ]

            },


            options: {

                responsive: true,

                maintainAspectRatio: false,


                plugins: {

                    legend: {

                        display: false

                    }

                },


                scales: {

                    y: {

                        beginAtZero: true,


                        ticks: {

                            precision: 0

                        }

                    }

                }

            }

        });

    </script>
    

<?php include "../footer.php"; ?>
</body>

</html>