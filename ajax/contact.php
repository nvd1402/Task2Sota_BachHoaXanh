<?php
session_start();
require_once '../config/database.php';

header('Content-Type: application/json');

// Kết nối database
$conn = connectDB();

$action = $_POST['action'] ?? 'submit';
$response = ['success' => false, 'message' => ''];

try {
    if ($action === 'submit') {
        // Xử lý form liên hệ
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        
        // Validate
        if (empty($name)) {
            throw new Exception('Vui lòng nhập họ và tên');
        }
        
        if (empty($email)) {
            throw new Exception('Vui lòng nhập email');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email không hợp lệ');
        }
        
        if (empty($message)) {
            throw new Exception('Vui lòng nhập nội dung liên hệ');
        }
        
        // Lấy IP address
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
        
        // Lưu vào database
        $sql = "INSERT INTO contact (name, email, phone, subject, message, ip_address, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'new')";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $name, $email, $phone, $subject, $message, $ipAddress);
        
        if ($stmt->execute()) {
            $response = [
                'success' => true,
                'message' => 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.'
            ];
        } else {
            throw new Exception('Có lỗi xảy ra khi gửi liên hệ. Vui lòng thử lại.');
        }
        
        $stmt->close();
    } else {
        throw new Exception('Action không hợp lệ');
    }
} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Đóng kết nối
closeDB($conn);

echo json_encode($response);
exit;

