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
                    <input type="text" id="nama_operator" name="nama_operator" class="form-control" value="<?= $foto['nama_operator']; ?>" readonly>
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

                // Simpan status canvas sebelum melakukan clip
                context.save();

                // Gambar dari video (kamera) di dalam lingkaran
                const centerX = canvas.width / 2;
                const centerY = canvas.height / 2 - 17;
                const radius = 45; // Sesuaikan dengan radius lingkaran pada template

                // Membuat lingkaran dan melakukan clipping
                context.beginPath();
                context.arc(centerX, centerY, radius, 0, 2 * Math.PI);
                context.closePath();
                context.clip(); // Klip untuk gambar kamera

                // Gambar video di lingkaran dengan ukuran yang disesuaikan
                context.drawImage(video, centerX - radius, centerY - radius, radius * 2, radius * 2);

                // Kembalikan status canvas ke sebelum clip
                context.restore();

                // Tambahkan Nomor Peserta
                const noPeserta = document.getElementById('no_peserta').value;
                context.font = 'bold 14px Arial';
                context.fillStyle = '#000';
                context.fillText(noPeserta, 85, 263); // Sesuaikan koordinat x, y

                // Nama Peserta
                const name = document.getElementById('nama_lengkap').value;
                console.log(name);
                context.font = 'bold 11px Arial';
                const textWidth = context.measureText(name).width;
                const centerXText = (canvas.width / 2) - (textWidth / 2);
                context.fillText(name, centerXText, 280); // Sesuaikan koordinat x, y

                // Barcode
                const barcodeCanvas = document.createElement('canvas');
                JsBarcode(barcodeCanvas, noPeserta, {
                    format: "CODE128",
                    displayValue: false,
                    width: 1,
                    height: 30,
                    margin: 0
                });
                context.drawImage(barcodeCanvas, 42, 296, 130, 30);

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