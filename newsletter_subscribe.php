<?php
/**
 * File xử lý đăng ký newsletter
 */
session_start();
require_once 'config/database.php';

header('Content-Type: application/json; charset=utf-8');

// Chỉ chấp nhận POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Phương thức không hợp lệ'
    ]);
    exit();
}

// Lấy email từ form
$email = trim($_POST['email'] ?? '');

// Validate email
if (empty($email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Vui lòng nhập địa chỉ email'
    ]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        'success' => false,
        'message' => 'Địa chỉ email không hợp lệ'
    ]);
    exit();
}

// Kết nối database
$conn = connectDB();

// Kiểm tra email đã tồn tại chưa
$checkSql = "SELECT id, status FROM newsletter_subscriptions WHERE email = ?";
$checkStmt = $conn->prepare($checkSql);
$checkStmt->bind_param("s", $email);
$checkStmt->execute();
$result = $checkStmt->get_result();
$existing = $result->fetch_assoc();
$checkStmt->close();

if ($existing) {
    // Nếu email đã tồn tại
    if ($existing['status'] === 'active') {
        echo json_encode([
            'success' => false,
            'message' => 'Email này đã được đăng ký rồi'
        ]);
    } else {
        // Nếu đã hủy đăng ký trước đó, cập nhật lại thành active
        $updateSql = "UPDATE newsletter_subscriptions SET status = 'active', updated_at = CURRENT_TIMESTAMP WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("i", $existing['id']);
        
        if ($updateStmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Đăng ký lại thành công! Cảm ơn bạn đã quan tâm.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau.'
            ]);
        }
        $updateStmt->close();
    }
} else {
    // Thêm email mới vào database
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $insertSql = "INSERT INTO newsletter_subscriptions (email, status, ip_address, user_agent) VALUES (?, 'active', ?, ?)";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->bind_param("sss", $email, $ipAddress, $userAgent);
    
    if ($insertStmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Đăng ký thành công! Cảm ơn bạn đã đăng ký nhận khuyến mãi.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Có lỗi xảy ra khi đăng ký. Vui lòng thử lại sau.'
        ]);
    }
    $insertStmt->close();
}

// Đóng kết nối
closeDB($conn);
?>

