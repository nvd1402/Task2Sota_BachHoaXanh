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

<main class="py-4 py-md-5">
    <div class="container">
        <div class="row g-4">
            <!-- Nội dung chi tiết bài viết -->
            <article class="col-lg-9">
                <h1 class="fw-bold mb-3 mb-md-4" style="font-size: clamp(1.5rem, 4vw, 2rem);"><?= htmlspecialchars($news['title']) ?></h1>
                
                <div class="mb-4 pb-3 border-bottom">
                    <small class="text-muted text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">POSTED ON <?= htmlspecialchars($publishedDateFormatted) ?> BY <?= htmlspecialchars($authorName) ?></small>
                </div>

                <div class="news-content mb-4">
                    <?= $news['content'] ?>
                </div>

                <!-- Promo Text -->
                <div class="bg-light p-4 rounded mb-4">
                    <p class="mb-0">Bạn đừng quên sàn TMĐT Shop Thương gia & Thị trường lúc nào cũng có sẵn những mã hàng giảm giá lên đến 60%. Đặc biệt, tất cả mã hàng đều là hàng chính hãng, chuẩn chất lượng không cần lăn tăn.</p>
                </div>

                <!-- Share Section -->
                <div class="text-center py-4 border-top border-bottom my-4">
                    <div class="d-flex justify-content-center gap-3 flex-wrap">
                        <a href="#" class="btn btn-outline-primary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="Share on Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="btn btn-outline-info btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="Share on Twitter">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" class="btn btn-outline-secondary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="Share via Email">
                            <i class="bi bi-envelope"></i>
                        </a>
                        <a href="#" class="btn btn-outline-danger btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="Pin on Pinterest">
                            <i class="bi bi-pinterest"></i>
                        </a>
                        <a href="#" class="btn btn-outline-primary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;" title="Share on LinkedIn">
                            <i class="bi bi-linkedin"></i>
                        </a>
                    </div>
                </div>

                <!-- Related Articles -->
                <div class="mt-5 pt-4">
                    <h3 class="fw-bold mb-4">Bài viết liên quan</h3>
                    <div class="related-carousel-wrapper position-relative">
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
                <div class="bg-light p-4 p-md-5 rounded mt-5">
                    <h3 class="fw-bold mb-3">Để lại một bình luận</h3>
                    <p class="text-muted small mb-4">Email của bạn sẽ không được hiển thị công khai. Các trường bắt buộc được đánh dấu *</p>
                
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
                    
                    <form action="news-detail.php?slug=<?= htmlspecialchars($slug) ?>" method="post">
                        <div class="mb-3">
                            <label for="comment" class="form-label fw-semibold">Bình luận *</label>
                            <textarea class="form-control" id="comment" name="comment" rows="6" required><?= isset($_POST['comment']) ? htmlspecialchars($_POST['comment']) : '' ?></textarea>
                        </div>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="comment-name" class="form-label fw-semibold">Tên *</label>
                                <input type="text" class="form-control" id="comment-name" name="name" 
                                       value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="comment-email" class="form-label fw-semibold">Email *</label>
                                <input type="email" class="form-control" id="comment-email" name="email" 
                                       value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                            </div>
                            <div class="col-md-4">
                                <label for="comment-website" class="form-label fw-semibold">Trang web</label>
                                <input type="url" class="form-control" id="comment-website" name="website" 
                                       value="<?= isset($_POST['website']) ? htmlspecialchars($_POST['website']) : '' ?>">
                            </div>
                        </div>
                        
                        <div class="form-check mb-4">
                            <input class="form-check-input" type="checkbox" id="save-info" name="save_info" <?= isset($_POST['save_info']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="save-info">
                                Lưu tên của tôi, email, và trang web trong trình duyệt này cho lần bình luận kế tiếp của tôi.
                            </label>
                        </div>
                        
                        <button type="submit" name="submit_comment" class="btn btn-success text-uppercase fw-bold px-4">GỬI BÌNH LUẬN</button>
                    </form>
                </div>
            </article>

            <aside class="col-lg-3 order-lg-2">
                <div class="card border-0 mb-4">
                    <div class="card-header bg-success text-white fw-bold text-uppercase" style="font-size: 0.875rem;">
                        CHUYÊN MỤC TIN TỨC
                    </div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item"><a href="#" class="text-decoration-none text-dark">Thời trang &amp; cuộc sống</a></li>
                        <li class="list-group-item"><a href="#" class="text-decoration-none text-dark">Tin công nghệ</a></li>
                        <li class="list-group-item active"><a href="#" class="text-decoration-none text-white">Tin tức</a></li>
                        <li class="list-group-item"><a href="#" class="text-decoration-none text-dark">Tuyển dụng</a></li>
                    </ul>
                </div>

                <div class="card border-0">
                    <div class="card-header bg-success text-white fw-bold text-uppercase" style="font-size: 0.875rem;">
                        TIN TỨC MỚI NHẤT
                    </div>
                    <div class="card-body p-3">
                        <?php
                        if (empty($latestNews)) {
                            echo '<p class="text-muted mb-0 small">Chưa có tin tức nào.</p>';
                        } else {
                            foreach ($latestNews as $item): 
                                $itemImg = !empty($item['featured_image']) ? 'assets/images/' . $item['featured_image'] : 'assets/images/lesterblur__2.jpg';
                                ?>
                                <a href="news-detail.php?slug=<?= htmlspecialchars($item['slug']) ?>" class="d-flex gap-3 text-decoration-none text-dark mb-3 pb-3 border-bottom">
                                    <img src="<?= htmlspecialchars($itemImg) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="rounded" style="width: 64px; height: 64px; object-fit: cover;">
                                    <p class="mb-0 small"><?= htmlspecialchars($item['title']) ?></p>
                                </a>
                            <?php endforeach;
                        }
                        ?>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</main>

<?php 
// Đóng kết nối database
if (isset($conn)) {
    closeDB($conn);
}
include 'includes/footer.php'; 
?>

<script>
(function() {
    const carouselContainer = document.querySelector('.related-carousel-container');
    const carouselTrack = document.querySelector('.related-carousel-track');
    const cards = document.querySelectorAll('.related-article-card');
    const prevBtn = document.querySelector('.related-prev');
    const nextBtn = document.querySelector('.related-next');
    
    if (!carouselContainer || !carouselTrack) return;
    
    let isDown = false;
    let startX;
    let scrollLeft;
    let isScrolling = false;
    let scrollTimeout;
    let velocity = 0;
    let lastScrollLeft = 0;
    let lastTime = Date.now();
    
    // Tính toán scroll limits
    function getScrollLimits() {
        const trackWidth = carouselTrack.scrollWidth;
        const containerWidth = carouselContainer.offsetWidth;
        const maxScrollLeft = Math.max(0, trackWidth - containerWidth);
        return {
            min: 0,
            max: maxScrollLeft,
            trackWidth: trackWidth,
            containerWidth: containerWidth
        };
    }
    
    // Giới hạn scroll position
    function constrainScroll(scrollValue) {
        const limits = getScrollLimits();
        return Math.max(limits.min, Math.min(limits.max, scrollValue));
    }
    
    // Ngăn click khi đang scroll
    function setScrolling() {
        isScrolling = true;
        cards.forEach(card => card.classList.add('is-scrolling'));
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            isScrolling = false;
            cards.forEach(card => card.classList.remove('is-scrolling'));
        }, 150);
    }
    
    // Tính toán velocity cho momentum scrolling
    function updateVelocity() {
        const currentScrollLeft = carouselContainer.scrollLeft;
        const currentTime = Date.now();
        const timeDiff = currentTime - lastTime;
        
        if (timeDiff > 0) {
            velocity = (currentScrollLeft - lastScrollLeft) / timeDiff;
        }
        
        lastScrollLeft = currentScrollLeft;
        lastTime = currentTime;
    }
    
    // Momentum scrolling với giới hạn
    function applyMomentum() {
        if (Math.abs(velocity) > 0.1) {
            const friction = 0.95;
            velocity *= friction;
            const newScrollLeft = constrainScroll(carouselContainer.scrollLeft + velocity * 16);
            carouselContainer.scrollLeft = newScrollLeft;
            
            // Dừng momentum nếu đã đến giới hạn
            const limits = getScrollLimits();
            if ((newScrollLeft <= limits.min && velocity < 0) || 
                (newScrollLeft >= limits.max && velocity > 0)) {
                velocity = 0;
                return;
            }
            
            if (Math.abs(velocity) > 0.1) {
                requestAnimationFrame(applyMomentum);
            } else {
                velocity = 0;
            }
        }
    }
    
    // Mouse events
    carouselContainer.addEventListener('mousedown', (e) => {
        isDown = true;
        carouselContainer.style.cursor = 'grabbing';
        startX = e.pageX - carouselContainer.offsetLeft;
        scrollLeft = carouselContainer.scrollLeft;
        setScrolling();
        e.preventDefault();
    });
    
    carouselContainer.addEventListener('mouseleave', () => {
        isDown = false;
        carouselContainer.style.cursor = 'grab';
        if (Math.abs(velocity) > 0.1) {
            applyMomentum();
        }
    });
    
    carouselContainer.addEventListener('mouseup', () => {
        isDown = false;
        carouselContainer.style.cursor = 'grab';
        if (Math.abs(velocity) > 0.1) {
            applyMomentum();
        }
    });
    
    carouselContainer.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - carouselContainer.offsetLeft;
        const walk = (x - startX) * 1.5; // Tốc độ scroll
        const newScrollLeft = constrainScroll(scrollLeft - walk);
        carouselContainer.scrollLeft = newScrollLeft;
        updateVelocity();
        setScrolling();
    });
    
    // Touch events
    let touchStartX = 0;
    let touchScrollLeft = 0;
    
    carouselContainer.addEventListener('touchstart', (e) => {
        touchStartX = e.touches[0].pageX - carouselContainer.offsetLeft;
        touchScrollLeft = carouselContainer.scrollLeft;
        setScrolling();
    }, { passive: true });
    
    carouselContainer.addEventListener('touchmove', (e) => {
        const x = e.touches[0].pageX - carouselContainer.offsetLeft;
        const walk = (x - touchStartX) * 1.5;
        const newScrollLeft = constrainScroll(touchScrollLeft - walk);
        carouselContainer.scrollLeft = newScrollLeft;
        updateVelocity();
        setScrolling();
    }, { passive: true });
    
    carouselContainer.addEventListener('touchend', () => {
        if (Math.abs(velocity) > 0.1) {
            applyMomentum();
        }
    }, { passive: true });
    
    // Kiểm tra và cập nhật trạng thái nút điều hướng
    function updateNavButtons() {
        const limits = getScrollLimits();
        const currentScroll = carouselContainer.scrollLeft;
        const threshold = 5; // Ngưỡng để xác định đã đến đầu/cuối
        
        if (prevBtn) {
            if (currentScroll <= limits.min + threshold) {
                prevBtn.style.opacity = '0.5';
                prevBtn.style.pointerEvents = 'none';
            } else {
                prevBtn.style.opacity = '1';
                prevBtn.style.pointerEvents = 'auto';
            }
        }
        
        if (nextBtn) {
            if (currentScroll >= limits.max - threshold) {
                nextBtn.style.opacity = '0.5';
                nextBtn.style.pointerEvents = 'none';
            } else {
                nextBtn.style.opacity = '1';
                nextBtn.style.pointerEvents = 'auto';
            }
        }
    }
    
    // Scroll event để tính velocity và giới hạn scroll
    carouselContainer.addEventListener('scroll', () => {
        const limits = getScrollLimits();
        const currentScroll = carouselContainer.scrollLeft;
        
        // Giới hạn scroll nếu vượt quá
        if (currentScroll < limits.min) {
            carouselContainer.scrollLeft = limits.min;
        } else if (currentScroll > limits.max) {
            carouselContainer.scrollLeft = limits.max;
        }
        
        updateVelocity();
        setScrolling();
        updateNavButtons();
    }, { passive: true });
    
    // Wheel event cho mouse wheel scrolling mượt mà
    carouselContainer.addEventListener('wheel', (e) => {
        if (Math.abs(e.deltaY) < Math.abs(e.deltaX)) {
            e.preventDefault();
            const newScrollLeft = constrainScroll(carouselContainer.scrollLeft + e.deltaX * 0.5);
            carouselContainer.scrollLeft = newScrollLeft;
            setScrolling();
        }
    }, { passive: false });
    
    // Navigation buttons
    if (prevBtn) {
        prevBtn.addEventListener('click', () => {
            const limits = getScrollLimits();
            const cardWidth = cards[0]?.offsetWidth || 0;
            const gap = 24;
            const scrollAmount = cardWidth + gap;
            const newScrollLeft = constrainScroll(carouselContainer.scrollLeft - scrollAmount);
            
            carouselContainer.scrollTo({
                left: newScrollLeft,
                behavior: 'smooth'
            });
            setScrolling();
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', () => {
            const limits = getScrollLimits();
            const cardWidth = cards[0]?.offsetWidth || 0;
            const gap = 24;
            const scrollAmount = cardWidth + gap;
            const newScrollLeft = constrainScroll(carouselContainer.scrollLeft + scrollAmount);
            
            carouselContainer.scrollTo({
                left: newScrollLeft,
                behavior: 'smooth'
            });
            setScrolling();
        });
    }
    
    // Xử lý resize để tính lại limits
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            const limits = getScrollLimits();
            const currentScroll = carouselContainer.scrollLeft;
            carouselContainer.scrollLeft = constrainScroll(currentScroll);
            updateNavButtons();
        }, 250);
    });
    
    // Khởi tạo trạng thái nút điều hướng
    updateNavButtons();
    
    // Ngăn click khi đang scroll
    cards.forEach(card => {
        const link = card.querySelector('.related-card-link');
        if (link) {
            link.addEventListener('click', (e) => {
                if (isScrolling) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
        }
    });
})();
</script>

