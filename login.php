<?php
session_start();
$pageTitle = "Đăng nhập - Bách Hóa Xanh";

// Nếu đã đăng nhập, redirect theo role
if (isset($_SESSION['user_id'])) {
    if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
        header('Location: admin/index.php');
        exit();
    } else {
        header('Location: index.php');
        exit();
    }
}

require_once 'includes/auth.php';

$error = '';
$success = '';

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Vui lòng nhập đầy đủ thông tin đăng nhập.';
    } else {
        if (loginUser($username, $password)) {
            // Đăng nhập thành công, redirect theo role
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
                header('Location: admin/index.php');
                exit();
            } else {
                header('Location: index.php');
                exit();
            }
        } else {
            $error = 'Tên đăng nhập hoặc mật khẩu không đúng.';
        }
    }
}

include 'includes/header.php';
?>

<main class="login-page">
    <div class="container">
        <h1 class="login-page-title">MY ACCOUNT</h1>
        
        <div class="login-section">
            <h2 class="login-heading">LOGIN</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <form class="login-form" action="" method="post">
                <input type="hidden" name="login" value="1">
                <div class="form-group">
                    <label for="username" class="form-label">Username or email address *</label>
                    <input type="text" id="username" name="username" class="form-input" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password *</label>
                    <input type="password" id="password" name="password" class="form-input" required>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" id="remember" name="remember" class="form-checkbox">
                    <label for="remember" class="checkbox-label">Remember me</label>
                </div>
                
                <button type="submit" class="login-submit-btn">LOG IN</button>
                
                <div class="lost-password-link">
                    <a href="#" id="toggle-lost-password" class="lost-password">Lost your password?</a>
                </div>
            </form>
            
            <!-- Lost Password Form -->
            <div id="lost-password-form" class="lost-password-section" style="display: none;">
                <p class="lost-password-instruction">
                    Lost your password? Please enter your username or email address. You will receive a link to create a new password via email.
                </p>
                
                <form class="reset-password-form" action="#" method="post">
                    <div class="form-group">
                        <label for="reset-username" class="form-label">Username or email</label>
                        <input type="text" id="reset-username" name="username" class="form-input" required>
                    </div>
                    
                    <button type="submit" class="reset-password-btn">RESET PASSWORD</button>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleLink = document.getElementById('toggle-lost-password');
    const loginForm = document.querySelector('.login-form');
    const lostPasswordForm = document.getElementById('lost-password-form');
    
    if (toggleLink && lostPasswordForm) {
        toggleLink.addEventListener('click', function(e) {
            e.preventDefault();
            if (lostPasswordForm.style.display === 'none') {
                lostPasswordForm.style.display = 'block';
                loginForm.style.display = 'none';
            } else {
                lostPasswordForm.style.display = 'none';
                loginForm.style.display = 'flex';
            }
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>

