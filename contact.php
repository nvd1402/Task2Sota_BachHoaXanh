<?php
session_start();
$pageTitle = "Liên hệ - Bách Hóa Xanh";
include 'includes/header.php';
?>

<main class="contact-page">

    <!-- ================= MAP SECTION ================= -->
    <div class="contact-map-section">
        <div class="container">
            <div class="map-container">
                <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3725.727471746137!2d105.82941152189149!3d20.963456786090013!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ac5526be0f83%3A0x8bd2ffe68188acfd!2zQ2h1bmcgY8awIFZQNSBMaW5oIMSQw6Bt!5e0!3m2!1svi!2s!4v1766109793147!5m2!1svi!2s"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>

    <!-- ================= CONTACT CONTENT ================= -->
    <div class="container">
        <div class="contact-content">

            <!-- LEFT: INFO -->
            <div class="contact-info-col">
                <h2 class="contact-heading">GREEN FOOD</h2>

                <p class="contact-intro">
                    Mọi thắc mắc quý khách vui lòng liên hệ tới chúng tôi thông qua thông tin bên dưới
                    hoặc điền thông tin vào form bên cạnh. Chúng tôi sẽ phản hồi trong thời gian sớm nhất.
                </p>

                <div class="contact-details">
                    <div class="contact-item">
                        <i class="bi bi-geo-alt-fill contact-icon"></i>
                        <span class="contact-text">
                            Địa chỉ: Chung cư VP5 Linh Đàm, P. Hoàng Liệt, Q. Hoàng Mai, Hà Nội
                        </span>
                    </div>

                    <div class="contact-item">
                        <i class="bi bi-envelope-fill contact-icon"></i>
                        <span class="contact-text">Email: webdemo@gmail.com</span>
                    </div>

                    <div class="contact-item">
                        <i class="bi bi-telephone-fill contact-icon"></i>
                        <span class="contact-text">Điện thoại: 0387 969 xxx</span>
                    </div>
                </div>

                <div class="contact-badge">
                    <img src="assets/images/Untitled-7.png" alt="Đã thông báo Bộ Công Thương">
                </div>
            </div>

            <!-- RIGHT: FORM -->
            <div class="contact-form-col">
                <form class="contact-form" action="#" method="post">
                    <div class="form-group">
                        <input type="text" name="name" class="form-control" placeholder="Họ và tên..." required>
                    </div>

                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Địa chỉ email..." required>
                    </div>

                    <div class="form-group">
                        <input type="tel" name="phone" class="form-control" placeholder="Số điện thoại..." required>
                    </div>

                    <div class="form-group">
                        <textarea name="message" class="form-control" rows="6"
                                  placeholder="Nhập nội dung liên hệ..." required></textarea>
                    </div>

                    <button type="submit" class="contact-submit-btn">
                        GỬI LIÊN HỆ
                    </button>
                </form>
            </div>

        </div>
    </div>

</main>

<?php include 'includes/footer.php'; ?>
