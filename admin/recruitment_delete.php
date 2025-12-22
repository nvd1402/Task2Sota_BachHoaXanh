<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

// Lấy ID tuyển dụng
$recruitment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($recruitment_id <= 0) {
    header('Location: recruitment.php?error=invalid');
    exit();
}

// Kết nối database
$conn = connectDB();

// Xóa tuyển dụng (cascade sẽ xóa recruitment_applications tự động)
$sql = "DELETE FROM recruitment WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $recruitment_id);

if ($stmt->execute()) {
    $stmt->close();
    closeDB($conn);
    header('Location: recruitment.php?deleted=1');
    exit();
} else {
    $stmt->close();
    closeDB($conn);
    header('Location: recruitment.php?error=delete_failed');
    exit();
}

