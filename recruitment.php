<?php
session_start();
$pageTitle = "Tuyển dụng - Bách Hóa Xanh";
include 'includes/header.php';
?>

<main class="recruit-page">
    <div class="container recruit-layout">
        <?php
        $jobs = [
            [
                'title' => 'Tuyển dụng nhận viên phụ trách cửa hàng',
                'desc'  => 'Nếu trước kia thế giới chia thành 2 loại người là mua đồ tận nơi',
                'slug'  => 'tuyen-dung-nhan-vien-phu-trach-cua-hang'
            ],
            [
                'title' => 'Tuyển nhân viên marketing',
                'desc'  => 'Nếu trước kia thế giới chia thành 2 loại người là mua đồ tận nơi',
                'slug'  => 'tuyen-nhan-vien-marketing'
            ],
            [
                'title' => 'Tuyển nhân viên bán hàng',
                'desc'  => 'Nếu trước kia thế giới chia thành 2 loại người là mua đồ tận nơi',
                'slug'  => 'tuyen-nhan-vien-ban-hang'
            ],
        ];
        foreach ($jobs as $job): ?>
            <a href="recruitment-detail.php?slug=<?= htmlspecialchars($job['slug']) ?>" class="recruit-card">
                <div class="recruit-card-content">
                    <span class="recruit-title"><?= htmlspecialchars($job['title']) ?></span>
                    <span class="recruit-desc"><?= htmlspecialchars($job['desc']) ?></span>
                </div>
            </a>
        <?php endforeach; ?>

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
