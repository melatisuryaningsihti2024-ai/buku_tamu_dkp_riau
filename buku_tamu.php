<?php
require_once "config/database.php";
?>

<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Isi Buku Tamu | Dinas Kelautan dan Perikanan</title>


    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
     <link rel="icon" type="image/png" href="assets/img/SAPA_icon.png">



    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>


<body>


    <!-- =========================
         NAVBAR
    ========================== -->

    <nav class="navbar">

        <div class="logo-area">

            <img
                src="assets/img/SAPA_icon.png"
                alt="Logo Dinas Kelautan dan Perikanan"
            >

            <div class="logo-text">

                <h3>
                    SAPA
                </h3>

                <p>
                    Sistem Administrasi Penerimaan Tamu
                </p>

            </div>

        </div>


        <a
            href="index.php"
            class="btn-kembali"
        >
            Kembali
        </a>

    </nav>



    <!-- =========================
         FORM BUKU TAMU
    ========================== -->

    <main class="form-page">

        <div class="form-container">


            <!-- Header Form -->

            <div class="form-header">

                <span class="form-badge">
                    BUKU TAMU DIGITAL
                </span>

                <h1>
                    Formulir Kunjungan
                </h1>

                <p>
                    Silakan lengkapi data kunjungan Anda
                    dengan benar.
                </p>

            </div>



            <!-- Form -->

            <form id="formBukuTamu">


                <!-- Nama Lengkap -->

                <div class="form-group">

                    <label for="nama_lengkap">

                        Nama Lengkap
                        <span>*</span>

                    </label>


                    <input
                        type="text"
                        id="nama_lengkap"
                        name="nama_lengkap"
                        placeholder="Masukkan nama lengkap"
                        required
                    >

                </div>



                <!-- Instansi -->

                <div class="form-group">

                    <label for="instansi">

                        Asal Instansi / Organisasi
                        <span>*</span>

                    </label>


                    <input
                        type="text"
                        id="instansi"
                        name="instansi"
                        placeholder="Contoh: Universitas Lancang Kuning"
                        required
                    >

                </div>



                <!-- Nomor Telepon -->

                <div class="form-group">

                    <label for="no_telepon">

                        Nomor Telepon

                    </label>


                    <input
                        type="tel"
                        id="no_telepon"
                        name="no_telepon"
                        placeholder="Contoh: 081234567890"
                    >

                </div>



                <!-- Bidang / Unit -->

                <div class="form-group">

                    <label for="bidang_tujuan">

                        Bidang / Unit yang Dituju

                    </label>


                    <select
                        id="bidang_tujuan"
                        name="bidang_tujuan"
                    >

                        <option value="">
                            -- Pilih Bidang / Unit --
                        </option>


                        <option value="Sekretariat">
                            Sekretariat
                        </option>


                        <option value="Bidang Perikanan Tangkap">
                            Bidang Perikanan Tangkap
                        </option>


                        <option value="Bidang Perikanan Budidaya">
                            Bidang Perikanan Budidaya
                        </option>


                        <option value="Bidang Kelautan">
                            Bidang Kelautan
                        </option>


                        <option value="Bidang Pengolahan dan Pemasaran Hasil Kelautan dan Perikanan">
                            Bidang Pengolahan dan Pemasaran Hasil Kelautan dan Perikanan
                        </option>


                        <option value="UPT Penerapan Mutu Hasil">
                            UPT Penerapan Mutu Hasil
                        </option>


                        <option value="UPT Pelabuhan Perikanan">
                            UPT Pelabuhan Perikanan
                        </option>


                        <option value="UPT Budidaya Perikanan">
                            UPT Budidaya Perikanan
                        </option>


                        <option value="UPT PSDKP Wilayah I,II,III">
                            UPT PSDKP Wilayah I,II,III
                        </option>

                    </select>

                </div>



                <!-- Tujuan Kunjungan -->

                <div class="form-group">

                    <label for="tujuan">

                        Tujuan Kunjungan
                        <span>*</span>

                    </label>


                    <textarea
                        id="tujuan"
                        name="tujuan"
                        rows="4"
                        placeholder="Tuliskan tujuan kunjungan Anda"
                        required
                    ></textarea>

                </div>



                <!-- Jumlah Rombongan -->

                <div class="form-group">

                    <label for="jumlah_rombongan">

                        Jumlah Rombongan

                    </label>


                    <input
                        type="number"
                        id="jumlah_rombongan"
                        name="jumlah_rombongan"
                        value="1"
                        min="1"
                        required
                    >

                </div>



                <!-- Informasi -->

                <div class="form-info">

                    <strong>
                        Informasi:
                    </strong>

                    Tanggal dan waktu kunjungan akan tercatat
                    secara otomatis saat formulir dikirim.

                </div>



                <!-- Tombol Submit -->

                <button
                    type="submit"
                    class="btn-submit"
                    id="btnSubmit"
                >

                    Kirim Data Kunjungan

                </button>


            </form>

        </div>

    </main>

<?php include "footer.php"; ?>

    <!-- =========================
         JAVASCRIPT
    ========================== -->

    <script>

        const form = document.getElementById("formBukuTamu");

        const btnSubmit = document.getElementById("btnSubmit");


        form.addEventListener("submit", function(event) {

            event.preventDefault();


            // Ambil data form
            const formData = new FormData(form);


            // Nonaktifkan tombol sementara
            btnSubmit.disabled = true;

            btnSubmit.textContent = "Menyimpan...";


            // Loading
            Swal.fire({

                title: "Menyimpan...",

                text: "Data kunjungan sedang diproses.",

                allowOutsideClick: false,

                allowEscapeKey: false,

                showConfirmButton: false,

                didOpen: () => {

                    Swal.showLoading();

                }

            });


            // Kirim data ke proses_tamu.php
            fetch("proses_tamu.php", {

                method: "POST",

                body: formData

            })


            // Ambil response JSON
            .then(response => {

                if (!response.ok) {

                    throw new Error(
                        "Terjadi kesalahan pada server."
                    );

                }

                return response.json();

            })


            // Proses hasil
            .then(data => {


                if (data.status === "success") {


                    Swal.fire({

                        icon: "success",

                        title: "Selamat!",

                        text: "Terima kasih, data kunjungan Anda berhasil disimpan.",

                        confirmButtonText: "OK",

                        confirmButtonColor: "#6dc5e8",

                        timer: 3000,

                        timerProgressBar: true,

                        allowOutsideClick: false,

                        allowEscapeKey: false

                    })


                    .then(() => {

                        window.location.href = "index.php";

                    });


                } else {


                    Swal.fire({

                        icon: "error",

                        title: "Gagal!",

                        text: data.message,

                        confirmButtonText: "OK",

                        confirmButtonColor: "#d33"

                    });


                    // Aktifkan tombol kembali
                    btnSubmit.disabled = false;

                    btnSubmit.textContent =
                        "Kirim Data Kunjungan";

                }

            })


            // Jika terjadi error
            .catch(error => {


                console.error(error);


                Swal.fire({

                    icon: "error",

                    title: "Terjadi Kesalahan",

                    text: "Data tidak dapat diproses. Silakan coba kembali.",

                    confirmButtonText: "OK",

                    confirmButtonColor: "#d33"

                });


                // Aktifkan tombol kembali
                btnSubmit.disabled = false;

                btnSubmit.textContent =
                    "Kirim Data Kunjungan";

            });

        });

    </script>

</body>

</html>