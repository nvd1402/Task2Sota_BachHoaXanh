<?php
session_start();
$pageTitle = "Chi tiết tin tức - Bách Hóa Xanh";

// Lấy slug từ URL
$slug = $_GET['slug'] ?? '';

// Mock data - trong thực tế sẽ lấy từ database
$newsDetails = [
    '10-loai-rau-cu-qua-tot-cho-co-the' => [
        'title' => '10 loại rau củ quả tốt cho cơ thể',
        'date' => 'THÁNG 8 3, 2022',
        'author' => 'Admin',
        'content' => [
            '10 loại rau củ quả tốt cho cơ thể: Bí ngô (Bí đỏ):',
            'Bí ngô chứa nhiều sắt, kẽm, vitamin và khoáng chất, axit hữu cơ tốt cho sự phát triển não bộ, huyết áp cao, gan và giải độc.',
            'Rau ngót:',
            'Rau ngót là một loại rau rất giàu dinh dưỡng, đặc biệt là protein, canxi, sắt và vitamin C. Loại rau này có tác dụng thanh nhiệt, giải độc, lợi tiểu và hỗ trợ tiêu hóa.',
            'Rau ngót cũng rất tốt cho phụ nữ mang thai và sau sinh, giúp tăng cường sức đề kháng và bổ sung các dưỡng chất cần thiết cho cơ thể.',
            'Cà rốt:',
            'Cà rốt chứa nhiều beta-carotene, một chất chống oxy hóa mạnh giúp bảo vệ mắt, tăng cường hệ miễn dịch và làm đẹp da. Ngoài ra, cà rốt còn chứa nhiều vitamin A, C, K và các khoáng chất như kali, mangan.',
            'Cà chua:',
            'Cà chua là nguồn cung cấp lycopene dồi dào, một chất chống oxy hóa mạnh giúp giảm nguy cơ mắc các bệnh tim mạch và ung thư. Cà chua cũng chứa nhiều vitamin C, K và kali.',
            'Bông cải xanh:',
            'Bông cải xanh là một trong những loại rau giàu dinh dưỡng nhất, chứa nhiều vitamin C, K, A, folate và các chất chống oxy hóa. Loại rau này có tác dụng chống viêm, tăng cường hệ miễn dịch và hỗ trợ sức khỏe tim mạch.'
        ],
        'image' => 'assets/images/lesterblur__2.jpg'
    ],
    '10-loai-rau-cu-qua-tot-cho-co-the-2' => [
        'title' => '10 loại rau củ quả tốt cho cơ thể',
        'date' => 'THÁNG 7 15, 2022',
        'author' => 'Admin',
        'content' => [
            'Rau củ quả là nguồn cung cấp vitamin và khoáng chất quan trọng cho cơ thể. Dưới đây là 10 loại rau củ quả tốt cho sức khỏe mà bạn nên bổ sung vào chế độ ăn hàng ngày.',
            'Các loại rau củ quả này không chỉ cung cấp dinh dưỡng mà còn giúp tăng cường hệ miễn dịch, cải thiện sức khỏe tim mạch và hỗ trợ tiêu hóa.'
        ],
        'image' => 'assets/images/4.jpg'
    ],
    '10-loai-rau-cu-qua-tot-cho-co-the-3' => [
        'title' => '10 loại rau củ quả tốt cho cơ thể',
        'date' => 'THÁNG 6 20, 2022',
        'author' => 'Admin',
        'content' => [
            'Việc bổ sung rau củ quả vào chế độ ăn hàng ngày là rất quan trọng để duy trì sức khỏe tốt.',
            'Mỗi loại rau củ quả đều có những lợi ích riêng biệt và cung cấp các dưỡng chất cần thiết cho cơ thể.'
        ],
        'image' => 'assets/images/2.jpg'
    ]
];

$news = $newsDetails[$slug] ?? $newsDetails['10-loai-rau-cu-qua-tot-cho-co-the'];
$pageTitle = htmlspecialchars($news['title']) . " - Bách Hóa Xanh";

include 'includes/header.php';
?>

<main class="recruit-detail-page">
    <div class="container recruit-detail-layout">
        <!-- Nội dung chi tiết bài viết -->
        <article class="recruit-detail-content">
            <h1 class="recruit-detail-title"><?= htmlspecialchars($news['title']) ?></h1>
            
            <div class="recruit-detail-meta">
                <span class="recruit-meta-text">POSTED ON <?= htmlspecialchars($news['date']) ?> BY <?= htmlspecialchars($news['author']) ?></span>
            </div>

            <div class="recruit-detail-body">
                <?php foreach ($news['content'] as $paragraph): ?>
                    <p><?= htmlspecialchars($paragraph) ?></p>
                <?php endforeach; ?>
            </div>

            <div class="recruit-detail-image">
                <img src="<?= htmlspecialchars($news['image']) ?>" alt="<?= htmlspecialchars($news['title']) ?>">
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
                            $relatedArticles = [
                                [
                                    'title' => '10 loại rau củ quả tốt cho cơ thể',
                                    'date' => '03',
                                    'month' => 'Th8',
                                    'desc' => '10 loại rau củ quả tốt cho cơ thể: Bí ngô (Bí đỏ): – Được',
                                    'img' => 'assets/images/4.jpg',
                                    'slug' => '10-loai-rau-cu-qua-tot-cho-co-the-2'
                                ],
                                [
                                    'title' => 'Cách chọn rau củ quả tươi ngon',
                                    'date' => '05',
                                    'month' => 'Th8',
                                    'desc' => 'Hướng dẫn cách chọn rau củ quả tươi ngon, đảm bảo chất lượng và an toàn cho sức khỏe.',
                                    'img' => 'assets/images/2.jpg',
                                    'slug' => 'cach-chon-rau-cu-qua-tuoi-ngon'
                                ],
                                [
                                    'title' => 'Lợi ích của việc ăn rau xanh mỗi ngày',
                                    'date' => '07',
                                    'month' => 'Th8',
                                    'desc' => 'Rau xanh cung cấp nhiều vitamin và khoáng chất cần thiết cho cơ thể, giúp tăng cường sức đề kháng.',
                                    'img' => 'assets/images/5.jpg',
                                    'slug' => 'loi-ich-cua-viec-an-rau-xanh-moi-ngay'
                                ],
                                [
                                    'title' => 'Công thức nấu canh rau củ thơm ngon',
                                    'date' => '10',
                                    'month' => 'Th8',
                                    'desc' => 'Những công thức nấu canh rau củ đơn giản, dễ làm và giàu dinh dưỡng cho cả gia đình.',
                                    'img' => 'assets/images/4.jpg',
                                    'slug' => 'cong-thuc-nau-canh-rau-cu-thom-ngon'
                                ],
                                [
                                    'title' => 'Bảo quản rau củ quả đúng cách',
                                    'date' => '12',
                                    'month' => 'Th8',
                                    'desc' => 'Mẹo bảo quản rau củ quả để giữ được độ tươi ngon và dinh dưỡng lâu hơn.',
                                    'img' => 'assets/images/2.jpg',
                                    'slug' => 'bao-quan-rau-cu-qua-dung-cach'
                                ],
                                [
                                    'title' => 'Rau củ quả theo mùa - Lựa chọn thông minh',
                                    'date' => '15',
                                    'month' => 'Th8',
                                    'desc' => 'Tìm hiểu về các loại rau củ quả theo mùa để có được sản phẩm tươi ngon và giá tốt nhất.',
                                    'img' => 'assets/images/5.jpg',
                                    'slug' => 'rau-cu-qua-theo-mua-lua-chon-thong-minh'
                                ],
                                [
                                    'title' => 'Chế độ ăn uống lành mạnh với rau củ',
                                    'date' => '18',
                                    'month' => 'Th8',
                                    'desc' => 'Xây dựng chế độ ăn uống lành mạnh với rau củ quả để có sức khỏe tốt và vóc dáng đẹp.',
                                    'img' => 'assets/images/4.jpg',
                                    'slug' => 'che-do-an-uong-lanh-manh-voi-rau-cu'
                                ],
                                [
                                    'title' => 'Rau củ quả hữu cơ - Xu hướng mới',
                                    'date' => '20',
                                    'month' => 'Th8',
                                    'desc' => 'Tìm hiểu về rau củ quả hữu cơ và những lợi ích vượt trội so với sản phẩm thông thường.',
                                    'img' => 'assets/images/2.jpg',
                                    'slug' => 'rau-cu-qua-huu-co-xu-huong-moi'
                                ],
                                [
                                    'title' => 'Các loại nước ép rau củ tốt cho sức khỏe',
                                    'date' => '22',
                                    'month' => 'Th8',
                                    'desc' => 'Khám phá các loại nước ép rau củ giàu dinh dưỡng, tốt cho sức khỏe và làm đẹp da.',
                                    'img' => 'assets/images/5.jpg',
                                    'slug' => 'cac-loai-nuoc-ep-rau-cu-tot-cho-suc-khoe'
                                ],
                                [
                                    'title' => 'Rau củ quả cho người ăn kiêng',
                                    'date' => '25',
                                    'month' => 'Th8',
                                    'desc' => 'Danh sách các loại rau củ quả phù hợp cho người ăn kiêng, giảm cân hiệu quả.',
                                    'img' => 'assets/images/4.jpg',
                                    'slug' => 'rau-cu-qua-cho-nguoi-an-kieng'
                                ],
                            ];
                            // Loại trừ bài viết hiện tại
                            $relatedArticles = array_filter($relatedArticles, function($article) use ($slug) {
                                return $article['slug'] !== $slug;
                            });
                            $relatedArticles = array_values($relatedArticles);
                            // Hiển thị tất cả bài viết liên quan để test carousel
                            
                            foreach ($relatedArticles as $related): ?>
                                <article class="related-article-card">
                                    <a href="news-detail.php?slug=<?= htmlspecialchars($related['slug']) ?>" class="related-card-link">
                                        <div class="related-card-image">
                                            <div class="related-date-badge">
                                                <span class="related-date-day"><?= htmlspecialchars($related['date']) ?></span>
                                                <span class="related-date-month"><?= htmlspecialchars($related['month']) ?></span>
                                            </div>
                                            <img src="<?= htmlspecialchars($related['img']) ?>" alt="<?= htmlspecialchars($related['title']) ?>">
                                        </div>
                                        <div class="related-card-content">
                                            <h4 class="related-card-title"><?= htmlspecialchars($related['title']) ?></h4>
                                            <p class="related-card-desc"><?= htmlspecialchars($related['desc']) ?></p>
                                        </div>
                                    </a>
                                </article>
                            <?php endforeach; ?>
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
                    <li class="active"><a href="#">Tin tức</a></li>
                    <li><a href="#">Tuyển dụng</a></li>
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

