<?php
session_start();
require_once 'config/database.php';

$pageTitle = "Trang chủ - Bách Hóa Xanh";

// Kết nối database
$conn = connectDB();

// Lấy sản phẩm nổi bật (featured)
$featuredProducts = [];
$sqlFeatured = "
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c
      ON c.name = p.category OR c.slug = p.category
    WHERE p.status = 'active' AND p.featured = 1
    ORDER BY p.created_at DESC
    LIMIT 10
";
if ($result = $conn->query($sqlFeatured)) {
    while ($row = $result->fetch_assoc()) {
        $featuredProducts[] = $row;
    }
    $result->free();
}

// Nếu chưa có sản phẩm nổi bật, fallback lấy sản phẩm mới nhất
if (empty($featuredProducts)) {
    $sqlLatest = "
        SELECT p.*, c.name AS category_name
        FROM products p
        LEFT JOIN categories c
          ON c.name = p.category OR c.slug = p.category
        WHERE p.status = 'active'
        ORDER BY p.created_at DESC
        LIMIT 10
    ";
    if ($result = $conn->query($sqlLatest)) {
        while ($row = $result->fetch_assoc()) {
            $featuredProducts[] = $row;
        }
        $result->free();
    }
}

// Sản phẩm cho block RAU – CỦ – QUẢ (6 sản phẩm mới nhất)
$blockProducts = [];
$sqlBlock = "
    SELECT p.*, c.name AS category_name
    FROM products p
    LEFT JOIN categories c
      ON c.name = p.category OR c.slug = p.category
    WHERE p.status = 'active'
    ORDER BY p.created_at DESC
    LIMIT 6
";
if ($result = $conn->query($sqlBlock)) {
    while ($row = $result->fetch_assoc()) {
        $blockProducts[] = $row;
    }
    $result->free();
}

include 'includes/header.php';
?>

<!-- Banner Carousel -->
<div class="hero-carousel my-4">
    <div id="heroCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="assets/images/banner1.jpg" class="d-block w-100" alt="Banner 1">
            </div>
            <div class="carousel-item">
                <img src="assets/images/banner3.jpg" class="d-block w-100" alt="Banner 3">
            </div>
            <div class="carousel-item">
                <img src="assets/images/banner4.jpg" class="d-block w-100" alt="Banner 4">
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
</div>

<!-- Sản phẩm nổi bật -->
<section class="featured-section">
    <div class="container featured-container">
        <div class="featured-header">
            <div class="featured-label">
                <img src="assets/images/icon_hotsale.png" alt="Hot">
            </div>
            <h2 class="featured-title">Sản phẩm nổi bật</h2>
            <div class="featured-nav">
                <button id="featuredPrev" class="featured-nav-btn" type="button" aria-label="Prev">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button id="featuredNext" class="featured-nav-btn" type="button" aria-label="Next">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>
        <div class="featured-grid">
            <div id="featuredTrack" class="featured-track">
            <?php foreach ($featuredProducts as $item): ?>
                <?php
                $price     = (float)$item['price'];
                $salePrice = isset($item['sale_price']) ? (float)$item['sale_price'] : 0;
                $hasSale   = $salePrice > 0 && $salePrice < $price;
                $offPercent = $hasSale ? round(100 - ($salePrice / $price) * 100) : 0;
                $imgPath   = !empty($item['image']) ? 'assets/images/' . $item['image'] : 'assets/images/1.jpg';

                // Giá hiển thị từ khoảng price_min / price_max, fallback về price
                $displayMin = isset($item['price_min']) && $item['price_min'] > 0 ? (float)$item['price_min'] : $price;
                $displayMax = isset($item['price_max']) && $item['price_max'] > 0 ? (float)$item['price_max'] : ($hasSale ? $salePrice : $price);
                ?>
                <a href="product-detail.php?id=<?= (int)$item['id'] ?>" class="featured-card">
                    <div class="featured-thumb">
                        <?php if ($hasSale): ?>
                        <img src="assets/images/bg_sale.png" class="sale-badge" alt="Sale">
                        <span class="sale-text">-<?= $offPercent ?>%</span>
                        <?php endif; ?>
                        <img src="<?= htmlspecialchars($imgPath) ?>" class="featured-product-img" alt="<?= htmlspecialchars($item['name']) ?>">
                    </div>
                    <div class="featured-body">
                        <p class="featured-name"><?= htmlspecialchars($item['name']) ?></p>
                        <p class="featured-price">
                            <?= number_format($displayMin, 0, ',', '.') ?>₫ – <?= number_format($displayMax, 0, ',', '.') ?>₫
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- Banner GrabKitchen -->
<section class="gk-banner">
    <div class="container">
        <img src="assets/images/Banner-GK.png" alt="GrabKitchen" class="img-fluid gk-banner-img">
    </div>
</section>

<!-- ===== DANH MỤC RAU – CỦ – QUẢ ===== -->
<section class="category-block">
    <div class="container">

        <!-- HEADER -->
        <div class="category-block-header">
            <div class="left">
                <img src="assets/images/icon_ns.png" alt="Icon">
                <h3>RAU – CỦ – QUẢ</h3>
            </div>
            <div class="right">
                <a href="#">Rau</a>
                <a href="#">Củ</a>
                <a href="#">Quả</a>
                <a href="#" class="view-all">Xem tất cả ›</a>
            </div>
        </div>

        <!-- CONTENT -->
        <div class="category-block-body">

            <!-- BANNER TRÁI -->
            <div class="category-banner">
                <img src="assets/images/banner_prduct3.png" alt="Banner trái cây">
            </div>

            <!-- GRID SẢN PHẨM -->
            <div class="category-products">
                <?php foreach ($blockProducts as $p): ?>
                    <?php
                    $price     = (float)$p['price'];
                    $salePrice = isset($p['sale_price']) ? (float)$p['sale_price'] : 0;
                    $hasSale   = $salePrice > 0 && $salePrice < $price;
                    $offPercent = $hasSale ? round(100 - ($salePrice / $price) * 100) : 0;
                    $imgPath   = !empty($p['image']) ? 'assets/images/' . $p['image'] : 'assets/images/1.jpg';

                    $displayMin = isset($p['price_min']) && $p['price_min'] > 0 ? (float)$p['price_min'] : $price;
                    $displayMax = isset($p['price_max']) && $p['price_max'] > 0 ? (float)$p['price_max'] : ($hasSale ? $salePrice : $price);
                    ?>
                    <a href="product-detail.php?id=<?= (int)$p['id'] ?>" class="product-item">
                        <div class="product-thumb">
                            <?php if ($hasSale): ?>
                            <img src="assets/images/bg_sale.png" class="sale-badge" alt="Sale">
                            <span class="sale-text">-<?= $offPercent ?>%</span>
                            <?php endif; ?>
                            <img src="<?= htmlspecialchars($imgPath) ?>" class="product-img" alt="<?= htmlspecialchars($p['name']) ?>">
                        </div>
                        <p class="product-name"><?= htmlspecialchars($p['name']) ?></p>
                        <p class="product-price">
                            <?= number_format($displayMin, 0, ',', '.') ?>₫ – <?= number_format($displayMax, 0, ',', '.') ?>₫
                        </p>
                    </a>
                <?php endforeach; ?>
            </div>

        </div>
    </div>
</section>

<!-- ===== TIN TỨC ===== -->
<section class="news-block">
    <div class="container">

        <!-- HEADER -->
        <div class="news-header">
            <div class="left">
                <img src="assets/images/icon_ns.png" alt="Icon">
                <h3>TIN TỨC</h3>
            </div>
            <div class="right">
                <button class="news-nav prev" id="newsPrev">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="news-nav next" id="newsNext">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>

        <!-- CAROUSEL -->
        <div class="news-carousel">
            <div class="news-track" id="newsTrack">
                <?php
                // Hiện tại tin tức vẫn dùng dữ liệu mẫu tĩnh
                $news = [
                    ['title'=>'10 loại rau củ quả tốt cho cơ thể','date'=>'Tháng 8 3, 2022'],
                    ['title'=>'10 loại rau củ quả tốt cho cơ thể','date'=>'Tháng 8 3, 2022'],
                    ['title'=>'10 loại rau củ quả tốt cho cơ thể','date'=>'Tháng 8 3, 2022'],
                    ['title'=>'10 loại rau củ quả tốt cho cơ thể','date'=>'Tháng 8 3, 2022'],
                    ['title'=>'10 loại rau củ quả tốt cho cơ thể','date'=>'Tháng 8 3, 2022'],
                    ['title'=>'10 loại rau củ quả tốt cho cơ thể','date'=>'Tháng 8 3, 2022'],
                ];

                foreach ($news as $n):
                    ?>
                    <div class="news-card">
                        <div class="news-thumb">
                            <img src="assets/images/lesterblur__2.jpg" alt="<?= $n['title'] ?>">
                        </div>
                        <h4 class="news-title"><?= $n['title'] ?></h4>
                        <p class="news-date"><?= $n['date'] ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</section>

<?php
// Đóng kết nối database
if (isset($conn)) {
    closeDB($conn);
}

include 'includes/footer.php';
?>
