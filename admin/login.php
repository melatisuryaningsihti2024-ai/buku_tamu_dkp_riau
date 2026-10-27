
<?php

session_start();

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

$error = $_GET["error"] ?? "";

$pesan_error = "";

switch ($error) {
    case "empty":
        $pesan_error = "Username dan password wajib diisi.";
        break;

    case "username":
        $pesan_error = "Username tidak ditemukan. Periksa kembali username kamu.";
        break;

    case "password":
        $pesan_error = "Password yang kamu masukkan salah. Silakan coba lagi.";
        break;

    case "system":
        $pesan_error = "Terjadi kesalahan pada sistem. Silakan coba kembali.";
        break;

    case "invalid":
        $pesan_error = "Username atau password salah.";
        break;
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login Admin | Buku Tamu Digital</title>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >

    <!-- CSS -->
    <link rel="stylesheet" href="../assets/css/style.css">
<link rel="icon" type="image/png" href="../assets/img/SAPA_icon.png">


    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <main class="login-page">

        <div class="login-card">

            <div class="login-logo">
                <img
                    src="../assets/img/logo.png"
                    alt="Logo Dinas Kelautan dan Perikanan"
                >
            </div>

            <div class="login-header">
                <h1>Login Admin</h1>

                <p>
                    Buku Tamu Digital
                    <br>
                    Dinas Kelautan dan Perikanan Provinsi Riau
                </p>
            </div>

            <form action="proses_login.php" method="POST">

                <!-- Username -->
                <div class="form-group">
                    <label for="username">Username</label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Masukkan username"
                        required
                        autocomplete="username"
                    >
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Masukkan password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn-submit">
                    Login
                </button>

            </form>

            <a href="../index.php" class="login-back">
                ← Kembali ke halaman utama
            </a>

        </div>

    </main>
<?php include "../footer.php"; ?>
    <!-- NOTIFIKASI LOGIN -->
    <?php if ($pesan_error !== ""): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Login Gagal',
                text: <?= json_encode(
                    $pesan_error,
                    JSON_HEX_TAG |
                    JSON_HEX_APOS |
                    JSON_HEX_QUOT |
                    JSON_HEX_AMP
                ); ?>,
                confirmButtonText: 'Coba Lagi',
                confirmButtonColor: '#1769aa'
            });
        </script>
    <?php endif; ?>

</body>
</html>
