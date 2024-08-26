<?php
require 'config.php';

if (!check_login()) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Pastikan hanya master yang bisa mengubah role dan akses
if ($_SESSION['user']['role'] !== 'master') {
    echo json_encode(['status' => 'error', 'message' => 'Anda tidak diizinkan untuk mengubah role dan akses']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_POST['id'];
    $type = $_POST['type'];
    $value = $_POST['value'];

    if ($type === 'role') {
        // Update role user
        $stmt = $conn_foto->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->bind_param("si", $value, $userId);
    } elseif ($type === 'akses') {
        // Update akses user
        $stmt = $conn_foto->prepare("UPDATE users SET akses = ? WHERE id = ?");
        $stmt->bind_param("ii", $value, $userId);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
        exit();
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Update successful']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Update failed']);
    }

    $stmt->close();
    $conn_foto->close();
}
?>
