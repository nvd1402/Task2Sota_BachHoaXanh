<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

// Lấy ID đơn hàng
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    header('Location: orders.php?error=invalid');
    exit();
}

// Kết nối database
$conn = connectDB();

// Xóa đơn hàng (cascade sẽ xóa order_items tự động)
$sql = "DELETE FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);

if ($stmt->execute()) {
    $stmt->close();
    closeDB($conn);
    header('Location: orders.php?deleted=1');
    exit();
} else {
    $stmt->close();
    closeDB($conn);
    header('Location: orders.php?error=delete_failed');
    exit();
}

