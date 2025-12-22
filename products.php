<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = "Sản phẩm - Bách Hóa Xanh";

// Kết nối database
$conn = connectDB();

// Lấy các tham số từ URL
$category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$price_filter = isset($_GET['price']) ? trim($_GET['price']) : '';
$brand_id = isset($_GET['brand']) ? (int)$_GET['brand'] : null;
$size_id = isset($_GET['size']) ? (int)$_GET['size'] : null;
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'default';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;

// Xử lý filter giá
$price_min = null;
$price_max = null;
if (!empty($price_filter) && strpos($price_filter, '-') !== false) {
    list($min, $max) = explode('-', $price_filter);
    $price_min = (float)$min;
    $price_max = (float)$max;
}

// Lấy danh sách danh mục (cha và con)
$categories = getAllCategoriesGrouped($conn);

// Lấy danh sách thương hiệu
$brandsQuery = "SELECT * FROM brands WHERE status = 'active' ORDER BY name ASC";
$brandsResult = $conn->query($brandsQuery);
$brands = [];
if ($brandsResult && $brandsResult->num_rows > 0) {
    while ($row = $brandsResult->fetch_assoc()) {
        $brands[] = $row;
    }
}

// Lấy danh sách kích thước
$sizesQuery = "SELECT * FROM sizes WHERE status = 'active' ORDER BY name ASC";
$sizesResult = $conn->query($sizesQuery);
$sizes = [];
if ($sizesResult && $sizesResult->num_rows > 0) {
    while ($row = $sizesResult->fetch_assoc()) {
        $sizes[] = $row;
    }
}

// Lấy danh sách sản phẩm
$perPage = 16; // Số sản phẩm mỗi trang
$options = [
    'category_id' => $category_id,
    'search' => $search,
    'price_min' => $price_min,
    'price_max' => $price_max,
    'brand_id' => $brand_id,
    'size_id' => $size_id,
    'sort' => $sort,
    'page' => $page,
    'per_page' => $perPage
];
$productsData = getProducts($conn, $options);
$products = $productsData['products'];
$totalProducts = $productsData['total'];
$totalPages = $productsData['total_pages'];

// Lấy thông tin danh mục hiện tại (nếu có)
$currentCategory = null;
if ($category_id) {
    $currentCategory = getCategoryById($conn, $category_id);
}

include 'includes/header.php';
?>

<main class="products-page">
    <div class="container">
        <div class="products-layout">
            
            <!-- SIDEBAR -->
            <aside class="products-sidebar">
                <!-- DANH MỤC SẢN PHẨM -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title">DANH MỤC SẢN PHẨM</h3>
                    <ul class="category-list">
                        <li>
                            <a href="products.php" class="<?= !$category_id ? 'active' : '' ?>">
                                Tất cả sản phẩm
                            </a>
                        </li>
                        <?php foreach ($categories as $cat): ?>
                            <?php if (!empty($cat['children'])): ?>
                                <!-- Danh mục có con -->
                                <li class="has-submenu">
                                    <a href="products.php?category=<?= $cat['id'] ?>" 
                                       class="category-parent <?= ($category_id == $cat['id']) ? 'active' : '' ?>"
                                       data-category-id="<?= $cat['id'] ?>">
                                        <?= htmlspecialchars($cat['name']) ?> 
                                        <i class="bi bi-chevron-down"></i>
                                    </a>
                                    <ul class="submenu" id="submenu-<?= $cat['id'] ?>" 
                                        style="<?= ($category_id == $cat['id']) ? 'display: block;' : 'display: none;' ?>">
                                        <?php foreach ($cat['children'] as $child): ?>
                                            <li>
                                                <a href="products.php?category=<?= $child['id'] ?>" 
                                                   class="<?= ($category_id == $child['id']) ? 'active' : '' ?>">
                                                    <?= htmlspecialchars($child['name']) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <!-- Danh mục không có con -->
                                <li>
                                    <a href="products.php?category=<?= $cat['id'] ?>" 
                                       class="<?= ($category_id == $cat['id']) ? 'active' : '' ?>">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- TÌM THEO GIÁ -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title">TÌM THEO GIÁ</h3>
                    <div class="filter-group">
                        <label class="filter-option">
                            <input type="radio" name="price" value="100000-300000" <?= $price_filter === '100000-300000' ? 'checked' : '' ?>>
                            <span>Từ: 100,000₫ Đến: 300,000₫</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="price" value="300000-500000" <?= $price_filter === '300000-500000' ? 'checked' : '' ?>>
                            <span>Từ: 300,000₫ Đến: 500,000₫</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="price" value="500000-1000000" <?= $price_filter === '500000-1000000' ? 'checked' : '' ?>>
                            <span>Từ: 500,000₫ Đến: 1,000,000₫</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="price" value="" <?= empty($price_filter) ? 'checked' : '' ?>>
                            <span>Tất cả giá</span>
                        </label>
                    </div>
                </div>

                <!-- TÌM THEO KÍCH THƯỚC -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title">TÌM THEO KÍCH THƯỚC</h3>
                    <div class="filter-group">
                        <label class="filter-option">
                            <input type="radio" name="size" value="" <?= empty($size_id) ? 'checked' : '' ?>>
                            <span>Tất cả kích thước</span>
                        </label>
                        <?php foreach ($sizes as $size): ?>
                        <label class="filter-option">
                            <input type="radio" name="size" value="<?= $size['id'] ?>" <?= $size_id == $size['id'] ? 'checked' : '' ?>>
                            <span><?= htmlspecialchars($size['name']) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- THƯƠNG HIỆU -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title">THƯƠNG HIỆU</h3>
                    <div class="filter-group">
                        <label class="filter-option">
                            <input type="radio" name="brand" value="" <?= empty($brand_id) ? 'checked' : '' ?>>
                            <span>Tất cả thương hiệu</span>
                        </label>
                        <?php foreach ($brands as $brand): ?>
                        <label class="filter-option">
                            <input type="radio" name="brand" value="<?= $brand['id'] ?>" <?= $brand_id == $brand['id'] ? 'checked' : '' ?>>
                            <span><?= htmlspecialchars($brand['name']) ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

            </aside>

            <!-- MAIN CONTENT -->
            <div class="products-main">
                
                <!-- FILTER BUTTON MOBILE -->
                <button class="filter-toggle-btn" id="filterToggleBtn" type="button">
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path d="M6 10.5a.5.5 0 0 1 .5-.5h3a.5.5 0 0 1 0 1h-3a.5.5 0 0 1-.5-.5zM3 6.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM1 2.5a.5.5 0 0 1 .5-.5h13a.5.5 0 0 1 0 1h-13a.5.5 0 0 1-.5-.5z"/>
                    </svg>
                    <span>Filter</span>
                </button>
                
                <!-- OVERLAY MOBILE -->
                <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeProductsSidebar()"></div>
                
                <!-- CLOSE BUTTON MOBILE - Ngoài sidebar -->
                <button class="products-sidebar-close" id="productsSidebarClose" onclick="closeProductsSidebar()">
                    <i class="bi bi-x-lg"></i>
                </button>
                
                <!-- TOP BAR -->
                <div class="products-topbar">
                    <div class="results-info">
                        <?php if ($totalProducts > 0): ?>
                            <?php 
                            $start = ($page - 1) * $perPage + 1;
                            $end = min($page * $perPage, $totalProducts);
                            ?>
                            <span>Hiển thị <?= $start ?>-<?= $end ?> trong tổng số <?= $totalProducts ?> kết quả</span>
                        <?php else: ?>
                            <span>Không tìm thấy sản phẩm nào</span>
                        <?php endif; ?>
                        <?php if ($currentCategory): ?>
                            <span class="ms-2 text-muted">- <?= htmlspecialchars($currentCategory['name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="sort-dropdown-wrapper">
                        <div class="sort-dropdown-custom" id="sortDropdown">
                            <button class="sort-dropdown-btn" type="button" id="sortDropdownBtn">
                                <span id="sortDropdownText">
                                    <?php
                                    $sortLabels = [
                                        'default' => 'Sắp xếp mặc định',
                                        'popular' => 'Sắp xếp theo mức độ phổ biến',
                                        'rating' => 'Sắp xếp theo xếp hạng trung bình',
                                        'latest' => 'Sắp xếp theo mới nhất',
                                        'price_asc' => 'Sắp xếp theo giá: từ thấp đến cao',
                                        'price_desc' => 'Sắp xếp theo giá: từ cao đến thấp'
                                    ];
                                    echo $sortLabels[$sort] ?? 'Sắp xếp mặc định';
                                    ?>
                                </span>
                                <i class="bi bi-chevron-down"></i>
                            </button>
                            <div class="sort-dropdown-menu" id="sortDropdownMenu">
                                <a href="#" class="sort-option <?= $sort === 'default' ? 'active' : '' ?>" data-sort="default">Sắp xếp mặc định</a>
                                <a href="#" class="sort-option <?= $sort === 'popular' ? 'active' : '' ?>" data-sort="popular">Sắp xếp theo mức độ phổ biến</a>
                                <a href="#" class="sort-option <?= $sort === 'rating' ? 'active' : '' ?>" data-sort="rating">Sắp xếp theo xếp hạng trung bình</a>
                                <a href="#" class="sort-option <?= $sort === 'latest' ? 'active' : '' ?>" data-sort="latest">Sắp xếp theo mới nhất</a>
                                <a href="#" class="sort-option <?= $sort === 'price_asc' ? 'active' : '' ?>" data-sort="price_asc">Sắp xếp theo giá: từ thấp đến cao</a>
                                <a href="#" class="sort-option <?= $sort === 'price_desc' ? 'active' : '' ?>" data-sort="price_desc">Sắp xếp theo giá: từ cao đến thấp</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PRODUCTS GRID -->
                <div class="products-grid">
                    <?php if (!empty($products)): ?>
                        <?php foreach ($products as $p): ?>
                            <?php
                            $price = (float)$p['price'];
                            $salePrice = isset($p['sale_price']) ? (float)$p['sale_price'] : 0;
                            $hasSale = $salePrice > 0 && $salePrice < $price;
                            $offPercent = $hasSale ? round(100 - ($salePrice / $price) * 100) : 0;
                            $imgPath = !empty($p['image']) ? 'assets/images/' . $p['image'] : 'assets/images/1.jpg';
                            
                            // Giá hiển thị
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
                    <?php else: ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                            <p>Không tìm thấy sản phẩm nào.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- PAGINATION -->
                <?php if ($totalPages > 1): 
                    $paginationParams = [];
                    if ($category_id) $paginationParams[] = 'category=' . $category_id;
                    if ($search) $paginationParams[] = 'search=' . urlencode($search);
                    if ($price_filter) $paginationParams[] = 'price=' . urlencode($price_filter);
                    if ($brand_id) $paginationParams[] = 'brand=' . $brand_id;
                    if ($size_id) $paginationParams[] = 'size=' . $size_id;
                    if ($sort && $sort !== 'default') $paginationParams[] = 'sort=' . urlencode($sort);
                    $paginationQuery = !empty($paginationParams) ? '&' . implode('&', $paginationParams) : '';
                ?>
                <div class="products-pagination">
                    <?php if ($page > 1): ?>
                        <a href="products.php?page=<?= $page - 1 ?><?= $paginationQuery ?>" class="page-btn">
                            <i class="bi bi-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <?php if ($i == 1 || $i == $totalPages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                            <a href="products.php?page=<?= $i ?><?= $paginationQuery ?>" 
                               class="page-btn <?= $i == $page ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                            <span class="page-btn" style="cursor: default;">...</span>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="products.php?page=<?= $page + 1 ?><?= $paginationQuery ?>" class="page-btn">
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            </div>

        </div>
    </div>
</main>

<?php 
// Đóng kết nối database
if (isset($conn)) {
    closeDB($conn);
}
include 'includes/footer.php'; 
?>

<script>
// Xử lý dropdown danh mục con
document.addEventListener('DOMContentLoaded', function() {
    const categoryParents = document.querySelectorAll('.category-parent');
    
    categoryParents.forEach(function(parent) {
        parent.addEventListener('click', function(e) {
            const categoryId = this.getAttribute('data-category-id');
            const submenu = document.getElementById('submenu-' + categoryId);
            
            if (submenu) {
                e.preventDefault();
                // Toggle submenu
                if (submenu.style.display === 'none' || submenu.style.display === '') {
                    submenu.style.display = 'block';
                    this.querySelector('i').classList.remove('bi-chevron-down');
                    this.querySelector('i').classList.add('bi-chevron-up');
                } else {
                    submenu.style.display = 'none';
                    this.querySelector('i').classList.remove('bi-chevron-up');
                    this.querySelector('i').classList.add('bi-chevron-down');
                }
            }
        });
    });
    
    // Xử lý filter giá
    const priceFilters = document.querySelectorAll('input[name="price"]');
    priceFilters.forEach(function(filter) {
        filter.addEventListener('change', function() {
            applyFilters();
        });
    });
    
    // Xử lý filter brand
    const brandFilters = document.querySelectorAll('input[name="brand"]');
    brandFilters.forEach(function(filter) {
        filter.addEventListener('change', function() {
            applyFilters();
        });
    });
    
    // Xử lý filter size
    const sizeFilters = document.querySelectorAll('input[name="size"]');
    sizeFilters.forEach(function(filter) {
        filter.addEventListener('change', function() {
            applyFilters();
        });
    });
    
    // Xử lý dropdown sắp xếp custom
    const sortDropdownBtn = document.getElementById('sortDropdownBtn');
    const sortDropdownMenu = document.getElementById('sortDropdownMenu');
    const sortDropdown = document.getElementById('sortDropdown');
    const sortOptions = document.querySelectorAll('.sort-option');
    
    if (sortDropdownBtn && sortDropdownMenu) {
        // Toggle dropdown
        sortDropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            sortDropdownMenu.classList.toggle('show');
            const icon = sortDropdownBtn.querySelector('i');
            if (sortDropdownMenu.classList.contains('show')) {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
            } else {
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
            }
        });
        
        // Đóng dropdown khi click bên ngoài
        document.addEventListener('click', function(e) {
            if (!sortDropdown.contains(e.target)) {
                sortDropdownMenu.classList.remove('show');
                const icon = sortDropdownBtn.querySelector('i');
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
            }
        });
        
        // Xử lý chọn option
        sortOptions.forEach(function(option) {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                const sortValue = this.getAttribute('data-sort');
                const sortText = this.textContent;
                
                // Cập nhật text hiển thị
                document.getElementById('sortDropdownText').textContent = sortText;
                
                // Cập nhật active state
                sortOptions.forEach(function(opt) {
                    opt.classList.remove('active');
                });
                this.classList.add('active');
                
                // Đóng dropdown
                sortDropdownMenu.classList.remove('show');
                const icon = sortDropdownBtn.querySelector('i');
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
                
                // Áp dụng filter
                applyFilters(sortValue);
            });
        });
    }
    
    // Hàm toggle sidebar (giống header sidebar) - Đặt vào window scope để có thể gọi từ onclick
    window.toggleProductsSidebar = function() {
        const sidebar = document.querySelector('.products-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const closeBtn = document.getElementById('productsSidebarClose');
        if (sidebar && overlay && closeBtn) {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            closeBtn.classList.toggle('show');
            const open = sidebar.classList.contains('active');
            document.body.style.overflow = open ? 'hidden' : '';
            document.body.classList.toggle('products-sidebar-open', open);
        }
    };

    // Hàm đóng sidebar (giống header sidebar) - Đặt vào window scope để có thể gọi từ onclick
    window.closeProductsSidebar = function() {
        const sidebar = document.querySelector('.products-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const closeBtn = document.getElementById('productsSidebarClose');
        if (sidebar && overlay && closeBtn) {
            sidebar.classList.remove('active');
            overlay.classList.remove('active');
            closeBtn.classList.remove('show');
            document.body.style.overflow = '';
            document.body.classList.remove('products-sidebar-open');
        }
    };
    
    // Xử lý toggle sidebar trên mobile
    const filterToggleBtn = document.getElementById('filterToggleBtn');
    
    if (filterToggleBtn) {
        filterToggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            window.toggleProductsSidebar();
        });
        
        // Thêm touch event cho mobile
        filterToggleBtn.addEventListener('touchend', function(e) {
            e.preventDefault();
            e.stopPropagation();
            window.toggleProductsSidebar();
        });
    }
    
    // Đóng sidebar khi click vào link trong sidebar (trừ filter radio)
    const sidebar = document.querySelector('.products-sidebar');
    if (sidebar) {
        const sidebarLinks = sidebar.querySelectorAll('a');
        sidebarLinks.forEach(function(link) {
            link.addEventListener('click', function() {
                // Chỉ đóng nếu không phải là link filter (radio button)
                if (!this.closest('.filter-option')) {
                    setTimeout(function() {
                        window.closeProductsSidebar();
                    }, 300);
                }
            });
        });
        
        // Đóng sidebar khi nhấn phím ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && sidebar.classList.contains('active')) {
                window.closeProductsSidebar();
            }
        });
    }
    
    // Hàm áp dụng filter
    function applyFilters(sortValue = null) {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Lấy giá trị filter giá
        const priceFilter = document.querySelector('input[name="price"]:checked');
        if (priceFilter && priceFilter.value) {
            urlParams.set('price', priceFilter.value);
        } else {
            urlParams.delete('price');
        }
        
        // Lấy giá trị filter brand
        const brandFilter = document.querySelector('input[name="brand"]:checked');
        if (brandFilter && brandFilter.value) {
            urlParams.set('brand', brandFilter.value);
        } else {
            urlParams.delete('brand');
        }
        
        // Lấy giá trị filter size
        const sizeFilter = document.querySelector('input[name="size"]:checked');
        if (sizeFilter && sizeFilter.value) {
            urlParams.set('size', sizeFilter.value);
        } else {
            urlParams.delete('size');
        }
        
        // Lấy giá trị sort
        if (sortValue) {
            if (sortValue !== 'default') {
                urlParams.set('sort', sortValue);
            } else {
                urlParams.delete('sort');
            }
        } else {
            // Nếu không có sortValue, giữ nguyên sort hiện tại
            const currentSort = urlParams.get('sort');
            if (!currentSort || currentSort === 'default') {
                urlParams.delete('sort');
            }
        }
        
        // Reset về trang 1 khi filter
        urlParams.set('page', '1');
        
        // Chuyển hướng với filter mới
        window.location.href = 'products.php?' + urlParams.toString();
    }
});
</script>

<style>
.category-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.category-list li {
    border-bottom: 1px solid #f0f0f0;
}

.category-list li:last-child {
    border-bottom: none;
}

.category-list a {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 16px;
    color: #333;
    text-decoration: none;
    font-size: 14px;
    transition: all 0.2s ease;
}

.category-list a:hover {
    background: #f5f5f5;
    color: #3da04d;
    padding-left: 20px;
}

.category-list a.active {
    background: #f0f7f1;
    color: #3da04d;
    font-weight: 600;
}

.category-list .has-submenu {
    position: relative;
}

.category-list .submenu {
    list-style: none;
    padding: 0;
    margin: 0;
    background: #fafafa;
}

.category-list .submenu li {
    border-bottom: 1px solid #f0f0f0;
}

.category-list .submenu a {
    padding-left: 32px;
    font-size: 13px;
}

.category-list .submenu a:hover {
    padding-left: 40px;
}

.category-list .has-submenu i {
    font-size: 12px;
    color: #999;
    transition: transform 0.2s;
}
</style>
