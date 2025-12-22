<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

// Lấy ID sản phẩm
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    header('Location: products.php');
    exit();
}

$conn = connectDB();

// Lấy thông tin sản phẩm để xóa ảnh
$stmt = $conn->prepare("SELECT image FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $product = $result->fetch_assoc();
    
    // Xóa sản phẩm
    $deleteStmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $deleteStmt->bind_param("i", $id);
    
    if ($deleteStmt->execute()) {
        // Xóa ảnh nếu có
        if ($product['image'] && file_exists('../assets/images/' . $product['image'])) {
            @unlink('../assets/images/' . $product['image']);
        }
        
        header('Location: products.php?deleted=1');
    } else {
        header('Location: products.php?error=1');
    }
    
    $deleteStmt->close();
} else {
    header('Location: products.php');
}

$stmt->close();
closeDB($conn);
exit();
?>

