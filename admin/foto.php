<?php
require '../config/config.php';

if (!check_login()) {
    header("Location: ../index.php");
    exit();
}

// Cek role
if ($_SESSION['user']['role'] !== 'master' && $_SESSION['user']['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Ambil nomor peserta dari database
$nomor_peserta = getNomorPeserta($conn_ku);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ambil Foto Peserta</title>
    <link rel="icon" href="../assets/icon.png" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.min.css" rel="stylesheet">

    <style>
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap");

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: "Poppins", sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            color: #333;
        }

        .card {
            max-width: 700px;
            width: 100%;
            margin: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 10px;
        }

        .card-header {
            background: #3C5B6F;
            color: #fff;
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            padding: 15px 20px;
        }

        .card-title {
            font-weight: 600;
            font-size: 22px;
            margin: 0;
        }

        .card-body {
            padding: 25px 30px;
        }

        #camera {
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
        }

        #output {
            width: 100%;
            max-width: 300px;
            margin: 20px auto;
            position: relative;
        }

        #output canvas {
            width: 100%;
            height: auto;
        }

        .form-group {
            margin-bottom: 15px;
        }

        label {
            font-weight: 500;
            color: #3C5B6F;
        }
    </style>
</head>

<body>
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Ambil Foto Peserta</h2>
        </div>
        <div class="card-body">
            <form id="participantForm">
                <!-- Tombol Kembali ke Dashboard -->
                <div class="text-left mb-4">
                    <a href="dashboard.php" style="background: #3C5B6F;" class="btn text-white">⬅ Kembali</a>
                </div>

                <!-- Nama Operator [Hidden] -->
                <input type="hidden" id="nama_operator" name="nama_operator" value="<?php echo $_SESSION['user']['nama_lengkap']; ?>">

                <!-- Field Nomor Peserta -->
                <div class="form-group">
                    <label for="no_peserta">Nomor Peserta</label>
                    <select id="no_peserta" name="no_peserta" class="form-control" required>
                        <option value="">Pilih Nomor Peserta</option>
                        <?php foreach ($nomor_peserta as $no): ?>
                            <option value="<?php echo $no; ?>"><?php echo $no; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Field Nama Peserta -->
                <div class="form-group">
                    <label for="nama_lengkap">Nama Peserta</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" readonly>
                </div>

                <!-- Kamera untuk mengambil foto -->
                <div class="form-group" id="camera">
                    <video id="video" width="300" height="300" autoplay></video>
                    <button type="button" id="capture" class="btn btn-primary mb-2">Ambil Foto</button>
                </div>

                <!-- Output untuk menampilkan template dan foto -->
                <div id="output">
                    <canvas id="photoCanvas"></canvas>
                </div>

                <!-- Tambahkan button Simpan Gambar -->
                <div style="display: flex; justify-content: center;">
                    <button id="saveImage" class="btn btn-success mt-3" style="display: none;">Simpan Gambar</button>
                </div>
            </form>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.all.min.js"></script>
    <!-- JsBarcode -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi Select2
            $('#no_peserta').select2().on('select2:select', function(e) {
                const noPeserta = $(this).val();
                if (noPeserta) {
                    fetch(`get_nama_peserta.php?no_peserta=${noPeserta}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                document.getElementById('nama_lengkap').value = data.nama_lengkap;
                            } else {
                                alert(data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error fetching participant name:', error);
                        });
                } else {
                    document.getElementById('nama_lengkap').value = '';
                }
            });
        });

        const video = document.getElementById('video');
        const captureButton = document.getElementById('capture');
        const saveButton = document.getElementById('saveImage');
        const canvas = document.getElementById('photoCanvas');
        const context = canvas.getContext('2d');

        // Mengakses kamera
        navigator.mediaDevices.getUserMedia({
            video: true
        }).then((stream) => {
            video.srcObject = stream;
        }).catch((err) => {
            console.error('Gagal mengakses kamera: ', err);
        });

        // Fungsi untuk menangkap gambar dan menggabungkannya dengan template
        captureButton.addEventListener('click', () => {
            const twibbon = new Image();
            twibbon.src = '../assets/twibbon.png'; // Pastikan path ini benar

            twibbon.onload = () => {
                // Sesuaikan ukuran kanvas dengan ukuran twibbon
                canvas.width = twibbon.width;
                canvas.height = twibbon.height;

                // Gambar twibbon (template)
                context.drawImage(twibbon, 0, 0, canvas.width, canvas.height);

                // Menghitung posisi untuk foto peserta
                const videoWidth = video.videoWidth;
                const videoHeight = video.videoHeight;

                // Menyesuaikan parameter ini untuk menempatkan foto di lingkaran tengah
                const photoCenterX = canvas.width / 2 + 2; // Titik tengah X untuk foto
                const photoCenterY = canvas.height / 2 - 57; // Titik tengah Y untuk foto
                const photoRadius = 157; // Radius lingkaran yang disesuaikan

                // Memotong area lingkaran untuk foto
                context.save();
                context.beginPath();
                context.arc(photoCenterX, photoCenterY, photoRadius, 0, 2 * Math.PI);
                context.clip();

                // Gambar foto peserta di dalam lingkaran
                context.drawImage(video, photoCenterX - photoRadius, photoCenterY - photoRadius, photoRadius * 2, photoRadius * 2);
                context.restore(); // Kembalikan context untuk menghapus kliping

                // Menambahkan "Nomor Peserta"
                const noPeserta = document.getElementById('no_peserta').value;
                context.font = 'bold 45px Arial'; // Ukuran font yang disesuaikan
                context.fillStyle = '#000';
                context.fillText(noPeserta, (canvas.width / 2) - (context.measureText(noPeserta).width / 2), canvas.height - 255); // Teks diposisikan di tengah bawah

                // Menambahkan "Nama Peserta"
                const name = document.getElementById('nama_lengkap').value;
                context.font = 'bold 45px Arial'; // Ukuran font yang disesuaikan
                const nameWidth = context.measureText(name).width;
                context.fillText(name, (canvas.width / 2) - (context.measureText(name).width / 2), canvas.height - 190);

                // Tambahkan barcode di bawah nama
                const barcodeCanvas = document.createElement('canvas');
                JsBarcode(barcodeCanvas, noPeserta, {
                    format: "CODE128",
                    displayValue: false,
                    width: 2, // Lebar garis barcode yang lebih tebal
                    height: 60, // Tinggi barcode yang lebih besar agar lebih terlihat
                    margin: 0
                });

                // Atur posisi dan ukuran barcode agar lebih sesuai
                const barcodeWidth = 400; // Lebar barcode yang disesuaikan
                const barcodeHeight = 80; // Tinggi barcode yang disesuaikan
                const barcodeX = (canvas.width - barcodeWidth) / 2 - 2; // Pusatkan barcode secara horizontal
                const barcodeY = canvas.height - 140; // Sesuaikan posisi Y sesuai kebutuhan

                context.drawImage(barcodeCanvas, barcodeX, barcodeY, barcodeWidth, barcodeHeight);

                saveButton.style.display = 'inline-block';
            };
        });

        // Menyimpan gambar
        saveButton.addEventListener('click', async (e) => {
            e.preventDefault(); // Mencegah pengiriman ulang form

            const noPeserta = document.getElementById('no_peserta').value.trim();
            const namaOperator = document.getElementById('nama_operator').value.trim();
            const namaLengkap = document.getElementById('nama_lengkap').value.trim();

            if (!noPeserta || !namaOperator || !namaLengkap) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Tidak Lengkap',
                    text: 'Pastikan semua data telah diisi dengan benar.',
                });
                return;
            }

            const result = await Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Pastikan data sudah benar sebelum menyimpan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, simpan!',
                cancelButtonText: 'Batal'
            });

            if (result.isConfirmed) {
                const dataURL = canvas.toDataURL('image/png');
                try {
                    const response = await fetch('../config/save_foto.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: new URLSearchParams({
                            image: dataURL,
                            no_peserta: noPeserta,
                            nama_operator: namaOperator,
                            nama_lengkap: namaLengkap
                        })
                    });

                    const resultText = await response.text();
                    if (resultText.trim() === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Berhasil Disimpan!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = 'dashboard.php'; // Redirect setelah sukses
                        });
                    } else if (resultText.trim() === 'duplicate') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Nomor Peserta Sudah Mengambil Foto',
                            text: 'Peserta ini sudah memiliki foto. Silakan periksa kembali atau edit data yang ada.',
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Menyimpan Data',
                            text: 'Silakan coba lagi.',
                        });
                    }
                } catch (error) {
                    console.error('Gagal menyimpan gambar:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Menyimpan Gambar',
                        text: 'Coba lagi.',
                    });
                }
            }
        });
    </script>
</body>

</html>