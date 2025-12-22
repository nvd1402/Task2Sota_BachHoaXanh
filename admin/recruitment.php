<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Quản lý Tuyển dụng - Admin Dashboard";

// Xử lý tìm kiếm, filter và phân trang
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Lấy danh sách tuyển dụng
$conn = connectDB();
$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(title LIKE ? OR position LIKE ? OR department LIKE ?)";
    $searchTerm = "%{$search}%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
    $types .= 'sss';
}

if (!empty($status)) {
    $where[] = "status = ?";
    $params[] = $status;
    $types .= 's';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Đếm tổng số
$countSql = "SELECT COUNT(*) as total FROM recruitment $whereClause";
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalRecruitment = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRecruitment / $perPage);
$countStmt->close();

// Lấy danh sách với phân trang
$sql = "SELECT * FROM recruitment $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types . 'ii', ...array_merge($params, [$perPage, $offset]));
} else {
    $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$recruitmentList = [];
while ($row = $result->fetch_assoc()) {
    $recruitmentList[] = $row;
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
  <?php $currentPage = 'recruitment'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
      <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> Đã thêm tuyển dụng.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>
      
      <?php if (isset($_GET['deleted'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> Đã xóa tuyển dụng.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>
      
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <div class="row">
                <div class="col-lg-6 col-7">
                  <h6>Quản lý Tuyển dụng</h6>
                  <p class="text-sm mb-0">
                    <i class="fa fa-check text-info" aria-hidden="true"></i>
                    <span class="font-weight-bold ms-1"><?= $totalRecruitment ?> vị trí</span>
                  </p>
                </div>
                <div class="col-lg-6 col-5 my-auto text-end">
                  <a href="recruitment_add.php" class="btn bg-gradient-primary btn-sm mb-0">
                    <i class="fas fa-plus"></i> Thêm tuyển dụng
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
                      <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                      <option value="open" <?= $status === 'open' ? 'selected' : '' ?>>Đang tuyển</option>
                      <option value="closed" <?= $status === 'closed' ? 'selected' : '' ?>>Đã đóng</option>
                      <option value="filled" <?= $status === 'filled' ? 'selected' : '' ?>>Đã đủ</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <button type="submit" class="btn bg-gradient-info btn-sm w-100">Tìm kiếm</button>
                  </div>
                  <div class="col-md-3">
                    <a href="recruitment.php" class="btn bg-gradient-secondary btn-sm w-100">Reset</a>
                  </div>
                </form>
              </div>

              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Vị trí</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Phòng ban</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Địa điểm</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Lương</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Số lượng</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Lượt xem</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng thái</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($recruitmentList)): ?>
                      <tr>
                        <td colspan="8" class="text-center py-4">Không có vị trí tuyển dụng nào</td>
                      </tr>
                    <?php else: ?>
                      <?php
                      $statusLabels = [
                          'draft' => 'Bản nháp',
                          'open' => 'Đang tuyển',
                          'closed' => 'Đã đóng',
                          'filled' => 'Đã đủ'
                      ];
                      $statusClass = [
                          'draft' => 'bg-gradient-warning',
                          'open' => 'bg-gradient-success',
                          'closed' => 'bg-gradient-secondary',
                          'filled' => 'bg-gradient-info'
                      ];
                      
                      foreach ($recruitmentList as $rec):
                        $recStatus = $rec['status'];
                        $statusLabel = $statusLabels[$recStatus] ?? $recStatus;
                        $statusBg = $statusClass[$recStatus] ?? 'bg-gradient-secondary';
                        $salaryDisplay = $rec['salary_display'] ?? 
                                         ($rec['salary_min'] && $rec['salary_max'] ? 
                                          number_format($rec['salary_min'], 0, ',', '.') . ' - ' . number_format($rec['salary_max'], 0, ',', '.') . '₫' : 
                                          'Thỏa thuận');
                      ?>
                      <tr>
                        <td>
                          <div class="d-flex flex-column">
                            <h6 class="mb-0 text-sm"><?= htmlspecialchars($rec['title']) ?></h6>
                            <p class="text-xs text-secondary mb-0"><?= htmlspecialchars($rec['position']) ?></p>
                          </div>
                        </td>
                        <td>
                          <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($rec['department'] ?? 'N/A') ?></p>
                        </td>
                        <td class="align-middle text-center">
                          <span class="text-xs font-weight-bold"><?= htmlspecialchars($rec['location'] ?? 'N/A') ?></span>
                        </td>
                        <td class="align-middle text-center">
                          <span class="text-xs font-weight-bold"><?= htmlspecialchars($salaryDisplay) ?></span>
                        </td>
                        <td class="align-middle text-center">
                          <span class="text-xs font-weight-bold"><?= $rec['quantity'] ?></span>
                        </td>
                        <td class="align-middle text-center">
                          <span class="text-xs font-weight-bold"><?= number_format($rec['views']) ?></span>
                        </td>
                        <td class="align-middle text-center">
                          <span class="badge badge-sm <?= $statusBg ?>"><?= htmlspecialchars($statusLabel) ?></span>
                        </td>
                        <td class="align-middle text-center">
                          <a href="recruitment_edit.php?id=<?= $rec['id'] ?>" class="btn btn-link text-primary px-2 mb-0" title="Sửa">
                            <i class="fas fa-edit text-sm"></i>
                          </a>
                          <a href="recruitment_delete.php?id=<?= $rec['id'] ?>" class="btn btn-link text-danger px-2 mb-0" 
                             onclick="return confirm('Bạn có chắc muốn xóa vị trí này?')" title="Xóa">
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

