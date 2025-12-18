<?php
session_start();
$pageTitle = "Tin tức - Bách Hóa Xanh";
include 'includes/header.php';
?>

<main class="news-page">
    <div class="container news-layout">
        <?php
        $newsArticles = [
            [
                'title' => '10 loại rau củ quả tốt cho cơ thể',
                'desc' => '10 loại rau củ quả tốt cho cơ thể: Bí ngô (Bí đỏ): – Được',
                'img' => 'assets/images/4.jpg',
                'slug' => '10-loai-rau-cu-qua-tot-cho-co-the'
            ],
            [
                'title' => '10 loại rau củ quả tốt cho cơ thể',
                'desc' => '10 loại rau củ quả tốt cho cơ thể: Bí ngô (Bí đỏ): – Được',
                'img' => 'assets/images/2.jpg',
                'slug' => '10-loai-rau-cu-qua-tot-cho-co-the-2'
            ],
            [
                'title' => '10 loại rau củ quả tốt cho cơ thể',
                'desc' => '10 loại rau củ quả tốt cho cơ thể: Bí ngô (Bí đỏ): – Được',
                'img' => 'assets/images/2.jpg',
                'slug' => '10-loai-rau-cu-qua-tot-cho-co-the-3'
            ],
            [
                'title' => '10 loại rau củ quả tốt cho cơ thể',
                'desc' => '10 loại rau củ quả tốt cho cơ thể: Bí ngô (Bí đỏ): – Được',
                'img' => 'assets/images/4.jpg',
                'slug' => '10-loai-rau-cu-qua-tot-cho-co-the-4'
            ],
            [
                'title' => '10 loại rau củ quả tốt cho cơ thể',
                'desc' => '10 loại rau củ quả tốt cho cơ thể: Bí ngô (Bí đỏ): – Được',
                'img' => 'assets/images/5.jpg',
                'slug' => '10-loai-rau-cu-qua-tot-cho-co-the-5'
            ],
            [
                'title' => '10 loại rau củ quả tốt cho cơ thể',
                'desc' => '10 loại rau củ quả tốt cho cơ thể: Bí ngô (Bí đỏ): – Được',
                'img' => 'assets/images/4.jpg',
                'slug' => '10-loai-rau-cu-qua-tot-cho-co-the-6'
            ],
            [
                'title' => '10 loại rau củ quả tốt cho cơ thể',
                'desc' => '10 loại rau củ quả tốt cho cơ thể: Bí ngô (Bí đỏ): – Được',
                'img' => 'assets/images/2.jpg',
                'slug' => '10-loai-rau-cu-qua-tot-cho-co-the-7'
            ],
            [
                'title' => '10 loại rau củ quả tốt cho cơ thể',
                'desc' => '10 loại rau củ quả tốt cho cơ thể: Bí ngô (Bí đỏ): – Được',
                'img' => 'assets/images/2.jpg',
                'slug' => '10-loai-rau-cu-qua-tot-cho-co-the-8'
            ],
            [
                'title' => '10 loại rau củ quả tốt cho cơ thể',
                'desc' => '10 loại rau củ quả tốt cho cơ thể: Bí ngô (Bí đỏ): – Được',
                'img' => 'assets/images/4.jpg',
                'slug' => '10-loai-rau-cu-qua-tot-cho-co-the-9'
            ],
        ];
        
        // Chia thành 3 cột, mỗi cột 3 bài
        $columns = array_chunk($newsArticles, 3);
        
        foreach ($columns as $column): ?>
            <div class="news-column">
                <?php foreach ($column as $article): ?>
                    <a href="news-detail.php?slug=<?= htmlspecialchars($article['slug']) ?>" class="news-card">
                        <div class="news-card-thumb">
                            <img src="<?= htmlspecialchars($article['img']) ?>" alt="<?= htmlspecialchars($article['title']) ?>">
                        </div>
                        <div class="news-card-body">
                            <h3 class="news-card-title"><?= htmlspecialchars($article['title']) ?></h3>
                            <p class="news-card-desc"><?= htmlspecialchars($article['desc']) ?></p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

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
        
        <!-- Pagination - Căn giữa 3 cột tin tức -->
        <div class="news-pagination-col">
            <div class="pagination-wrapper">
                <a href="#" class="page-btn active">1</a>
                <a href="#" class="page-btn">2</a>
                <a href="#" class="page-btn">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>



