<?php
require '../config/config.php'; // Inisialisasi koneksi database

if (!check_login()) {
    header("Location: ../index.php");
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
                    <a href="dashboard.php" class="btn btn-secondary">⬅ Kembali</a>
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

                // Gambar dari video (kamera)
                const videoWidth = video.videoWidth;
                const videoHeight = video.videoHeight;

                // Hitung proporsi untuk menempatkan gambar dalam lingkaran pada template
                const centerX = canvas.width / 2;
                const centerY = canvas.height / 2 - 17;
                const radius = 45; // Sesuaikan dengan radius lingkaran pada template

                context.beginPath();
                context.arc(centerX, centerY, radius, 0, 2 * Math.PI);
                context.closePath();
                context.clip();

                // Gambar video di lingkaran
                const scale = Math.max((radius * 2) / videoWidth, (radius * 2) / videoHeight);
                const scaledWidth = videoWidth * scale;
                const scaledHeight = videoHeight * scale;

                context.drawImage(video, centerX - scaledWidth / 2, centerY - scaledHeight / 2, scaledWidth, scaledHeight);

                // Opsional: Menambahkan teks untuk Nama
                const name = document.getElementById('nama_lengkap').value;

                context.font = 'bold 16px Poppins';
                context.fillStyle = '#000';
                context.textAlign = 'center';
                context.fillText(name, canvas.width / 2, canvas.height - 100); // Atur posisi Y sesuai kebutuhan

                // Tampilkan tombol simpan gambar
                saveButton.style.display = 'block';

                // Konversi gambar ke base64 dan simpan ke input hidden
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
                    console.log("Server Response:", resultText); // Tambahkan logging untuk melihat respon dari server
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