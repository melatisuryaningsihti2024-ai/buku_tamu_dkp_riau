<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$active_page = $active_page ?? "";

?>
<button class="sidebar-toggle" id="sidebarToggle"
        type="button" aria-label="Tutup sidebar">
    <svg viewBox="0 0 24 24" width="20" height="20"
         fill="none" stroke="currentColor"
         stroke-width="2" stroke-linecap="round"
         stroke-linejoin="round">
        <path d="m15 18-6-6 6-6"/>
    </svg>
</button>
<aside class="admin-sidebar">

    <!-- LOGO & NAMA APLIKASI -->

    <div class="sidebar-brand">

        <img
            src="../assets/img/SAPA_icon.png"
            alt="Logo DKP Provinsi Riau"
        >

        <h2>SAPA</h2>

        <p>Buku Tamu Digital - DKP Provinsi Riau</p>

    </div>


    <!-- PROFIL ADMIN -->

    <div class="sidebar-profile">

        <div class="profile-icon">
            👤
        </div>

        <div class="profile-info">

            <span class="profile-name">
                <?= htmlspecialchars(
                    $_SESSION["admin_nama"] ?? "Admin",
                    ENT_QUOTES,
                    "UTF-8"
                ); ?>
            </span>

            <span class="profile-level">
                <?= strtoupper(htmlspecialchars(
                    $_SESSION["admin_level"] ?? "admin",
                    ENT_QUOTES,
                    "UTF-8"
                )); ?>
            </span>

        </div>

    </div>


    <!-- MENU SIDEBAR -->

    <nav class="sidebar-menu">

        <!-- DASHBOARD -->

        <a
            href="dashboard.php"
            class="sidebar-menu-item <?= $active_page === "dashboard" ? "active" : ""; ?>"
        >
            <span class="menu-icon">🏠</span>
            <span>Dashboard</span>
        </a>


        <!-- DATA TAMU -->

        <a
            href="data_tamu.php"
            class="sidebar-menu-item <?= $active_page === "data_tamu" ? "active" : ""; ?>"
        >
            <span class="menu-icon">👥</span>
            <span>Data Tamu</span>
        </a>


        <!-- TAMBAH TAMU: ADMIN DAN MASTER -->

        <a
            href="tambah_tamu.php"
            class="sidebar-menu-item <?= $active_page === "tambah_tamu" ? "active" : ""; ?>"
        >
            <span class="menu-icon">➕</span>
            <span>Tambah Tamu</span>
        </a>


        <!-- ADMINS: KHUSUS MASTER -->

        <?php if (($_SESSION["admin_level"] ?? "") === "master"): ?>

            <a
                href="admins.php"
                class="sidebar-menu-item <?= $active_page === "admins" ? "active" : ""; ?>"
            >
                <span class="menu-icon">🛡️</span>
                <span>Admins</span>
            </a>

        <?php endif; ?>


        <!-- LAPORAN -->

        <a
            href="laporan_tamu.php"
            class="sidebar-menu-item <?= $active_page === "laporan" ? "active" : ""; ?>"
        >
            <span class="menu-icon">📊</span>
            <span>Laporan</span>
        </a>

    </nav>


    <!-- LOGOUT -->

    <div class="sidebar-bottom">

        <a
            href="logout.php"
            class="sidebar-menu-item logout"
        >
            <span class="menu-icon">🚪</span>
            <span>Logout</span>
        </a>

    </div>

</aside>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const toggle = document.getElementById("sidebarToggle");
    const sidebar = document.querySelector(".admin-sidebar");
    const content = document.querySelector(".admin-content");

    if (!toggle || !sidebar) return;

    function updateSidebar() {
        const isClosed = sidebar.classList.contains("closed");
        const mobile = window.matchMedia("(max-width: 600px)").matches;
        const sidebarWidth = mobile ? 155 : 250;

        toggle.classList.toggle("closed", isClosed);

        toggle.innerHTML = isClosed
            ? `<svg viewBox="0 0 24 24" width="20" height="20"
                fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="m9 18 6-6-6-6"/>
               </svg>`
            : `<svg viewBox="0 0 24 24" width="20" height="20"
                fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
               </svg>`;

        toggle.setAttribute(
            "aria-label",
            isClosed ? "Buka sidebar" : "Tutup sidebar"
        );

        if (content) {
            content.style.marginLeft = isClosed ? "0" : sidebarWidth + "px";
            content.style.width = isClosed
                ? "100%"
                : `calc(100% - ${sidebarWidth}px)`;
            content.style.boxSizing = "border-box";
        }

        // Pastikan posisi tombol mengikuti keadaan sidebar
        toggle.style.left = isClosed ? "15px" : (sidebarWidth - 30) + "px";
    }

    toggle.addEventListener("click", function () {
        sidebar.classList.toggle("closed");
        updateSidebar();
    });

    // Atur posisi langsung ketika halaman dibuka
    updateSidebar();

    // Sesuaikan jika ukuran layar berubah
    window.addEventListener("resize", updateSidebar);
});
</script>
