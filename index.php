<?php
session_start();

$pageTitle = "Trang chủ - Bách Hóa Xanh";
include 'includes/header.php';
?>

<div class="container my-5">
    <h1 class="text-center">Chào mừng đến với Bách Hóa Xanh</h1>
    <p class="text-center text-muted">Bắt đầu code tại đây...</p>
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

