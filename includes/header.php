<?php
if (session_status() === PHP_SESSION_NONE) session_start();

$cartCount = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['quantity'];
    }
}

$pageTitle = $pageTitle ?? 'Bách Hóa Xanh';

// Xác định trang hiện tại - đơn giản và chính xác
$currentPage = basename($_SERVER['PHP_SELF']);

// Xử lý trường hợp trang chủ
if (empty($currentPage) || $currentPage == '/' || $currentPage == 'index.php') {
    $currentPage = 'index.php';
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- FLASH SALE -->
<div class="flash-sale-bar">
    <div class="container d-flex justify-content-between align-items-center">
        <span><strong>FLASH SALE</strong> – GIẢM GIÁ THẢ GA 50%</span>
        <a href="#" class="flash-sale-btn">Xem ngay</a>
    </div>
</div>

<!-- HEADER -->
<header class="header-main">
    <div class="container header-row">

        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle d-lg-none" onclick="toggleMobileMenu()">
            <i class="bi bi-list"></i>
        </button>

        <!-- LOGO -->
        <a href="index.php" class="logo">
            <img src="assets/images/logo.png" alt="GREEN">
        </a>

        <!-- SEARCH -->
        <form class="search-box" action="search.php">
            <input type="text" name="q" placeholder="Search...">
            <button><i class="bi bi-search"></i></button>
        </form>

        <!-- SUPPORT -->
        <div class="support">
            <div class="support-icon">
                <img src="assets/images/phone-2-100x100-1.png" alt="Phone">
            </div>
            <div class="support-text">
                <small>Hỗ trợ khách hàng</small>
                <strong>0988382xxx</strong>
            </div>
        </div>

        <!-- CART -->
        <a href="cart.php" class="cart-btn">
            <span class="cart-btn-text">CART</span>
            <img src="assets/images/c.png" alt="Cart" class="cart-icon-img">
            <?php if ($cartCount > 0): ?>
                <span class="cart-badge"><?= $cartCount ?></span>
            <?php endif; ?>
        </a>
        
        <!-- CART ICON (Mobile only) -->
        <a href="cart.php" class="cart-icon-btn d-lg-none">
            <img src="assets/images/c.png" alt="Cart">
            <?php if ($cartCount > 0): ?>
                <span class="cart-badge"><?= $cartCount ?></span>
            <?php endif; ?>
        </a>

        <!-- USER -->
        <a href="login.php" class="user-btn">
            <i class="bi bi-person-fill"></i>
        </a>

    </div>
</header>

<!-- MENU -->
<nav class="main-nav">
    <div class="container nav-row">
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle d-lg-none" onclick="toggleMobileMenu()">
            <i class="bi bi-list"></i>
        </button>
        
        <div class="category-toggle">
            <i class="bi bi-list"></i> Danh mục sản phẩm
        </div>
        
        <ul class="nav-menu">
            <li><a href="index.php" class="<?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">TRANG CHỦ</a></li>
            <li><a href="about.php" class="<?php echo ($currentPage == 'about.php') ? 'active' : ''; ?>">GIỚI THIỆU</a></li>
            <li><a href="products.php" class="<?php echo ($currentPage == 'products.php') ? 'active' : ''; ?>">SẢN PHẨM</a></li>
            <li><a href="recruitment.php" class="<?php echo ($currentPage == 'recruitment.php') ? 'active' : ''; ?>">TUYỂN DỤNG</a></li>
            <li><a href="news.php" class="<?php echo ($currentPage == 'news.php') ? 'active' : ''; ?>">TIN TỨC</a></li>
            <li><a href="contact.php" class="<?php echo ($currentPage == 'contact.php') ? 'active' : ''; ?>">LIÊN HỆ</a></li>
        </ul>
    </div>
</nav>

<!-- Overlay -->
<div class="menu-overlay" onclick="closeMobileMenu()"></div>

<!-- Close Button - Ngoài sidebar -->
<button class="mobile-menu-close" onclick="closeMobileMenu()">
    <i class="bi bi-x-lg"></i>
</button>

<!-- Mobile Menu Sidebar -->
<div class="mobile-menu-sidebar">
    <!-- Search Bar -->
    <form class="mobile-menu-search" action="search.php">
        <input type="text" name="q" placeholder="Search...">
        <button type="submit"><i class="bi bi-search"></i></button>
    </form>
    
    <!-- Menu Items -->
    <ul class="nav-menu">
        <li><a href="index.php" class="<?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>" onclick="closeMobileMenu()">TRANG CHỦ</a></li>
        <li><a href="about.php" class="<?php echo ($currentPage == 'about.php') ? 'active' : ''; ?>" onclick="closeMobileMenu()">GIỚI THIỆU</a></li>
        <li><a href="products.php" class="<?php echo ($currentPage == 'products.php') ? 'active' : ''; ?>" onclick="closeMobileMenu()">SẢN PHẨM</a></li>
        <li><a href="recruitment.php" class="<?php echo ($currentPage == 'recruitment.php') ? 'active' : ''; ?>" onclick="closeMobileMenu()">TUYỂN DỤNG</a></li>
        <li><a href="news.php" class="<?php echo ($currentPage == 'news.php') ? 'active' : ''; ?>" onclick="closeMobileMenu()">TIN TỨC</a></li>
        <li><a href="contact.php" class="<?php echo ($currentPage == 'contact.php') ? 'active' : ''; ?>" onclick="closeMobileMenu()">LIÊN HỆ</a></li>
    </ul>
</div>

<script>
function toggleMobileMenu() {
    const sidebar = document.querySelector('.mobile-menu-sidebar');
    const overlay = document.querySelector('.menu-overlay');
    const closeBtn = document.querySelector('.mobile-menu-close');
    if (sidebar && overlay && closeBtn) {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
        closeBtn.classList.toggle('show');
        document.body.style.overflow = sidebar.classList.contains('active') ? 'hidden' : '';
    }
}

function closeMobileMenu() {
    const sidebar = document.querySelector('.mobile-menu-sidebar');
    const overlay = document.querySelector('.menu-overlay');
    const closeBtn = document.querySelector('.mobile-menu-close');
    if (sidebar && overlay && closeBtn) {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
        closeBtn.classList.remove('show');
        document.body.style.overflow = '';
    }
}
</script>
