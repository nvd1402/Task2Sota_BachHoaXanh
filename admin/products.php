<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Quản lý Sản phẩm - Admin Dashboard";

// Xử lý tìm kiếm và phân trang
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Lấy danh sách sản phẩm
$conn = connectDB();
$whereClause = '';
$params = [];
$types = '';

if (!empty($search)) {
    $whereClause = "WHERE name LIKE ? OR sku LIKE ? OR category LIKE ?";
    $searchTerm = "%{$search}%";
    $params = [$searchTerm, $searchTerm, $searchTerm];
    $types = 'sss';
}

// Đếm tổng số sản phẩm
$countSql = "SELECT COUNT(*) as total FROM products $whereClause";
$countStmt = $conn->prepare($countSql);
if (!empty($params)) {
    $countStmt->bind_param($types, ...$params);
}
$countStmt->execute();
$countResult = $countStmt->get_result();
$totalProducts = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $perPage);
$countStmt->close();

// Lấy danh sách sản phẩm với phân trang
$sql = "SELECT * FROM products $whereClause ORDER BY created_at DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types . 'ii', ...array_merge($params, [$perPage, $offset]));
} else {
    $stmt->bind_param('ii', $perPage, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();
closeDB($conn);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title><?= $pageTitle ?></title>
  <!-- Fonts and icons -->
  <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <!-- CSS Files -->
  <link id="pagestyle" href="../assets/css/soft-ui-dashboard.css?v=1.1.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
  <!-- Sidebar -->
  <aside class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3" id="sidenav-main">
    <div class="sidenav-header">
      <i class="fas fa-times p-3 cursor-pointer text-secondary opacity-5 position-absolute end-0 top-0 d-none d-xl-none" aria-hidden="true" id="iconSidenav"></i>
      <a class="navbar-brand m-0" href="index.php">
        <img src="../assets/img/logo-ct-dark.png" class="navbar-brand-img h-100" alt="main_logo">
        <span class="ms-1 font-weight-bold">Admin Dashboard</span>
      </a>
    </div>
    <hr class="horizontal dark mt-0">
    <div class="collapse navbar-collapse w-auto" id="sidenav-collapse-main">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="index.php">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <svg width="12px" height="12px" viewBox="0 0 45 40" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink">
                <title>shop</title>
                <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                  <g transform="translate(-1716.000000, -439.000000)" fill="#FFFFFF" fill-rule="nonzero">
                    <g transform="translate(1716.000000, 291.000000)">
                      <g transform="translate(0.000000, 148.000000)">
                        <path class="color-background opacity-6" d="M46.7199583,10.7414583 L40.8449583,0.949791667 C40.4909749,0.360605034 39.8540131,0 39.1666667,0 L7.83333333,0 C7.1459869,0 6.50902508,0.360605034 6.15504167,0.949791667 L0.280041667,10.7414583 C0.0969176761,11.0460037 -1.23209662e-05,11.3946378 -1.23209662e-05,11.75 C-0.00758042603,16.0663731 3.48367543,19.5725301 7.80004167,19.5833333 L7.81570833,19.5833333 C9.75003686,19.5882688 11.6168794,18.8726691 13.0522917,17.5760417 C16.0171492,20.2556967 20.5292675,20.2556967 23.494125,17.5760417 C26.4604562,20.2616016 30.9794188,20.2616016 33.94575,17.5760417 C36.2421905,19.6477597 39.5441143,20.1708521 42.3684437,18.9103691 C45.1927731,17.649886 47.0084685,14.8428276 47.0000295,11.75 C47.0000295,11.3946378 46.9030823,11.0460037 46.7199583,10.7414583 Z"></path>
                        <path class="color-background" d="M39.198,22.4912623 C37.3776246,22.4928106 35.5817531,22.0149171 33.951625,21.0951667 L33.92225,21.1107282 C31.1430221,22.6838032 27.9255001,22.9318916 24.9844167,21.7998837 C24.4750389,21.605469 23.9777983,21.3722567 23.4960833,21.1018359 L23.4745417,21.1129513 C20.6961809,22.6871153 17.4786145,22.9344611 14.5386667,21.7998837 C14.029926,21.6054643 13.533337,21.3722507 13.0522917,21.1018359 C11.4250962,22.0190609 9.63246555,22.4947009 7.81570833,22.4912623 C7.16510551,22.4842162 6.51607673,22.4173045 5.875,22.2911849 L5.875,44.7220845 C5.875,45.9498589 6.7517757,46.9451667 7.83333333,46.9451667 L19.5833333,46.9451667 L19.5833333,33.6066734 L27.4166667,33.6066734 L27.4166667,46.9451667 L39.1666667,46.9451667 C40.2482243,46.9451667 41.125,45.9498589 41.125,44.7220845 L41.125,22.2822926 C40.4887822,22.4116582 39.8442868,22.4815492 39.198,22.4912623 Z"></path>
                      </g>
                    </g>
                  </g>
                </g>
              </svg>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="products.php">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-box-2 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Sản phẩm</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="categories.php">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-tag text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Danh mục</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="orders.php">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-cart text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Đơn hàng</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="users.php">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-circle-08 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Người dùng</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="news.php">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-paper-diploma text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Tin tức</span>
          </a>
        </li>
        <li class="nav-item mt-3">
          <h6 class="ps-4 ms-2 text-uppercase text-xs font-weight-bolder opacity-6">Tài khoản</h6>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="profile.php">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-single-02 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Hồ sơ</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="logout.php">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-button-power text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Đăng xuất</span>
          </a>
        </li>
      </ul>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="index.php">Trang</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Sản phẩm</li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Quản lý sản phẩm</li>
          </ol>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <ul class="navbar-nav justify-content-end">
            <li class="nav-item d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body font-weight-bold px-0">
                <i class="fa fa-user me-sm-1"></i>
              </a>
            </li>
            <li class="nav-item d-xl-none ps-3 d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body p-0" id="iconNavbarSidenav">
                <div class="sidenav-toggler-inner">
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                  <i class="sidenav-toggler-line"></i>
                </div>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <!-- End Navbar -->

    <div class="container-fluid py-4">
      <!-- Thông báo thành công -->
      <?php if (isset($_GET['success'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> Sản phẩm đã được lưu thành công.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>
      
      <?php if (isset($_GET['deleted'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> Sản phẩm đã được xóa thành công.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>
      
      <?php if (isset($_GET['error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Lỗi!</strong> Đã xảy ra lỗi khi xử lý. Vui lòng thử lại.
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>
      
      <!-- Header với nút thêm mới -->
      <div class="row mb-4">
        <div class="col-lg-6 col-md-6 col-12">
          <h5 class="mb-0">Danh sách Sản phẩm</h5>
          <p class="text-sm text-muted mb-0">Tổng cộng: <?= number_format($totalProducts) ?> sản phẩm</p>
        </div>
        <div class="col-lg-6 col-md-6 col-12 text-end">
          <a href="product_add.php" class="btn btn-sm mb-0" style="background-color: #000; border-color: #000; color: white;">
            <i class="fas fa-plus me-2"></i>Thêm sản phẩm mới
          </a>
        </div>
      </div>

      <!-- Tìm kiếm -->
      <div class="row mb-4">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              <form method="GET" action="products.php" class="row g-3">
                <div class="col-md-10">
                  <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo tên, SKU, danh mục..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-md-2">
                  <button type="submit" class="btn w-100" style="background-color: #000; border-color: #000; color: white;">
                    <i class="fas fa-search me-2"></i>Tìm kiếm
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- Bảng danh sách sản phẩm -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-body px-0 pb-2">
              <div class="table-responsive">
                <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Ảnh</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Tên sản phẩm</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">SKU</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Giá</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tồn kho</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Danh mục</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Trạng thái</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Thao tác</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (empty($products)): ?>
                    <tr>
                      <td colspan="8" class="text-center py-4">
                        <p class="text-muted mb-0">Không có sản phẩm nào</p>
                      </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($products as $product): ?>
                    <tr>
                      <td>
                        <div class="d-flex px-2 py-1">
                          <div>
                            <?php if ($product['image']): ?>
                            <img src="../assets/images/<?= htmlspecialchars($product['image']) ?>" class="avatar avatar-sm me-3" alt="<?= htmlspecialchars($product['name']) ?>">
                            <?php else: ?>
                            <div class="avatar avatar-sm bg-gradient-secondary me-3">
                              <i class="ni ni-image text-white"></i>
                            </div>
                            <?php endif; ?>
                          </div>
                        </div>
                      </td>
                      <td>
                        <div class="d-flex flex-column">
                          <h6 class="mb-0 text-sm"><?= htmlspecialchars($product['name']) ?></h6>
                          <?php if ($product['short_description']): ?>
                          <p class="text-xs text-secondary mb-0"><?= htmlspecialchars(mb_substr($product['short_description'], 0, 50)) ?>...</p>
                          <?php endif; ?>
                        </div>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="text-xs font-weight-bold"><?= htmlspecialchars($product['sku'] ?? 'N/A') ?></span>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <?php if ($product['sale_price']): ?>
                        <span class="text-xs text-decoration-line-through text-secondary"><?= number_format($product['price'], 0, ',', '.') ?>₫</span><br>
                        <span class="text-xs font-weight-bold text-danger"><?= number_format($product['sale_price'], 0, ',', '.') ?>₫</span>
                        <?php else: ?>
                        <span class="text-xs font-weight-bold"><?= number_format($product['price'], 0, ',', '.') ?>₫</span>
                        <?php endif; ?>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="text-xs font-weight-bold <?= $product['stock'] > 0 ? 'text-success' : 'text-danger' ?>">
                          <?= number_format($product['stock']) ?>
                        </span>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <span class="text-xs font-weight-bold"><?= htmlspecialchars($product['category'] ?? 'N/A') ?></span>
                      </td>
                      <td class="align-middle">
                        <div class="d-flex flex-column">
                          <span class="text-xs font-weight-bold <?= $product['status'] === 'active' ? 'text-success' : 'text-secondary' ?>">
                            <?= $product['status'] === 'active' ? 'Hoạt động' : 'Ngừng bán' ?>
                          </span>
                          <?php if ($product['featured']): ?>
                          <span class="text-xs font-weight-bold text-info">Nổi bật</span>
                          <?php endif; ?>
                        </div>
                      </td>
                      <td class="align-middle text-center">
                        <div class="d-flex align-items-center justify-content-center gap-2">
                          <a href="product_edit.php?id=<?= $product['id'] ?>" class="btn btn-sm mb-0 px-3 py-2" data-bs-toggle="tooltip" title="Sửa" style="background-color: #000; border-color: #000; color: #fff; border-radius: 10px;">
                            <i class="fas fa-pen" style="font-size: 14px;"></i>
                            <span class="ms-1">Sửa</span>
                          </a>
                          <a href="product_delete.php?id=<?= $product['id'] ?>" class="btn btn-sm mb-0 px-3 py-2" onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này?')" data-bs-toggle="tooltip" title="Xóa" style="background-color: #000; border-color: #000; color: #fff; border-radius: 10px;">
                            <i class="fas fa-trash" style="font-size: 14px;"></i>
                            <span class="ms-1">Xóa</span>
                          </a>
                        </div>
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

      <!-- Phân trang -->
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

  <!-- Core JS Files -->
  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      var options = {
        damping: '0.5'
      }
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
    }
  </script>
  <!-- Control Center for Soft Dashboard -->
  <script src="../assets/js/soft-ui-dashboard.min.js?v=1.1.0"></script>
  <script>
    // Ẩn thông báo sau 4 giây
    setTimeout(function() {
      document.querySelectorAll('.alert').forEach(function(el) {
        try {
          var alert = new bootstrap.Alert(el);
          alert.close();
        } catch (e) {
          el.style.display = 'none';
        }
      });
    }, 4000);
  </script>
  <style>
    /* Đảm bảo icon thao tác luôn hiển thị rõ ràng */
    .table td a.btn i.fas {
      display: inline-block !important;
      opacity: 1 !important;
      visibility: visible !important;
      line-height: 1 !important;
    }
    .table td a.btn:hover i.fas {
      opacity: 0.9 !important;
    }
  </style>
</body>
</html>

