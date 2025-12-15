<?php
/**
 * File cấu hình kết nối database
 */

// Thông tin kết nối database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'bachhoaxanh_db');

/**
 * Hàm kết nối database
 */
function connectDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Kết nối thất bại: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8mb4");
    
    return $conn;
}

/**
 * Hàm đóng kết nối database
 */
function closeDB($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>

