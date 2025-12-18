<?php
session_start();

$pageTitle = "Trang chủ - Bách Hóa Xanh";
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
            <?php
            $featured = [
                ['name' => 'Thực phẩm hữu cơ sạch', 'price' => '120,000₫ – 170,000₫', 'off' => '20%', 'img' => 'assets/images/1.jpg'],
                ['name' => 'Nước giải khát có gas', 'price' => '90,000₫ – 130,000₫', 'off' => '19%', 'img' => 'assets/images/2.jpg'],
                ['name' => 'Rau củ tươi sạch', 'price' => '90,000₫ – 130,000₫', 'off' => '19%', 'img' => 'assets/images/4.jpg'],
                ['name' => 'Bánh mì tươi', 'price' => '90,000₫ – 130,000₫', 'off' => '19%', 'img' => 'assets/images/5.jpg'],
                ['name' => 'Ngũ cốc dinh dưỡng', 'price' => '110,000₫ – 150,000₫', 'off' => '18%', 'img' => 'assets/images/1.jpg'],
                ['name' => 'Nho tươi nhập khẩu', 'price' => '95,000₫ – 140,000₫', 'off' => '15%', 'img' => 'assets/images/2.jpg'],
                ['name' => 'Rau củ mix salad', 'price' => '85,000₫ – 120,000₫', 'off' => '17%', 'img' => 'assets/images/4.jpg'],
                ['name' => 'Bánh ngọt cao cấp', 'price' => '100,000₫ – 160,000₫', 'off' => '22%', 'img' => 'assets/images/5.jpg'],
                ['name' => 'Nước ép trái cây', 'price' => '75,000₫ – 110,000₫', 'off' => '16%', 'img' => 'assets/images/1.jpg'],
                ['name' => 'Sữa chua trái cây', 'price' => '60,000₫ – 90,000₫', 'off' => '14%', 'img' => 'assets/images/2.jpg'],
            ];
            foreach ($featured as $item): ?>
                <a href="product-detail.php" class="featured-card">
                    <div class="featured-thumb">
                        <img src="assets/images/bg_sale.png" class="sale-badge" alt="Sale">
                        <span class="sale-text">-<?= htmlspecialchars($item['off']) ?></span>
                        <img src="<?= htmlspecialchars($item['img']) ?>" class="featured-product-img" alt="<?= htmlspecialchars($item['name']) ?>">
                    </div>
                    <div class="featured-body">
                        <p class="featured-name"><?= htmlspecialchars($item['name']) ?></p>
                        <p class="featured-price"><?= htmlspecialchars($item['price']) ?></p>
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
                <?php
                $products = [
                    ['name'=>'Thực phẩm hữu cơ sạch','price'=>'90,000₫ – 130,000₫','sale'=>'19%','img'=>'1.jpg'],
                    ['name'=>'Thực phẩm hữu cơ sạch','price'=>'90,000₫ – 130,000₫','sale'=>'19%','img'=>'2.jpg'],
                    ['name'=>'Thực phẩm hữu cơ sạch','price'=>'120,000₫ – 170,000₫','sale'=>'20%','img'=>'4.jpg'],
                    ['name'=>'Thực phẩm hữu cơ sạch','price'=>'120,000₫ – 170,000₫','sale'=>'20%','img'=>'5.jpg'],
                    ['name'=>'Thực phẩm hữu cơ sạch','price'=>'120,000₫ – 170,000₫','sale'=>'20%','img'=>'1.jpg'],
                    ['name'=>'Thực phẩm hữu cơ sạch','price'=>'120,000₫ – 170,000₫','sale'=>'20%','img'=>'2.jpg'],
                ];

                foreach ($products as $p):
                    ?>
                    <a href="product-detail.php" class="product-item">
                        <div class="product-thumb">
                            <img src="assets/images/bg_sale.png" class="sale-badge" alt="Sale">
                            <span class="sale-text">-<?= $p['sale'] ?></span>
                            <img src="assets/images/<?= $p['img'] ?>" class="product-img" alt="<?= $p['name'] ?>">
                        </div>
                        <p class="product-name"><?= $p['name'] ?></p>
                        <p class="product-price"><?= $p['price'] ?></p>
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

<!-- Newsletter Section -->
<section class="newsletter-section">
    <div class="newsletter-overlay"></div>
    <div class="container newsletter-content">
        <div class="newsletter-banner">
            <img src="assets/images/banner_newsletter.png" alt="Nhận khuyến mãi mới">
        </div>
        <form class="newsletter-form" action="#" method="post">
            <input type="email" name="email" placeholder="Địa chỉ email (*)" required>
            <button type="submit">Đăng ký</button>
        </form>
    </div>
    <div class="newsletter-bg" style="background-image: url('assets/images/thuxu-huong-am-thuc.jpg');"></div>
</section>

<?php include 'includes/footer.php'; ?>

