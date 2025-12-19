<?php
/**
 * File xử lý authentication và phân quyền
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Kiểm tra user đã đăng nhập chưa
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Kiểm tra user có phải admin không
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Kiểm tra user có phải customer không
 */
function isCustomer() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'customer';
}

/**
 * Yêu cầu đăng nhập
 */
function requireLogin() {
    if (!isLoggedIn()) {
        // Xác định đường dẫn login dựa trên vị trí file gọi
        $loginPath = 'login.php';
        if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
            $loginPath = '../login.php';
        }
        
        header('Location: ' . $loginPath);
        exit();
    }
}

/**
 * Yêu cầu quyền admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: ../index.php');
        exit();
    }
}

/**
 * Yêu cầu quyền customer
 */
function requireCustomer() {
    requireLogin();
    if (!isCustomer()) {
        header('Location: ../index.php');
        exit();
    }
}

/**
 * Đăng nhập user
 */
function loginUser($username, $password) {
    $conn = connectDB();
    
    // Tìm user theo username hoặc email
    $stmt = $conn->prepare("SELECT id, username, email, password, full_name, role, status FROM users WHERE (username = ? OR email = ?) AND status = 1");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Kiểm tra password
        if (password_verify($password, $user['password'])) {
            // Lưu thông tin vào session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['user_role'] = $user['role'];
            
            $stmt->close();
            closeDB($conn);
            
            return true;
        }
    }
    
    $stmt->close();
    closeDB($conn);
    
    return false;
}

/**
 * Đăng xuất user
 */
function logoutUser() {
    session_unset();
    session_destroy();
    
    // Xác định đường dẫn login dựa trên vị trí file gọi
    $loginPath = 'login.php';
    if (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
        $loginPath = '../login.php';
    }
    
    header('Location: ' . $loginPath);
    exit();
}

/**
 * Lấy thông tin user hiện tại
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $conn = connectDB();
    $stmt = $conn->prepare("SELECT id, username, email, full_name, phone, address, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $stmt->close();
        closeDB($conn);
        return $user;
    }
    
    $stmt->close();
    closeDB($conn);
    return null;
}
?>

