<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Sửa Tuyển dụng - Admin Dashboard";

// Lấy ID tuyển dụng
$recruitment_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($recruitment_id <= 0) {
    header('Location: recruitment.php');
    exit();
}

// Kết nối database
$conn = connectDB();

// Lấy thông tin tuyển dụng
$recruitment = null;
$sql = "SELECT * FROM recruitment WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $recruitment_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $recruitment = $result->fetch_assoc();
}

$stmt->close();

if (!$recruitment) {
    closeDB($conn);
    header('Location: recruitment.php');
    exit();
}

// Xử lý cập nhật
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_recruitment'])) {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $position = trim($_POST['position'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $employmentType = trim($_POST['employment_type'] ?? '');
    $salaryMin = !empty($_POST['salary_min']) ? floatval($_POST['salary_min']) : null;
    $salaryMax = !empty($_POST['salary_max']) ? floatval($_POST['salary_max']) : null;
    $salaryDisplay = trim($_POST['salary_display'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $benefits = trim($_POST['benefits'] ?? '');
    $deadline = !empty($_POST['deadline']) ? trim($_POST['deadline']) : null;
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $status = trim($_POST['status'] ?? 'draft');
    
    // Validation
    if (empty($title)) {
        $errors[] = "Tiêu đề không được để trống";
    }
    
    if (empty($position)) {
        $errors[] = "Vị trí công việc không được để trống";
    }
    
    if (empty($description)) {
        $errors[] = "Mô tả công việc không được để trống";
    }
    
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = preg_replace('/-+/', '-', $slug);
    }
    
    // Nếu không có lỗi, cập nhật
    if (empty($errors)) {
        // Kiểm tra slug đã tồn tại chưa (trừ chính tuyển dụng này)
        $checkStmt = $conn->prepare("SELECT id FROM recruitment WHERE slug = ? AND id != ?");
        $checkStmt->bind_param("si", $slug, $recruitment_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $slug = $slug . '-' . time();
        }
        $checkStmt->close();
        
        // Cập nhật
        $updateSql = "UPDATE recruitment SET title = ?, slug = ?, position = ?, department = ?, location = ?, employment_type = ?, salary_min = ?, salary_max = ?, salary_display = ?, description = ?, requirements = ?, benefits = ?, deadline = ?, quantity = ?, status = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        
        $deadlineFormatted = $deadline ? date('Y-m-d', strtotime($deadline)) : null;
        
        $updateStmt->bind_param("ssssssddsssssisi", 
            $title, $slug, $position, $department, $location, $employmentType,
            $salaryMin, $salaryMax, $salaryDisplay, $description, $requirements, $benefits,
            $deadlineFormatted, $quantity, $status, $recruitment_id
        );
        
        if ($updateStmt->execute()) {
            $success = true;
            // Reload recruitment data
            $sql = "SELECT * FROM recruitment WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $recruitment_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $recruitment = $result->fetch_assoc();
            $stmt->close();
        } else {
            $errors[] = "Có lỗi xảy ra khi cập nhật";
        }
        $updateStmt->close();
    }
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
  <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
</head>

<body class="g-sidenav-show bg-gray-100">
  <?php $currentPage = 'recruitment'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
      <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> Đã cập nhật tuyển dụng.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>
      
      <?php if (!empty($errors)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Lỗi!</strong>
        <ul class="mb-0">
          <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <div class="row">
                <div class="col-lg-6">
                  <h6>Sửa Tuyển dụng</h6>
                </div>
                <div class="col-lg-6 text-end">
                  <a href="recruitment.php" class="btn bg-gradient-secondary btn-sm mb-0">Quay lại</a>
                </div>
              </div>
            </div>
            <div class="card-body">
              <form method="POST">
                <div class="row">
                  <div class="col-md-8">
                    <div class="mb-3">
                      <label class="form-label">Tiêu đề *</label>
                      <input type="text" name="title" class="form-control" 
                             value="<?= htmlspecialchars($recruitment['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Slug</label>
                      <input type="text" name="slug" class="form-control" 
                             value="<?= htmlspecialchars($recruitment['slug']) ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Vị trí công việc *</label>
                      <input type="text" name="position" class="form-control" 
                             value="<?= htmlspecialchars($recruitment['position']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Mô tả công việc *</label>
                      <textarea name="description" id="description" class="form-control" rows="8" required><?= htmlspecialchars($recruitment['description']) ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Yêu cầu</label>
                      <textarea name="requirements" id="requirements" class="form-control" rows="6"><?= htmlspecialchars($recruitment['requirements'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Quyền lợi</label>
                      <textarea name="benefits" id="benefits" class="form-control" rows="6"><?= htmlspecialchars($recruitment['benefits'] ?? '') ?></textarea>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label">Phòng ban</label>
                      <input type="text" name="department" class="form-control" 
                             value="<?= htmlspecialchars($recruitment['department'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Địa điểm làm việc</label>
                      <input type="text" name="location" class="form-control" 
                             value="<?= htmlspecialchars($recruitment['location'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Loại hình</label>
                      <select name="employment_type" class="form-select">
                        <option value="">Chọn loại hình</option>
                        <option value="fulltime" <?= $recruitment['employment_type'] === 'fulltime' ? 'selected' : '' ?>>Full-time</option>
                        <option value="parttime" <?= $recruitment['employment_type'] === 'parttime' ? 'selected' : '' ?>>Part-time</option>
                        <option value="contract" <?= $recruitment['employment_type'] === 'contract' ? 'selected' : '' ?>>Hợp đồng</option>
                      </select>
                    </div>
                    <div class="row">
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Lương tối thiểu (₫)</label>
                        <input type="number" name="salary_min" class="form-control" step="0.01" 
                               value="<?= $recruitment['salary_min'] ?>">
                      </div>
                      <div class="col-md-6 mb-3">
                        <label class="form-label">Lương tối đa (₫)</label>
                        <input type="number" name="salary_max" class="form-control" step="0.01" 
                               value="<?= $recruitment['salary_max'] ?>">
                      </div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Hiển thị lương</label>
                      <input type="text" name="salary_display" class="form-control" 
                             value="<?= htmlspecialchars($recruitment['salary_display'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Số lượng cần tuyển</label>
                      <input type="number" name="quantity" class="form-control" min="1" 
                             value="<?= $recruitment['quantity'] ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Hạn nộp hồ sơ</label>
                      <input type="date" name="deadline" class="form-control" 
                             value="<?= $recruitment['deadline'] ? date('Y-m-d', strtotime($recruitment['deadline'])) : '' ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Trạng thái *</label>
                      <select name="status" class="form-select" required>
                        <option value="draft" <?= $recruitment['status'] === 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                        <option value="open" <?= $recruitment['status'] === 'open' ? 'selected' : '' ?>>Đang tuyển</option>
                        <option value="closed" <?= $recruitment['status'] === 'closed' ? 'selected' : '' ?>>Đã đóng</option>
                        <option value="filled" <?= $recruitment['status'] === 'filled' ? 'selected' : '' ?>>Đã đủ</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <p class="text-sm text-secondary">
                        <strong>Lượt xem:</strong> <?= number_format($recruitment['views']) ?><br>
                        <strong>Ngày tạo:</strong> <?= date('d/m/Y H:i', strtotime($recruitment['created_at'])) ?><br>
                        <strong>Cập nhật:</strong> <?= date('d/m/Y H:i', strtotime($recruitment['updated_at'])) ?>
                      </p>
                    </div>
                  </div>
                </div>
                <div class="mt-4">
                  <button type="submit" name="update_recruitment" class="btn bg-gradient-primary">Cập nhật</button>
                  <a href="recruitment.php" class="btn bg-gradient-secondary">Hủy</a>
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
  <script src="../assets/js/soft-ui-dashboard.min.js?v=1.1.0"></script>
  <script>
    CKEDITOR.replace('description');
    CKEDITOR.replace('requirements');
    CKEDITOR.replace('benefits');
  </script>
</body>
</html>

