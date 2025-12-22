<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Quản lý Đánh giá - Admin Dashboard";

// Xử lý tìm kiếm, filter và phân trang
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Lấy danh sách đánh giá
$conn = connectDB();
$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(r.customer_name LIKE ? OR r.comment LIKE ? OR p.name LIKE ?)";
    $searchTerm = "%{$search}%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    $types .= 'sss';
}

if (!empty($status)) {
    $where[] = "r.status = ?";
    $params[] = $status;
    $types .= 's';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Đếm tổng số
$countSql = "SELECT COUNT(*) as total FROM reviews r 
             LEFT JOIN products p ON r.product_id = p.id 
             $whereClause";
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalReviews = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalReviews / $perPage);
$countStmt->close();

// Lấy danh sách với phân trang
$sql = "SELECT r.*, p.name as product_name, p.image as product_image 
        FROM reviews r 
        LEFT JOIN products p ON r.product_id = p.id 
        $whereClause 
        ORDER BY r.created_at DESC 
        LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types . 'ii', ...array_merge($params, [$perPage, $offset]));
} else {
    $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$reviews = [];
while ($row = $result->fetch_assoc()) {
    $reviews[] = $row;
}
$stmt->close();

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
  <?php $currentPage = 'reviews'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
      <?php if (isset($_GET['deleted'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> Đã xóa đánh giá.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>
      
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <div class="row">
                <div class="col-lg-6 col-7">
                  <h6>Quản lý Đánh giá</h6>
                  <p class="text-sm mb-0">
                    <i class="fa fa-check text-info" aria-hidden="true"></i>
                    <span class="font-weight-bold ms-1"><?= $totalReviews ?> đánh giá</span>
                  </p>
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
                      <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                      <option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                      <option value="rejected" <?= $status === 'rejected' ? 'selected' : '' ?>>Từ chối</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <button type="submit" class="btn bg-gradient-info btn-sm w-100">Tìm kiếm</button>
                  </div>
                  <div class="col-md-3">
                    <a href="reviews.php" class="btn bg-gradient-secondary btn-sm w-100">Reset</a>
                  </div>
                </form>
              </div>

              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Sản phẩm</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Khách hàng</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Đánh giá</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Bình luận</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng thái</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ngày tạo</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($reviews)): ?>
                      <tr>
                        <td colspan="7" class="text-center py-4">Không có đánh giá nào</td>
                      </tr>
                    <?php else: ?>
                      <?php
                      $statusLabels = [
                          'pending' => 'Chờ duyệt',
                          'approved' => 'Đã duyệt',
                          'rejected' => 'Từ chối'
                      ];
                      $statusClass = [
                          'pending' => 'bg-gradient-warning',
                          'approved' => 'bg-gradient-success',
                          'rejected' => 'bg-gradient-danger'
                      ];
                      
                      foreach ($reviews as $review):
                        $reviewStatus = $review['status'];
                        $statusLabel = $statusLabels[$reviewStatus] ?? $reviewStatus;
                        $statusBg = $statusClass[$reviewStatus] ?? 'bg-gradient-secondary';
                        $reviewDate = date('d/m/Y H:i', strtotime($review['created_at']));
                        $productImg = !empty($review['product_image']) ? '../assets/images/' . $review['product_image'] : '../assets/images/lesterblur__2.jpg';
                      ?>
                      <tr>
                        <td>
                          <div class="d-flex px-2 py-1">
                            <img src="<?= htmlspecialchars($productImg) ?>" class="avatar avatar-sm me-3" alt="<?= htmlspecialchars($review['product_name'] ?? 'N/A') ?>">
                            <div class="d-flex flex-column justify-content-center">
                              <h6 class="mb-0 text-sm"><?= htmlspecialchars($review['product_name'] ?? 'N/A') ?></h6>
                            </div>
                          </div>
                        </td>
                        <td>
                          <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($review['customer_name']) ?></p>
                          <?php if (!empty($review['customer_email'])): ?>
                          <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($review['customer_email']) ?></p>
                          <?php endif; ?>
                        </td>
                        <td class="align-middle text-center">
                          <div class="d-flex justify-content-center">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                              <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-warning' : 'text-secondary' ?>"></i>
                            <?php endfor; ?>
                          </div>
                          <span class="text-xs"><?= $review['rating'] ?>/5</span>
                        </td>
                        <td class="align-middle">
                          <p class="text-xs mb-0"><?= htmlspecialchars(mb_substr($review['comment'] ?? '', 0, 100)) ?><?= mb_strlen($review['comment'] ?? '') > 100 ? '...' : '' ?></p>
                        </td>
                        <td class="align-middle text-center">
                          <span class="badge badge-sm <?= $statusBg ?>"><?= htmlspecialchars($statusLabel) ?></span>
                        </td>
                        <td class="align-middle text-center">
                          <span class="text-secondary text-xs font-weight-bold"><?= htmlspecialchars($reviewDate) ?></span>
                        </td>
                        <td class="align-middle text-center">
                          <a href="review_detail.php?id=<?= $review['id'] ?>" class="btn btn-link text-dark px-2 mb-0" title="Xem chi tiết">
                            <i class="fas fa-eye text-sm"></i>
                          </a>
                          <a href="review_delete.php?id=<?= $review['id'] ?>" class="btn btn-link text-danger px-2 mb-0" 
                             onclick="return confirm('Bạn có chắc muốn xóa đánh giá này?')" title="Xóa">
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

