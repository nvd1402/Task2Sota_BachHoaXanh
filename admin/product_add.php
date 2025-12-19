<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Thêm sản phẩm mới - Admin Dashboard";

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $short_description = trim($_POST['short_description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $sale_price = !empty($_POST['sale_price']) ? floatval($_POST['sale_price']) : null;
    // Khoảng giá và trọng lượng / khuyến mãi
    $price_min = isset($_POST['price_min']) ? floatval($_POST['price_min']) : null;
    $price_max = isset($_POST['price_max']) ? floatval($_POST['price_max']) : null;
    $weight_options = trim($_POST['weight_options'] ?? '');
    $promo_heading = trim($_POST['promo_heading'] ?? '');
    $promo_content = trim($_POST['promo_content'] ?? '');
    $sku = trim($_POST['sku'] ?? '');
    $stock = intval($_POST['stock'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $status = $_POST['status'] ?? 'active';
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    // Validation
    if (empty($name)) {
        $errors[] = "Tên sản phẩm không được để trống";
    }
    
    if (empty($slug)) {
        // Tự động tạo slug từ tên
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }
    
    if ($price <= 0) {
        $errors[] = "Giá sản phẩm phải lớn hơn 0";
    }
    // Nếu nhập khoảng giá thì tối thiểu phải nhỏ hơn hoặc bằng tối đa
    if ($price_min !== null && $price_max !== null && $price_min > $price_max) {
        $errors[] = "Giá từ phải nhỏ hơn hoặc bằng giá đến";
    }
    
    // Xử lý upload ảnh
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/images/';
        $fileName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $fileName;
        
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                $image = $fileName;
            } else {
                $errors[] = "Lỗi khi upload ảnh";
            }
        } else {
            $errors[] = "Chỉ chấp nhận file ảnh: JPG, JPEG, PNG, GIF, WEBP";
        }
    }
    
    // Nếu không có lỗi, thêm vào database
    if (empty($errors)) {
        $conn = connectDB();
        
        // Kiểm tra slug đã tồn tại chưa
        $checkStmt = $conn->prepare("SELECT id FROM products WHERE slug = ?");
        $checkStmt->bind_param("s", $slug);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $slug = $slug . '-' . time();
        }
        $checkStmt->close();
        
        // Thêm sản phẩm
        $stmt = $conn->prepare("INSERT INTO products (name, slug, description, short_description, price, sale_price, price_min, price_max, weight_options, promo_heading, promo_content, sku, stock, category, image, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param(
          "ssssdddddssssiss",
          $name,
          $slug,
          $description,
          $short_description,
          $price,
          $sale_price,
          $price_min,
          $price_max,
          $weight_options,
          $promo_heading,
          $promo_content,
          $sku,
          $stock,
          $category,
          $image,
          $status,
          $featured
        );
        
        if ($stmt->execute()) {
            $success = true;
            header('Location: products.php?success=1');
            exit();
        } else {
            $errors[] = "Lỗi khi thêm sản phẩm: " . $stmt->error;
        }
        
        $stmt->close();
        closeDB($conn);
    }
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
            <li class="breadcrumb-item text-sm"><a class="opacity-5 text-dark" href="products.php">Sản phẩm</a></li>
            <li class="breadcrumb-item text-sm text-dark active" aria-current="page">Thêm mới</li>
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
      <!-- Thông báo lỗi -->
      <?php if (!empty($errors)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Lỗi!</strong>
        <ul class="mb-0">
          <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
      <?php endif; ?>

      <!-- Form thêm sản phẩm -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <a href="products.php" class="btn btn-link text-dark px-3 mb-0">
                  <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>
              </div>
            </div>
            <div class="card-body">
              <form method="POST" enctype="multipart/form-data">
                <div class="row">
                  <div class="col-md-8">
                    <div class="mb-3">
                      <label class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                      <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Slug (URL)</label>
                      <input type="text" name="slug" class="form-control" value="<?= htmlspecialchars($_POST['slug'] ?? '') ?>" placeholder="Tự động tạo từ tên sản phẩm">
                      <small class="text-muted">Để trống để tự động tạo từ tên sản phẩm</small>
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Mô tả ngắn</label>
                      <textarea name="short_description" class="form-control" rows="3"><?= htmlspecialchars($_POST['short_description'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Mô tả chi tiết</label>
                      <textarea name="description" class="form-control" rows="6"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                    </div>
                  </div>
                  
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label">Ảnh sản phẩm</label>
                      <input type="file" name="image" class="form-control" accept="image/*">
                      <small class="text-muted">Chấp nhận: JPG, PNG, GIF, WEBP</small>
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Giá cơ bản (dùng cho tính toán) <span class="text-danger">*</span></label>
                      <input type="number" name="price" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Giá hiển thị từ</label>
                      <input type="number" name="price_min" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($_POST['price_min'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Giá hiển thị đến</label>
                      <input type="number" name="price_max" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($_POST['price_max'] ?? '') ?>">
                      <small class="text-muted">Ví dụ: 90000 và 130000 để hiển thị 90,000đ – 130,000đ</small>
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Giá khuyến mãi</label>
                      <input type="number" name="sale_price" class="form-control" step="0.01" min="0" value="<?= htmlspecialchars($_POST['sale_price'] ?? '') ?>">
                      <small class="text-muted">Để trống nếu không có khuyến mãi</small>
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">SKU</label>
                      <input type="text" name="sku" class="form-control" value="<?= htmlspecialchars($_POST['sku'] ?? '') ?>">
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Số lượng tồn kho</label>
                      <input type="number" name="stock" class="form-control" min="0" value="<?= htmlspecialchars($_POST['stock'] ?? '0') ?>">
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Danh mục</label>
                      <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($_POST['category'] ?? '') ?>" placeholder="VD: Đồ uống, Bánh kẹo...">
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Trọng lượng / kích thước</label>
                      <input type="text" name="weight_options" class="form-control" value="<?= htmlspecialchars($_POST['weight_options'] ?? '') ?>" placeholder="VD: 1kg,2kg,3kg,4kg,5kg">
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Trạng thái</label>
                      <select name="status" class="form-control">
                        <option value="active" <?= (isset($_POST['status']) && $_POST['status'] === 'active') ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="inactive" <?= (isset($_POST['status']) && $_POST['status'] === 'inactive') ? 'selected' : '' ?>>Ngừng bán</option>
                      </select>
                    </div>
                    
                    <div class="mb-3">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="featured" id="featured" <?= (isset($_POST['featured']) && $_POST['featured']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="featured">Sản phẩm nổi bật</label>
                      </div>
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Tiêu đề khuyến mãi</label>
                      <input type="text" name="promo_heading" class="form-control" value="<?= htmlspecialchars($_POST['promo_heading'] ?? '') ?>" placeholder="VD: KHUYẾN MÃI TRỊ GIÁ 300.000₫">
                    </div>
                    
                    <div class="mb-3">
                      <label class="form-label">Nội dung khuyến mãi</label>
                      <textarea name="promo_content" class="form-control" rows="3" placeholder="Mỗi dòng một ưu đãi"><?= htmlspecialchars($_POST['promo_content'] ?? '') ?></textarea>
                    </div>
                  </div>
                </div>
                
                <div class="row mt-4">
                  <div class="col-12">
                    <button type="submit" class="btn" style="background-color: #000; border-color: #000; color: white;">
                      <i class="fas fa-save me-2"></i>Lưu sản phẩm
                    </button>
                    <a href="products.php" class="btn" style="background-color: #000; border-color: #000; color: white;">
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
    
    // Tự động tạo slug từ tên sản phẩm
    document.querySelector('input[name="name"]').addEventListener('input', function() {
      const slugInput = document.querySelector('input[name="slug"]');
      if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
        const slug = this.value.toLowerCase()
          .normalize('NFD')
          .replace(/[\u0300-\u036f]/g, '')
          .replace(/[^a-z0-9]+/g, '-')
          .replace(/^-+|-+$/g, '');
        slugInput.value = slug;
        slugInput.dataset.autoGenerated = 'true';
      }
    });
    
    document.querySelector('input[name="slug"]').addEventListener('input', function() {
      this.dataset.autoGenerated = 'false';
    });
  </script>
  <!-- Control Center for Soft Dashboard -->
  <script src="../assets/js/soft-ui-dashboard.min.js?v=1.1.0"></script>
</body>
</html>

