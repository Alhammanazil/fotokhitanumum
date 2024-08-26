<?php
require '../config/config.php'; // Inisialisasi koneksi database

if (!check_login()) {
    header("Location: ../index.php");
    exit();
}

// Cek role
if ($_SESSION['user']['role'] !== 'master' && $_SESSION['user']['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Ambil ID foto dari URL
$id = isset($_GET['id']) ? $_GET['id'] : '';
if (empty($id)) {
    echo "ID tidak ditemukan.";
    exit();
}

// Ambil data foto berdasarkan ID dari database 'foto'
$stmt = $conn_foto->prepare("SELECT id, no_peserta, nama_operator, nama_lengkap FROM foto WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$foto = $result->fetch_assoc();

// Pastikan data ditemukan
if (!$foto) {
    echo "Data tidak ditemukan.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Foto Peserta</title>
    <link rel="icon" href="../assets/icon.png" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.min.css" rel="stylesheet">
    <!-- JsBarcode -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

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
            <h2 class="card-title">Edit Foto Peserta</h2>
        </div>
        <div class="card-body">
            <form id="participantForm">
                <!-- Tombol Kembali ke Dashboard -->
                <div class="text-left mb-4">
                    <a href="dashboard.php" style="background: #3C5B6F;" class="btn text-white">⬅ Kembali</a>
                </div>

                <!-- Field Nomor Peserta -->
                <div class="form-group">
                    <label for="no_peserta">Nomor Peserta</label>
                    <input type="text" id="no_peserta" name="no_peserta" class="form-control" value="<?= $foto['no_peserta']; ?>" readonly>
                </div>

                <!-- Field Nama Peserta -->
                <div class="form-group">
                    <label for="nama_lengkap">Nama Peserta</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" class="form-control" value="<?= $foto['nama_lengkap']; ?>" readonly>
                </div>

                <!-- Field Nama Operator -->
                <div class="form-group">
                    <label for="nama_operator">Nama Operator</label>
                    <input type="text" id="nama_operator" name="nama_operator" class="form-control"
                        value="<?= $_SESSION['user']['nama_lengkap']; ?>" readonly>
                </div>

                <!-- Kamera untuk mengambil foto -->
                <div class="form-group" id="camera">
                    <video id="video" width="300" height="300" autoplay></video>
                    <button type="button" id="capture" class="btn btn-primary mt-3">Ambil Foto Baru</button>
                </div>

                <!-- Output untuk menampilkan template dan foto -->
                <div id="output">
                    <canvas id="photoCanvas"></canvas>
                </div>

                <!-- Hidden Input for Image Data -->
                <input type="hidden" id="image_data" name="image">

                <!-- Hidden Input for ID -->
                <input type="hidden" name="id" value="<?= $foto['id']; ?>">

                <!-- Submit Button -->
                <div style="display: flex; justify-content: center;">
                    <button type="submit" class="btn btn-primary mt-3">Update</button>
                </div>
            </form>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.12.4/dist/sweetalert2.all.min.js"></script>

    <script>
        const video = document.getElementById('video');
        const captureButton = document.getElementById('capture');
        const saveButton = document.querySelector('button[type="submit"]');
        const canvas = document.getElementById('photoCanvas');
        const context = canvas.getContext('2d');
        const imageDataInput = document.getElementById('image_data');

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
            twibbon.src = '../assets/twibbon.png'; // Ganti dengan path ke template Anda

            twibbon.onload = () => {
                // Mengatur ukuran kanvas sesuai dengan ukuran template
                canvas.width = twibbon.width;
                canvas.height = twibbon.height;

                // Menggambar template terlebih dahulu
                context.drawImage(twibbon, 0, 0, canvas.width, canvas.height);

                // Menghitung posisi untuk foto peserta
                const videoWidth = video.videoWidth;
                const videoHeight = video.videoHeight;

                // Menyesuaikan parameter ini untuk menempatkan foto di lingkaran tengah
                const photoCenterX = canvas.width / 2 + 2; // Titik tengah X untuk foto
                const photoCenterY = canvas.height / 2 - 57; // Titik tengah Y untuk foto
                const photoRadius = 157; // Radius lingkaran yang disesuaikan

                // Tentukan rasio aspek video
                const aspectRatio = videoWidth / videoHeight;

                // Tentukan ukuran gambar yang akan diambil berdasarkan rasio aspek
                let drawWidth, drawHeight;
                if (aspectRatio > 1) {
                    drawWidth = photoRadius * 2 * aspectRatio;
                    drawHeight = photoRadius * 2;
                } else {
                    drawWidth = photoRadius * 2;
                    drawHeight = photoRadius * 2 / aspectRatio;
                }

                // Simpan status canvas sebelum melakukan clip
                context.save();

                // Membuat lingkaran dan melakukan clipping untuk gambar kamera
                context.beginPath();
                context.arc(photoCenterX, photoCenterY, photoRadius, 0, 2 * Math.PI);
                context.clip();

                // Gambar dari video (kamera) di dalam lingkaran
                context.drawImage(video, photoCenterX - (drawWidth / 2), photoCenterY - (drawHeight / 2), drawWidth, drawHeight);

                // Kembalikan status canvas ke sebelum clip
                context.restore();

                // Menambahkan "Nama Peserta" dengan penyesuaian ukuran font dan posisi
                const name = document.getElementById('nama_lengkap').value;
                context.font = 'bold 35px Arial'; // Mulai dengan ukuran font standar

                // Cek apakah nama muat dalam satu baris
                let nameWidth = context.measureText(name).width;

                if (nameWidth > canvas.width - 60) { // Jika nama terlalu panjang
                    context.font = 'bold 25px Arial'; // Perkecil font

                    nameWidth = context.measureText(name).width;

                    if (nameWidth > canvas.width - 60) { // Jika masih terlalu panjang
                        const words = name.split(" ");
                        let firstLine = "";
                        let secondLine = "";

                        // Coba memecah nama menjadi dua baris
                        for (let i = 0; i < words.length; i++) {
                            if (context.measureText(firstLine + words[i]).width < canvas.width - 60) {
                                firstLine += words[i] + " ";
                            } else {
                                secondLine += words[i] + " ";
                            }
                        }

                        // Jika kedua baris masih terlalu panjang, perkecil lagi font-nya
                        if (secondLine && context.measureText(secondLine).width > canvas.width - 60) {
                            context.font = 'bold 25px Arial'; // Perkecil lagi
                        }

                        // Tampilkan nama dalam dua baris
                        context.fillText(firstLine.trim(), (canvas.width / 2) - (context.measureText(firstLine.trim()).width / 2), canvas.height - 270);
                        context.fillText(secondLine.trim(), (canvas.width / 2) - (context.measureText(secondLine.trim()).width / 2), canvas.height - 235);

                    } else {
                        // Jika cukup dengan font yang diperkecil
                        context.fillText(name, (canvas.width / 2) - (context.measureText(name).width / 2), canvas.height - 255);
                    }
                } else {
                    // Jika nama muat dalam satu baris dengan font default
                    context.fillText(name, (canvas.width / 2) - (context.measureText(name).width / 2), canvas.height - 255);
                }

                // Tambahkan "Nomor Peserta"
                const noPeserta = document.getElementById('no_peserta').value;
                context.font = 'bold 40px Arial'; // Ukuran font yang disesuaikan
                context.fillStyle = '#000';
                context.fillText(noPeserta, (canvas.width / 2) - (context.measureText(noPeserta).width / 2), canvas.height - 190); // Teks diposisikan di tengah bawah

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

                // Menyimpan data gambar ke input hidden untuk pengiriman
                imageDataInput.value = canvas.toDataURL('image/png');
            };
        });

        // Proses submit form
        document.getElementById('participantForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Mencegah pengiriman ulang form

            // Validasi gambar yang sudah diambil
            if (!imageDataInput.value) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Gambar Belum Diambil',
                    text: 'Ambil foto terlebih dahulu sebelum mengupdate.',
                });
                return;
            }

            // Ambil nilai dari input hidden
            const id = document.querySelector('input[name="id"]').value;
            const imageDataURL = imageDataInput.value;

            // Submit form melalui AJAX
            fetch('../config/update_foto.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        id: id,
                        image: imageDataURL
                    })
                })
                .then(response => response.text())
                .then(resultText => {
                    console.log("Server Response:", resultText);
                    if (resultText.trim() === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data Berhasil Diupdate!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(() => {
                            window.location.href = 'dashboard.php'; // Redirect setelah sukses
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal Mengupdate Data',
                            text: 'Silakan coba lagi.',
                        });
                    }
                })
                .catch(error => {
                    console.error('Gagal mengupdate gambar:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengupdate Gambar',
                        text: 'Coba lagi.',
                    });
                });
        });
    </script>
</body>

</html>