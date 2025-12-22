<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

$conn = connectDB();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'process_payment':
            // Xử lý thanh toán
            $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
            $payment_method = trim($_POST['payment_method'] ?? '');
            $payment_data = $_POST['payment_data'] ?? [];
            
            if ($order_id <= 0) {
                throw new Exception('ID đơn hàng không hợp lệ');
            }
            
            // Lấy thông tin đơn hàng
            $sql = "SELECT * FROM orders WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result || $result->num_rows === 0) {
                $stmt->close();
                throw new Exception('Không tìm thấy đơn hàng');
            }
            
            $order = $result->fetch_assoc();
            $stmt->close();
            
            // Xử lý theo phương thức thanh toán
            switch ($payment_method) {
                case 'cod':
                    // Thanh toán khi nhận hàng - không cần xử lý gì
                    $payment_status = 'pending';
                    break;
                    
                case 'bank_transfer':
                    // Chuyển khoản ngân hàng - cần xác nhận sau
                    $payment_status = 'pending';
                    // Có thể lưu thông tin chuyển khoản vào payment_data
                    break;
                    
                case 'e_wallet':
                    // Ví điện tử - giả lập xử lý
                    $payment_status = 'paid';
                    // Trong thực tế sẽ gọi API của ví điện tử
                    break;
                    
                default:
                    throw new Exception('Phương thức thanh toán không hợp lệ');
            }
            
            // Cập nhật trạng thái thanh toán
            $updateSql = "UPDATE orders SET payment_status = ?, payment_method = ?, updated_at = NOW() WHERE id = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ssi", $payment_status, $payment_method, $order_id);
            
            if ($updateStmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Thanh toán thành công';
                $response['data'] = [
                    'order_id' => $order_id,
                    'payment_status' => $payment_status,
                    'payment_method' => $payment_method
                ];
            } else {
                throw new Exception('Có lỗi xảy ra khi xử lý thanh toán');
            }
            $updateStmt->close();
            break;
            
        case 'verify_payment':
            // Xác minh thanh toán (cho bank transfer)
            $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
            $transaction_code = trim($_POST['transaction_code'] ?? '');
            
            if ($order_id <= 0) {
                throw new Exception('ID đơn hàng không hợp lệ');
            }
            
            if (empty($transaction_code)) {
                throw new Exception('Vui lòng nhập mã giao dịch');
            }
            
            // Cập nhật trạng thái thanh toán thành paid
            $sql = "UPDATE orders SET payment_status = 'paid', updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Xác minh thanh toán thành công';
            } else {
                throw new Exception('Có lỗi xảy ra khi xác minh');
            }
            $stmt->close();
            break;
            
        case 'payment_callback':
            // Callback từ cổng thanh toán (webhook)
            $order_number = trim($_POST['order_number'] ?? $_GET['order_number'] ?? '');
            $transaction_id = trim($_POST['transaction_id'] ?? $_GET['transaction_id'] ?? '');
            $status = trim($_POST['status'] ?? $_GET['status'] ?? '');
            
            if (empty($order_number)) {
                throw new Exception('Mã đơn hàng không hợp lệ');
            }
            
            // Lấy đơn hàng
            $sql = "SELECT * FROM orders WHERE order_number = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $order_number);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result || $result->num_rows === 0) {
                $stmt->close();
                throw new Exception('Không tìm thấy đơn hàng');
            }
            
            $order = $result->fetch_assoc();
            $stmt->close();
            
            // Cập nhật trạng thái thanh toán
            $payment_status = ($status === 'success' || $status === 'paid') ? 'paid' : 'failed';
            
            $updateSql = "UPDATE orders SET payment_status = ?, updated_at = NOW() WHERE order_number = ?";
            $updateStmt = $conn->prepare($updateSql);
            $updateStmt->bind_param("ss", $payment_status, $order_number);
            
            if ($updateStmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Cập nhật trạng thái thanh toán thành công';
            } else {
                throw new Exception('Có lỗi xảy ra khi cập nhật');
            }
            $updateStmt->close();
            break;
            
        default:
            throw new Exception('Action không hợp lệ');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

closeDB($conn);
echo json_encode($response);

