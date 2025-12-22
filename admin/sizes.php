<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

requireAdmin();

$pageTitle = "Quản lý Kích thước - Admin Dashboard";

// Lọc, phân trang
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page   = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$conn = connectDB();

$where = '';
$params = [];
$types  = '';
if ($search !== '') {
    $where = "WHERE name LIKE ? OR slug LIKE ?";
    $term = "%{$search}%";
    $params = [$term, $term];
    $types  = 'ss';
}

// Tổng số
$countSql = "SELECT COUNT(*) AS total FROM sizes $where";
$countStmt = $conn->prepare($countSql);
if ($params) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$totalSizes = $countStmt->get_result()->fetch_assoc()['total'] ?? 0;
$countStmt->close();

$totalPages = max(1, ceil($totalSizes / $perPage));

// Danh sách
$sql = "SELECT * FROM sizes $where ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types . 'ii', ...array_merge($params, [$perPage, $offset]));
} else {
    $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$sizes = [];
while ($row = $result->fetch_assoc()) {
    $sizes[] = $row;
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
  <?php $currentPage = 'sizes'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="index.php">Trang</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Kích thước</li>
          </ol>
        </nav>
      </div>
    </nav>

    <div class="container-fluid py-4">
      <div class="row mb-4">
        <div class="col-lg-6 col-md-6 col-12">
          <h5 class="mb-0">Danh sách Kích thước</h5>
          <p class="text-sm text-muted mb-0">Tổng cộng: <?= number_format($totalSizes) ?> kích thước</p>
        </div>
        <div class="col-lg-6 col-md-6 col-12 text-end">
          <a href="size_add.php" class="btn btn-sm mb-0" style="background-color:#000;border-color:#000;color:#fff;">
            <i class="fas fa-plus me-2"></i>Thêm kích thước mới
          </a>
        </div>
      </div>

      <div class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <form method="GET" action="sizes.php" class="row g-3">
                <div class="col-md-10">
                  <input type="text" name="search" class="form-control" placeholder="Tìm theo tên hoặc slug..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                  <button type="submit" class="btn w-100" style="background-color:#000;border-color:#000;color:#fff;">
                    <i class="fas fa-search me-2"></i>Tìm kiếm
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-body px-0 pb-2">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tên kích thước</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Slug</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Mô tả</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng thái</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($sizes)): ?>
                    <tr>
                      <td colspan="5" class="text-center py-4">
                        <p class="text-muted mb-0">Chưa có kích thước nào</p>
                      </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($sizes as $size): ?>
                    <tr>
                      <td>
                        <h6 class="mb-0 text-sm"><?= htmlspecialchars($size['name']) ?></h6>
                      </td>
                      <td>
                        <p class="text-xs font-weight-bold mb-0"><?= htmlspecialchars($size['slug']) ?></p>
                      </td>
                      <td>
                        <p class="text-xs mb-0 text-secondary">
                          <?= htmlspecialchars(mb_substr($size['description'] ?? '', 0, 60)) ?><?= strlen($size['description'] ?? '') > 60 ? '...' : '' ?>
                        </p>
                      </td>
                      <td class="align-middle">
                        <span class="text-xs font-weight-bold <?= $size['status'] === 'active' ? 'text-success' : 'text-secondary' ?>">
                          <?= $size['status'] === 'active' ? 'Hoạt động' : 'Ngừng dùng' ?>
                        </span>
                      </td>
                      <td class="align-middle text-center">
                        <a href="size_edit.php?id=<?= $size['id'] ?>"
                           class="text-xs font-weight-bold"
                           style="color:#3da04d; text-decoration:none; margin-right:12px;">
                          Sửa
                        </a>
                        <a href="size_delete.php?id=<?= $size['id'] ?>"
                           class="text-xs font-weight-bold"
                           style="color:#e53935; text-decoration:none;"
                           onclick="return confirm('Xóa kích thước này? Các sản phẩm đang dùng kích thước này sẽ không bị xóa.')">
                          Xóa
                        </a>
                      </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <?php if ($totalPages > 1): ?>
      <div class="row mt-4">
        <div class="col-12">
          <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
              <?php if ($page > 1): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $page - 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">Trước</a>
              </li>
              <?php endif; ?>
              <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
              <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?><?= $search ? '&search=' . urlencode($search) : '' ?>"><?= $i ?></a>
              </li>
              <?php endfor; ?>
              <?php if ($page < $totalPages): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $page + 1 ?><?= $search ? '&search=' . urlencode($search) : '' ?>">Sau</a>
              </li>
              <?php endif; ?>
            </ul>
          </nav>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </main>

  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script src="../assets/js/soft-ui-dashboard.min.js?v=1.1.0"></script>
</body>
</html>

