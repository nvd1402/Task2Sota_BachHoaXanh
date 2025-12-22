<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

// Lấy ID người dùng
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    header('Location: users.php?error=invalid');
    exit();
}

// Không cho phép xóa chính mình
if ($user_id == $_SESSION['user_id']) {
    header('Location: users.php?error=cannot_delete_self');
    exit();
}

// Kết nối database
$conn = connectDB();

// Xóa người dùng
$sql = "DELETE FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    $stmt->close();
    closeDB($conn);
    header('Location: users.php?deleted=1');
    exit();
} else {
    $stmt->close();
    closeDB($conn);
    header('Location: users.php?error=delete_failed');
    exit();
}

