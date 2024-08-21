<?php
require '../config/config.php';

header('Content-Type: application/json');

if (isset($_GET['no_peserta'])) {
    $no_peserta = $_GET['no_peserta'];

    // Ambil nama peserta dari database berdasarkan nomor pendaftaran
    $stmt = $conn_ku->prepare("SELECT nama_lengkap FROM pendaftar WHERE no_peserta = ?");
    if ($stmt) {
        $stmt->bind_param("s", $no_peserta);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode(['success' => true, 'nama_lengkap' => $row['nama_lengkap']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Nama peserta tidak ditemukan.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Query preparation failed.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No no_peserta parameter provided.']);
}
