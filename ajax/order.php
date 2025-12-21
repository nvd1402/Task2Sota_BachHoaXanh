<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit();
}

$conn = connectDB();
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'update_status':
            // Cập nhật trạng thái đơn hàng
            $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
            $status = trim($_POST['status'] ?? '');
            $payment_status = trim($_POST['payment_status'] ?? '');
            
            if ($order_id <= 0) {
                throw new Exception('ID đơn hàng không hợp lệ');
            }
            
            $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
            $validPaymentStatuses = ['pending', 'paid', 'failed', 'refunded'];
            
            if (!in_array($status, $validStatuses)) {
                throw new Exception('Trạng thái đơn hàng không hợp lệ');
            }
            
            if (!in_array($payment_status, $validPaymentStatuses)) {
                throw new Exception('Trạng thái thanh toán không hợp lệ');
            }
            
            $sql = "UPDATE orders SET status = ?, payment_status = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssi", $status, $payment_status, $order_id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Cập nhật trạng thái thành công';
                $response['data'] = [
                    'order_id' => $order_id,
                    'status' => $status,
                    'payment_status' => $payment_status
                ];
            } else {
                throw new Exception('Có lỗi xảy ra khi cập nhật');
            }
            $stmt->close();
            break;
            
        case 'get_order':
            // Lấy thông tin đơn hàng
            $order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
            
            if ($order_id <= 0) {
                throw new Exception('ID đơn hàng không hợp lệ');
            }
            
            $sql = "SELECT * FROM orders WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result && $result->num_rows > 0) {
                $order = $result->fetch_assoc();
                
                // Lấy chi tiết đơn hàng
                $itemsSql = "SELECT * FROM order_items WHERE order_id = ?";
                $itemsStmt = $conn->prepare($itemsSql);
                $itemsStmt->bind_param("i", $order_id);
                $itemsStmt->execute();
                $itemsResult = $itemsStmt->get_result();
                $items = [];
                while ($row = $itemsResult->fetch_assoc()) {
                    $items[] = $row;
                }
                $itemsStmt->close();
                
                $order['items'] = $items;
                
                $response['success'] = true;
                $response['data'] = $order;
            } else {
                throw new Exception('Không tìm thấy đơn hàng');
            }
            $stmt->close();
            break;
            
        case 'cancel_order':
            // Hủy đơn hàng
            $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
            $reason = trim($_POST['reason'] ?? '');
            
            if ($order_id <= 0) {
                throw new Exception('ID đơn hàng không hợp lệ');
            }
            
            $sql = "UPDATE orders SET status = 'cancelled', admin_notes = ?, updated_at = NOW() WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $reason, $order_id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Đơn hàng đã được hủy';
            } else {
                throw new Exception('Có lỗi xảy ra khi hủy đơn hàng');
            }
            $stmt->close();
            break;
            
        default:
            throw new Exception('Action không hợp lệ');
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

closeDB($conn);
echo json_encode($response);

