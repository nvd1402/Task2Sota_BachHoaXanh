<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Sửa Tin tức - Admin Dashboard";

// Lấy ID tin tức
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($news_id <= 0) {
    header('Location: news.php');
    exit();
}

// Kết nối database
$conn = connectDB();

// Lấy thông tin tin tức
$news = null;
$sql = "SELECT * FROM news WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $news_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $news = $result->fetch_assoc();
}

$stmt->close();

if (!$news) {
    closeDB($conn);
    header('Location: news.php');
    exit();
}

// Xử lý cập nhật
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_news'])) {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    $featured = isset($_POST['featured']) ? 1 : 0;
    $publishedAt = !empty($_POST['published_at']) ? trim($_POST['published_at']) : null;
    
    // Validation
    if (empty($title)) {
        $errors[] = "Tiêu đề không được để trống";
    }
    
    if (empty($content)) {
        $errors[] = "Nội dung không được để trống";
    }
    
    if (empty($slug)) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = preg_replace('/-+/', '-', $slug);
    }
    
    // Xử lý upload ảnh mới
    $featuredImage = $news['featured_image'];
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/images/';
        $fileName = time() . '_' . basename($_FILES['featured_image']['name']);
        $targetFile = $uploadDir . $fileName;
        
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $targetFile)) {
                // Xóa ảnh cũ nếu có
                if (!empty($news['featured_image']) && file_exists($uploadDir . $news['featured_image'])) {
                    @unlink($uploadDir . $news['featured_image']);
                }
                $featuredImage = $fileName;
            } else {
                $errors[] = "Lỗi khi upload ảnh";
            }
        } else {
            $errors[] = "Chỉ chấp nhận file ảnh: JPG, JPEG, PNG, GIF, WEBP";
        }
    }
    
    // Xử lý gallery
    $gallery = [];
    $existingGallery = !empty($news['gallery']) ? json_decode($news['gallery'], true) : [];
    
    // Giữ lại gallery cũ nếu không upload mới
    if (isset($_FILES['gallery']) && !empty($_FILES['gallery']['name'][0])) {
        $uploadDir = '../assets/images/';
        foreach ($_FILES['gallery']['name'] as $key => $name) {
            if ($_FILES['gallery']['error'][$key] === UPLOAD_ERR_OK) {
                $fileName = time() . '_' . $key . '_' . basename($name);
                $targetFile = $uploadDir . $fileName;
                $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
                $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                
                if (in_array($imageFileType, $allowedTypes)) {
                    if (move_uploaded_file($_FILES['gallery']['tmp_name'][$key], $targetFile)) {
                        $gallery[] = $fileName;
                    }
                }
            }
        }
        $galleryJson = !empty($gallery) ? json_encode($gallery) : null;
    } else {
        $galleryJson = $news['gallery'];
    }
    
    // Nếu không có lỗi, cập nhật
    if (empty($errors)) {
        // Kiểm tra slug đã tồn tại chưa (trừ chính tin này)
        $checkStmt = $conn->prepare("SELECT id FROM news WHERE slug = ? AND id != ?");
        $checkStmt->bind_param("si", $slug, $news_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $slug = $slug . '-' . time();
        }
        $checkStmt->close();
        
        // Cập nhật
        $updateSql = "UPDATE news SET title = ?, slug = ?, excerpt = ?, content = ?, featured_image = ?, gallery = ?, category = ?, tags = ?, status = ?, featured = ?, published_at = ? WHERE id = ?";
        $updateStmt = $conn->prepare($updateSql);
        
        if ($publishedAt && $status === 'published') {
            $publishedAt = date('Y-m-d H:i:s', strtotime($publishedAt));
        } else {
            $publishedAt = $news['published_at'];
        }
        
        $updateStmt->bind_param("sssssssssisi", 
            $title, $slug, $excerpt, $content, $featuredImage, $galleryJson,
            $category, $tags, $status, $featured, $publishedAt, $news_id
        );
        
        if ($updateStmt->execute()) {
            $success = true;
            // Reload news data
            $sql = "SELECT * FROM news WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $news_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $news = $result->fetch_assoc();
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
  <?php $currentPage = 'news'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
      <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <strong>Thành công!</strong> Đã cập nhật tin tức.
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
                  <h6>Sửa Tin tức</h6>
                </div>
                <div class="col-lg-6 text-end">
                  <a href="news.php" class="btn bg-gradient-secondary btn-sm mb-0">Quay lại</a>
                </div>
              </div>
            </div>
            <div class="card-body">
              <form method="POST" enctype="multipart/form-data">
                <div class="row">
                  <div class="col-md-8">
                    <div class="mb-3">
                      <label class="form-label">Tiêu đề *</label>
                      <input type="text" name="title" class="form-control" 
                             value="<?= htmlspecialchars($news['title']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Slug</label>
                      <input type="text" name="slug" class="form-control" 
                             value="<?= htmlspecialchars($news['slug']) ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Tóm tắt</label>
                      <textarea name="excerpt" class="form-control" rows="3"><?= htmlspecialchars($news['excerpt'] ?? '') ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Nội dung *</label>
                      <textarea name="content" id="content" class="form-control" rows="10" required><?= htmlspecialchars($news['content']) ?></textarea>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label">Ảnh đại diện</label>
                      <?php if (!empty($news['featured_image'])): ?>
                      <div class="mb-2">
                        <img src="../assets/images/<?= htmlspecialchars($news['featured_image']) ?>" 
                             class="img-thumbnail" style="max-width: 200px;">
                      </div>
                      <?php endif; ?>
                      <input type="file" name="featured_image" class="form-control" accept="image/*">
                      <small class="text-muted">Để trống nếu không đổi ảnh</small>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Gallery (nhiều ảnh)</label>
                      <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                      <small class="text-muted">Chọn nhiều ảnh để thay thế gallery hiện tại</small>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Danh mục</label>
                      <input type="text" name="category" class="form-control" 
                             value="<?= htmlspecialchars($news['category'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Tags</label>
                      <input type="text" name="tags" class="form-control" 
                             value="<?= htmlspecialchars($news['tags'] ?? '') ?>">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Trạng thái *</label>
                      <select name="status" class="form-select" required>
                        <option value="draft" <?= $news['status'] === 'draft' ? 'selected' : '' ?>>Bản nháp</option>
                        <option value="published" <?= $news['status'] === 'published' ? 'selected' : '' ?>>Đã xuất bản</option>
                        <option value="archived" <?= $news['status'] === 'archived' ? 'selected' : '' ?>>Đã lưu trữ</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Ngày xuất bản</label>
                      <input type="datetime-local" name="published_at" class="form-control" 
                             value="<?= $news['published_at'] ? date('Y-m-d\TH:i', strtotime($news['published_at'])) : '' ?>">
                    </div>
                    <div class="mb-3">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="featured" id="featured" value="1"
                               <?= $news['featured'] ? 'checked' : '' ?>>
                        <label class="form-check-label" for="featured">
                          Tin nổi bật
                        </label>
                      </div>
                    </div>
                    <div class="mb-3">
                      <p class="text-sm text-secondary">
                        <strong>Lượt xem:</strong> <?= number_format($news['views']) ?><br>
                        <strong>Ngày tạo:</strong> <?= date('d/m/Y H:i', strtotime($news['created_at'])) ?><br>
                        <strong>Cập nhật:</strong> <?= date('d/m/Y H:i', strtotime($news['updated_at'])) ?>
                      </p>
                    </div>
                  </div>
                </div>
                <div class="mt-4">
                  <button type="submit" name="update_news" class="btn bg-gradient-primary">Cập nhật</button>
                  <a href="news.php" class="btn bg-gradient-secondary">Hủy</a>
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
    CKEDITOR.replace('content');
  </script>
</body>
</html>

