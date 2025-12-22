<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

// Lấy ID liên hệ
$contact_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($contact_id <= 0) {
    header('Location: contact.php?error=invalid');
    exit();
}

// Kết nối database
$conn = connectDB();

// Xóa liên hệ
$sql = "DELETE FROM contact WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $contact_id);

if ($stmt->execute()) {
    $stmt->close();
    closeDB($conn);
    header('Location: contact.php?deleted=1');
    exit();
} else {
    $stmt->close();
    closeDB($conn);
    header('Location: contact.php?error=delete_failed');
    exit();
}

