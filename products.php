<?php
session_start();
$pageTitle = "Sản phẩm - Bách Hóa Xanh";
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
                        <li><a href="#">Dầu ăn - Gia vị</a></li>
                        <li><a href="#">Đồ đông lạnh</a></li>
                        <li><a href="#">Đồ uống các loại</a></li>
                        <li class="has-submenu">
                            <a href="#">Mì - cháo - phở <i class="bi bi-chevron-down"></i></a>
                        </li>
                        <li class="has-submenu">
                            <a href="#">Rau - củ - quả <i class="bi bi-chevron-down"></i></a>
                        </li>
                        <li><a href="#">Sản phẩm khác</a></li>
                        <li class="has-submenu">
                            <a href="#">Thịt - cá - trứng <i class="bi bi-chevron-down"></i></a>
                        </li>
                        <li><a href="#">Thực Phẩm Chế Biến</a></li>
                        <li><a href="#">Thực Phẩm Tết</a></li>
                    </ul>
                </div>

                <!-- TÌM THEO GIÁ -->
                <div class="sidebar-section">
                    <h3 class="sidebar-title">TÌM THEO GIÁ</h3>
                    <div class="filter-group">
                        <label class="filter-option">
                            <input type="radio" name="price" value="100000-300000">
                            <span>Từ: 100,000₫ Đến: 300,000₫</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="price" value="300000-500000">
                            <span>Từ: 300,000₫ Đến: 500,000₫</span>
                        </label>
                        <label class="filter-option">
                            <input type="radio" name="price" value="500000-1000000">
                            <span>Từ: 500,000₫ Đến: 1,000,000₫</span>
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
                        <span>Showing 1-16 of 18 results</span>
                    </div>
                    <div class="sort-dropdown">
                        <select class="form-select">
                            <option>Default sorting</option>
                            <option>Sort by popularity</option>
                            <option>Sort by average rating</option>
                            <option>Sort by latest</option>
                            <option>Sort by price: low to high</option>
                            <option>Sort by price: high to low</option>
                        </select>
                    </div>
                </div>

                <!-- PRODUCTS GRID -->
                <div class="products-grid">
                    <?php
                    $products = [
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'90,000₫ – 130,000₫','sale'=>'19%','img'=>'1.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'120,000₫ – 170,000₫','sale'=>'20%','img'=>'2.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'90,000₫ – 130,000₫','sale'=>'19%','img'=>'4.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'120,000₫ – 170,000₫','sale'=>'20%','img'=>'5.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'90,000₫ – 130,000₫','sale'=>'19%','img'=>'1.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'120,000₫ – 170,000₫','sale'=>'20%','img'=>'2.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'90,000₫ – 130,000₫','sale'=>'19%','img'=>'4.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'120,000₫ – 170,000₫','sale'=>'20%','img'=>'5.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'90,000₫ – 130,000₫','sale'=>'19%','img'=>'1.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'120,000₫ – 170,000₫','sale'=>'20%','img'=>'2.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'90,000₫ – 130,000₫','sale'=>'19%','img'=>'4.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'120,000₫ – 170,000₫','sale'=>'20%','img'=>'5.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'90,000₫ – 130,000₫','sale'=>'19%','img'=>'1.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'120,000₫ – 170,000₫','sale'=>'20%','img'=>'2.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'90,000₫ – 130,000₫','sale'=>'19%','img'=>'4.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'120,000₫ – 170,000₫','sale'=>'20%','img'=>'5.jpg'],
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

                <!-- PAGINATION -->
                <div class="products-pagination">
                    <a href="#" class="page-btn active">1</a>
                    <a href="#" class="page-btn">2</a>
                    <a href="#" class="page-btn">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </div>

            </div>

        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>
