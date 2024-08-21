<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $id = $_POST['id'];
    $no_peserta = $_POST['no_peserta'];
    $nama_operator = $_POST['nama_operator'];
    $image_data = $_POST['image'];

    // Menghapus bagian "data:image/png;base64,"
    $image_data = str_replace('data:image/png;base64,', '', $image_data);
    $image_data = str_replace(' ', '+', $image_data);
    $image_data = base64_decode($image_data);

    // Ambil nama file yang lama untuk menimpa
    $stmt = $conn_foto->prepare("SELECT file FROM foto WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($old_file_name);
    $stmt->fetch();
    $stmt->close();

    // Path ke file yang lama
    $file_path = '../foto/' . $old_file_name;

    // Simpan file baru, menimpa file yang lama
    if (file_put_contents($file_path, $image_data)) {
        // Update data di database
        $stmt = $conn_foto->prepare("UPDATE foto SET nama_operator = ?, edited = NOW() WHERE id = ?");
        $stmt->bind_param("si", $nama_operator, $id);

        if ($stmt->execute()) {
            header("Location: ../admin/dashboard.php?status=updated");
            exit();
        } else {
            echo "Gagal memperbarui data";
        }
        $stmt->close();
    } else {
        echo "Gagal menyimpan gambar baru";
    }
} else {
    echo "Invalid request";
}
