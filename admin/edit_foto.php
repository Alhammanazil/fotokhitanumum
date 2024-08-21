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
$stmt = $conn_foto->prepare("SELECT id, no_peserta, nama_operator, nama_lengkap, file FROM foto WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$foto = $result->fetch_assoc();

// Pastikan data ditemukan
if (!$foto) {
    echo "Data tidak ditemukan.";
    exit();
}

// Ambil nama operator dari sesi
$nama_operator = $_SESSION['user']['nama_lengkap'];
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

        #output img {
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
            <form id="participantForm" method="POST" action="../config/update_foto.php">
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
                    <input type="text" id="nama_operator" name="nama_operator" class="form-control" value="<?= $nama_operator; ?>" readonly>
                </div>

                <!-- Preview Foto -->
                <div class="form-group" id="output">
                    <img src="../foto/<?= $foto['file']; ?>" alt="Foto" class="img-fluid">
                </div>

                <!-- Hidden ID Field -->
                <input type="hidden" name="id" value="<?= $foto['id']; ?>">

                <!-- Video Stream for New Photo -->
                <div class="form-group" id="camera">
                    <video id="video" width="300" height="300" autoplay></video>
                    <button type="button" id="capture" class="btn btn-primary mt-3">Ambil Foto Baru</button>
                </div>

                <!-- Hidden Input for Image Data -->
                <input type="hidden" id="image_data" name="image">

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary mt-3">Update</button>
            </form>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        const video = document.getElementById('video');
        const captureButton = document.getElementById('capture');
        const imageDataInput = document.getElementById('image_data');
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');

        // Mengakses kamera
        navigator.mediaDevices.getUserMedia({
            video: true
        }).then((stream) => {
            video.srcObject = stream;
        }).catch((err) => {
            console.error('Gagal mengakses kamera: ', err);
        });

        // Fungsi untuk menangkap gambar
        captureButton.addEventListener('click', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Konversi gambar ke base64
            const imageDataURL = canvas.toDataURL('image/png');
            imageDataInput.value = imageDataURL;

            // Ganti preview gambar
            document.getElementById('output').innerHTML = `<img src="${imageDataURL}" alt="Foto Baru" class="img-fluid">`;
        });
    </script>
</body>

</html>