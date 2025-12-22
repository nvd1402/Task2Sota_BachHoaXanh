<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Sửa Đơn hàng - Admin Dashboard";

// Lấy ID đơn hàng
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    header('Location: orders.php');
    exit();
}

// Kết nối database
$conn = connectDB();

// Lấy thông tin đơn hàng
$order = null;
$sql = "SELECT * FROM orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $order = $result->fetch_assoc();
}

$stmt->close();

if (!$order) {
    closeDB($conn);
    header('Location: orders.php');
    exit();
}

// Xử lý cập nhật
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_order'])) {
    $customerName = trim($_POST['customer_name'] ?? '');
    $customerEmail = trim($_POST['customer_email'] ?? '');
    $customerPhone = trim($_POST['customer_phone'] ?? '');
    $customerAddress = trim($_POST['customer_address'] ?? '');
    $customerCity = trim($_POST['customer_city'] ?? '');
    $status = trim($_POST['status'] ?? '');
    $paymentStatus = trim($_POST['payment_status'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? '');
    $shippingMethod = trim($_POST['shipping_method'] ?? '');
    $shippingFee = isset($_POST['shipping_fee']) ? (float)$_POST['shipping_fee'] : 0;
    $discount = isset($_POST['discount']) ? (float)$_POST['discount'] : 0;
    $subtotal = isset($_POST['subtotal']) ? (float)$_POST['subtotal'] : 0;
    $total = $subtotal + $shippingFee - $discount;
    $adminNotes = trim($_POST['admin_notes'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    
    $updateSql = "UPDATE orders SET 
                  customer_name = ?, customer_email = ?, customer_phone = ?, 
                  customer_address = ?, customer_city = ?, 
                  status = ?, payment_status = ?, payment_method = ?, 
                  shipping_method = ?, shipping_fee = ?, 
                  subtotal = ?, discount = ?, total = ?, 
                  admin_notes = ?, notes = ? 
                  WHERE id = ?";
    
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sssssssssddddssi", 
        $customerName, $customerEmail, $customerPhone,
        $customerAddress, $customerCity,
        $status, $paymentStatus, $paymentMethod,
        $shippingMethod, $shippingFee,
        $subtotal, $discount, $total,
        $adminNotes, $notes, $order_id
    );
    
    if ($updateStmt->execute()) {
        $success = true;
        // Reload order data
        $sql = "SELECT * FROM orders WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $order_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $order = $result->fetch_assoc();
        $stmt->close();
    } else {
        $error = 'Có lỗi xảy ra khi cập nhật';
    }
    $updateStmt->close();
}

closeDB($conn);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= $pageTitle ?></title>
  <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" rel="stylesheet" />
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link id="pagestyle" href="../assets/css/soft-ui-dashboard.css?v=1.1.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
  <?php $currentPage = 'orders'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
      <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> Đã cập nhật đơn hàng.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>
      
      <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Lỗi!</strong> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <div class="row">
                <div class="col-lg-6">
                  <h6>Sửa Đơn hàng: <?= htmlspecialchars($order['order_number']) ?></h6>
                </div>
                <div class="col-lg-6 text-end">
                  <a href="order_detail.php?id=<?= $order_id ?>" class="btn bg-gradient-secondary btn-sm mb-0">Xem chi tiết</a>
                  <a href="orders.php" class="btn bg-gradient-secondary btn-sm mb-0">Quay lại</a>
                </div>
              </div>
            </div>
            <div class="card-body">
              <form method="POST">
                <div class="row">
                  <div class="col-md-6">
                    <h6 class="mb-3">Thông tin khách hàng</h6>
                    <div class="mb-3">
                      <label class="form-label">Tên khách hàng *</label>
                      <input type="text" name="customer_name" class="form-control" 
                             value="<?= htmlspecialchars($order['customer_name']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Email *</label>
                      <input type="email" name="customer_email" class="form-control" 
                             value="<?= htmlspecialchars($order['customer_email']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Điện thoại *</label>
                      <input type="tel" name="customer_phone" class="form-control" 
                             value="<?= htmlspecialchars($order['customer_phone']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Địa chỉ *</label>
                      <textarea name="customer_address" class="form-control" rows="2" required><?= htmlspecialchars($order['customer_address']) ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Thành phố</label>
                      <input type="text" name="customer_city" class="form-control" 
                             value="<?= htmlspecialchars($order['customer_city'] ?? '') ?>">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <h6 class="mb-3">Thông tin đơn hàng</h6>
                    <div class="mb-3">
                      <label class="form-label">Trạng thái</label>
                      <select name="status" class="form-select">
                        <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                        <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                        <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Đang giao</option>
                        <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Đã giao</option>
                        <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Trạng thái thanh toán</label>
                      <select name="payment_status" class="form-select">
                        <option value="pending" <?= $order['payment_status'] === 'pending' ? 'selected' : '' ?>>Chờ thanh toán</option>
                        <option value="paid" <?= $order['payment_status'] === 'paid' ? 'selected' : '' ?>>Đã thanh toán</option>
                        <option value="failed" <?= $order['payment_status'] === 'failed' ? 'selected' : '' ?>>Thất bại</option>
                        <option value="refunded" <?= $order['payment_status'] === 'refunded' ? 'selected' : '' ?>>Đã hoàn tiền</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Phương thức thanh toán</label>
                      <select name="payment_method" class="form-select">
                        <option value="cod" <?= $order['payment_method'] === 'cod' ? 'selected' : '' ?>>COD</option>
                        <option value="bank_transfer" <?= $order['payment_method'] === 'bank_transfer' ? 'selected' : '' ?>>Chuyển khoản</option>
                        <option value="e_wallet" <?= $order['payment_method'] === 'e_wallet' ? 'selected' : '' ?>>Ví điện tử</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Phương thức vận chuyển</label>
                      <input type="text" name="shipping_method" class="form-control" 
                             value="<?= htmlspecialchars($order['shipping_method'] ?? 'standard') ?>">
                    </div>
                    <div class="row">
                      <div class="col-md-4 mb-3">
                        <label class="form-label">Tạm tính (₫)</label>
                        <input type="number" name="subtotal" class="form-control" step="0.01" 
                               value="<?= $order['subtotal'] ?>" required>
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label">Giảm giá (₫)</label>
                        <input type="number" name="discount" class="form-control" step="0.01" 
                               value="<?= $order['discount'] ?? 0 ?>">
                      </div>
                      <div class="col-md-4 mb-3">
                        <label class="form-label">Phí vận chuyển (₫)</label>
                        <input type="number" name="shipping_fee" class="form-control" step="0.01" 
                               value="<?= $order['shipping_fee'] ?? 0 ?>">
                      </div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Tổng cộng</label>
                      <input type="text" class="form-control" 
                             value="<?= number_format($order['total'], 0, ',', '.') ?>₫" readonly>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Ghi chú của khách hàng</label>
                      <textarea name="notes" class="form-control" rows="2"><?= htmlspecialchars($order['notes'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Ghi chú của admin</label>
                      <textarea name="admin_notes" class="form-control" rows="2"><?= htmlspecialchars($order['admin_notes'] ?? '') ?></textarea>
                    </div>
                  </div>
                </div>
                <div class="mt-4">
                  <button type="submit" name="update_order" class="btn bg-gradient-primary">Cập nhật</button>
                  <a href="orders.php" class="btn bg-gradient-secondary">Hủy</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/soft-ui-dashboard.min.js?v=1.1.0"></script>
  <script>
  // Tính tổng tự động
  document.addEventListener('DOMContentLoaded', function() {
    const subtotalInput = document.querySelector('input[name="subtotal"]');
    const discountInput = document.querySelector('input[name="discount"]');
    const shippingInput = document.querySelector('input[name="shipping_fee"]');
    const totalInput = document.querySelector('input[readonly]');
    
    function calculateTotal() {
      const subtotal = parseFloat(subtotalInput.value) || 0;
      const discount = parseFloat(discountInput.value) || 0;
      const shipping = parseFloat(shippingInput.value) || 0;
      const total = subtotal + shipping - discount;
      totalInput.value = new Intl.NumberFormat('vi-VN').format(total) + '₫';
    }
    
    [subtotalInput, discountInput, shippingInput].forEach(input => {
      input.addEventListener('input', calculateTotal);
    });
  });
  </script>
</body>
</html>

