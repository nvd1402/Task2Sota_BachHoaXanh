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
            
            // Lấy user_id và session_id
            $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            $sessionId = session_id();
            
            // Kiểm tra sản phẩm đã có trong giỏ hàng chưa (trong database)
            // Xử lý weight_option: normalize empty string thành NULL
            $weightOptionNormalized = (!empty($weight_option) && trim($weight_option) !== '') ? trim($weight_option) : null;
            
            // Đơn giản hóa: sử dụng COALESCE để xử lý cả NULL và empty string
            if ($userId) {
                $checkSql = "SELECT id, quantity FROM cart WHERE product_id = ? AND COALESCE(weight_option, '') = COALESCE(?, '') AND user_id = ?";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bind_param("isi", $product_id, $weightOptionNormalized, $userId);
            } else {
                $checkSql = "SELECT id, quantity FROM cart WHERE product_id = ? AND COALESCE(weight_option, '') = COALESCE(?, '') AND session_id = ? AND user_id IS NULL";
                $checkStmt = $conn->prepare($checkSql);
                $checkStmt->bind_param("iss", $product_id, $weightOptionNormalized, $sessionId);
            }
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            $existingItem = $checkResult->fetch_assoc();
            $checkStmt->close();
            
            if ($existingItem) {
                // Cập nhật số lượng
                $newQuantity = $existingItem['quantity'] + $quantity;
                $updateSql = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("ii", $newQuantity, $existingItem['id']);
                $updateStmt->execute();
                $updateStmt->close();
            } else {
                // Thêm mới vào database
                // Sử dụng weightOptionNormalized (NULL nếu empty)
                $insertSql = "INSERT INTO cart (user_id, session_id, product_id, quantity, weight_option) VALUES (?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->bind_param("isisi", $userId, $sessionId, $product_id, $quantity, $weightOptionNormalized);
                $insertStmt->execute();
                $insertStmt->close();
            }
            
            // Đồng bộ với session để tương thích ngược
            if (!isset($_SESSION['cart'])) {
                $_SESSION['cart'] = [];
            }
            $cartKey = $product_id . '_' . ($weight_option ?? 'default');
            // Lấy số lượng mới từ database sau khi cập nhật
            $finalQuantity = $existingItem ? ($existingItem['quantity'] + $quantity) : $quantity;
            $_SESSION['cart'][$cartKey] = [
                'product_id' => $product_id,
                'quantity' => $finalQuantity,
                'weight_option' => $weight_option,
                'price' => $price,
                'sale_price' => $product['sale_price'] ?? null
            ];
            
            // Tính tổng số sản phẩm trong giỏ hàng từ database
            if ($userId) {
                $countSql = "SELECT COUNT(*) as total FROM cart WHERE user_id = ?";
                $countStmt = $conn->prepare($countSql);
                $countStmt->bind_param("i", $userId);
            } else {
                $countSql = "SELECT COUNT(*) as total FROM cart WHERE session_id = ? AND user_id IS NULL";
                $countStmt = $conn->prepare($countSql);
                $countStmt->bind_param("s", $sessionId);
            }
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $totalItems = $countResult->fetch_assoc()['total'] ?? 0;
            $countStmt->close();
            
            // Đảm bảo trả về số lượng chính xác từ database (số loại sản phẩm khác nhau)
            $response = [
                'success' => true,
                'message' => 'Đã thêm sản phẩm vào giỏ hàng',
                'cart_count' => (int)$totalItems
            ];
            
            error_log('Cart add - Product ID: ' . $product_id . ', Quantity: ' . $quantity . ', Total items: ' . $totalItems);
            break;
            
        case 'update':
            // Cập nhật số lượng sản phẩm
            $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
            $weight_option = isset($_POST['weight_option']) ? trim($_POST['weight_option']) : null;
            
            if ($product_id <= 0) {
                throw new Exception('ID sản phẩm không hợp lệ');
            }
            
            $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            $sessionId = session_id();
            
            // Tìm item trong database
            // Xử lý weight_option: normalize empty string thành NULL
            $weightOptionNormalized = (!empty($weight_option) && trim($weight_option) !== '') ? trim($weight_option) : null;
            
            // Đơn giản hóa: sử dụng COALESCE để xử lý cả NULL và empty string
            if ($userId) {
                $findSql = "SELECT id FROM cart WHERE product_id = ? AND COALESCE(weight_option, '') = COALESCE(?, '') AND user_id = ?";
                $findStmt = $conn->prepare($findSql);
                $findStmt->bind_param("isi", $product_id, $weightOptionNormalized, $userId);
            } else {
                $findSql = "SELECT id FROM cart WHERE product_id = ? AND COALESCE(weight_option, '') = COALESCE(?, '') AND session_id = ? AND user_id IS NULL";
                $findStmt = $conn->prepare($findSql);
                $findStmt->bind_param("iss", $product_id, $weightOptionNormalized, $sessionId);
            }
            $findStmt->execute();
            $findResult = $findStmt->get_result();
            $cartItem = $findResult->fetch_assoc();
            $findStmt->close();
            
            if (!$cartItem) {
                // Debug: Log để kiểm tra
                error_log("Cart update/remove - Product not found. product_id: $product_id, weight_option: " . ($weightOptionNormalized ?? 'NULL') . ", user_id: " . ($userId ?? 'NULL') . ", session_id: $sessionId");
                throw new Exception('Sản phẩm không có trong giỏ hàng');
            }
            
            if ($quantity <= 0) {
                // Xóa sản phẩm nếu số lượng <= 0
                $deleteSql = "DELETE FROM cart WHERE id = ?";
                $deleteStmt = $conn->prepare($deleteSql);
                $deleteStmt->bind_param("i", $cartItem['id']);
                $deleteStmt->execute();
                $deleteStmt->close();
                
                // Xóa khỏi session
                $cartKey = $product_id . '_' . ($weight_option ?? 'default');
                if (isset($_SESSION['cart'][$cartKey])) {
                    unset($_SESSION['cart'][$cartKey]);
                }
                
                $response = [
                    'success' => true,
                    'message' => 'Đã xóa sản phẩm khỏi giỏ hàng'
                ];
            } else {
                // Cập nhật số lượng trong database
                $updateSql = "UPDATE cart SET quantity = ?, updated_at = NOW() WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("ii", $quantity, $cartItem['id']);
                $updateStmt->execute();
                $updateStmt->close();
                
                // Cập nhật session
                $cartKey = $product_id . '_' . ($weight_option ?? 'default');
                if (isset($_SESSION['cart'][$cartKey])) {
                    $_SESSION['cart'][$cartKey]['quantity'] = $quantity;
                }
                
                $response = [
                    'success' => true,
                    'message' => 'Đã cập nhật số lượng'
                ];
            }
            
            // Tính lại tổng từ database
            if ($userId) {
                $totalSql = "SELECT SUM(c.quantity * COALESCE(p.sale_price, p.price)) as subtotal 
                            FROM cart c 
                            INNER JOIN products p ON c.product_id = p.id 
                            WHERE c.user_id = ?";
                $totalStmt = $conn->prepare($totalSql);
                $totalStmt->bind_param("i", $userId);
            } else {
                $totalSql = "SELECT SUM(c.quantity * COALESCE(p.sale_price, p.price)) as subtotal 
                            FROM cart c 
                            INNER JOIN products p ON c.product_id = p.id 
                            WHERE c.session_id = ? AND c.user_id IS NULL";
                $totalStmt = $conn->prepare($totalSql);
                $totalStmt->bind_param("s", $sessionId);
            }
            $totalStmt->execute();
            $totalResult = $totalStmt->get_result();
            $subtotal = $totalResult->fetch_assoc()['subtotal'] ?? 0;
            $totalStmt->close();
            
            $response['subtotal'] = $subtotal;
            $response['total'] = $subtotal;
            
            // Cập nhật cart count sau khi update (số loại sản phẩm khác nhau)
            if ($userId) {
                $countSql = "SELECT COUNT(*) as total FROM cart WHERE user_id = ?";
                $countStmt = $conn->prepare($countSql);
                $countStmt->bind_param("i", $userId);
            } else {
                $countSql = "SELECT COUNT(*) as total FROM cart WHERE session_id = ? AND user_id IS NULL";
                $countStmt = $conn->prepare($countSql);
                $countStmt->bind_param("s", $sessionId);
            }
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $response['cart_count'] = (int)($countResult->fetch_assoc()['total'] ?? 0);
            $countStmt->close();
            
            break;
            
        case 'remove':
            // Xóa sản phẩm khỏi giỏ hàng
            $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
            $weight_option = isset($_POST['weight_option']) ? trim($_POST['weight_option']) : null;
            
            if ($product_id <= 0) {
                throw new Exception('ID sản phẩm không hợp lệ');
            }
            
            $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            $sessionId = session_id();
            
            // Tìm và xóa từ database
            // Xử lý weight_option: normalize empty string thành NULL
            $weightOptionNormalized = (!empty($weight_option) && trim($weight_option) !== '') ? trim($weight_option) : null;
            
            // Đơn giản hóa: sử dụng COALESCE để xử lý cả NULL và empty string
            if ($userId) {
                $findSql = "SELECT id FROM cart WHERE product_id = ? AND COALESCE(weight_option, '') = COALESCE(?, '') AND user_id = ?";
                $findStmt = $conn->prepare($findSql);
                $findStmt->bind_param("isi", $product_id, $weightOptionNormalized, $userId);
            } else {
                $findSql = "SELECT id FROM cart WHERE product_id = ? AND COALESCE(weight_option, '') = COALESCE(?, '') AND session_id = ? AND user_id IS NULL";
                $findStmt = $conn->prepare($findSql);
                $findStmt->bind_param("iss", $product_id, $weightOptionNormalized, $sessionId);
            }
            $findStmt->execute();
            $findResult = $findStmt->get_result();
            $cartItem = $findResult->fetch_assoc();
            $findStmt->close();
            
            if (!$cartItem) {
                // Debug: Log để kiểm tra
                error_log("Cart remove - Product not found. product_id: $product_id, weight_option: " . ($weightOptionNormalized ?? 'NULL') . ", user_id: " . ($userId ?? 'NULL') . ", session_id: $sessionId");
                throw new Exception('Sản phẩm không có trong giỏ hàng');
            }
            
            // Xóa từ database
            $deleteSql = "DELETE FROM cart WHERE id = ?";
            $deleteStmt = $conn->prepare($deleteSql);
            $deleteStmt->bind_param("i", $cartItem['id']);
            $deleteStmt->execute();
            $deleteStmt->close();
            
            // Xóa khỏi session
            $cartKey = $product_id . '_' . ($weight_option ?? 'default');
            if (isset($_SESSION['cart'][$cartKey])) {
                unset($_SESSION['cart'][$cartKey]);
            }
            
            $response = [
                'success' => true,
                'message' => 'Đã xóa sản phẩm khỏi giỏ hàng'
            ];
            
            // Tính lại tổng từ database
            if ($userId) {
                $totalSql = "SELECT SUM(c.quantity * COALESCE(p.sale_price, p.price)) as subtotal 
                            FROM cart c 
                            INNER JOIN products p ON c.product_id = p.id 
                            WHERE c.user_id = ?";
                $totalStmt = $conn->prepare($totalSql);
                $totalStmt->bind_param("i", $userId);
            } else {
                $totalSql = "SELECT SUM(c.quantity * COALESCE(p.sale_price, p.price)) as subtotal 
                            FROM cart c 
                            INNER JOIN products p ON c.product_id = p.id 
                            WHERE c.session_id = ? AND c.user_id IS NULL";
                $totalStmt = $conn->prepare($totalSql);
                $totalStmt->bind_param("s", $sessionId);
            }
            $totalStmt->execute();
            $totalResult = $totalStmt->get_result();
            $subtotal = $totalResult->fetch_assoc()['subtotal'] ?? 0;
            $totalStmt->close();
            
            $response['subtotal'] = $subtotal;
            $response['total'] = $subtotal;
            
            // Cập nhật cart count sau khi remove (số loại sản phẩm khác nhau)
            if ($userId) {
                $countSql = "SELECT COUNT(*) as total FROM cart WHERE user_id = ?";
                $countStmt = $conn->prepare($countSql);
                $countStmt->bind_param("i", $userId);
            } else {
                $countSql = "SELECT COUNT(*) as total FROM cart WHERE session_id = ? AND user_id IS NULL";
                $countStmt = $conn->prepare($countSql);
                $countStmt->bind_param("s", $sessionId);
            }
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $response['cart_count'] = (int)($countResult->fetch_assoc()['total'] ?? 0);
            $countStmt->close();
            
            break;
            
        case 'get_count':
            // Lấy số loại sản phẩm khác nhau trong giỏ hàng từ database (COUNT thay vì SUM)
            $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            $sessionId = session_id();
            
            if ($userId) {
                $countSql = "SELECT COUNT(*) as total FROM cart WHERE user_id = ?";
                $countStmt = $conn->prepare($countSql);
                $countStmt->bind_param("i", $userId);
            } else {
                $countSql = "SELECT COUNT(*) as total FROM cart WHERE session_id = ? AND user_id IS NULL";
                $countStmt = $conn->prepare($countSql);
                $countStmt->bind_param("s", $sessionId);
            }
            $countStmt->execute();
            $countResult = $countStmt->get_result();
            $totalItems = $countResult->fetch_assoc()['total'] ?? 0;
            $countStmt->close();
            
            $response = [
                'success' => true,
                'cart_count' => (int)$totalItems
            ];
            
            error_log('Cart get_count - User ID: ' . ($userId ?? 'NULL') . ', Session ID: ' . $sessionId . ', Total product types: ' . $totalItems);
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

