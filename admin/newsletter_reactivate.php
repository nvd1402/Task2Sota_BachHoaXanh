<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: newsletters.php');
    exit();
}

$id = (int)$_GET['id'];

$conn = connectDB();

$sql = "UPDATE newsletter_subscriptions SET status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $_SESSION['success_message'] = 'Đã kích hoạt lại đăng ký thành công';
} else {
    $_SESSION['error_message'] = 'Có lỗi xảy ra khi kích hoạt lại';
}

$stmt->close();
closeDB($conn);

header('Location: newsletters.php');
exit();
?>

