<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

// Lấy ID tin tức
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($news_id <= 0) {
    header('Location: news.php?error=invalid');
    exit();
}

// Kết nối database
$conn = connectDB();

// Lấy thông tin tin tức để xóa ảnh
$sql = "SELECT featured_image, gallery FROM news WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $news_id);
$stmt->execute();
$result = $stmt->get_result();
$news = $result->fetch_assoc();
$stmt->close();

// Xóa tin tức
$deleteSql = "DELETE FROM news WHERE id = ?";
$deleteStmt = $conn->prepare($deleteSql);
$deleteStmt->bind_param("i", $news_id);

if ($deleteStmt->execute()) {
    // Xóa ảnh nếu có
    if ($news) {
        $uploadDir = '../assets/images/';
        if (!empty($news['featured_image']) && file_exists($uploadDir . $news['featured_image'])) {
            @unlink($uploadDir . $news['featured_image']);
        }
        if (!empty($news['gallery'])) {
            $gallery = json_decode($news['gallery'], true);
            if (is_array($gallery)) {
                foreach ($gallery as $image) {
                    if (file_exists($uploadDir . $image)) {
                        @unlink($uploadDir . $image);
                    }
                }
            }
        }
    }
    
    $deleteStmt->close();
    closeDB($conn);
    header('Location: news.php?deleted=1');
    exit();
} else {
    $deleteStmt->close();
    closeDB($conn);
    header('Location: news.php?error=delete_failed');
    exit();
}

