<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $no_peserta = $_POST['no_peserta'];
    $nama_operator = $_POST['nama_operator'];
    $nama_lengkap = $_POST['nama_lengkap'];
    $image_data = $_POST['image'];

    // Menghapus bagian "data:image/png;base64,"
    $image_data = str_replace('data:image/png;base64,', '', $image_data);
    $image_data = str_replace(' ', '+', $image_data);
    $image_data = base64_decode($image_data);

    // Nama file yang akan disimpan
    $file_name = 'foto_' . $no_peserta . '.png';
    $file_path = '../foto/' . $file_name;

    if ($conn_foto) {
        // Cek apakah nomor peserta sudah ada di database
        $stmt = $conn_foto->prepare("SELECT id FROM foto WHERE no_peserta = ?");
        if ($stmt === false) {
            error_log('Error in statement preparation: ' . $conn_foto->error);
            echo 'error';
            exit;
        }
        $stmt->bind_param("s", $no_peserta);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Jika nomor peserta sudah ada, kirim pesan error
            echo 'duplicate'; // Kirim pesan duplikasi ke AJAX
            $stmt->close();
            exit();
        }
        $stmt->close();

        // Simpan file
        if (file_put_contents($file_path, $image_data)) {
            $stmt = $conn_foto->prepare("INSERT INTO foto (no_peserta, nama_operator, nama_lengkap, file) VALUES (?, ?, ?, ?)");
            if ($stmt === false) {
                error_log('Error in statement preparation: ' . $conn_foto->error);
                echo 'error';
                exit();
            }
            $stmt->bind_param("ssss", $no_peserta, $nama_operator, $nama_lengkap, $file_name);
            if ($stmt->execute()) {
                echo 'success'; // Kirim pesan sukses ke AJAX
            } else {
                error_log('Error in statement execution: ' . $stmt->error);
                echo 'error';
            }
            $stmt->close();
        } else {
            error_log('Error writing file to ' . $file_path);
            echo 'error';
        }
    } else {
        error_log('Database connection failed: ' . mysqli_connect_error());
        echo 'error';
    }
} else {
    echo 'invalid_request';
    error_log('Invalid request method');
}
