<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

requireAdmin();

$pageTitle = "Thêm thương hiệu - Admin Dashboard";
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $status = $_POST['status'] ?? 'active';

    if ($name === '') {
        $errors[] = "Tên thương hiệu không được để trống";
    }
    if ($slug === '') {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }

    if (empty($errors)) {
        $conn = connectDB();
        $check = $conn->prepare("SELECT id FROM brands WHERE slug = ?");
        $check->bind_param("s", $slug);
        $check->execute();
        $res = $check->get_result();
        if ($res->num_rows > 0) {
            $slug = $slug . '-' . time();
        }
        $check->close();

        $stmt = $conn->prepare("INSERT INTO brands (name, slug, description, status) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $slug, $description, $status);
        if ($stmt->execute()) {
            $stmt->close();
            closeDB($conn);
            header('Location: brands.php?success=1');
            exit;
        } else {
            $errors[] = "Lỗi khi thêm thương hiệu: " . $stmt->error;
            $stmt->close();
            closeDB($conn);
        }
    }
}
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
  <?php $currentPage = 'brands'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" navbar-scroll="true">
      <div class="container-fluid py-1 px-3">
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0 me-sm-6 me-5">
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="index.php">Trang</a></li>
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="brands.php">Thương hiệu</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Thêm mới</li>
          </ol>
        </nav>
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
              <a href="brands.php" class="btn btn-link text-dark px-3 mb-0">
                <i class="fas fa-arrow-left me-2"></i>Quay lại
              </a>
            </div>
            <div class="card-body">
              <form method="POST">
                <div class="row">
                  <div class="col-md-8">
                    <div class="mb-3">
                      <label class="form-label">Tên thương hiệu <span class="text-danger">*</span></label>
                      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Slug (URL)</label>
                      <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>" placeholder="Tự động tạo từ tên">
                      <small class="text-muted">Để trống để tự tạo từ tên</small>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Mô tả</label>
                      <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label">Trạng thái</label>
                      <select name="status" class="form-control">
                        <option value="active" <?= (($_POST['status'] ?? '') === 'active') ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="inactive" <?= (($_POST['status'] ?? '') === 'inactive') ? 'selected' : '' ?>>Ngừng dùng</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="row mt-4">
                  <div class="col-12">
                    <button type="submit" class="btn" style="background-color:#000;border-color:#000;color:#fff;">
                      <i class="fas fa-save me-2"></i>Lưu thương hiệu
                    </button>
                    <a href="brands.php" class="btn" style="background-color:#000;border-color:#000;color:#fff;">
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
  <script src="../assets/js/soft-ui-dashboard.min.js?v=1.1.0"></script>
</body>
</html>

