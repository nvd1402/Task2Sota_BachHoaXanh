<?php
session_start();
$pageTitle = "Giới thiệu - Bách Hóa Xanh";
include 'includes/header.php';
?>

<!-- ===== HERO GIỚI THIỆU ===== -->
<section class="about-hero" style="background-image:url('assets/images/banner3.jpg')">
    <div class="about-hero-overlay"></div>
    <div class="container">
        <h1>Trang giới thiệu</h1>
        <p>Trang chủ / <span>Giới thiệu</span></p>
    </div>
</section>

<!-- ===== NỘI DUNG ===== -->
<main class="about-wrapper">
    <div class="container">

        <p class="intro-text">
            <strong>Thương mại điện tử (E-Commerce)</strong> là hình thái hoạt động kinh doanh bằng các phương pháp điện tử;
            là việc trao đổi <strong>“thông tin”</strong> kinh doanh thông qua các phương tiện công nghệ điện tử.
        </p>

        <h2>1. Khái niệm Thương mại điện tử</h2>
        <p>
            Công nghệ tiên tiến hiện nay giúp doanh nghiệp biến Website của mình thành những siêu thị hàng hóa trên Internet,
            biến người mua thực sự trở thành những người chủ với toàn quyền trong việc lựa chọn sản phẩm, tìm kiếm thông tin,
            so sánh giá cả, đặt mua hàng và thanh toán tự động.
        </p>

        <!-- ẢNH TRONG NỘI DUNG (DÙNG CHUNG banner3.jpg) -->
        <div class="about-image">
            <img src="assets/images/banner3.jpg" alt="Giới thiệu thương mại điện tử">
        </div>

        <p>
            Thương mại điện tử không chỉ là bán hàng trên Internet mà còn bao gồm toàn bộ hoạt động kinh doanh như giao dịch,
            mua bán, thanh toán, đặt hàng, quảng cáo và giao hàng. Các phương tiện điện tử bao gồm Internet, điện thoại,
            máy FAX, truyền hình và hệ thống thanh toán điện tử.
        </p>

        <h2>2. Ưu điểm của Website Thương mại điện tử</h2>
        <ul class="about-list">
            <li>Tiếp cận khách hàng toàn cầu</li>
            <li>Giảm chi phí vận hành</li>
            <li>Kinh doanh 24/7</li>
            <li>Tự động hóa quy trình bán hàng</li>
            <li>Cập nhật giá và thông tin tức thời</li>
        </ul>

        <div class="about-quote">
            “Website Thương mại điện tử cho phép bạn kinh doanh ngay cả khi đang ngủ.”
        </div>

    </div>
</main>

<?php include 'includes/footer.php'; ?>
