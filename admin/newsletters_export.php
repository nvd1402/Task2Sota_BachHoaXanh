<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$conn = connectDB();

// Lấy tất cả email đăng ký
$sql = "SELECT email, status, ip_address, created_at, updated_at FROM newsletter_subscriptions ORDER BY created_at DESC";
$result = $conn->query($sql);

// Set headers for Excel download
header('Content-Type: application/vnd.ms-excel; charset=utf-8');
header('Content-Disposition: attachment; filename="newsletter_subscriptions_' . date('Y-m-d') . '.xls"');
header('Pragma: no-cache');
header('Expires: 0');

// BOM for UTF-8
echo "\xEF\xBB\xBF";

// Start Excel content
echo '<table border="1">';
echo '<tr>';
echo '<th>STT</th>';
echo '<th>Email</th>';
echo '<th>Trạng thái</th>';
echo '<th>IP Address</th>';
echo '<th>Ngày đăng ký</th>';
echo '<th>Ngày cập nhật</th>';
echo '</tr>';

$stt = 1;
while ($row = $result->fetch_assoc()) {
    echo '<tr>';
    echo '<td>' . $stt . '</td>';
    echo '<td>' . htmlspecialchars($row['email']) . '</td>';
    echo '<td>' . ($row['status'] === 'active' ? 'Đang đăng ký' : 'Đã hủy') . '</td>';
    echo '<td>' . htmlspecialchars($row['ip_address'] ?? 'N/A') . '</td>';
    echo '<td>' . date('d/m/Y H:i', strtotime($row['created_at'])) . '</td>';
    echo '<td>' . date('d/m/Y H:i', strtotime($row['updated_at'])) . '</td>';
    echo '</tr>';
    $stt++;
}

echo '</table>';

closeDB($conn);
exit();
?>

