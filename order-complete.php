<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = "Đặt hàng thành công - Bách Hóa Xanh";

// Kết nối database
$conn = connectDB();

// Lấy mã đơn hàng từ URL
$orderNumber = isset($_GET['order']) ? trim($_GET['order']) : '';

if (empty($orderNumber)) {
    header('Location: index.php');
    exit();
}

// Lấy thông tin đơn hàng
$order = null;
$orderItems = [];

$sql = "SELECT * FROM orders WHERE order_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $orderNumber);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $order = $result->fetch_assoc();
    
    // Lấy chi tiết đơn hàng
    $itemsSql = "SELECT * FROM order_items WHERE order_id = ?";
    $itemsStmt = $conn->prepare($itemsSql);
    $itemsStmt->bind_param("i", $order['id']);
    $itemsStmt->execute();
    $itemsResult = $itemsStmt->get_result();
    
    while ($row = $itemsResult->fetch_assoc()) {
        $orderItems[] = $row;
    }
    $itemsStmt->close();
}

$stmt->close();

if (!$order) {
    header('Location: index.php');
    exit();
}

include 'includes/header.php';
?>

<main class="order-complete-page">
    <!-- Breadcrumb -->
    <div class="order-complete-breadcrumb">
        <div class="container">
            <div class="breadcrumb-content">
                <span class="breadcrumb-prev">SHOPPING CART</span>
                <span class="breadcrumb-separator">></span>
                <span class="breadcrumb-prev">CHECKOUT DETAILS</span>
                <span class="breadcrumb-separator">></span>
                <span class="breadcrumb-current">ORDER COMPLETE</span>
            </div>
        </div>
    </div>

    <!-- Order Complete Content -->
    <div class="container">
        <div class="order-complete-wrapper">
            <!-- Success Message -->
            <div class="order-success-message text-center mb-5">
                <div class="success-icon mb-3" style="font-size: 80px; color: #3da04d;">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <h1 class="success-title mb-3">Cảm ơn bạn đã đặt hàng!</h1>
                <p class="success-text text-muted">Đơn hàng của bạn đã được tiếp nhận và đang được xử lý.</p>
            </div>

            <!-- Order Details -->
            <div class="order-details-box border rounded p-4 mb-4">
                <h2 class="order-details-title mb-4">Chi tiết đơn hàng</h2>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Mã đơn hàng:</strong> <?= htmlspecialchars($order['order_number']) ?></p>
                        <p><strong>Ngày đặt:</strong> <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                        <p><strong>Trạng thái:</strong> 
                            <span class="badge bg-warning"><?= htmlspecialchars($order['status']) ?></span>
                        </p>
                        <p><strong>Phương thức thanh toán:</strong> 
                            <?php
                            $paymentMethods = [
                                'cod' => 'Trả tiền mặt khi nhận hàng',
                                'bank_transfer' => 'Chuyển khoản ngân hàng',
                                'e_wallet' => 'Ví điện tử'
                            ];
                            echo htmlspecialchars($paymentMethods[$order['payment_method']] ?? $order['payment_method']);
                            ?>
                        </p>
                        <p><strong>Trạng thái thanh toán:</strong> 
                            <?php
                            $paymentStatusLabels = [
                                'pending' => 'Chờ thanh toán',
                                'paid' => 'Đã thanh toán',
                                'failed' => 'Thất bại',
                                'refunded' => 'Đã hoàn tiền'
                            ];
                            $paymentStatusClass = [
                                'pending' => 'bg-warning',
                                'paid' => 'bg-success',
                                'failed' => 'bg-danger',
                                'refunded' => 'bg-secondary'
                            ];
                            $statusLabel = $paymentStatusLabels[$order['payment_status']] ?? $order['payment_status'];
                            $statusClass = $paymentStatusClass[$order['payment_status']] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabel) ?></span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Khách hàng:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></p>
                        <p><strong>Điện thoại:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
                    </div>
                </div>

                <div class="mb-3">
                    <p><strong>Địa chỉ giao hàng:</strong></p>
                    <p class="ms-3">
                        <?= htmlspecialchars($order['customer_address']) ?><br>
                        <?php if (!empty($order['customer_city'])): ?>
                            <?= htmlspecialchars($order['customer_city']) ?><br>
                        <?php endif; ?>
                    </p>
                </div>

                <!-- Order Items -->
                <div class="order-items-table mt-4">
                    <h3 class="mb-3">Sản phẩm đã đặt</h3>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Sản phẩm</th>
                                <th>Số lượng</th>
                                <th>Giá</th>
                                <th>Tổng</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orderItems as $item): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if (!empty($item['product_image'])): ?>
                                        <img src="assets/images/<?= htmlspecialchars($item['product_image']) ?>" 
                                             alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                             style="width: 60px; height: 60px; object-fit: contain;">
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= htmlspecialchars($item['product_name']) ?></strong>
                                            <?php if (!empty($item['weight_option'])): ?>
                                                <br><small class="text-muted"><?= htmlspecialchars($item['weight_option']) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td><?= $item['quantity'] ?></td>
                                <td><?= number_format($item['unit_price'], 0, ',', '.') ?>₫</td>
                                <td><strong><?= number_format($item['subtotal'], 0, ',', '.') ?>₫</strong></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Tạm tính:</strong></td>
                                <td><strong><?= number_format($order['subtotal'], 0, ',', '.') ?>₫</strong></td>
                            </tr>
                            <?php if ($order['discount'] > 0): ?>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Giảm giá:</strong></td>
                                <td><strong>-<?= number_format($order['discount'], 0, ',', '.') ?>₫</strong></td>
                            </tr>
                            <?php endif; ?>
                            <?php if ($order['shipping_fee'] > 0): ?>
                            <tr>
                                <td colspan="3" class="text-end"><strong>Phí vận chuyển:</strong></td>
                                <td><strong><?= number_format($order['shipping_fee'], 0, ',', '.') ?>₫</strong></td>
                            </tr>
                            <?php endif; ?>
                            <tr class="table-primary">
                                <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                                <td><strong><?= number_format($order['total'], 0, ',', '.') ?>₫</strong></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <?php if (!empty($order['notes'])): ?>
                <div class="order-notes mt-3">
                    <p><strong>Ghi chú:</strong> <?= nl2br(htmlspecialchars($order['notes'])) ?></p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Action Buttons -->
            <div class="order-actions text-center">
                <a href="index.php" class="btn btn-primary me-2">Về trang chủ</a>
                <a href="products.php" class="btn btn-outline-primary">Tiếp tục mua sắm</a>
            </div>
        </div>
    </div>
</main>

<?php 
// Đóng kết nối database
if (isset($conn)) {
    closeDB($conn);
}
include 'includes/footer.php'; 
?>
