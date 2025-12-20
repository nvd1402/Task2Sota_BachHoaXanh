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
$sort = isset($_GET['sort']) ? trim($_GET['sort']) : 'latest';
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

// Lấy danh sách sản phẩm
$options = [
    'category_id' => $category_id,
    'search' => $search,
    'price_min' => $price_min,
    'price_max' => $price_max,
    'sort' => $sort,
    'page' => $page,
    'per_page' => 16
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
                            <input type="radio" name="size" value="L">
                            <span>L</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="size" value="M">
                            <span>M</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="size" value="S">
                            <span>S</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="size" value="XL">
                            <span>XL</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="size" value="XXL">
                            <span>XXL</span>
                        </label>
                    </div>
                </div>

                <!-- THƯƠNG HIỆU -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title">THƯƠNG HIỆU</h3>
                    <div class="filter-group">
                        <label class="filter-option">
                            <input type="radio" name="brand" value="5TFOODS">
                            <span>5TFOODS</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="brand" value="HAIHACO">
                            <span>HAIHACO</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="brand" value="KIDO">
                            <span>KIDO</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="brand" value="Nutifood">
                            <span>Nutifood</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="brand" value="Vissan">
                            <span>Vissan</span>
                        </label>
                    </div>
                </div>

            </aside>

            <!-- MAIN CONTENT -->
            <div class="products-main">
                
                <!-- TOP BAR -->
                <div class="products-topbar">
                    <div class="results-info">
                        <?php if ($totalProducts > 0): ?>
                            <span>Hiển thị <?= ($page - 1) * 16 + 1 ?>-<?= min($page * 16, $totalProducts) ?> của <?= $totalProducts ?> kết quả</span>
                        <?php else: ?>
                            <span>Không tìm thấy sản phẩm nào</span>
                        <?php endif; ?>
                        <?php if ($currentCategory): ?>
                            <span class="ms-2 text-muted">- <?= htmlspecialchars($currentCategory['name']) ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="sort-dropdown">
                        <select class="form-select" id="sortSelect">
                            <option value="latest" <?= $sort === 'latest' ? 'selected' : '' ?>>Mới nhất</option>
                            <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Giá: thấp đến cao</option>
                            <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Giá: cao đến thấp</option>
                            <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Tên: A-Z</option>
                        </select>
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
                    if ($sort && $sort !== 'latest') $paginationParams[] = 'sort=' . urlencode($sort);
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
    
    // Xử lý sắp xếp
    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', function() {
            applyFilters();
        });
    }
    
    // Hàm áp dụng filter
    function applyFilters() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Lấy giá trị filter giá
        const priceFilter = document.querySelector('input[name="price"]:checked');
        if (priceFilter && priceFilter.value) {
            urlParams.set('price', priceFilter.value);
        } else {
            urlParams.delete('price');
        }
        
        // Lấy giá trị sort
        if (sortSelect && sortSelect.value && sortSelect.value !== 'latest') {
            urlParams.set('sort', sortSelect.value);
        } else {
            urlParams.delete('sort');
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
