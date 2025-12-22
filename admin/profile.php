<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Hồ sơ - Admin Dashboard";

// Kết nối database
$conn = connectDB();

// Lấy thông tin user hiện tại
$user_id = $_SESSION['user_id'];
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
    header('Location: index.php');
    exit();
}

// Xử lý cập nhật
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $username = trim($_POST['username'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $currentPassword = trim($_POST['current_password'] ?? '');
    
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
            // Nếu đổi mật khẩu, kiểm tra mật khẩu hiện tại
            if (!empty($password)) {
                if (empty($currentPassword)) {
                    $error = 'Vui lòng nhập mật khẩu hiện tại';
                } elseif (!password_verify($currentPassword, $user['password'])) {
                    $error = 'Mật khẩu hiện tại không đúng';
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $updateSql = "UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?";
                    $updateStmt = $conn->prepare($updateSql);
                    $updateStmt->bind_param("ssssssi", $username, $fullName, $email, $phone, $address, $hashedPassword, $user_id);
                }
            } else {
                $updateSql = "UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("sssssi", $username, $fullName, $email, $phone, $address, $user_id);
            }
            
            if (isset($updateStmt) && $updateStmt->execute()) {
                $success = true;
                // Reload user data
                $sql = "SELECT * FROM users WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();
                
                // Cập nhật session
                $_SESSION['user_name'] = $user['full_name'] ?? $user['username'];
                $_SESSION['user_email'] = $user['email'];
            } else {
                $error = 'Có lỗi xảy ra khi cập nhật';
            }
            if (isset($updateStmt)) {
                $updateStmt->close();
            }
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
  <?php $currentPage = 'profile'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
      <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> Đã cập nhật hồ sơ.
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
              <h6>Hồ sơ của tôi</h6>
            </div>
            <div class="card-body">
              <form method="POST">
                <div class="row">
                  <div class="col-md-6">
                    <h6 class="mb-3">Thông tin cá nhân</h6>
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
                    <div class="mb-3">
                      <label class="form-label">Địa chỉ</label>
                      <textarea name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <h6 class="mb-3">Thông tin tài khoản</h6>
                    <div class="mb-3">
                      <label class="form-label">Vai trò</label>
                      <input type="text" class="form-control" 
                             value="<?= $user['role'] === 'admin' ? 'Admin' : 'Khách hàng' ?>" readonly>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Trạng thái</label>
                      <input type="text" class="form-control" 
                             value="<?= $user['status'] == 1 ? 'Hoạt động' : 'Khóa' ?>" readonly>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Ngày tạo</label>
                      <input type="text" class="form-control" 
                             value="<?= date('d/m/Y H:i', strtotime($user['created_at'])) ?>" readonly>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Cập nhật lần cuối</label>
                      <input type="text" class="form-control" 
                             value="<?= date('d/m/Y H:i', strtotime($user['updated_at'])) ?>" readonly>
                    </div>
                    
                    <hr class="my-4">
                    
                    <h6 class="mb-3">Đổi mật khẩu</h6>
                    <div class="mb-3">
                      <label class="form-label">Mật khẩu hiện tại</label>
                      <input type="password" name="current_password" class="form-control" 
                             placeholder="Nhập mật khẩu hiện tại để đổi mật khẩu">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Mật khẩu mới</label>
                      <input type="password" name="password" class="form-control" 
                             placeholder="Để trống nếu không đổi mật khẩu">
                      <small class="text-muted">Chỉ nhập nếu muốn thay đổi mật khẩu</small>
                    </div>
                  </div>
                </div>
                <div class="mt-4">
                  <button type="submit" name="update_profile" class="btn bg-gradient-primary">Cập nhật hồ sơ</button>
                  <a href="index.php" class="btn bg-gradient-secondary">Quay lại</a>
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

