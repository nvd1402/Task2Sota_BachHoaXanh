<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Thêm Tin tức - Admin Dashboard";

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_news'])) {
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
        // Tự động tạo slug từ tiêu đề
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $slug = preg_replace('/-+/', '-', $slug);
    }
    
    // Xử lý upload ảnh
    $featuredImage = '';
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../assets/images/';
        $fileName = time() . '_' . basename($_FILES['featured_image']['name']);
        $targetFile = $uploadDir . $fileName;
        
        $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $targetFile)) {
                $featuredImage = $fileName;
            } else {
                $errors[] = "Lỗi khi upload ảnh";
            }
        } else {
            $errors[] = "Chỉ chấp nhận file ảnh: JPG, JPEG, PNG, GIF, WEBP";
        }
    }
    
    // Xử lý gallery (nhiều ảnh)
    $gallery = [];
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
    }
    $galleryJson = !empty($gallery) ? json_encode($gallery) : null;
    
    // Nếu không có lỗi, thêm vào database
    if (empty($errors)) {
        $conn = connectDB();
        
        // Kiểm tra slug đã tồn tại chưa
        $checkStmt = $conn->prepare("SELECT id FROM news WHERE slug = ?");
        $checkStmt->bind_param("s", $slug);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        
        if ($checkResult->num_rows > 0) {
            $slug = $slug . '-' . time();
        }
        $checkStmt->close();
        
        // Thêm tin tức
        $authorId = $_SESSION['user_id'];
        $insertSql = "INSERT INTO news (title, slug, excerpt, content, featured_image, gallery, author_id, category, tags, status, featured, published_at) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        
        if ($publishedAt && $status === 'published') {
            $publishedAt = date('Y-m-d H:i:s', strtotime($publishedAt));
        } else {
            $publishedAt = null;
        }
        
        $insertStmt->bind_param("ssssssisssis", 
            $title, $slug, $excerpt, $content, $featuredImage, $galleryJson,
            $authorId, $category, $tags, $status, $featured, $publishedAt
        );
        
        if ($insertStmt->execute()) {
            $success = true;
            header('Location: news.php?success=1');
            exit();
        } else {
            $errors[] = "Có lỗi xảy ra khi thêm tin tức";
        }
        $insertStmt->close();
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
  <script src="https://cdn.ckeditor.com/4.16.2/standard/ckeditor.js"></script>
</head>

<body class="g-sidenav-show bg-gray-100">
  <?php $currentPage = 'news'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
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
                  <h6>Thêm Tin tức</h6>
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
                             value="<?= isset($_POST['title']) ? htmlspecialchars($_POST['title']) : '' ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Slug</label>
                      <input type="text" name="slug" class="form-control" 
                             value="<?= isset($_POST['slug']) ? htmlspecialchars($_POST['slug']) : '' ?>"
                             placeholder="Tự động tạo từ tiêu đề nếu để trống">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Tóm tắt</label>
                      <textarea name="excerpt" class="form-control" rows="3"><?= isset($_POST['excerpt']) ? htmlspecialchars($_POST['excerpt']) : '' ?></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Nội dung *</label>
                      <textarea name="content" id="content" class="form-control" rows="10" required><?= isset($_POST['content']) ? htmlspecialchars($_POST['content']) : '' ?></textarea>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="mb-3">
                      <label class="form-label">Ảnh đại diện</label>
                      <input type="file" name="featured_image" class="form-control" accept="image/*">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Gallery (nhiều ảnh)</label>
                      <input type="file" name="gallery[]" class="form-control" accept="image/*" multiple>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Danh mục</label>
                      <input type="text" name="category" class="form-control" 
                             value="<?= isset($_POST['category']) ? htmlspecialchars($_POST['category']) : '' ?>"
                             placeholder="VD: Tin tức, Khuyến mãi...">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Tags</label>
                      <input type="text" name="tags" class="form-control" 
                             value="<?= isset($_POST['tags']) ? htmlspecialchars($_POST['tags']) : '' ?>"
                             placeholder="Phân cách bằng dấu phẩy">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Trạng thái *</label>
                      <select name="status" class="form-select" required>
                        <option value="draft" <?= (isset($_POST['status']) && $_POST['status'] === 'draft') || !isset($_POST['status']) ? 'selected' : '' ?>>Bản nháp</option>
                        <option value="published" <?= isset($_POST['status']) && $_POST['status'] === 'published' ? 'selected' : '' ?>>Đã xuất bản</option>
                        <option value="archived" <?= isset($_POST['status']) && $_POST['status'] === 'archived' ? 'selected' : '' ?>>Đã lưu trữ</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Ngày xuất bản</label>
                      <input type="datetime-local" name="published_at" class="form-control" 
                             value="<?= isset($_POST['published_at']) ? htmlspecialchars($_POST['published_at']) : '' ?>">
                    </div>
                    <div class="mb-3">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="featured" id="featured" value="1"
                               <?= isset($_POST['featured']) ? 'checked' : '' ?>>
                        <label class="form-check-label" for="featured">
                          Tin nổi bật
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="mt-4">
                  <button type="submit" name="add_news" class="btn bg-gradient-primary">Thêm tin tức</button>
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

