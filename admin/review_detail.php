<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Chi tiết Đánh giá - Admin Dashboard";

// Lấy ID đánh giá
$review_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($review_id <= 0) {
    header('Location: reviews.php');
    exit();
}

// Kết nối database
$conn = connectDB();

// Lấy thông tin đánh giá
$review = null;
$sql = "SELECT r.*, p.name as product_name, p.image as product_image 
        FROM reviews r 
        LEFT JOIN products p ON r.product_id = p.id 
        WHERE r.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $review_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $review = $result->fetch_assoc();
}

$stmt->close();

if (!$review) {
    closeDB($conn);
    header('Location: reviews.php');
    exit();
}

// Xử lý cập nhật trạng thái
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_review'])) {
    $newStatus = trim($_POST['status'] ?? '');
    
    $updateSql = "UPDATE reviews SET status = ? WHERE id = ?";
    $updateStmt = $conn->prepare($updateSql);
    $updateStmt->bind_param("si", $newStatus, $review_id);
    
    if ($updateStmt->execute()) {
        $success = true;
        $review['status'] = $newStatus;
    } else {
        $error = 'Có lỗi xảy ra khi cập nhật';
    }
    $updateStmt->close();
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
  <?php $currentPage = 'reviews'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
      <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> Đã cập nhật trạng thái đánh giá.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>
      
      <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Lỗi!</strong> <?= htmlspecialchars($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <div class="row">
                <div class="col-lg-6">
                  <h6>Chi tiết Đánh giá</h6>
                </div>
                <div class="col-lg-6 text-end">
                  <a href="reviews.php" class="btn bg-gradient-secondary btn-sm mb-0">Quay lại</a>
                </div>
              </div>
            </div>
            <div class="card-body">
              <form method="POST">
                <div class="row">
                  <div class="col-md-6">
                    <h6 class="mb-3">Thông tin sản phẩm</h6>
                    <div class="d-flex align-items-center mb-3">
                      <?php if (!empty($review['product_image'])): ?>
                      <img src="../assets/images/<?= htmlspecialchars($review['product_image']) ?>" 
                           class="avatar avatar-lg me-3" alt="<?= htmlspecialchars($review['product_name'] ?? 'N/A') ?>">
                      <?php endif; ?>
                      <div>
                        <h6 class="mb-0"><?= htmlspecialchars($review['product_name'] ?? 'N/A') ?></h6>
                        <a href="../product-detail.php?id=<?= $review['product_id'] ?>" class="text-sm text-primary" target="_blank">Xem sản phẩm</a>
                      </div>
                    </div>
                    
                    <h6 class="mb-3 mt-4">Thông tin khách hàng</h6>
                    <p><strong>Tên:</strong> <?= htmlspecialchars($review['customer_name']) ?></p>
                    <?php if (!empty($review['customer_email'])): ?>
                    <p><strong>Email:</strong> <?= htmlspecialchars($review['customer_email']) ?></p>
                    <?php endif; ?>
                    <p><strong>Ngày đánh giá:</strong> <?= date('d/m/Y H:i:s', strtotime($review['created_at'])) ?></p>
                  </div>
                  <div class="col-md-6">
                    <h6 class="mb-3">Quản lý</h6>
                    <div class="mb-3">
                      <label class="form-label">Trạng thái</label>
                      <select name="status" class="form-select">
                        <option value="pending" <?= $review['status'] === 'pending' ? 'selected' : '' ?>>Chờ duyệt</option>
                        <option value="approved" <?= $review['status'] === 'approved' ? 'selected' : '' ?>>Đã duyệt</option>
                        <option value="rejected" <?= $review['status'] === 'rejected' ? 'selected' : '' ?>>Từ chối</option>
                      </select>
                    </div>
                    <button type="submit" name="update_review" class="btn bg-gradient-primary btn-sm">Cập nhật trạng thái</button>
                  </div>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                  <h6>Đánh giá</h6>
                  <div class="d-flex align-items-center mb-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                      <i class="fas fa-star fa-lg <?= $i <= $review['rating'] ? 'text-warning' : 'text-secondary' ?> me-1"></i>
                    <?php endfor; ?>
                    <span class="ms-2"><strong><?= $review['rating'] ?>/5</strong></span>
                  </div>
                </div>

                <div class="mb-3">
                  <h6>Bình luận</h6>
                  <div class="border rounded p-3 bg-gray-100">
                    <?= nl2br(htmlspecialchars($review['comment'] ?? 'Không có bình luận')) ?>
                  </div>
                </div>

                <?php if (!empty($review['images'])): ?>
                <div class="mb-3">
                  <h6>Hình ảnh đính kèm</h6>
                  <div class="row">
                    <?php 
                    $images = json_decode($review['images'], true);
                    if (is_array($images)):
                      foreach ($images as $image): 
                    ?>
                    <div class="col-md-3 mb-3">
                      <img src="../assets/images/<?= htmlspecialchars($image) ?>" class="img-thumbnail" style="max-width: 100%;">
                    </div>
                    <?php 
                      endforeach;
                    endif;
                    ?>
                  </div>
                </div>
                <?php endif; ?>
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
  <script src="../assets/js/soft-ui-dashboard.min.js?v=1.1.0"></script>
</body>
</html>

