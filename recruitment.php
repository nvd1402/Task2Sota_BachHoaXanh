<?php
session_start();
$pageTitle = "Tuyển dụng - Bách Hóa Xanh";
include 'includes/header.php';
?>

<section class="recruit-hero">
    <div class="recruit-hero-overlay"></div>
    <div class="container recruit-hero-content">
        <h1>Chuyên mục tuyển dụng</h1>
        <p>Trang chủ / Tuyển dụng</p>
    </div>
</section>

<main class="recruit-page">
    <div class="container recruit-layout">
        <div class="recruit-list">
            <?php
            $jobs = [
                [
                    'title' => 'Tuyển dụng nhận viên phụ trách cửa hàng',
                    'desc'  => 'Nếu trước kia thế giới chia thành 2 loại người là mua đồ tận nơi',
                    'date'  => '15/05/2025'
                ],
                [
                    'title' => 'Tuyển nhân viên marketing',
                    'desc'  => 'Nếu trước kia thế giới chia thành 2 loại người là mua đồ tận nơi',
                    'date'  => '12/05/2025'
                ],
                [
                    'title' => 'Tuyển nhân viên bán hàng',
                    'desc'  => 'Nếu trước kia thế giới chia thành 2 loại người là mua đồ tận nơi',
                    'date'  => '08/05/2025'
                ],
            ];
            foreach ($jobs as $job): ?>
                <article class="recruit-card">
                    <div class="recruit-card-body">
                        <h3><?= htmlspecialchars($job['title']) ?></h3>
                        <p><?= htmlspecialchars($job['desc']) ?></p>
                        <div class="recruit-meta">
                            <span class="recruit-date"><i class="bi bi-calendar-event"></i> <?= htmlspecialchars($job['date']) ?></span>
                            <a href="#" class="recruit-link">Xem chi tiết</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <aside class="recruit-sidebar">
            <div class="recruit-widget">
                <h4>Chuyên mục tuyển dụng</h4>
                <ul class="recruit-cats">
                    <li><a href="#">Thời trang &amp; cuộc sống</a></li>
                    <li><a href="#">Tin công nghệ</a></li>
                    <li><a href="#">Tin tức</a></li>
                    <li class="active"><a href="#">Tuyển dụng</a></li>
                </ul>
            </div>

            <div class="recruit-widget">
                <h4>Tin tức mới nhất</h4>
                <div class="recruit-latest">
                    <?php
                    $latest = [
                        ['title'=>'10 loại rau củ quả tốt cho cơ thể','img'=>'assets/images/4.jpg'],
                        ['title'=>'5 loại trái cây giàu vitamin C','img'=>'assets/images/2.jpg'],
                        ['title'=>'Bí quyết chọn thực phẩm sạch','img'=>'assets/images/1.jpg'],
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
