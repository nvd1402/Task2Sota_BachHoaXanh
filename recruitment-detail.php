<?php
session_start();
$pageTitle = "Chi tiết tuyển dụng - Bách Hóa Xanh";

// Lấy slug từ URL
$slug = $_GET['slug'] ?? '';

// Mock data - trong thực tế sẽ lấy từ database
$jobDetails = [
    'tuyen-nhan-vien-marketing' => [
        'title' => 'Tuyển nhân viên marketing',
        'date' => 'THÁNG 6 22, 2022',
        'author' => 'Admin',
        'content' => [
            'Nếu trước kia thế giới chia thành 2 loại người là mua đồ tận nơi và mua đồ online thì giờ đây, mua đồ online đã trở thành xu hướng chủ đạo. Tuy nhiên, việc mua sắm online cũng có những mặt trái mà bạn cần lưu ý.',
            'Một trong những vấn đề lớn nhất khi mua sắm online là dễ bị "cuốn theo" và chi tiêu quá mức. Những chương trình khuyến mãi, giảm giá liên tục có thể khiến bạn mua những món đồ không thực sự cần thiết.',
            'Ngoài ra, việc đánh giá chất lượng sản phẩm qua hình ảnh cũng là một thách thức. Hình ảnh trên website có thể khác với thực tế, khiến bạn thất vọng khi nhận hàng.',
            'Để mua sắm online hiệu quả, bạn nên:',
            '• Lập danh sách mua sắm trước khi vào website',
            '• So sánh giá ở nhiều nơi khác nhau',
            '• Đọc kỹ các đánh giá và nhận xét từ khách hàng',
            '• Kiểm tra chính sách đổi trả và bảo hành',
            '• Chỉ mua từ những website uy tín và có chứng nhận bảo mật'
        ],
        'image' => 'assets/images/lesterblur__2.jpg'
    ],
    'tuyen-dung-nhan-vien-phu-trach-cua-hang' => [
        'title' => 'Tuyển dụng nhận viên phụ trách cửa hàng',
        'date' => 'THÁNG 5 15, 2025',
        'author' => 'Admin',
        'content' => [
            'Chúng tôi đang tìm kiếm một nhân viên phụ trách cửa hàng có kinh nghiệm và đam mê với ngành bán lẻ.',
            'Vị trí này đòi hỏi khả năng quản lý, giao tiếp tốt và tinh thần trách nhiệm cao.'
        ],
        'image' => 'assets/images/lesterblur__2.jpg'
    ],
    'tuyen-nhan-vien-ban-hang' => [
        'title' => 'Tuyển nhân viên bán hàng',
        'date' => 'THÁNG 5 8, 2025',
        'author' => 'Admin',
        'content' => [
            'Chúng tôi đang tuyển dụng nhân viên bán hàng với mức lương cạnh tranh và môi trường làm việc chuyên nghiệp.',
            'Ứng viên cần có kỹ năng giao tiếp tốt và tinh thần phục vụ khách hàng nhiệt tình.'
        ],
        'image' => 'assets/images/lesterblur__2.jpg'
    ]
];

$job = $jobDetails[$slug] ?? $jobDetails['tuyen-nhan-vien-marketing'];
$pageTitle = htmlspecialchars($job['title']) . " - Bách Hóa Xanh";

include 'includes/header.php';
?>

<main class="recruit-detail-page">
    <div class="container recruit-detail-layout">
        <!-- Nội dung chi tiết bài viết -->
        <article class="recruit-detail-content">
            <h1 class="recruit-detail-title"><?= htmlspecialchars($job['title']) ?></h1>
            
            <div class="recruit-detail-meta">
                <span class="recruit-meta-text">POSTED ON <?= htmlspecialchars($job['date']) ?> BY <?= htmlspecialchars($job['author']) ?></span>
            </div>

            <div class="recruit-detail-body">
                <?php foreach ($job['content'] as $paragraph): ?>
                    <p><?= htmlspecialchars($paragraph) ?></p>
                <?php endforeach; ?>
            </div>

            <div class="recruit-detail-image">
                <img src="<?= htmlspecialchars($job['image']) ?>" alt="<?= htmlspecialchars($job['title']) ?>">
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
                <div class="related-articles">
                    <?php
                    $relatedArticles = [
                        [
                            'title' => 'Tuyển dụng nhận viên phụ trách cửa hàng',
                            'date' => 'Tháng 6 22, 2022',
                            'desc' => 'Nếu trước kia thế giới chia thành 2 loại người là mua đồ tận nơi',
                            'slug' => 'tuyen-dung-nhan-vien-phu-trach-cua-hang'
                        ],
                        [
                            'title' => 'Tuyển nhân viên bán hàng',
                            'date' => 'Tháng 6 22, 2022',
                            'desc' => 'Nếu trước kia thế giới chia thành 2 loại người là mua đồ tận nơi',
                            'slug' => 'tuyen-nhan-vien-ban-hang'
                        ],
                    ];
                    // Loại trừ bài viết hiện tại
                    $relatedArticles = array_filter($relatedArticles, function($article) use ($slug) {
                        return $article['slug'] !== $slug;
                    });
                    $relatedArticles = array_values($relatedArticles);
                    $relatedArticles = array_slice($relatedArticles, 0, 2);
                    
                    foreach ($relatedArticles as $related): ?>
                        <article class="related-article-item">
                            <h4 class="related-article-title">
                                <a href="recruitment-detail.php?slug=<?= htmlspecialchars($related['slug']) ?>">
                                    <?= htmlspecialchars($related['title']) ?>
                                </a>
                            </h4>
                            <p class="related-article-date"><?= htmlspecialchars($related['date']) ?></p>
                            <p class="related-article-desc"><?= htmlspecialchars($related['desc']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Comment Form -->
            <div class="recruit-comment-section">
                <h3 class="comment-section-title">Để lại một bình luận</h3>
                <p class="comment-notice">Email của bạn sẽ không được hiển thị công khai. Các trường bắt buộc được đánh dấu *</p>
                
                <form class="comment-form" action="#" method="post">
                    <div class="form-group">
                        <label for="comment">Bình luận *</label>
                        <textarea id="comment" name="comment" rows="6" required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="comment-name">Tên *</label>
                            <input type="text" id="comment-name" name="name" required>
                        </div>
                        <div class="form-group">
                            <label for="comment-email">Email *</label>
                            <input type="email" id="comment-email" name="email" required>
                        </div>
                        <div class="form-group">
                            <label for="comment-website">Trang web</label>
                            <input type="url" id="comment-website" name="website">
                        </div>
                    </div>
                    
                    <div class="form-group checkbox-group">
                        <input type="checkbox" id="save-info" name="save_info">
                        <label for="save-info">Lưu tên của tôi, email, và trang web trong trình duyệt này cho lần bình luận kế tiếp của tôi.</label>
                    </div>
                    
                    <button type="submit" class="comment-submit-btn">GỬI BÌNH LUẬN</button>
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
                    $latest = [
                        ['title'=>'10 loại rau củ quả tốt cho cơ thể','img'=>'assets/images/4.jpg'],
                        ['title'=>'10 loại rau củ quả tốt cho cơ thể','img'=>'assets/images/2.jpg'],
                        ['title'=>'10 loại rau củ quả tốt cho cơ thể','img'=>'assets/images/2.jpg'],
                        ['title'=>'10 loại rau củ quả tốt cho cơ thể','img'=>'assets/images/4.jpg'],
                        ['title'=>'10 loại rau củ quả tốt cho cơ thể','img'=>'assets/images/5.jpg'],
                    ];
                    foreach ($latest as $item): ?>
                    <a href="#" class="latest-item">
                        <div class="latest-thumb">
                            <img src="<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                        </div>
                        <div class="latest-info">
                            <p><?= htmlspecialchars($item['title']) ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

