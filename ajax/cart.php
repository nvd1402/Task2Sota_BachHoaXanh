<?php
session_start();
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

// Kết nối database
$conn = connectDB();

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'add':
            // Thêm sản phẩm vào giỏ hàng
            $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            $weight_option = isset($_POST['weight_option']) ? trim($_POST['weight_option']) : null;
            
            if ($product_id <= 0) {
                throw new Exception('ID sản phẩm không hợp lệ');
            }
            
            if ($quantity <= 0) {
                throw new Exception('Số lượng phải lớn hơn 0');
            }
            
            // Lấy thông tin sản phẩm
            $sql = "SELECT * FROM products WHERE id = ? AND status = 'active'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$result || $result->num_rows === 0) {
                $stmt->close();
                throw new Exception('Sản phẩm không tồn tại hoặc đã ngừng bán');
            }
            
            $product = $result->fetch_assoc();
            $stmt->close();
            
            // Tính giá (ưu tiên sale_price)
            $price = (float)($product['sale_price'] > 0 ? $product['sale_price'] : $product['price']);
            
            // Khởi tạo giỏ hàng nếu chưa có
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            
            // Tạo key duy nhất cho mỗi sản phẩm (product_id + weight_option)
            $cartKey = $product_id . '_' . ($weight_option ?? 'default');
            
            // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
            if (isset($_SESSION['cart'][$cartKey])) {
                // Cập nhật số lượng
                $_SESSION['cart'][$cartKey]['quantity'] += $quantity;
            } else {
                // Thêm mới
                $_SESSION['cart'][$cartKey] = [
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'weight_option' => $weight_option,
                    'price' => $price,
                    'sale_price' => $product['sale_price'] ?? null
                ];
            }
            
            // Tính tổng số sản phẩm trong giỏ hàng
            $totalItems = 0;
            foreach ($_SESSION['cart'] as $item) {
                $totalItems += $item['quantity'];
            }
            
            $response = [
                'success' => true,
                'message' => 'Đã thêm sản phẩm vào giỏ hàng',
                'cart_count' => $totalItems
            ];
            break;
            
        case 'update':
            // Cập nhật số lượng sản phẩm
            $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
            $weight_option = isset($_POST['weight_option']) ? trim($_POST['weight_option']) : null;
            
            if ($product_id <= 0) {
                throw new Exception('ID sản phẩm không hợp lệ');
            }
            
            if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
                throw new Exception('Giỏ hàng trống');
            }
            
            $cartKey = $product_id . '_' . ($weight_option ?? 'default');
            
            if (!isset($_SESSION['cart'][$cartKey])) {
                throw new Exception('Sản phẩm không có trong giỏ hàng');
            }
            
            if ($quantity <= 0) {
                // Xóa sản phẩm nếu số lượng <= 0
                unset($_SESSION['cart'][$cartKey]);
                $response = [
                    'success' => true,
                    'message' => 'Đã xóa sản phẩm khỏi giỏ hàng'
                ];
            } else {
                // Cập nhật số lượng
                $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
                $response = [
                    'success' => true,
                    'message' => 'Đã cập nhật số lượng'
                ];
            }
            
            // Tính lại tổng
            $subtotal = 0;
            foreach ($_SESSION['cart'] as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $response['subtotal'] = $subtotal;
            $response['total'] = $subtotal;
            break;
            
        case 'remove':
            // Xóa sản phẩm khỏi giỏ hàng
            $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $weight_option = isset($_POST['weight_option']) ? trim($_POST['weight_option']) : null;
            
            if ($product_id <= 0) {
                throw new Exception('ID sản phẩm không hợp lệ');
            }
            
            if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
                throw new Exception('Giỏ hàng trống');
            }
            
            $cartKey = $product_id . '_' . ($weight_option ?? 'default');
            
            if (isset($_SESSION['cart'][$cartKey])) {
                unset($_SESSION['cart'][$cartKey]);
                $response = [
                    'success' => true,
                    'message' => 'Đã xóa sản phẩm khỏi giỏ hàng'
                ];
            } else {
                throw new Exception('Sản phẩm không có trong giỏ hàng');
            }
            
            // Tính lại tổng
            $subtotal = 0;
            foreach ($_SESSION['cart'] as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $response['subtotal'] = $subtotal;
            $response['total'] = $subtotal;
            break;
            
        case 'get_count':
            // Lấy số lượng sản phẩm trong giỏ hàng
            $totalItems = 0;
            if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                foreach ($_SESSION['cart'] as $item) {
                    $totalItems += $item['quantity'];
                }
            }
            
            $response = [
                'success' => true,
                'cart_count' => $totalItems
            ];
            break;
            
        default:
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

