<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = "Chi tiết tin tức - Bách Hóa Xanh";

// Kết nối database
$conn = connectDB();

// Lấy slug từ URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

// Lấy tin tức từ database
$news = null;
if (!empty($slug)) {
    $news = getNewsBySlug($conn, $slug);
}

// Nếu không tìm thấy, redirect về trang tin tức
if (!$news) {
    closeDB($conn);
    header('Location: news.php');
    exit();
}

$pageTitle = htmlspecialchars($news['title']) . " - Bách Hóa Xanh";

// Lấy thông tin tác giả
$authorName = 'Admin';
if (!empty($news['author_id'])) {
    $authorSql = "SELECT full_name FROM users WHERE id = ?";
    $authorStmt = $conn->prepare($authorSql);
    $authorStmt->bind_param("i", $news['author_id']);
    $authorStmt->execute();
    $authorResult = $authorStmt->get_result();
    if ($authorResult && $authorResult->num_rows > 0) {
        $author = $authorResult->fetch_assoc();
        $authorName = $author['full_name'] ?? 'Admin';
    }
    $authorStmt->close();
}

// Format ngày đăng
$publishedDate = $news['published_at'] ? date('d/m/Y', strtotime($news['published_at'])) : date('d/m/Y', strtotime($news['created_at']));
$publishedDateFormatted = strtoupper(date('F d, Y', strtotime($news['published_at'] ?: $news['created_at'])));

// Lấy tin tức liên quan (cùng category hoặc mới nhất)
$relatedNews = [];
$relatedSql = "SELECT * FROM news WHERE status = 'published' AND id != ?";
if (!empty($news['category'])) {
    $relatedSql .= " AND (category = ? OR category IS NULL)";
    $relatedStmt = $conn->prepare($relatedSql);
    $relatedStmt->bind_param("is", $news['id'], $news['category']);
} else {
    $relatedStmt = $conn->prepare($relatedSql);
    $relatedStmt->bind_param("i", $news['id']);
}
$relatedStmt->execute();
$relatedResult = $relatedStmt->get_result();
while ($row = $relatedResult->fetch_assoc()) {
    $relatedNews[] = $row;
}
$relatedStmt->close();

// Lấy 5 tin tức mới nhất cho sidebar
$latestNewsData = getNews($conn, ['per_page' => 5, 'status' => 'published']);
$latestNews = $latestNewsData['news'];

include 'includes/header.php';
?>

<main class="recruit-detail-page">
    <div class="container recruit-detail-layout">
        <!-- Nội dung chi tiết bài viết -->
        <article class="recruit-detail-content">
            <h1 class="recruit-detail-title"><?= htmlspecialchars($news['title']) ?></h1>
            
            <div class="recruit-detail-meta">
                <span class="recruit-meta-text">POSTED ON <?= htmlspecialchars($publishedDateFormatted) ?> BY <?= htmlspecialchars($authorName) ?></span>
            </div>

            <?php if (!empty($news['featured_image'])): ?>
            <div class="recruit-detail-image mb-4">
                <img src="assets/images/<?= htmlspecialchars($news['featured_image']) ?>" alt="<?= htmlspecialchars($news['title']) ?>" class="img-fluid">
            </div>
            <?php endif; ?>

            <div class="recruit-detail-body">
                <?= nl2br(htmlspecialchars($news['content'])) ?>
            </div>

            <!-- Promo Text -->
            <div class="recruit-detail-promo">
                <p>Bạn đừng quên sàn TMĐT Shop Thương gia & Thị trường lúc nào cũng có sẵn những mã hàng giảm giá lên đến 60%. Đặc biệt, tất cả mã hàng đều là hàng chính hãng, chuẩn chất lượng không cần lăn tăn.</p>
            </div>

            <!-- Share Section -->
            <div class="recruit-share-section">
                <div class="social-share-icons">
                    <a href="#" class="social-icon facebook" title="Share on Facebook">
                        <i class="bi bi-facebook"></i>
                        <span class="social-tooltip">Share on Facebook</span>
                    </a>
                    <a href="#" class="social-icon twitter" title="Share on Twitter">
                        <i class="bi bi-twitter"></i>
                        <span class="social-tooltip">Share on Twitter</span>
                    </a>
                    <a href="#" class="social-icon email" title="Share via Email">
                        <i class="bi bi-envelope"></i>
                        <span class="social-tooltip">Share via Email</span>
                    </a>
                    <a href="#" class="social-icon pinterest" title="Pin on Pinterest">
                        <i class="bi bi-pinterest"></i>
                        <span class="social-tooltip">Pin on Pinterest</span>
                    </a>
                    <a href="#" class="social-icon linkedin" title="Share on LinkedIn">
                        <i class="bi bi-linkedin"></i>
                        <span class="social-tooltip">Share on LinkedIn</span>
                    </a>
                </div>
            </div>

            <!-- Related Articles -->
            <div class="recruit-related-section">
                <h3 class="related-title">Bài viết liên quan</h3>
                <div class="related-carousel-wrapper">
                    <button class="related-nav-btn related-prev" aria-label="Previous">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <div class="related-carousel-container">
                        <div class="related-carousel-track">
                            <?php
                            if (empty($relatedNews)) {
                                echo '<p class="text-muted">Chưa có bài viết liên quan.</p>';
                            } else {
                                foreach ($relatedNews as $related): 
                                    $relatedImg = !empty($related['featured_image']) ? 'assets/images/' . $related['featured_image'] : 'assets/images/lesterblur__2.jpg';
                                    $relatedDate = $related['published_at'] ? date('d', strtotime($related['published_at'])) : date('d', strtotime($related['created_at']));
                                    $relatedMonth = $related['published_at'] ? date('M', strtotime($related['published_at'])) : date('M', strtotime($related['created_at']));
                                    $relatedMonthShort = str_replace(['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'], 
                                        ['Th1', 'Th2', 'Th3', 'Th4', 'Th5', 'Th6', 'Th7', 'Th8', 'Th9', 'Th10', 'Th11', 'Th12'], $relatedMonth);
                                    $relatedDesc = !empty($related['excerpt']) ? $related['excerpt'] : mb_substr(strip_tags($related['content']), 0, 100) . '...';
                                    ?>
                                    <article class="related-article-card">
                                        <a href="news-detail.php?slug=<?= htmlspecialchars($related['slug']) ?>" class="related-card-link">
                                            <div class="related-card-image">
                                                <div class="related-date-badge">
                                                    <span class="related-date-day"><?= htmlspecialchars($relatedDate) ?></span>
                                                    <span class="related-date-month"><?= htmlspecialchars($relatedMonthShort) ?></span>
                                                </div>
                                                <img src="<?= htmlspecialchars($relatedImg) ?>" alt="<?= htmlspecialchars($related['title']) ?>">
                                            </div>
                                            <div class="related-card-content">
                                                <h4 class="related-card-title"><?= htmlspecialchars($related['title']) ?></h4>
                                                <p class="related-card-desc"><?= htmlspecialchars($relatedDesc) ?></p>
                                            </div>
                                        </a>
                                    </article>
                                <?php endforeach;
                            }
                            ?>
                        </div>
                    </div>
                    <button class="related-nav-btn related-next" aria-label="Next">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>

            <!-- Comment Form -->
            <div class="recruit-comment-section">
                <h3 class="comment-section-title">Để lại một bình luận</h3>
                <p class="comment-notice">Email của bạn sẽ không được hiển thị công khai. Các trường bắt buộc được đánh dấu *</p>
                
                <?php
                // Xử lý form comment
                $commentSuccess = false;
                $commentError = '';
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_comment'])) {
                    $commentName = trim($_POST['name'] ?? '');
                    $commentEmail = trim($_POST['email'] ?? '');
                    $commentMessage = trim($_POST['comment'] ?? '');
                    
                    if (empty($commentName)) {
                        $commentError = 'Vui lòng nhập tên';
                    } elseif (empty($commentEmail)) {
                        $commentError = 'Vui lòng nhập email';
                    } elseif (!filter_var($commentEmail, FILTER_VALIDATE_EMAIL)) {
                        $commentError = 'Email không hợp lệ';
                    } elseif (empty($commentMessage)) {
                        $commentError = 'Vui lòng nhập bình luận';
                    } else {
                        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
                        $subject = 'Bình luận về tin tức: ' . $news['title'];
                        
                        $commentSql = "INSERT INTO contact (name, email, phone, subject, message, ip_address, status) 
                                       VALUES (?, ?, ?, ?, ?, ?, 'new')";
                        
                        $commentStmt = $conn->prepare($commentSql);
                        $phone = '';
                        $commentStmt->bind_param("ssssss", $commentName, $commentEmail, $phone, $subject, $commentMessage, $ipAddress);
                        
                        if ($commentStmt->execute()) {
                            $commentSuccess = true;
                        } else {
                            $commentError = 'Có lỗi xảy ra khi gửi bình luận. Vui lòng thử lại.';
                        }
                        
                        $commentStmt->close();
                    }
                }
                ?>
                
                <?php if ($commentSuccess): ?>
                    <div class="alert alert-success mb-3" role="alert">
                        <i class="bi bi-check-circle"></i> Cảm ơn bạn đã bình luận! Bình luận của bạn đang chờ được duyệt.
                    </div>
                <?php elseif ($commentError): ?>
                    <div class="alert alert-danger mb-3" role="alert">
                        <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($commentError) ?>
                    </div>
                <?php endif; ?>
                
                <form class="comment-form" action="news-detail.php?slug=<?= htmlspecialchars($slug) ?>" method="post">
                    <div class="form-group">
                        <label for="comment">Bình luận *</label>
                        <textarea id="comment" name="comment" rows="6" required><?= isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : '' ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="comment-name">Tên *</label>
                            <input type="text" id="comment-name" name="name" 
                                   value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="comment-email">Email *</label>
                            <input type="email" id="comment-email" name="email" 
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="comment-website">Trang web</label>
                            <input type="url" id="comment-website" name="website" 
                                   value="<?= isset($_POST['website']) ? htmlspecialchars($_POST['website']) : '' ?>">
                        </div>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="save-info" name="save_info" <?= isset($_POST['save_info']) ? 'checked' : '' ?>>
                        <label for="save-info">Lưu tên của tôi, email, và trang web trong trình duyệt này cho lần bình luận kế tiếp của tôi.</label>
                    </div>
                    
                    <button type="submit" name="submit_comment" class="comment-submit-btn">GỬI BÌNH LUẬN</button>
                </form>
            </div>
        </article>

        <aside class="recruit-sidebar">
            <div class="recruit-widget">
                <h4>CHUYÊN MỤC TIN TỨC</h4>
                <ul class="recruit-cats">
                    <li><a href="#">Thời trang &amp; cuộc sống</a></li>
                    <li><a href="#">Tin công nghệ</a></li>
                    <li class="active"><a href="#">Tin tức</a></li>
                    <li><a href="#">Tuyển dụng</a></li>
                </ul>
            </div>

            <div class="recruit-widget">
                <h4>TIN TỨC MỚI NHẤT</h4>
                <div class="recruit-latest">
                    <?php
                    if (empty($latestNews)) {
                        echo '<p class="text-muted">Chưa có tin tức nào.</p>';
                    } else {
                        foreach ($latestNews as $item): 
                            $itemImg = !empty($item['featured_image']) ? 'assets/images/' . $item['featured_image'] : 'assets/images/lesterblur__2.jpg';
                            ?>
                            <a href="news-detail.php?slug=<?= htmlspecialchars($item['slug']) ?>" class="latest-item">
                                <div class="latest-thumb">
                                    <img src="<?= htmlspecialchars($itemImg) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                </div>
                                <div class="latest-info">
                                    <p><?= htmlspecialchars($item['title']) ?></p>
                                </div>
                            </a>
                        <?php endforeach;
                    }
                    ?>
                </div>
            </div>
        </aside>
    </div>
</main>

<?php 
// Đóng kết nối database
if (isset($conn)) {
    closeDB($conn);
}
include 'includes/footer.php'; 
?>

