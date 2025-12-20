<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Chi tiết Đơn hàng - Admin Dashboard";

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
$orderItems = [];

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
    
    while ($row = $itemsResult->fetch_assoc()) {
        $orderItems[] = $row;
    }
    $itemsStmt->close();
}

$stmt->close();

if (!$order) {
    closeDB($conn);
    header('Location: orders.php');
    exit();
}

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $newStatus = trim($_POST['status'] ?? '');
    $newPaymentStatus = trim($_POST['payment_status'] ?? '');
    $adminNotes = trim($_POST['admin_notes'] ?? '');
    
    $updateSql = "UPDATE orders SET status = ?, payment_status = ?, admin_notes = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("sssi", $newStatus, $newPaymentStatus, $adminNotes, $order_id);
    
    if ($updateStmt->execute()) {
        $order['status'] = $newStatus;
        $order['payment_status'] = $newPaymentStatus;
        $order['admin_notes'] = $adminNotes;
        $success = true;
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
      <?php if (isset($success)): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> Đã cập nhật trạng thái đơn hàng.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>
      
      <?php if (isset($error)): ?>
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
                  <h6>Chi tiết Đơn hàng: <?= htmlspecialchars($order['order_number']) ?></h6>
                </div>
                <div class="col-lg-6 text-end">
                  <a href="orders.php" class="btn bg-gradient-secondary btn-sm mb-0">Quay lại</a>
                </div>
              </div>
            </div>
            <div class="card-body">
              <form method="POST">
                <div class="row">
                  <div class="col-md-6">
                    <h6 class="mb-3">Thông tin khách hàng</h6>
                    <p><strong>Tên:</strong> <?= htmlspecialchars($order['customer_name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_email']) ?></p>
                    <p><strong>Điện thoại:</strong> <?= htmlspecialchars($order['customer_phone']) ?></p>
                    <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($order['customer_address']) ?></p>
                    <p><strong>Thành phố:</strong> <?= htmlspecialchars($order['customer_city'] ?? '') ?></p>
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
                      <label class="form-label">Ghi chú của admin</label>
                      <textarea name="admin_notes" class="form-control" rows="3"><?= htmlspecialchars($order['admin_notes'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" name="update_status" class="btn bg-gradient-primary btn-sm">Cập nhật</button>
                  </div>
                </div>
              </form>

              <hr class="my-4">

              <h6 class="mb-3">Sản phẩm trong đơn hàng</h6>
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th>Sản phẩm</th>
                      <th class="text-center">Số lượng</th>
                      <th class="text-center">Đơn giá</th>
                      <th class="text-center">Thành tiền</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($orderItems as $item): ?>
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <?php if (!empty($item['product_image'])): ?>
                          <img src="../assets/images/<?= htmlspecialchars($item['product_image']) ?>" 
                               class="avatar avatar-sm me-3" alt="<?= htmlspecialchars($item['product_name']) ?>">
                          <?php endif; ?>
                          <div>
                            <h6 class="mb-0 text-sm"><?= htmlspecialchars($item['product_name']) ?></h6>
                            <?php if (!empty($item['weight_option'])): ?>
                            <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($item['weight_option']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($item['product_sku'])): ?>
                            <p class="text-xs text-secondary mb-0">SKU: <?= htmlspecialchars($item['product_sku']) ?></p>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-sm"><?= $item['quantity'] ?></span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-sm"><?= number_format($item['unit_price'], 0, ',', '.') ?>₫</span>
                      </td>
                      <td class="align-middle text-center">
                        <span class="text-sm font-weight-bold"><?= number_format($item['subtotal'], 0, ',', '.') ?>₫</span>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                  <tfoot>
                    <tr>
                      <td colspan="3" class="text-end"><strong>Tạm tính:</strong></td>
                      <td class="text-center"><strong><?= number_format($order['subtotal'], 0, ',', '.') ?>₫</strong></td>
                    </tr>
                    <?php if ($order['discount'] > 0): ?>
                    <tr>
                      <td colspan="3" class="text-end"><strong>Giảm giá:</strong></td>
                      <td class="text-center"><strong>-<?= number_format($order['discount'], 0, ',', '.') ?>₫</strong></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($order['shipping_fee'] > 0): ?>
                    <tr>
                      <td colspan="3" class="text-end"><strong>Phí vận chuyển:</strong></td>
                      <td class="text-center"><strong><?= number_format($order['shipping_fee'], 0, ',', '.') ?>₫</strong></td>
                    </tr>
                    <?php endif; ?>
                    <tr class="table-primary">
                      <td colspan="3" class="text-end"><strong>Tổng cộng:</strong></td>
                      <td class="text-center"><strong><?= number_format($order['total'], 0, ',', '.') ?>₫</strong></td>
                    </tr>
                  </tfoot>
                </table>
              </div>

              <?php if (!empty($order['notes'])): ?>
              <div class="mt-4">
                <h6>Ghi chú của khách hàng</h6>
                <p class="text-sm"><?= nl2br(htmlspecialchars($order['notes'])) ?></p>
              </div>
              <?php endif; ?>

              <div class="mt-4">
                <p class="text-sm text-secondary">
                  <strong>Ngày đặt:</strong> <?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?><br>
                  <strong>Cập nhật lần cuối:</strong> <?= date('d/m/Y H:i:s', strtotime($order['updated_at'])) ?>
                </p>
              </div>
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
</body>
</html>

