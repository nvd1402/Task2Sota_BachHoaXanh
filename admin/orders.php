<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Quản lý Đơn hàng - Admin Dashboard";

// Xử lý tìm kiếm, filter và phân trang
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Lấy danh sách đơn hàng
$conn = connectDB();
$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(order_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ? OR customer_phone LIKE ?)";
    $searchTerm = "%{$search}%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= 'ssss';
}

if (!empty($status)) {
    $where[] = "status = ?";
    $params[] = $status;
    $types .= 's';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Đếm tổng số đơn hàng
$countSql = "SELECT COUNT(*) as total FROM orders $whereClause";
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalOrders = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalOrders / $perPage);
$countStmt->close();

// Lấy danh sách đơn hàng với phân trang
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) as item_count
        FROM orders o 
        $whereClause 
        ORDER BY o.created_at DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types . 'ii', ...array_merge($params, [$perPage, $offset]));
} else {
    $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}
$stmt->close();

// Thống kê theo trạng thái
$statusStatsSql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$statusStatsResult = $conn->query($statusStatsSql);
$statusStats = [];
while ($row = $statusStatsResult->fetch_assoc()) {
    $statusStats[$row['status']] = $row['count'];
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
  <?php 
  // Include sidebar và navbar
  $currentPage = 'orders';
  include 'includes/sidebar.php'; 
  ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <div class="row">
                <div class="col-lg-6 col-7">
                  <h6>Quản lý Đơn hàng</h6>
                  <p class="text-sm mb-0">
                    <i class="fa fa-check text-info" aria-hidden="true"></i>
                    <span class="font-weight-bold ms-1"><?= $totalOrders ?> đơn hàng</span>
                  </p>
                </div>
                <div class="col-lg-6 col-5 my-auto text-end">
                  <a href="order_add.php" class="btn bg-gradient-primary btn-sm mb-0">
                    <i class="fas fa-plus"></i> Thêm đơn hàng
                  </a>
                </div>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
              <!-- Filter và Search -->
              <div class="px-4 pb-3">
                <form method="GET" class="row g-3">
                  <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." 
                           value="<?= htmlspecialchars($search) ?>">
                  </div>
                  <div class="col-md-3">
                    <select name="status" class="form-select">
                      <option value="">Tất cả trạng thái</option>
                      <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Chờ xử lý</option>
                      <option value="processing" <?= $status === 'processing' ? 'selected' : '' ?>>Đang xử lý</option>
                      <option value="shipped" <?= $status === 'shipped' ? 'selected' : '' ?>>Đang giao</option>
                      <option value="delivered" <?= $status === 'delivered' ? 'selected' : '' ?>>Đã giao</option>
                      <option value="cancelled" <?= $status === 'cancelled' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <button type="submit" class="btn bg-gradient-info btn-sm w-100">Tìm kiếm</button>
                  </div>
                  <div class="col-md-3">
                    <a href="orders.php" class="btn bg-gradient-secondary btn-sm w-100">Reset</a>
                  </div>
                </form>
              </div>

              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mã đơn</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Khách hàng</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Số lượng</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tổng tiền</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng thái</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thanh toán</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ngày đặt</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($orders)): ?>
                      <tr>
                        <td colspan="8" class="text-center py-4">Không có đơn hàng nào</td>
                      </tr>
                    <?php else: ?>
                      <?php
                      $statusLabels = [
                          'pending' => 'Chờ xử lý',
                          'processing' => 'Đang xử lý',
                          'shipped' => 'Đang giao',
                          'delivered' => 'Đã giao',
                          'cancelled' => 'Đã hủy'
                      ];
                      $statusClass = [
                          'pending' => 'bg-gradient-warning',
                          'processing' => 'bg-gradient-info',
                          'shipped' => 'bg-gradient-primary',
                          'delivered' => 'bg-gradient-success',
                          'cancelled' => 'bg-gradient-danger'
                      ];
                      $paymentLabels = [
                          'pending' => 'Chờ thanh toán',
                          'paid' => 'Đã thanh toán',
                          'failed' => 'Thất bại',
                          'refunded' => 'Đã hoàn tiền'
                      ];
                      $paymentClass = [
                          'pending' => 'bg-gradient-warning',
                          'paid' => 'bg-gradient-success',
                          'failed' => 'bg-gradient-danger',
                          'refunded' => 'bg-gradient-secondary'
                      ];
                      
                      foreach ($orders as $order):
                        $orderStatus = $order['status'];
                        $orderStatusLabel = $statusLabels[$orderStatus] ?? $orderStatus;
                        $orderStatusBg = $statusClass[$orderStatus] ?? 'bg-gradient-secondary';
                        
                        $paymentStatus = $order['payment_status'];
                        $paymentLabel = $paymentLabels[$paymentStatus] ?? $paymentStatus;
                        $paymentBg = $paymentClass[$paymentStatus] ?? 'bg-gradient-secondary';
                        
                        $orderDate = date('d/m/Y H:i', strtotime($order['created_at']));
                      ?>
                      <tr>
                        <td>
                          <div class="d-flex px-2 py-1">
                            <div class="d-flex flex-column justify-content-center">
                              <h6 class="mb-0 text-sm">
                                <a href="order_detail.php?id=<?= $order['id'] ?>" class="text-dark">
                                  <?= htmlspecialchars($order['order_number']) ?>
                                </a>
                              </h6>
                            </div>
                          </div>
                        </td>
                        <td>
                          <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($order['customer_name']) ?></p>
                          <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($order['customer_email']) ?></p>
                          <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($order['customer_phone']) ?></p>
                        </td>
                        <td class="align-middle text-center">
                          <span class="text-xs font-weight-bold"><?= $order['item_count'] ?> sản phẩm</span>
                        </td>
                        <td class="align-middle text-center text-sm">
                          <span class="text-xs font-weight-bold"><?= number_format($order['total'], 0, ',', '.') ?>₫</span>
                        </td>
                        <td class="align-middle text-center">
                          <span class="badge badge-sm <?= $orderStatusBg ?>"><?= htmlspecialchars($orderStatusLabel) ?></span>
                        </td>
                        <td class="align-middle text-center">
                          <span class="badge badge-sm <?= $paymentBg ?>"><?= htmlspecialchars($paymentLabel) ?></span>
                        </td>
                        <td class="align-middle text-center">
                          <span class="text-secondary text-xs font-weight-bold"><?= htmlspecialchars($orderDate) ?></span>
                        </td>
                        <td class="align-middle text-center">
                          <a href="order_detail.php?id=<?= $order['id'] ?>" class="btn btn-link text-dark px-2 mb-0" title="Xem chi tiết">
                            <i class="fas fa-eye text-sm"></i>
                          </a>
                          <a href="order_edit.php?id=<?= $order['id'] ?>" class="btn btn-link text-primary px-2 mb-0" title="Sửa">
                            <i class="fas fa-edit text-sm"></i>
                          </a>
                          <a href="order_delete.php?id=<?= $order['id'] ?>" class="btn btn-link text-danger px-2 mb-0" 
                             onclick="return confirm('Bạn có chắc muốn xóa đơn hàng này?')" title="Xóa">
                            <i class="fas fa-trash text-sm"></i>
                          </a>
                        </td>
                      </tr>
                      <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              <?php if ($totalPages > 1): ?>
              <div class="px-4 py-3">
                <nav aria-label="Page navigation">
                  <ul class="pagination justify-content-center mb-0">
                    <?php if ($page > 1): ?>
                      <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $status ? '&status=' . urlencode($status) : '' ?>">Trước</a>
                      </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                      <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                          <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $status ? '&status=' . urlencode($status) : '' ?>"><?= $i ?></a>
                        </li>
                      <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                        <li class="page-item disabled">
                          <span class="page-link">...</span>
                        </li>
                      <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                      <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $status ? '&status=' . urlencode($status) : '' ?>">Sau</a>
                      </li>
                    <?php endif; ?>
                  </ul>
                </nav>
              </div>
              <?php endif; ?>
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

