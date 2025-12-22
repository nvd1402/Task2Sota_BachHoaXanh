<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Chi tiết Liên hệ - Admin Dashboard";

// Lấy ID liên hệ
$contact_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($contact_id <= 0) {
    header('Location: contact.php');
    exit();
}

// Kết nối database
$conn = connectDB();

// Lấy thông tin liên hệ
$contact = null;
$sql = "SELECT * FROM contact WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $contact_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $contact = $result->fetch_assoc();
}

$stmt->close();

if (!$contact) {
    closeDB($conn);
    header('Location: contact.php');
    exit();
}

// Xử lý cập nhật trạng thái và phản hồi
$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_contact'])) {
    $newStatus = trim($_POST['status'] ?? '');
    $reply = trim($_POST['reply'] ?? '');
    
    // Nếu có phản hồi, cập nhật status và reply
    if (!empty($reply)) {
        $updateSql = "UPDATE contact SET status = ?, reply = ?, replied_by = ?, replied_at = NOW() WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $repliedBy = $_SESSION['user_id'] ?? null;
        $updateStmt->bind_param("ssii", $newStatus, $reply, $repliedBy, $contact_id);
    } else {
        $updateSql = "UPDATE contact SET status = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        $updateStmt->bind_param("si", $newStatus, $contact_id);
    }
    
    if ($updateStmt->execute()) {
        // Đánh dấu đã đọc nếu chưa đọc
        if ($contact['status'] === 'new') {
            $readSql = "UPDATE contact SET status = 'read' WHERE id = ? AND status = 'new'";
            $readStmt = $conn->prepare($readSql);
            $readStmt->bind_param("i", $contact_id);
            $readStmt->execute();
            $readStmt->close();
        }
        
        $success = true;
        // Reload contact data
        $sql = "SELECT * FROM contact WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $contact_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $contact = $result->fetch_assoc();
        $stmt->close();
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
  <?php $currentPage = 'contact'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
      <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> Đã cập nhật liên hệ.
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
                  <h6>Chi tiết Liên hệ</h6>
                </div>
                <div class="col-lg-6 text-end">
                  <a href="contact.php" class="btn bg-gradient-secondary btn-sm mb-0">Quay lại</a>
                </div>
              </div>
            </div>
            <div class="card-body">
              <form method="POST">
                <div class="row">
                  <div class="col-md-6">
                    <h6 class="mb-3">Thông tin người gửi</h6>
                    <p><strong>Tên:</strong> <?= htmlspecialchars($contact['name']) ?></p>
                    <p><strong>Email:</strong> <?= htmlspecialchars($contact['email']) ?></p>
                    <p><strong>Điện thoại:</strong> <?= htmlspecialchars($contact['phone'] ?? 'N/A') ?></p>
                    <p><strong>IP Address:</strong> <?= htmlspecialchars($contact['ip_address'] ?? 'N/A') ?></p>
                    <p><strong>Ngày gửi:</strong> <?= date('d/m/Y H:i:s', strtotime($contact['created_at'])) ?></p>
                  </div>
                  <div class="col-md-6">
                    <h6 class="mb-3">Quản lý</h6>
                    <div class="mb-3">
                      <label class="form-label">Trạng thái</label>
                      <select name="status" class="form-select">
                        <option value="new" <?= $contact['status'] === 'new' ? 'selected' : '' ?>>Mới</option>
                        <option value="read" <?= $contact['status'] === 'read' ? 'selected' : '' ?>>Đã đọc</option>
                        <option value="replied" <?= $contact['status'] === 'replied' ? 'selected' : '' ?>>Đã phản hồi</option>
                        <option value="archived" <?= $contact['status'] === 'archived' ? 'selected' : '' ?>>Đã lưu trữ</option>
                      </select>
                    </div>
                    <button type="submit" name="update_contact" class="btn bg-gradient-primary btn-sm">Cập nhật trạng thái</button>
                  </div>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                  <h6>Chủ đề</h6>
                  <p><?= htmlspecialchars($contact['subject'] ?? 'Không có chủ đề') ?></p>
                </div>

                <div class="mb-3">
                  <h6>Nội dung liên hệ</h6>
                  <div class="border rounded p-3 bg-gray-100">
                    <?= nl2br(htmlspecialchars($contact['message'])) ?>
                  </div>
                </div>

                <?php if (!empty($contact['reply'])): ?>
                <div class="mb-3">
                  <h6>Phản hồi đã gửi</h6>
                  <div class="border rounded p-3 bg-success bg-opacity-10">
                    <?= nl2br(htmlspecialchars($contact['reply'])) ?>
                  </div>
                  <?php if ($contact['replied_at']): ?>
                  <p class="text-sm text-secondary mt-2">
                    Phản hồi lúc: <?= date('d/m/Y H:i:s', strtotime($contact['replied_at'])) ?>
                  </p>
                  <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="mb-3">
                  <h6>Phản hồi mới</h6>
                  <textarea name="reply" class="form-control" rows="5" 
                            placeholder="Nhập nội dung phản hồi..."><?= htmlspecialchars($contact['reply'] ?? '') ?></textarea>
                  <small class="text-muted">Để trống nếu không muốn thay đổi phản hồi hiện tại</small>
                </div>

                <div class="mt-4">
                  <button type="submit" name="update_contact" class="btn bg-gradient-primary">Lưu phản hồi</button>
                  <a href="contact.php" class="btn bg-gradient-secondary">Quay lại</a>
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

