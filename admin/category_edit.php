<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

requireAdmin();

$pageTitle = "Sửa danh mục - Admin Dashboard";
$errors = [];

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: categories.php');
    exit;
}

$conn = connectDB();
$stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    $stmt->close();
    closeDB($conn);
    header('Location: categories.php');
    exit;
}
$category = $result->fetch_assoc();
$stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if ($name === '') {
        $errors[] = "Tên danh mục không được để trống";
    }
    if ($slug === '') {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }

    if (empty($errors)) {
        // Check slug trùng
        $check = $conn->prepare("SELECT id FROM categories WHERE slug = ? AND id != ?");
        $check->bind_param("si", $slug, $id);
        $check->execute();
        $res = $check->get_result();
        if ($res->num_rows > 0) {
            $slug = $slug . '-' . time();
        }
        $check->close();

        $upd = $conn->prepare("UPDATE categories SET name = ?, slug = ?, description = ?, status = ? WHERE id = ?");
        $upd->bind_param("ssssi", $name, $slug, $description, $status, $id);
        if ($upd->execute()) {
          $upd->close();
          closeDB($conn);
          header('Location: categories.php?success=1');
          exit;
        } else {
          $errors[] = "Lỗi khi cập nhật: " . $upd->error;
          $upd->close();
        }
    }
    // giữ lại giá trị mới
    $category = array_merge($category, [
        'name' => $name,
        'slug' => $slug,
        'description' => $description,
        'status' => $status
    ]);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="../assets/img/apple-icon.png">
  <link rel="icon" type="image/png" href="../assets/img/favicon.png">
  <title><?= $pageTitle ?></title>
  <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" rel="stylesheet" />
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link id="pagestyle" href="../assets/css/soft-ui-dashboard.css?v=1.1.0" rel="stylesheet" />
</head>
<body class="g-sidenav-show bg-gray-100">
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
              <i class="ni ni-shop text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Dashboard</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="products.php">
            <div class="icon icon-shape icon-sm shadow border-radius-md bg-white text-center me-2 d-flex align-items-center justify-content-center">
              <i class="ni ni-box-2 text-dark text-sm opacity-10"></i>
            </div>
            <span class="nav-link-text ms-1">Sản phẩm</span>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link active" href="categories.php">
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

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="index.php">Trang</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="categories.php">Danh mục</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Sửa</li>
          </ol>
        </nav>
        <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
          <ul class="navbar-nav justify-content-end">
            <li class="nav-item d-flex align-items-center">
              <a href="javascript:;" class="nav-link text-body font-weight-bold px-0">
                <i class="fa fa-user me-sm-1"></i>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>

    <div class="container-fluid py-4">
      <?php if (!empty($errors)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Lỗi!</strong>
        <ul class="mb-0">
          <?php foreach ($errors as $err): ?>
            <li><?= htmlspecialchars($err) ?></li>
          <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <a href="categories.php" class="btn btn-link text-dark px-3 mb-0">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
              </a>
            </div>
            <div class="card-body">
              <form method="POST">
                <div class="row">
                  <div class="col-md-8">
                    <div class="mb-3">
                      <label class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($category['name']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Slug (URL)</label>
                      <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($category['slug']) ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Mô tả</label>
                      <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($category['description']) ?></textarea>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label">Trạng thái</label>
                      <select name="status" class="form-control">
                        <option value="active" <?= $category['status']==='active'?'selected':'' ?>>Hoạt động</option>
                        <option value="inactive" <?= $category['status']==='inactive'?'selected':'' ?>>Ngừng dùng</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="row mt-4">
                  <div class="col-12">
                    <button type="submit" class="btn" style="background-color:#000;border-color:#000;color:#fff;">
                      <i class="fas fa-save me-2"></i>Cập nhật danh mục
                    </button>
                    <a href="categories.php" class="btn" style="background-color:#000;border-color:#000;color:#fff;">
                      <i class="fas fa-times me-2"></i>Hủy
                    </a>
                  </div>
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
  <script src="../assets/js/plugins/smooth-scrollbar.min.js"></script>
  <script>
    var win = navigator.platform.indexOf('Win') > -1;
    if (win && document.querySelector('#sidenav-scrollbar')) {
      Scrollbar.init(document.querySelector('#sidenav-scrollbar'), { damping: '0.5' });
    }
  </script>
  <script src="../assets/js/soft-ui-dashboard.min.js?v=1.1.0"></script>
</body>
</html>

