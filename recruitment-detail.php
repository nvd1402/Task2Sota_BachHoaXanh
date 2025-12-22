<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = "Chi tiết tuyển dụng - Bách Hóa Xanh";

// Kết nối database
$conn = connectDB();

// Lấy slug từ URL
$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

// Lấy tuyển dụng từ database
$job = null;
if (!empty($slug)) {
    $job = getRecruitmentBySlug($conn, $slug);
}

// Nếu không tìm thấy, redirect về trang tuyển dụng
if (!$job) {
    closeDB($conn);
    header('Location: recruitment.php');
    exit();
}

$pageTitle = htmlspecialchars($job['title']) . " - Bách Hóa Xanh";

// Format ngày đăng
$publishedDate = $job['created_at'] ? date('d/m/Y', strtotime($job['created_at'])) : '';
$publishedDateFormatted = strtoupper(date('F d, Y', strtotime($job['created_at'])));

// Lấy tuyển dụng liên quan (cùng status)
$relatedJobs = [];
$relatedSql = "SELECT * FROM recruitment WHERE status = 'open' AND id != ? ORDER BY created_at DESC LIMIT 2";
$relatedStmt = $conn->prepare($relatedSql);
$relatedStmt->bind_param("i", $job['id']);
$relatedStmt->execute();
$relatedResult = $relatedStmt->get_result();
while ($row = $relatedResult->fetch_assoc()) {
    $relatedJobs[] = $row;
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
            <h1 class="recruit-detail-title"><?= htmlspecialchars($job['title']) ?></h1>
            
            <div class="recruit-detail-meta">
                <span class="recruit-meta-text">POSTED ON <?= htmlspecialchars($publishedDateFormatted) ?></span>
            </div>

            <!-- Thông tin tuyển dụng -->
            <div class="recruit-info-box mb-4 p-3 border rounded">
                <div class="row g-3">
                    <?php if (!empty($job['position'])): ?>
                    <div class="col-md-6">
                        <strong>Vị trí:</strong> <?= htmlspecialchars($job['position']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($job['department'])): ?>
                    <div class="col-md-6">
                        <strong>Phòng ban:</strong> <?= htmlspecialchars($job['department']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($job['location'])): ?>
                    <div class="col-md-6">
                        <strong>Địa điểm:</strong> <?= htmlspecialchars($job['location']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($job['employment_type'])): ?>
                    <div class="col-md-6">
                        <strong>Loại hình:</strong> <?= htmlspecialchars($job['employment_type']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($job['salary_display'])): ?>
                    <div class="col-md-6">
                        <strong>Mức lương:</strong> <?= htmlspecialchars($job['salary_display']) ?>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($job['deadline'])): ?>
                    <div class="col-md-6">
                        <strong>Hạn nộp hồ sơ:</strong> <?= date('d/m/Y', strtotime($job['deadline'])) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="recruit-detail-body">
                <h3>Mô tả công việc</h3>
                <div class="recruit-content-text">
                    <?php 
                    // Nếu nội dung chứa HTML tags, hiển thị trực tiếp
                    // Nếu không, chuyển đổi xuống dòng thành <br>
                    $description = $job['description'];
                    if (strip_tags($description) !== $description) {
                        // Có HTML tags, hiển thị trực tiếp
                        echo $description;
                    } else {
                        // Không có HTML, chuyển đổi xuống dòng
                        echo nl2br(htmlspecialchars($description));
                    }
                    ?>
                </div>
                
                <?php if (!empty($job['requirements'])): ?>
                <h3 class="mt-4">Yêu cầu ứng viên</h3>
                <div class="recruit-content-text">
                    <?php 
                    $requirements = $job['requirements'];
                    if (strip_tags($requirements) !== $requirements) {
                        echo $requirements;
                    } else {
                        // Chuyển đổi xuống dòng thành danh sách nếu có dấu gạch đầu dòng hoặc số
                        $lines = explode("\n", $requirements);
                        $hasListFormat = false;
                        foreach ($lines as $line) {
                            $trimmed = trim($line);
                            if (preg_match('/^[-•*]\s+|^\d+[\.\)]\s+/', $trimmed)) {
                                $hasListFormat = true;
                                break;
                            }
                        }
                        
                        if ($hasListFormat) {
                            echo '<ul>';
                            foreach ($lines as $line) {
                                $trimmed = trim($line);
                                if (!empty($trimmed)) {
                                    // Loại bỏ dấu gạch đầu dòng hoặc số
                                    $cleanLine = preg_replace('/^[-•*]\s+|^\d+[\.\)]\s+/', '', $trimmed);
                                    echo '<li>' . htmlspecialchars($cleanLine) . '</li>';
                                }
                            }
                            echo '</ul>';
                        } else {
                            echo nl2br(htmlspecialchars($requirements));
                        }
                    }
                    ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($job['benefits'])): ?>
                <h3 class="mt-4">Quyền lợi</h3>
                <div class="recruit-content-text">
                    <?php 
                    $benefits = $job['benefits'];
                    if (strip_tags($benefits) !== $benefits) {
                        echo $benefits;
                    } else {
                        // Chuyển đổi xuống dòng thành danh sách nếu có dấu gạch đầu dòng hoặc số
                        $lines = explode("\n", $benefits);
                        $hasListFormat = false;
                        foreach ($lines as $line) {
                            $trimmed = trim($line);
                            if (preg_match('/^[-•*]\s+|^\d+[\.\)]\s+/', $trimmed)) {
                                $hasListFormat = true;
                                break;
                            }
                        }
                        
                        if ($hasListFormat) {
                            echo '<ul>';
                            foreach ($lines as $line) {
                                $trimmed = trim($line);
                                if (!empty($trimmed)) {
                                    // Loại bỏ dấu gạch đầu dòng hoặc số
                                    $cleanLine = preg_replace('/^[-•*]\s+|^\d+[\.\)]\s+/', '', $trimmed);
                                    echo '<li>' . htmlspecialchars($cleanLine) . '</li>';
                                }
                            }
                            echo '</ul>';
                        } else {
                            echo nl2br(htmlspecialchars($benefits));
                        }
                    }
                    ?>
                </div>
                <?php endif; ?>
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
                <div class="related-articles">
                    <?php
                    if (empty($relatedJobs)) {
                        echo '<p class="text-muted">Chưa có vị trí tuyển dụng liên quan.</p>';
                    } else {
                        foreach ($relatedJobs as $related): 
                            $relatedDate = date('d/m/Y', strtotime($related['created_at']));
                            $relatedDesc = mb_substr(strip_tags($related['description']), 0, 150) . '...';
                            ?>
                            <article class="related-article-item">
                                <h4 class="related-article-title">
                                    <a href="recruitment-detail.php?slug=<?= htmlspecialchars($related['slug']) ?>">
                                        <?= htmlspecialchars($related['title']) ?>
                                    </a>
                                </h4>
                                <p class="related-article-date"><?= htmlspecialchars($relatedDate) ?></p>
                                <p class="related-article-desc"><?= htmlspecialchars($relatedDesc) ?></p>
                            </article>
                        <?php endforeach;
                    }
                    ?>
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
                        $subject = 'Bình luận về tuyển dụng: ' . $job['title'];
                        
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
                
                <form class="comment-form" action="recruitment-detail.php?slug=<?= htmlspecialchars($slug) ?>" method="post">
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
                    <li><a href="#">Tin tức</a></li>
                    <li class="active"><a href="#">Tuyển dụng</a></li>
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
                            $itemImg = !empty($item['featured_image']) ? 'assets/images/' . $item['featured_image'] : '';
                            ?>
                            <a href="news-detail.php?slug=<?= htmlspecialchars($item['slug']) ?>" class="latest-item">
                                <?php if (!empty($itemImg)): ?>
                                <div class="latest-thumb">
                                    <img src="<?= htmlspecialchars($itemImg) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                </div>
                                <?php endif; ?>
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

