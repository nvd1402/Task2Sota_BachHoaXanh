<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

// Lấy ID đánh giá
$review_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($review_id <= 0) {
    header('Location: reviews.php?error=invalid');
    exit();
}

// Kết nối database
$conn = connectDB();

// Lấy thông tin đánh giá để xóa ảnh nếu có
$sql = "SELECT images FROM reviews WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $review_id);
$stmt->execute();
$result = $stmt->get_result();
$review = $result->fetch_assoc();
$stmt->close();

// Xóa đánh giá
$deleteSql = "DELETE FROM reviews WHERE id = ?";
$deleteStmt = $conn->prepare($deleteSql);
$deleteStmt->bind_param("i", $review_id);

if ($deleteStmt->execute()) {
    // Xóa ảnh nếu có
    if ($review && !empty($review['images'])) {
        $images = json_decode($review['images'], true);
        if (is_array($images)) {
            $uploadDir = '../assets/images/';
            foreach ($images as $image) {
                if (file_exists($uploadDir . $image)) {
                    @unlink($uploadDir . $image);
                }
            }
        }
    }
    
    $deleteStmt->close();
    closeDB($conn);
    header('Location: reviews.php?deleted=1');
    exit();
} else {
    $deleteStmt->close();
    closeDB($conn);
    header('Location: reviews.php?error=delete_failed');
    exit();
}

