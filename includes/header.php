<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Lấy số lượng giỏ hàng từ database
$cartCount = 0;
if (file_exists('config/database.php')) {
    require_once 'config/database.php';
    // Tạo connection riêng để không ảnh hưởng đến connection chính
    $cartConn = connectDB();
    
    $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    $sessionId = session_id();
    
    if ($userId) {
        $countSql = "SELECT COUNT(*) as total FROM cart WHERE user_id = ?";
        $countStmt = $cartConn->prepare($countSql);
        $countStmt->bind_param("i", $userId);
    } else {
        $countSql = "SELECT COUNT(*) as total FROM cart WHERE session_id = ? AND user_id IS NULL";
        $countStmt = $cartConn->prepare($countSql);
        $countStmt->bind_param("s", $sessionId);
    }
    $countStmt->execute();
    $countResult = $countStmt->get_result();
    $cartCount = (int)($countResult->fetch_assoc()['total'] ?? 0);
    $countStmt->close();
    // Đóng connection riêng này
    closeDB($cartConn);
} else {
    // Fallback: lấy từ session nếu không có database
    if (!empty($_SESSION['cart'])) {
        // Đếm số loại sản phẩm khác nhau (số phần tử trong mảng)
        $cartCount = count($_SESSION['cart']);
    }
}

$pageTitle = $pageTitle ?? 'Bách Hóa Xanh';

// Lấy danh mục cha từ database để hiển thị trong sidebar
$parentCategories = [];
if (file_exists('config/database.php') && file_exists('includes/functions.php')) {
    require_once 'config/database.php';
    require_once 'includes/functions.php';
    $categoryConn = connectDB();
    $parentCategories = getParentCategories($categoryConn);
    closeDB($categoryConn);
}

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

    <!-- Google Fonts - Nunito -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/style.css">
    
    <!-- Global Font Style -->
    <style>
        * {
            font-family: 'Nunito', sans-serif !important;
        }
    </style>
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
            <!-- Category dropdown (lấy từ database) -->
            <div class="category-dropdown">
                <?php if (!empty($parentCategories)): ?>
                    <?php foreach ($parentCategories as $parentCat): ?>
                        <div class="category-item">
                            <a href="products.php?category=<?= (int)$parentCat['id'] ?>" style="text-decoration: none; color: inherit; display: block;">
                                <span><?= htmlspecialchars($parentCat['name']) ?></span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback nếu không có danh mục trong database -->
                    <div class="category-item">
                        <span>Chưa có danh mục</span>
                    </div>
                <?php endif; ?>
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
        'news-detail.php' => 'CHI TIẾT TIN TỨC',
        'contact.php' => 'LIÊN HỆ'
    ];
    $pageNames = [
        'about.php' => 'Giới thiệu',
        'products.php' => 'Sản phẩm',
        'recruitment.php' => 'Tuyển dụng',
        'news.php' => 'Tin tức',
        'news-detail.php' => 'Tin tức',
        'contact.php' => 'Liên hệ'
    ];
    
    // Xử lý đặc biệt cho trang chi tiết tin tức
    $breadcrumbPath = '';
    $breadcrumbTitle = $pageTitles[$currentPage] ?? 'TRANG';
    
    if ($currentPage == 'news-detail.php') {
        // Lấy thông tin tin tức từ URL nếu chưa có
        if (!isset($news) || empty($news)) {
            $slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
            if (!empty($slug)) {
                require_once __DIR__ . '/../config/database.php';
                require_once __DIR__ . '/functions.php';
                $tempConn = connectDB();
                $news = getNewsBySlug($tempConn, $slug);
                closeDB($tempConn);
            }
        }
        
        if (isset($news) && !empty($news['title'])) {
            // Trang chi tiết tin tức: Trang chủ / Tin tức / Tiêu đề bài tin tức
            $newsTitle = htmlspecialchars($news['title']);
            $breadcrumbPath = 'Trang chủ / <a href="news.php" style="color: inherit; text-decoration: none;">Tin tức</a> / <span>' . $newsTitle . '</span>';
            $breadcrumbTitle = 'CHI TIẾT TIN TỨC';
        } else {
            // Fallback nếu không tìm thấy tin tức
            $breadcrumbPath = 'Trang chủ / <span>Tin tức</span>';
        }
    } else {
        // Các trang khác: Trang chủ / Tên trang
        $currentPageName = $pageNames[$currentPage] ?? 'Trang';
        $breadcrumbPath = 'Trang chủ / <span>' . htmlspecialchars($currentPageName) . '</span>';
    }
?>
<div class="breadcrumb-banner">
    <div class="breadcrumb-overlay"></div>
    <div class="container">
        <h1 class="breadcrumb-title"><?= htmlspecialchars($breadcrumbTitle) ?></h1>
        <p class="breadcrumb-path"><?= $breadcrumbPath ?></p>
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
