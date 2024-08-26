<?php
require 'config.php';

// Cek apakah ada sesi yang aktif
session_start();

// Ambil data dari form
$id = $_POST['id'];
$image_data = $_POST['image'];
// Mengambil nama admin/operator dari sesi login
$admin = $_SESSION['user']['nama_lengkap'];

// Menghapus bagian "data:image/png;base64,"
$image_data = str_replace('data:image/png;base64,', '', $image_data);
$image_data = str_replace(' ', '+', $image_data);
$image_data = base64_decode($image_data);

// Nama file yang akan disimpan
$file_name = 'foto_' . $id . '.png';
$file_path = '../foto/' . $file_name;

// Simpan file
if (file_put_contents($file_path, $image_data)) {
    // Update data di database
    $stmt = $conn_foto->prepare("UPDATE foto SET file = ?, nama_operator = ? WHERE id = ?");
    $stmt->bind_param("ssi", $file_name, $admin, $id);

    if ($stmt->execute()) {
        echo 'success'; // Berikan respon sukses
    } else {
        error_log('Error in statement execution: ' . $stmt->error);
        echo 'error'; // Respon error jika eksekusi query gagal
    }
    $stmt->close();
} else {
    error_log('Error writing file to ' . $file_path);
    echo 'error'; // Respon error jika gagal menyimpan file
}
