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

