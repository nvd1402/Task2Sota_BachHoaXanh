<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Quản lý Người dùng - Admin Dashboard";

// Xử lý tìm kiếm, filter và phân trang
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$role = isset($_GET['role']) ? trim($_GET['role']) : '';
$status = isset($_GET['status']) ? trim($_GET['status']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 15;
$offset = ($page - 1) * $perPage;

// Lấy danh sách người dùng
$conn = connectDB();
$where = [];
$params = [];
$types = '';

if (!empty($search)) {
    $where[] = "(username LIKE ? OR full_name LIKE ? OR email LIKE ? OR phone LIKE ?)";
    $searchTerm = "%{$search}%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    $types .= 'ssss';
}

if (!empty($role)) {
    $where[] = "role = ?";
    $params[] = $role;
    $types .= 's';
}

if ($status !== '') {
    $where[] = "status = ?";
    $params[] = (int)$status;
    $types .= 'i';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Đếm tổng số
$countSql = "SELECT COUNT(*) as total FROM users $whereClause";
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalUsers = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalUsers / $perPage);
$countStmt->close();

// Lấy danh sách với phân trang
$sql = "SELECT * FROM users $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types . 'ii', ...array_merge($params, [$perPage, $offset]));
} else {
    $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
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
  <?php $currentPage = 'users'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
      <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> Đã cập nhật người dùng.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>
      
      <?php if (isset($_GET['deleted'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> Đã xóa người dùng.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <div class="row">
                <div class="col-lg-6 col-7">
                  <h6>Quản lý Người dùng</h6>
                  <p class="text-sm mb-0">
                    <i class="fa fa-check text-info" aria-hidden="true"></i>
                    <span class="font-weight-bold ms-1"><?= $totalUsers ?> người dùng</span>
                  </p>
                </div>
                <div class="col-lg-6 col-5 my-auto text-end">
                  <a href="user_add.php" class="btn bg-gradient-primary btn-sm mb-0">
                    <i class="fas fa-plus"></i> Thêm người dùng
                  </a>
                </div>
              </div>
            </div>
            <div class="card-body px-0 pb-2">
              <!-- Filter và Search -->
              <div class="px-4 pb-3">
                <form method="GET" class="row g-3">
                  <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Tìm kiếm..." 
                           value="<?= htmlspecialchars($search) ?>">
                  </div>
                  <div class="col-md-2">
                    <select name="role" class="form-select">
                      <option value="">Tất cả vai trò</option>
                      <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                      <option value="customer" <?= $role === 'customer' ? 'selected' : '' ?>>Khách hàng</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <select name="status" class="form-select">
                      <option value="">Tất cả trạng thái</option>
                      <option value="1" <?= $status === '1' ? 'selected' : '' ?>>Hoạt động</option>
                      <option value="0" <?= $status === '0' ? 'selected' : '' ?>>Khóa</option>
                    </select>
                  </div>
                  <div class="col-md-2">
                    <button type="submit" class="btn bg-gradient-info btn-sm w-100">Tìm kiếm</button>
                  </div>
                  <div class="col-md-3">
                    <a href="users.php" class="btn bg-gradient-secondary btn-sm w-100">Reset</a>
                  </div>
                </form>
              </div>

              <div class="table-responsive p-0">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tên</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Email</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Điện thoại</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Vai trò</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng thái</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ngày tạo</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($users)): ?>
                      <tr>
                        <td colspan="7" class="text-center py-4">Không có người dùng nào</td>
                      </tr>
                    <?php else: ?>
                      <?php foreach ($users as $user): ?>
                      <tr>
                        <td>
                          <div class="d-flex px-2 py-1">
                            <div class="d-flex flex-column justify-content-center">
                              <h6 class="mb-0 text-sm"><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></h6>
                              <p class="text-xs text-secondary mb-0">@<?= htmlspecialchars($user['username']) ?></p>
                            </div>
                          </div>
                        </td>
                        <td>
                          <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($user['email']) ?></p>
                        </td>
                        <td class="align-middle text-center">
                          <span class="text-xs font-weight-bold"><?= htmlspecialchars($user['phone'] ?? 'N/A') ?></span>
                        </td>
                        <td class="align-middle text-center">
                          <span class="badge badge-sm <?= $user['role'] === 'admin' ? 'bg-gradient-danger' : 'bg-gradient-info' ?>">
                            <?= $user['role'] === 'admin' ? 'Admin' : 'Khách hàng' ?>
                          </span>
                        </td>
                        <td class="align-middle text-center">
                          <span class="badge badge-sm <?= $user['status'] == 1 ? 'bg-gradient-success' : 'bg-gradient-secondary' ?>">
                            <?= $user['status'] == 1 ? 'Hoạt động' : 'Khóa' ?>
                          </span>
                        </td>
                        <td class="align-middle text-center">
                          <span class="text-secondary text-xs font-weight-bold"><?= date('d/m/Y', strtotime($user['created_at'])) ?></span>
                        </td>
                        <td class="align-middle text-center">
                          <a href="user_edit.php?id=<?= $user['id'] ?>" class="btn btn-link text-primary px-2 mb-0" title="Sửa">
                            <i class="fas fa-edit text-sm"></i>
                          </a>
                          <?php if ($user['id'] != $_SESSION['user_id']): ?>
                          <a href="user_delete.php?id=<?= $user['id'] ?>" class="btn btn-link text-danger px-2 mb-0" 
                             onclick="return confirm('Bạn có chắc muốn xóa người dùng này?')" title="Xóa">
                            <i class="fas fa-trash text-sm"></i>
                          </a>
                          <?php endif; ?>
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
                        <a class="page-link" href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $role ? '&role=' . urlencode($role) : '' ?><?= $status !== '' ? '&status=' . urlencode($status) : '' ?>">Trước</a>
                      </li>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                      <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                        <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                          <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $role ? '&role=' . urlencode($role) : '' ?><?= $status !== '' ? '&status=' . urlencode($status) : '' ?>"><?= $i ?></a>
                        </li>
                      <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                        <li class="page-item disabled">
                          <span class="page-link">...</span>
                        </li>
                      <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                      <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?><?= $role ? '&role=' . urlencode($role) : '' ?><?= $status !== '' ? '&status=' . urlencode($status) : '' ?>">Sau</a>
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

