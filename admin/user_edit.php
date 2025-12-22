<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Sửa Người dùng - Admin Dashboard";

// Lấy ID người dùng
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($user_id <= 0) {
    header('Location: users.php');
    exit();
}

// Kết nối database
$conn = connectDB();

// Lấy thông tin người dùng
$user = null;
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
}

$stmt->close();

if (!$user) {
    closeDB($conn);
    header('Location: users.php');
    exit();
}

// Xử lý cập nhật
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? 'customer');
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;
    
    if (empty($username) || empty($email)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc';
    } else {
        // Kiểm tra username và email đã tồn tại (trừ chính user này)
        $checkSql = "SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ssi", $username, $email, $user_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $error = 'Username hoặc Email đã tồn tại';
        } else {
            if (!empty($password)) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $updateSql = "UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, password = ?, role = ?, status = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("ssssssii", $username, $fullName, $email, $phone, $hashedPassword, $role, $status, $user_id);
            } else {
                $updateSql = "UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, role = ?, status = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("sssssii", $username, $fullName, $email, $phone, $role, $status, $user_id);
            }
            
            if ($updateStmt->execute()) {
                $success = true;
                // Reload user data
                $sql = "SELECT * FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();
            } else {
                $error = 'Có lỗi xảy ra khi cập nhật';
            }
            $updateStmt->close();
        }
        $checkStmt->close();
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
</head>

<body class="g-sidenav-show bg-gray-100">
  <?php $currentPage = 'users'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
      <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> Đã cập nhật người dùng.
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
                  <h6>Sửa Người dùng</h6>
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
                             value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Họ và tên</label>
                      <input type="text" name="full_name" class="form-control" 
                             value="<?= htmlspecialchars($user['full_name'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Email *</label>
                      <input type="email" name="email" class="form-control" 
                             value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Điện thoại</label>
                      <input type="tel" name="phone" class="form-control" 
                             value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="mb-3">
                      <label class="form-label">Mật khẩu mới</label>
                      <input type="password" name="password" class="form-control" 
                             placeholder="Để trống nếu không đổi mật khẩu">
                      <small class="text-muted">Chỉ nhập nếu muốn thay đổi mật khẩu</small>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Vai trò *</label>
                      <select name="role" class="form-select" required>
                        <option value="customer" <?= $user['role'] === 'customer' ? 'selected' : '' ?>>Khách hàng</option>
                        <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Trạng thái</label>
                      <select name="status" class="form-select">
                        <option value="1" <?= $user['status'] == 1 ? 'selected' : '' ?>>Hoạt động</option>
                        <option value="0" <?= $user['status'] == 0 ? 'selected' : '' ?>>Khóa</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <p class="text-sm text-secondary">
                        <strong>Ngày tạo:</strong> <?= date('d/m/Y H:i', strtotime($user['created_at'])) ?><br>
                        <strong>Cập nhật:</strong> <?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?>
                      </p>
                    </div>
                  </div>
                </div>
                <div class="mt-4">
                  <button type="submit" name="update_user" class="btn bg-gradient-primary">Cập nhật</button>
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

