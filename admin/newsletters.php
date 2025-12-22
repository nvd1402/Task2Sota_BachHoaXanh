<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Quản lý Newsletter - Admin Dashboard";

// Xử lý tìm kiếm, filter và phân trang
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Lấy danh sách newsletter subscriptions
$conn = connectDB();
$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(email LIKE ? OR ip_address LIKE ?)";
    $searchTerm = "%{$search}%";
    $params = array_merge($params, [$searchTerm, $searchTerm]);
    $types .= 'ss';
}

if (!empty($status)) {
    $where[] = "status = ?";
    $params[] = $status;
    $types .= 's';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Đếm tổng số
$countSql = "SELECT COUNT(*) as total FROM newsletter_subscriptions $whereClause";
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalSubscriptions = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalSubscriptions / $perPage);
$countStmt->close();

// Lấy danh sách với phân trang
$sql = "SELECT * FROM newsletter_subscriptions $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types . 'ii', ...array_merge($params, [$perPage, $offset]));
} else {
    $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$subscriptions = [];
while ($row = $result->fetch_assoc()) {
    $subscriptions[] = $row;
}
$stmt->close();

// Thống kê
$statsSql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_count,
    SUM(CASE WHEN status = 'unsubscribed' THEN 1 ELSE 0 END) as unsubscribed_count,
    SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_count
    FROM newsletter_subscriptions";
$statsResult = $conn->query($statsSql);
$stats = $statsResult->fetch_assoc();

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
  <?php $currentPage = 'newsletters'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
      <!-- Success/Error Messages -->
      <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <span class="alert-icon"><i class="ni ni-like-2"></i></span>
          <span class="alert-text"><?= htmlspecialchars($_SESSION['success_message']) ?></span>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
      <?php endif; ?>
      
      <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <span class="alert-icon"><i class="ni ni-bell-55"></i></span>
          <span class="alert-text"><?= htmlspecialchars($_SESSION['error_message']) ?></span>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <?php unset($_SESSION['error_message']); ?>
      <?php endif; ?>
      
      <!-- Statistics Cards -->
      <div class="row mb-4">
        <div class="col-lg-3 col-md-6 col-12 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md me-3">
                  <i class="fas fa-envelope text-white text-lg opacity-10"></i>
                </div>
                <div>
                  <h6 class="mb-0">Tổng đăng ký</h6>
                  <h4 class="mb-0"><?= number_format($stats['total']) ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md me-3">
                  <i class="fas fa-check-circle text-white text-lg opacity-10"></i>
                </div>
                <div>
                  <h6 class="mb-0">Đang đăng ký</h6>
                  <h4 class="mb-0"><?= number_format($stats['active_count']) ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md me-3">
                  <i class="fas fa-times-circle text-white text-lg opacity-10"></i>
                </div>
                <div>
                  <h6 class="mb-0">Đã hủy</h6>
                  <h4 class="mb-0"><?= number_format($stats['unsubscribed_count']) ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-3 col-md-6 col-12 mb-4">
          <div class="card">
            <div class="card-body p-3">
              <div class="d-flex align-items-center">
                <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md me-3">
                  <i class="fas fa-calendar-day text-white text-lg opacity-10"></i>
                </div>
                <div>
                  <h6 class="mb-0">Hôm nay</h6>
                  <h4 class="mb-0"><?= number_format($stats['today_count']) ?></h4>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <div class="row">
                <div class="col-lg-6 col-7">
                  <h6>Quản lý Newsletter</h6>
                  <p class="text-sm mb-0">
                    <i class="fa fa-check text-info" aria-hidden="true"></i>
                    <span class="font-weight-bold ms-1"><?= $totalSubscriptions ?> đăng ký</span>
                  </p>
                </div>
                <div class="col-lg-6 col-5 text-end">
                  <a href="newsletters_export.php" class="btn bg-gradient-success btn-sm">
                    <i class="fas fa-download"></i> Xuất Excel
                  </a>
                </div>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
              <!-- Filter và Search -->
              <div class="px-4 pb-3">
                <form method="GET" class="row g-3">
                  <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm email hoặc IP..." 
                           value="<?= htmlspecialchars($search) ?>">
                  </div>
                  <div class="col-md-3">
                    <select name="status" class="form-select">
                      <option value="">Tất cả trạng thái</option>
                      <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Đang đăng ký</option>
                      <option value="unsubscribed" <?= $status === 'unsubscribed' ? 'selected' : '' ?>>Đã hủy</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <button type="submit" class="btn bg-gradient-info btn-sm w-100">Tìm kiếm</button>
                  </div>
                  <div class="col-md-3">
                    <a href="newsletters.php" class="btn bg-gradient-secondary btn-sm w-100">Reset</a>
                  </div>
                </form>
              </div>

              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Email</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">IP Address</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng thái</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ngày đăng ký</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($subscriptions)): ?>
                      <tr>
                        <td colspan="5" class="text-center py-4">Không có đăng ký nào</td>
                      </tr>
                    <?php else: ?>
                      <?php
                      $statusLabels = [
                          'active' => 'Đang đăng ký',
                          'unsubscribed' => 'Đã hủy'
                      ];
                      $statusClass = [
                          'active' => 'bg-gradient-success',
                          'unsubscribed' => 'bg-gradient-secondary'
                      ];
                      
                      foreach ($subscriptions as $sub):
                        $subStatus = $sub['status'];
                        $statusLabel = $statusLabels[$subStatus] ?? $subStatus;
                        $statusBg = $statusClass[$subStatus] ?? 'bg-gradient-secondary';
                        $subDate = date('d/m/Y H:i', strtotime($sub['created_at']));
                      ?>
                      <tr>
                        <td>
                          <div class="d-flex px-2 py-1">
                            <div class="d-flex flex-column justify-content-center">
                              <h6 class="mb-0 text-sm"><?= htmlspecialchars($sub['email']) ?></h6>
                            </div>
                          </div>
                        </td>
                        <td>
                          <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($sub['ip_address'] ?? 'N/A') ?></p>
                        </td>
                        <td class="align-middle text-center">
                          <span class="badge badge-sm <?= $statusBg ?>"><?= htmlspecialchars($statusLabel) ?></span>
                        </td>
                        <td class="align-middle text-center">
                          <span class="text-secondary text-xs font-weight-bold"><?= htmlspecialchars($subDate) ?></span>
                        </td>
                        <td class="align-middle text-center">
                          <?php if ($sub['status'] === 'active'): ?>
                            <a href="newsletter_unsubscribe.php?id=<?= $sub['id'] ?>" class="btn btn-link text-warning px-2 mb-0" 
                               onclick="return confirm('Bạn có chắc muốn hủy đăng ký email này?')" title="Hủy đăng ký">
                              <i class="fas fa-ban text-sm"></i>
                            </a>
                          <?php else: ?>
                            <a href="newsletter_reactivate.php?id=<?= $sub['id'] ?>" class="btn btn-link text-success px-2 mb-0" 
                               onclick="return confirm('Bạn có chắc muốn kích hoạt lại email này?')" title="Kích hoạt lại">
                              <i class="fas fa-check text-sm"></i>
                            </a>
                          <?php endif; ?>
                          <a href="newsletter_delete.php?id=<?= $sub['id'] ?>" class="btn btn-link text-danger px-2 mb-0" 
                             onclick="return confirm('Bạn có chắc muốn xóa email này?')" title="Xóa">
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

