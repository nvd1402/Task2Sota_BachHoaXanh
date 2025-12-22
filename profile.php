<?php
session_start();
require_once 'includes/auth.php';

// Kiểm tra đăng nhập
requireLogin();

$pageTitle = "Hồ sơ - Bách Hóa Xanh";
include 'includes/header.php';

// Lấy thông tin user hiện tại
$user = getCurrentUser();
$error = '';
$success = '';

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($full_name) || empty($email)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc.';
    } else {
        $conn = connectDB();
        
        // Kiểm tra email đã tồn tại chưa (trừ email của user hiện tại)
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email này đã được sử dụng bởi tài khoản khác.';
            $stmt->close();
        } else {
            $stmt->close();
            
            // Cập nhật thông tin
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $full_name, $email, $phone, $address, $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success = 'Cập nhật thông tin thành công!';
                // Cập nhật session
                $_SESSION['email'] = $email;
                $_SESSION['full_name'] = $full_name;
                // Reload user data
                $user = getCurrentUser();
            } else {
                $error = 'Có lỗi xảy ra khi cập nhật thông tin.';
            }
            
            $stmt->close();
        }
        
        closeDB($conn);
    }
}

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'Vui lòng điền đầy đủ thông tin.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'Mật khẩu mới và xác nhận mật khẩu không khớp.';
    } elseif (strlen($new_password) < 6) {
        $error = 'Mật khẩu mới phải có ít nhất 6 ký tự.';
    } else {
        $conn = connectDB();
        
        // Kiểm tra mật khẩu hiện tại
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user_data = $result->fetch_assoc();
            
            if (password_verify($current_password, $user_data['password'])) {
                // Cập nhật mật khẩu mới
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt->close();
                
                $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->bind_param("si", $hashed_password, $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    $success = 'Đổi mật khẩu thành công!';
                } else {
                    $error = 'Có lỗi xảy ra khi đổi mật khẩu.';
                }
            } else {
                $error = 'Mật khẩu hiện tại không đúng.';
            }
        }
        
        $stmt->close();
        closeDB($conn);
    }
}
?>

<main class="profile-page">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="profile-page-title">HỒ SƠ CỦA TÔI</h1>
            </div>
        </div>

        <div class="row mt-4">
            <!-- Sidebar -->
            <div class="col-lg-3 col-md-4">
                <div class="profile-sidebar">
                    <div class="profile-avatar">
                        <div class="avatar-circle">
                            <i class="bi bi-person-fill"></i>
                        </div>
                        <h3 class="profile-name"><?= htmlspecialchars($user['full_name'] ?? $user['username']) ?></h3>
                        <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
                    </div>
                    <nav class="profile-nav">
                        <a href="#profile-info" class="profile-nav-item active" data-tab="profile-info">
                            <i class="bi bi-person"></i> Thông tin cá nhân
                        </a>
                        <a href="#change-password" class="profile-nav-item" data-tab="change-password">
                            <i class="bi bi-lock"></i> Đổi mật khẩu
                        </a>
                        <a href="../logout.php" class="profile-nav-item">
                            <i class="bi bi-box-arrow-right"></i> Đăng xuất
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9 col-md-8">
                <div class="profile-content">
                    <?php if ($error): ?>
                        <div class="alert alert-danger" role="alert">
                            <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success" role="alert">
                            <i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
                        </div>
                    <?php endif; ?>

                    <!-- Profile Info Tab -->
                    <div id="profile-info" class="profile-tab-content active">
                        <div class="profile-card">
                            <h2 class="profile-card-title">Thông tin cá nhân</h2>
                            <form method="post" action="">
                                <input type="hidden" name="update_profile" value="1">
                                
                                <div class="form-group">
                                    <label for="username" class="form-label">Tên đăng nhập</label>
                                    <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" disabled>
                                    <small class="form-text text-muted">Tên đăng nhập không thể thay đổi</small>
                                </div>

                                <div class="form-group">
                                    <label for="full_name" class="form-label">Họ và tên *</label>
                                    <input type="text" id="full_name" name="full_name" class="form-control" value="<?= htmlspecialchars($user['full_name'] ?? '') ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="email" class="form-label">Email *</label>
                                    <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="phone" class="form-label">Số điện thoại</label>
                                    <input type="tel" id="phone" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                                </div>

                                <div class="form-group">
                                    <label for="address" class="form-label">Địa chỉ</label>
                                    <textarea id="address" name="address" class="form-control" rows="3"><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Vai trò</label>
                                    <input type="text" class="form-control" value="<?= $user['role'] === 'admin' ? 'Quản trị viên' : 'Khách hàng' ?>" disabled>
                                </div>

                                <button type="submit" class="btn btn-primary">Cập nhật thông tin</button>
                            </form>
                        </div>
                    </div>

                    <!-- Change Password Tab -->
                    <div id="change-password" class="profile-tab-content">
                        <div class="profile-card">
                            <h2 class="profile-card-title">Đổi mật khẩu</h2>
                            <form method="post" action="">
                                <input type="hidden" name="change_password" value="1">
                                
                                <div class="form-group">
                                    <label for="current_password" class="form-label">Mật khẩu hiện tại *</label>
                                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                                </div>

                                <div class="form-group">
                                    <label for="new_password" class="form-label">Mật khẩu mới *</label>
                                    <input type="password" id="new_password" name="new_password" class="form-control" required minlength="6">
                                    <small class="form-text text-muted">Mật khẩu phải có ít nhất 6 ký tự</small>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới *</label>
                                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required minlength="6">
                                </div>

                                <button type="submit" class="btn btn-primary">Đổi mật khẩu</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<style>
.profile-page {
    padding: 40px 0 80px;
    background: #f5f5f5;
    min-height: 70vh;
}

.profile-page-title {
    font-size: 32px;
    font-weight: 700;
    color: #333;
    margin-bottom: 30px;
    text-align: center;
}

.profile-sidebar {
    background: #fff;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.profile-avatar {
    text-align: center;
    padding-bottom: 30px;
    border-bottom: 1px solid #eee;
    margin-bottom: 20px;
}

.avatar-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: #3da04d;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 15px;
    color: #fff;
    font-size: 48px;
}

.profile-name {
    font-size: 20px;
    font-weight: 600;
    color: #333;
    margin: 0 0 5px 0;
}

.profile-email {
    font-size: 14px;
    color: #666;
    margin: 0;
}

.profile-nav {
    list-style: none;
    padding: 0;
    margin: 0;
}

.profile-nav-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: #333;
    text-decoration: none;
    border-radius: 4px;
    margin-bottom: 5px;
    transition: all 0.3s;
}

.profile-nav-item i {
    margin-right: 10px;
    font-size: 18px;
}

.profile-nav-item:hover,
.profile-nav-item.active {
    background: #3da04d;
    color: #fff;
}

.profile-content {
    background: #fff;
    border-radius: 8px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.profile-tab-content {
    display: none;
}

.profile-tab-content.active {
    display: block;
}

.profile-card-title {
    font-size: 24px;
    font-weight: 600;
    color: #333;
    margin-bottom: 25px;
    padding-bottom: 15px;
    border-bottom: 2px solid #3da04d;
}

.profile-content .form-group {
    margin-bottom: 20px;
}

.profile-content .form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    display: block;
}

.profile-content .form-control {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 10px 15px;
    font-size: 14px;
}

.profile-content .form-control:focus {
    border-color: #3da04d;
    box-shadow: 0 0 0 0.2rem rgba(61, 160, 77, 0.25);
}

.profile-content .btn-primary {
    background: #3da04d;
    border: none;
    padding: 12px 30px;
    font-weight: 600;
    border-radius: 4px;
}

.profile-content .btn-primary:hover {
    background: #2d7a3a;
}

.profile-content .alert {
    margin-bottom: 25px;
    padding: 12px 15px;
    border-radius: 4px;
}

@media (max-width: 768px) {
    .profile-sidebar {
        margin-bottom: 20px;
    }
    
    .profile-page-title {
        font-size: 24px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.profile-nav-item[data-tab]');
    const tabContents = document.querySelectorAll('.profile-tab-content');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetTab = this.getAttribute('data-tab');
            
            // Remove active class from all nav items and tabs
            navItems.forEach(nav => nav.classList.remove('active'));
            tabContents.forEach(tab => tab.classList.remove('active'));
            
            // Add active class to clicked nav item and corresponding tab
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>



