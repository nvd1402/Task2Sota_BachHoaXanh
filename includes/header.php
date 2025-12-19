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
<?php
$bodyClass = 'page-' . pathinfo($currentPage, PATHINFO_FILENAME);
?>
<body class="<?= htmlspecialchars($bodyClass) ?>">

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
        <?php
        // Kiểm tra đăng nhập
        $isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
        if ($isLoggedIn):
        ?>
        <div class="user-dropdown">
            <button type="button" class="user-btn" title="Hồ sơ" id="userDropdownBtn">
                <i class="bi bi-person-fill"></i>
            </button>
            <div class="user-dropdown-menu" id="userDropdownMenu">
                <div class="user-dropdown-header">
                    <strong><?= htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['username']) ?></strong>
                    <small><?= htmlspecialchars($_SESSION['email']) ?></small>
                </div>
                <div class="user-dropdown-divider"></div>
                <a href="profile.php" class="user-dropdown-item">
                    <i class="bi bi-person"></i> Hồ sơ của tôi
                </a>
                <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <a href="admin/index.php" class="user-dropdown-item">
                    <i class="bi bi-speedometer2"></i> Trang quản trị
                </a>
                <?php endif; ?>
                <div class="user-dropdown-divider"></div>
                <a href="logout.php" class="user-dropdown-item">
                    <i class="bi bi-box-arrow-right"></i> Đăng xuất
                </a>
            </div>
        </div>
        <?php else: ?>
        <a href="login.php" class="user-btn" title="Đăng nhập">
            <i class="bi bi-person-fill"></i>
        </a>
        <?php endif; ?>

    </div>
</header>

<!-- MENU -->
<nav class="main-nav">
    <div class="container nav-row">
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle d-lg-none" onclick="toggleMobileMenu()">
            <i class="bi bi-list"></i>
        </button>
        
        <div class="category-wrap">
            <div class="category-toggle">
                <i class="bi bi-list"></i> Danh mục sản phẩm
            </div>
            <!-- Category dropdown (mock data) -->
            <div class="category-dropdown">
                <div class="category-item">
                    <span>Rau – củ – quả</span>
                </div>
                <div class="category-item">
                    <span>Thịt – cá – trứng</span>
                </div>
                <div class="category-item">
                    <span>Mì – cháo – phở</span>
                </div>
                <div class="category-item">
                    <span>Đồ uống các loại</span>
                </div>
                <div class="category-item">
                    <span>Dầu ăn – Gia vị</span>
                </div>
                <div class="category-item">
                    <span>Đồ đông lạnh</span>
                </div>
                <div class="category-item">
                    <span>Thực phẩm chế biến</span>
                </div>
                <div class="category-item">
                    <span>Thực phẩm Tết</span>
                </div>
            </div>
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

<!-- Breadcrumb Banner - Chỉ hiển thị khi không phải trang chủ -->
<?php if ($currentPage != 'index.php'): 
    // Mapping tên trang (tiêu đề và breadcrumb)
    $pageTitles = [
        'about.php' => 'GIỚI THIỆU',
        'products.php' => 'SẢN PHẨM',
        'recruitment.php' => 'TUYỂN DỤNG',
        'news.php' => 'TIN TỨC',
        'contact.php' => 'LIÊN HỆ'
    ];
    $pageNames = [
        'about.php' => 'Giới thiệu',
        'products.php' => 'Sản phẩm',
        'recruitment.php' => 'Tuyển dụng',
        'news.php' => 'Tin tức',
        'contact.php' => 'Liên hệ'
    ];
    $currentPageTitle = $pageTitles[$currentPage] ?? 'TRANG';
    $currentPageName = $pageNames[$currentPage] ?? 'Trang';
?>
<div class="breadcrumb-banner">
    <div class="breadcrumb-overlay"></div>
    <div class="container">
        <h1 class="breadcrumb-title"><?= htmlspecialchars($currentPageTitle) ?></h1>
        <p class="breadcrumb-path">Trang chủ / <span><?= htmlspecialchars($currentPageName) ?></span></p>
    </div>
</div>
<?php endif; ?>

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
        const open = sidebar.classList.contains('active');
        document.body.style.overflow = open ? 'hidden' : '';
        document.body.classList.toggle('mobile-menu-open', open);
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
        document.body.classList.remove('mobile-menu-open');
    }
}
</script>
