<?php
require '../config/config.php';

if (!check_login()) {
    header("Location: ../index.php");
    exit();
}

if ($_SESSION['user']['role'] !== 'master' && $_SESSION['user']['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit();
}

// Ambil ID foto yang akan dihapus
$id = $_GET['id'];

// Ambil data foto untuk mendapatkan nama file
$stmt = $conn_foto->prepare("SELECT file FROM foto WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$foto = $result->fetch_assoc();

// Hapus file foto jika ada
$file_path = "../foto/" . $foto['file'];
if (file_exists($file_path)) {
    unlink($file_path);
}

// Hapus data foto dari database
$stmt = $conn_foto->prepare("DELETE FROM foto WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../admin/dashboard.php?status=deleted");
    exit();
} else {
    header("Location: ../admin/dashboard.php?status=failed");
    exit();
}
