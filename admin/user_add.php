<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Thêm Người dùng - Admin Dashboard";

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? 'customer');
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    
    if (empty($username) || empty($email) || empty($password)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc';
    } else {
        $conn = connectDB();
        
        // Kiểm tra username và email đã tồn tại
        $checkSql = "SELECT id FROM users WHERE username = ? OR email = ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ss", $username, $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $error = 'Username hoặc Email đã tồn tại';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            $insertSql = "INSERT INTO users (username, full_name, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $insertStmt = $conn->prepare($insertSql);
            $insertStmt->bind_param("ssssssi", $username, $fullName, $email, $phone, $hashedPassword, $role, $status);
            
            if ($insertStmt->execute()) {
                $success = true;
                header('Location: users.php?success=1');
                exit();
            } else {
                $error = 'Có lỗi xảy ra khi thêm người dùng';
            }
            $insertStmt->close();
        }
        $checkStmt->close();
        closeDB($conn);
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
  <?php $currentPage = 'users'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
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
                  <h6>Thêm Người dùng</h6>
                </div>
                <div class="col-lg-6 text-end">
                  <a href="users.php" class="btn bg-gradient-secondary btn-sm mb-0">Quay lại</a>
                </div>
              </div>
            </div>
            <div class="card-body">
              <form method="POST">
                <div class="row">
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Username *</label>
                      <input type="text" name="username" class="form-control" 
                             value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Họ và tên</label>
                      <input type="text" name="full_name" class="form-control" 
                             value="<?= isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : '' ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Email *</label>
                      <input type="email" name="email" class="form-control" 
                             value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Điện thoại</label>
                      <input type="tel" name="phone" class="form-control" 
                             value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Mật khẩu *</label>
                      <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Vai trò *</label>
                      <select name="role" class="form-select" required>
                        <option value="customer" <?= (isset($_POST['role']) && $_POST['role'] === 'customer') || !isset($_POST['role']) ? 'selected' : '' ?>>Khách hàng</option>
                        <option value="admin" <?= isset($_POST['role']) && $_POST['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Trạng thái</label>
                      <select name="status" class="form-select">
                        <option value="1" <?= (!isset($_POST['status']) || $_POST['status'] == 1) ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="0" <?= isset($_POST['status']) && $_POST['status'] == 0 ? 'selected' : '' ?>>Khóa</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div class="mt-4">
                  <button type="submit" name="add_user" class="btn bg-gradient-primary">Thêm người dùng</button>
                  <a href="users.php" class="btn bg-gradient-secondary">Hủy</a>
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
</body>
</html>

