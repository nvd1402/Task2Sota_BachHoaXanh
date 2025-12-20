<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = "Chi tiết sản phẩm - Bách Hóa Xanh";

// Kết nối database
$conn = connectDB();

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin sản phẩm
$product = null;
if ($product_id > 0) {
    $sql = "SELECT * FROM products WHERE id = ? AND status = 'active'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $product = $result->fetch_assoc();
        $pageTitle = htmlspecialchars($product['name']) . " - Bách Hóa Xanh";

        // Xử lý gallery (JSON string -> array)
        if (!empty($product['gallery'])) {
            $gallery = json_decode($product['gallery'], true);
            if (!is_array($gallery)) {
                $gallery = [];
            }
        } else {
            $gallery = [];
        }
        // Thêm ảnh chính vào đầu gallery nếu chưa có
        if (!empty($product['image']) && !in_array($product['image'], $gallery)) {
            array_unshift($gallery, $product['image']);
        }
    }
    $stmt->close();
}

// Nếu không tìm thấy sản phẩm, redirect về trang sản phẩm
if (!$product) {
    closeDB($conn);
    header('Location: products.php');
    exit();
}

// Tính toán giá và giảm giá
$price = (float)$product['price'];
$salePrice = isset($product['sale_price']) ? (float)$product['sale_price'] : 0;
$hasSale = $salePrice > 0 && $salePrice < $price;
$offPercent = $hasSale ? round(100 - ($salePrice / $price) * 100) : 0;

// Lấy sản phẩm nổi bật để hiển thị sidebar
$featuredProducts = [];
$sqlFeatured = "SELECT * FROM products WHERE status = 'active' AND featured = 1 AND id != ? ORDER BY created_at DESC LIMIT 5";
$stmt = $conn->prepare($sqlFeatured);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $featuredProducts[] = $row;
}
$stmt->close();

// Lấy sản phẩm liên quan (cùng category)
$relatedProducts = [];
if (!empty($product['category'])) {
    $sqlRelated = "SELECT * FROM products WHERE status = 'active' AND category = ? AND id != ? ORDER BY created_at DESC LIMIT 6";
    $stmt = $conn->prepare($sqlRelated);
    $stmt->bind_param("si", $product['category'], $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $relatedProducts[] = $row;
    }
    $stmt->close();
}

// Đóng kết nối (sẽ đóng lại ở cuối file)
include 'includes/header.php';
?>

<main class="product-detail-page py-4">
    <div class="container">
        <div class="row g-4">

            <!-- LEFT COLUMN - PRODUCT IMAGE GALLERY -->
            <div class="col-12 col-lg-5">
                <div class="product-image-section">
                    <?php
                    $mainImage = !empty($product['image']) ? 'assets/images/' . $product['image'] : 'assets/images/1.jpg';
                    ?>
                    <div class="main-image-wrapper position-relative mb-3">
                        <?php if ($hasSale): ?>
                        <div class="product-badge-wrapper product-badge-main position-absolute top-0 end-0" style="z-index: 10;">
                            <img src="assets/images/bg_sale.png" class="product-badge-img" alt="Sale">
                            <span class="product-badge-text position-absolute top-50 start-50 translate-middle">-<?= $offPercent ?>%</span>
                        </div>
                        <?php endif; ?>
                        <button class="gallery-nav-btn prev-btn position-absolute start-0 top-50 translate-middle-y d-none d-md-flex" type="button" onclick="navigateGallery(-1)">
                            <i class="bi bi-chevron-left"></i>
                        </button>
                        <div class="main-image-container d-flex align-items-center justify-content-center" id="mainImageContainer" style="aspect-ratio: 1; background: transparent;">
                            <img src="<?= htmlspecialchars($mainImage) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="main-product-img img-fluid" id="mainProductImg" style="max-width: 80%; max-height: 80%; object-fit: contain;">
                        </div>
                        <button class="gallery-nav-btn next-btn position-absolute end-0 top-50 translate-middle-y d-none d-md-flex" type="button" onclick="navigateGallery(1)">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                        <button class="zoom-btn position-absolute bottom-0 end-0 m-3" type="button" onclick="openImageModal()">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </button>
                    </div>
                    
                    <!-- Thumbnail Images -->
                    <?php if (!empty($gallery)): ?>
                    <div class="thumbnail-images d-flex gap-2 flex-wrap justify-content-center">
                        <?php foreach ($gallery as $index => $img): ?>
                            <?php
                            $imgPath = 'assets/images/' . $img;
                            $isActive = ($index === 0) ? 'active border border-success border-2' : 'border border-secondary';
                            ?>
                            <div class="thumbnail-item <?= $isActive ?>" onclick="changeMainImage('<?= htmlspecialchars($imgPath) ?>', this, <?= $index ?>)" style="width: 80px; height: 80px; cursor: pointer; border-radius: 8px; overflow: hidden;">
                                <img src="<?= htmlspecialchars($imgPath) ?>" alt="Thumbnail <?= $index + 1 ?>" class="w-100 h-100" style="object-fit: contain;">
                        </div>
                        <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    </div>
                </div>

            <!-- CENTER COLUMN - PRODUCT INFO -->
            <div class="col-12 col-lg-4">
                <div class="product-info-section">
                    <?php
                    $displayMin = isset($product['price_min']) && $product['price_min'] > 0 ? (float)$product['price_min'] : $price;
                    $displayMax = isset($product['price_max']) && $product['price_max'] > 0 ? (float)$product['price_max'] : ($hasSale ? $salePrice : $price);

                    // Xử lý weight_options
                    $weightOptions = !empty($product['weight_options']) ? explode(',', $product['weight_options']) : [];
                    ?>

                    <h1 class="product-title fw-bold mb-3"><?= htmlspecialchars($product['name']) ?></h1>
                    <div class="product-price text-success fw-bold fs-4 mb-3">
                        <?= number_format($displayMin, 0, ',', '.') ?>₫ – <?= number_format($displayMax, 0, ',', '.') ?>₫
                    </div>
                    
                    <!-- Features -->
                    <ul class="product-features list-unstyled mb-4">
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Đạt chuẩn an toàn Viet Gap</li>
                        <li class="mb-2"><i class="bi bi-check-circle-fill text-success me-2"></i>Hàng tươi mới trong ngày</li>
                    </ul>

                    <!-- Promotion Box -->
                    <?php if (!empty($product['promo_heading']) || !empty($product['promo_content'])): ?>
                    <div class="promotion-box border border-danger border-2 border-dashed rounded p-3 mb-4" style="background: #fff;">
                        <?php if (!empty($product['promo_heading'])): ?>
                            <div class="promotion-title fw-bold text-danger mb-2 d-flex align-items-center gap-2">
                                <i class="bi bi-gift-fill"></i>
                                <?= htmlspecialchars($product['promo_heading']) ?>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($product['promo_content'])): ?>
                        <div class="promotion-items">
                                <?php
                                $promoLines = explode('\n', $product['promo_content']);
                                foreach ($promoLines as $index => $line):
                                    $line = trim($line);
                                    if (!empty($line)):
                                ?>
                                    <div class="promotion-item mb-1"><?= htmlspecialchars($line) ?></div>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>

                    <!-- Size/Weight Selection -->
                    <?php if (!empty($weightOptions)): ?>
                    <div class="size-selection mb-4">
                        <label class="size-label fw-semibold d-block mb-2">Trọng lượng :</label>
                        <div class="size-buttons d-flex gap-2 flex-wrap">
                            <?php foreach ($weightOptions as $index => $option): ?>
                                <?php $option = trim($option); ?>
                                <button class="size-btn btn <?= $index === 0 ? 'btn-success' : 'btn-outline-secondary' ?>" data-size="<?= htmlspecialchars($option) ?>" type="button"><?= htmlspecialchars($option) ?></button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Quantity Selection -->
                    <div class="quantity-selection d-flex align-items-center gap-3 mb-4">
                        <label class="quantity-label fw-semibold mb-0">Số lượng :</label>
                        <div class="quantity-controls d-flex align-items-center border rounded">
                            <button class="qty-btn minus btn btn-light border-0 rounded-0" type="button" style="width: 40px; height: 40px;">-</button>
                            <input type="number" class="qty-input form-control border-0 text-center" value="1" min="1" id="productQuantity" style="width: 60px; height: 40px;">
                            <button class="qty-btn plus btn btn-light border-0 rounded-0" type="button" style="width: 40px; height: 40px;">+</button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons d-flex gap-2">
                        <button class="btn-add-cart btn btn-success flex-fill d-flex align-items-center justify-content-center gap-2" type="button" id="addToCartBtn">
                            <i class="bi bi-cart-plus"></i> Add to cart
                        </button>
                        <button class="btn-buy-now btn text-white flex-fill" type="button" id="buyNowBtn" style="background: #e91e63;">BUY NOW</button>
                    </div>
                    </div>
            </div>

            <!-- RIGHT COLUMN - FEATURED PRODUCTS -->
            <div class="col-12 col-lg-3">
            <aside class="product-detail-sidebar">
                    <div class="sidebar-featured border">
                        <h3 class="sidebar-featured-title text-white text-center py-2 mb-0 fw-bold" style="background: #3da04d;">SẢN PHẨM NỔI BẬT</h3>
                        <div class="sidebar-products p-3">
                            <?php if (!empty($featuredProducts)): ?>
                                <?php 
                                $totalItems = count($featuredProducts);
                                foreach ($featuredProducts as $index => $item): 
                        ?>
                        <?php
                                    $itemPrice = (float)$item['price'];
                                    $itemSalePrice = isset($item['sale_price']) ? (float)$item['sale_price'] : 0;
                                    $itemHasSale = $itemSalePrice > 0 && $itemSalePrice < $itemPrice;
                                    $itemDisplayMin = isset($item['price_min']) && $item['price_min'] > 0 ? (float)$item['price_min'] : $itemPrice;
                                    $itemDisplayMax = isset($item['price_max']) && $item['price_max'] > 0 ? (float)$item['price_max'] : ($itemHasSale ? $itemSalePrice : $itemPrice);
                                    $itemImgPath = !empty($item['image']) ? 'assets/images/' . $item['image'] : 'assets/images/1.jpg';
                                    $isLast = ($index === $totalItems - 1);
                                    ?>
                                    <a href="product-detail.php?id=<?= (int)$item['id'] ?>" class="sidebar-product-item d-flex gap-3 text-decoration-none text-dark mb-3 pb-3 <?= !$isLast ? 'border-bottom border-success border-1 border-dashed' : '' ?>">
                                    <div class="sidebar-product-img" style="width: 80px; height: 80px; flex-shrink: 0;">
                                            <img src="<?= htmlspecialchars($itemImgPath) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-100 h-100" style="object-fit: contain; border-radius: 6px;">
                                </div>
                                    <div class="sidebar-product-info flex-grow-1">
                                        <h4 class="sidebar-product-name fw-semibold mb-1 small"><?= htmlspecialchars($item['name']) ?></h4>
                                            <p class="sidebar-product-price fw-bold mb-0 small" style="color: #ff9800;">
                                                <?= number_format($itemDisplayMin, 0, ',', '.') ?>₫ – <?= number_format($itemDisplayMax, 0, ',', '.') ?>₫
                                            </p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                            <?php else: ?>
                                <p class="text-center text-muted py-3">Chưa có sản phẩm nổi bật</p>
                            <?php endif; ?>
                    </div>
                </div>
            </aside>
            </div>

        </div>
    </div>
</main>

<!-- Product Detail Tabs -->
<section class="product-tabs-section py-5">
    <div class="container">
        <ul class="nav nav-tabs mb-4" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#desc" type="button" role="tab">Description</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#add-info" type="button" role="tab">Additional Information</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab">Reviews (0)</button>
            </li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="desc" role="tabpanel">
                <?php if (!empty($product['description'])): ?>
                    <div style="white-space: pre-line; line-height: 1.8;">
                        <?= nl2br(htmlspecialchars($product['description'])) ?>
        </div>
                <?php else: ?>
                    <p><?= htmlspecialchars($product['short_description'] ?? 'Sản phẩm chất lượng cao, đảm bảo an toàn vệ sinh thực phẩm.') ?></p>
                <?php endif; ?>
                <?php if (!empty($gallery) && count($gallery) > 1): ?>
                <div class="tab-image">
                    <img src="assets/images/<?= htmlspecialchars($gallery[1]) ?>" alt="Mô tả sản phẩm">
                </div>
                <?php endif; ?>
            </div>
            <div class="tab-pane fade" id="add-info" role="tabpanel">
                <?php if (!empty($product['weight_options'])): ?>
                <div class="info-row">
                    <span class="info-label">Kích thước</span>
                    <span class="info-value"><?= htmlspecialchars($product['weight_options']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($product['category'])): ?>
                <div class="info-row">
                    <span class="info-label">Danh mục</span>
                    <span class="info-value"><?= htmlspecialchars($product['category']) ?></span>
                </div>
                <?php endif; ?>
                <?php if (!empty($product['sku'])): ?>
                <div class="info-row">
                    <span class="info-label">SKU</span>
                    <span class="info-value"><?= htmlspecialchars($product['sku']) ?></span>
            </div>
                <?php endif; ?>
                <div class="info-row">
                    <span class="info-label">Tình trạng</span>
                    <span class="info-value"><?= $product['stock'] > 0 ? 'Còn hàng (' . $product['stock'] . ' sản phẩm)' : 'Hết hàng' ?></span>
                </div>
            </div>
            <div class="tab-pane fade" id="reviews" role="tabpanel">
                <p class="no-review">There are no reviews yet.</p>
                <form class="review-form">
                    <h4 class="review-title">Be the first to review "<?= htmlspecialchars($product['name']) ?>"</h4>
                    <div class="form-group">
                        <label>Your rating *</label>
                        <div class="stars">
                            ★★★★★
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Your review *</label>
                        <textarea rows="4"></textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label>Name *</label>
                            <input type="text">
                        </div>
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email">
                        </div>
                    </div>
                    <div class="form-group checkbox">
                        <label>
                            <input type="checkbox">
                            Lưu tên của tôi, email, và trang web trong trình duyệt này cho lần bình luận kế tiếp của tôi.
                        </label>
                    </div>
                    <button type="button" class="btn-submit">Submit</button>
                </form>
            </div>
        </div>
    </div>
</section>

<!-- Related Products Carousel -->
<section class="related-section">
    <div class="container related-container">
        <div class="related-header">
            <h3 class="related-title">Related Products</h3>
        </div>
        <div class="related-wrapper">
            <button id="relatedPrev" class="related-nav-btn" type="button" aria-label="Prev">
                <i class="bi bi-chevron-left"></i>
            </button>
            <div class="related-grid-wrapper no-scrollbar">
                <div id="relatedTrack" class="related-track">
                    <?php if (!empty($relatedProducts)): ?>
                        <?php foreach ($relatedProducts as $item): ?>
                    <?php
                            $itemPrice = (float)$item['price'];
                            $itemSalePrice = isset($item['sale_price']) ? (float)$item['sale_price'] : 0;
                            $itemHasSale = $itemSalePrice > 0 && $itemSalePrice < $itemPrice;
                            $itemOffPercent = $itemHasSale ? round(100 - ($itemSalePrice / $itemPrice) * 100) : 0;
                            $itemDisplayMin = isset($item['price_min']) && $item['price_min'] > 0 ? (float)$item['price_min'] : $itemPrice;
                            $itemDisplayMax = isset($item['price_max']) && $item['price_max'] > 0 ? (float)$item['price_max'] : ($itemHasSale ? $itemSalePrice : $itemPrice);
                            $itemImgPath = !empty($item['image']) ? 'assets/images/' . $item['image'] : 'assets/images/1.jpg';
                            ?>
                            <a href="product-detail.php?id=<?= (int)$item['id'] ?>" class="related-card">
                        <div class="related-thumb">
                                    <?php if ($itemHasSale): ?>
                            <img src="assets/images/bg_sale.png" class="sale-badge" alt="Sale">
                                    <span class="sale-text">-<?= $itemOffPercent ?>%</span>
                                    <?php endif; ?>
                                    <img src="<?= htmlspecialchars($itemImgPath) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        </div>
                        <div class="related-body">
                            <p class="related-name"><?= htmlspecialchars($item['name']) ?></p>
                                    <p class="related-price"><?= number_format($itemDisplayMin, 0, ',', '.') ?>₫ – <?= number_format($itemDisplayMax, 0, ',', '.') ?>₫</p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-center text-muted py-4">Chưa có sản phẩm liên quan</p>
                    <?php endif; ?>
                </div>
            </div>
            <button id="relatedNext" class="related-nav-btn" type="button" aria-label="Next">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
</section>

<!-- Image Modal -->
<div id="imageModal" class="image-modal">
    <div class="modal-content-wrapper" onclick="event.stopPropagation()">
        <!-- Modal Header -->
        <div class="modal-header">
            <div class="modal-header-left">
                <span class="modal-counter" id="modalCounter">1/<?= count($gallery) ?></span>
            </div>
            <div class="modal-header-center">
                <h3 class="modal-title">Chi tiết sản phẩm</h3>
            </div>
            <div class="modal-header-right">
                <button class="modal-icon-btn" type="button">
                    <i class="bi bi-search"></i>
                </button>
                <button class="modal-icon-btn modal-close-btn" type="button" onclick="closeImageModal()">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="modal-body">
            <button class="modal-nav-btn modal-prev" type="button" onclick="navigateModalGallery(-1)">
                <i class="bi bi-chevron-left"></i>
            </button>

            <div class="modal-image-wrapper">
                <div class="modal-image-container" id="modalImageContainer">
                    <img class="modal-image" id="modalImage" src="" alt="<?= htmlspecialchars($product['name']) ?>">
                </div>
            </div>

            <button class="modal-nav-btn modal-next" type="button" onclick="navigateModalGallery(1)">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>

        <!-- Modal Thumbnail Gallery -->
        <?php if (!empty($gallery)): ?>
        <div class="modal-thumbnail-gallery">
            <?php foreach ($gallery as $index => $img): ?>
                <?php
                $imgPath = 'assets/images/' . $img;
                $isActive = ($index === 0) ? 'active' : '';
                ?>
                <div class="modal-thumbnail-item <?= $isActive ?>" onclick="selectModalImage(<?= $index ?>)">
                    <img src="<?= htmlspecialchars($imgPath) ?>" alt="Thumbnail <?= $index + 1 ?>">
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
let currentGalleryIndex = 0;
const galleryImages = <?= json_encode($gallery) ?>;

function changeMainImage(src, element, index) {
    document.getElementById('mainProductImg').src = src;
    currentGalleryIndex = index !== undefined ? index : 0;
    // Update active thumbnail
    document.querySelectorAll('.thumbnail-item').forEach(item => {
        item.classList.remove('active');
    });
    if (element) {
        element.classList.add('active');
    }
}

function navigateGallery(direction) {
    if (galleryImages.length === 0) return;
    currentGalleryIndex += direction;
    if (currentGalleryIndex < 0) currentGalleryIndex = galleryImages.length - 1;
    if (currentGalleryIndex >= galleryImages.length) currentGalleryIndex = 0;

    const imgPath = 'assets/images/' + galleryImages[currentGalleryIndex];
    document.getElementById('mainProductImg').src = imgPath;

    // Update active thumbnail
    const thumbnails = document.querySelectorAll('.thumbnail-item');
    thumbnails.forEach((item, idx) => {
        item.classList.toggle('active', idx === currentGalleryIndex);
    });
}

// Quantity controls
document.querySelector('.qty-btn.minus').addEventListener('click', function() {
    const input = document.getElementById('productQuantity');
    if (parseInt(input.value) > 1) {
        input.value = parseInt(input.value) - 1;
    }
});

document.querySelector('.qty-btn.plus').addEventListener('click', function() {
    const input = document.getElementById('productQuantity');
    input.value = parseInt(input.value) + 1;
});

// Size selection
document.querySelectorAll('.size-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
    });
});

// Tabs
document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
        btn.classList.add('active');
        const target = btn.getAttribute('data-tab');
        const panel = document.getElementById(target);
        if (panel) panel.classList.add('active');

    });
});

// Related products carousel (scroll/drag, no auto slide)
(function() {
    const track = document.getElementById('relatedTrack');
    const prevBtn = document.getElementById('relatedPrev');
    const nextBtn = document.getElementById('relatedNext');
    if (!track || !prevBtn || !nextBtn) return;

    const scrollByCards = (direction) => {
        const cards = track.querySelectorAll('.related-card');
        if (!cards.length) return;
        const cardWidth = cards[0].offsetWidth + 16; // gap
        const perView = window.innerWidth >= 992 ? 4 : (window.innerWidth >= 768 ? 3 : 2);
        const delta = cardWidth * perView * direction;
        track.parentElement.scrollBy({ left: delta, behavior: 'smooth' });
    };

    prevBtn.addEventListener('click', () => scrollByCards(-1));
    nextBtn.addEventListener('click', () => scrollByCards(1));

    // Drag with mouse
    const wrapper = track.parentElement;
    let isDown = false, startX = 0, startScrollLeft = 0;

    wrapper.addEventListener('mousedown', (e) => {
        isDown = true;
        startX = e.pageX - wrapper.offsetLeft;
        startScrollLeft = wrapper.scrollLeft;
        wrapper.classList.add('dragging');
        e.preventDefault();
    });
    wrapper.addEventListener('mouseleave', () => {
        isDown = false;
        wrapper.classList.remove('dragging');
    });
    wrapper.addEventListener('mouseup', () => {
        isDown = false;
        wrapper.classList.remove('dragging');
    });
    wrapper.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        const x = e.pageX - wrapper.offsetLeft;
        const walk = (x - startX) * 1.5;
        wrapper.scrollLeft = startScrollLeft - walk;
    });

    // Touch
    let touchStartX = 0, touchScrollLeft = 0, isTouching = false;
    wrapper.addEventListener('touchstart', (e) => {
        if (e.touches.length !== 1) return;
        isTouching = true;
        touchStartX = e.touches[0].clientX;
        touchScrollLeft = wrapper.scrollLeft;
    }, { passive: true });
    wrapper.addEventListener('touchmove', (e) => {
        if (!isTouching || e.touches.length !== 1) return;
        const dx = e.touches[0].clientX - touchStartX;
        wrapper.scrollLeft = touchScrollLeft - dx;
    }, { passive: true });
    wrapper.addEventListener('touchend', () => { isTouching = false; }, { passive: true });
    wrapper.addEventListener('touchcancel', () => { isTouching = false; }, { passive: true });
})();
</script>

<?php
// Đóng kết nối database
if (isset($conn)) {
    closeDB($conn);
}
include 'includes/footer.php';
?>

<script>
// Hàm thêm vào giỏ hàng
function addToCart(productId) {
    const quantity = document.getElementById('productQuantity').value;
    const selectedSize = document.querySelector('.size-btn.active')?.dataset.size || '';

    // Có thể thêm AJAX call ở đây để thêm vào giỏ hàng
    fetch('cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=add&product_id=' + productId + '&quantity=' + quantity + '&size=' + selectedSize
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Đã thêm vào giỏ hàng!');
            location.reload(); // Reload để cập nhật số lượng trong giỏ
        } else {
            alert('Có lỗi xảy ra: ' + (data.message || 'Vui lòng thử lại'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Fallback: redirect đến cart với tham số
        window.location.href = 'cart.php?add=' + productId + '&qty=' + quantity;
    });
}

// Hàm mua ngay
function buyNow(productId) {
    const quantity = document.getElementById('productQuantity').value;
    const selectedSize = document.querySelector('.size-btn.active')?.dataset.size || '';

    // Thêm vào giỏ và chuyển đến checkout
    window.location.href = 'checkout.php?product_id=' + productId + '&quantity=' + quantity + '&size=' + selectedSize;
}

// Image Modal Functions
function openImageModal() {
    const modal = document.getElementById('imageModal');
    const mainImg = document.getElementById('mainProductImg');

    // Lấy index ảnh hiện tại
    const currentSrc = mainImg.src;
    const currentPath = currentSrc.substring(currentSrc.lastIndexOf('/') + 1);
    const currentIdx = galleryImages.indexOf(currentPath);
    if (currentIdx !== -1) {
        currentGalleryIndex = currentIdx;
    }

    updateModalImage();
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
}

// Close modal when clicking outside
document.getElementById('imageModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

function closeImageModal() {
    const modal = document.getElementById('imageModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

function updateModalImage() {
    if (galleryImages.length === 0) return;

    const imgPath = 'assets/images/' + galleryImages[currentGalleryIndex];
    const modalImg = document.getElementById('modalImage');
    if (modalImg) {
        modalImg.src = imgPath;
    }

    // Update counter
    document.getElementById('modalCounter').textContent = (currentGalleryIndex + 1) + '/' + galleryImages.length;

    // Update active thumbnail in modal
    const modalThumbnails = document.querySelectorAll('.modal-thumbnail-item');
    modalThumbnails.forEach((item, idx) => {
        item.classList.toggle('active', idx === currentGalleryIndex);
    });
}

function navigateModalGallery(direction) {
    if (galleryImages.length === 0) return;
    currentGalleryIndex += direction;
    if (currentGalleryIndex < 0) currentGalleryIndex = galleryImages.length - 1;
    if (currentGalleryIndex >= galleryImages.length) currentGalleryIndex = 0;

    updateModalImage();
    syncMainImage(currentGalleryIndex);
}

function selectModalImage(index) {
    if (index >= 0 && index < galleryImages.length) {
        currentGalleryIndex = index;
        updateModalImage();
        syncMainImage(index);
    }
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});

// Close modal when clicking outside (after DOM loaded)
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('imageModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeImageModal();
            }
        });
    }

    // Swipe/Drag functionality for modal image with smooth animation
    const modalImageWrapper = document.querySelector('.modal-image-wrapper');
    const modalImageContainer = document.getElementById('modalImageContainer');
    if (modalImageWrapper && modalImageContainer) {
        let startX = 0;
        let startY = 0;
        let currentX = 0;
        let currentY = 0;
        let isDragging = false;
        let hasMoved = false;
        let currentTranslateX = 0;
        let animationId = null;

        function updateTransform() {
            modalImageContainer.style.transform = `translateX(${currentTranslateX}px)`;
        }

        function resetTransform() {
            modalImageContainer.style.transition = 'transform 0.3s ease-out';
            currentTranslateX = 0;
            updateTransform();
            setTimeout(() => {
                modalImageContainer.style.transition = '';
            }, 300);
        }

        // Touch events (mobile)
        modalImageWrapper.addEventListener('touchstart', function(e) {
            if (e.touches.length === 1) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                isDragging = true;
                hasMoved = false;
                currentTranslateX = 0;
                modalImageContainer.style.transition = '';
            }
        }, { passive: true });

        modalImageWrapper.addEventListener('touchmove', function(e) {
            if (isDragging && e.touches.length === 1) {
                currentX = e.touches[0].clientX;
                currentY = e.touches[0].clientY;
                const diffX = currentX - startX;
                const diffY = currentY - startY;

                // Chỉ swipe ngang nếu di chuyển ngang nhiều hơn dọc
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 10) {
                    hasMoved = true;
                    currentTranslateX = diffX;
                    updateTransform();
                    e.preventDefault();
                }
            }
        }, { passive: false });

        modalImageWrapper.addEventListener('touchend', function(e) {
            if (isDragging && hasMoved) {
                const diffX = currentX - startX;
                const threshold = 100; // Minimum swipe distance

                if (Math.abs(diffX) > threshold) {
                    if (diffX > 0) {
                        // Swipe right - go to previous image
                        navigateModalGallery(-1);
                    } else {
                        // Swipe left - go to next image
                        navigateModalGallery(1);
                    }
                }
                resetTransform();
            } else if (isDragging) {
                resetTransform();
            }
            isDragging = false;
            hasMoved = false;
        }, { passive: true });

        // Mouse events (desktop)
        modalImageWrapper.addEventListener('mousedown', function(e) {
            startX = e.clientX;
            startY = e.clientY;
            isDragging = true;
            hasMoved = false;
            currentTranslateX = 0;
            modalImageContainer.style.transition = '';
            modalImageWrapper.style.cursor = 'grabbing';
        });

        modalImageWrapper.addEventListener('mousemove', function(e) {
            if (isDragging) {
                currentX = e.clientX;
                currentY = e.clientY;
                const diffX = currentX - startX;
                const diffY = currentY - startY;

                // Chỉ drag ngang nếu di chuyển ngang nhiều hơn dọc
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 10) {
                    hasMoved = true;
                    currentTranslateX = diffX;
                    updateTransform();
                }
            }
        });

        modalImageWrapper.addEventListener('mouseup', function(e) {
            if (isDragging && hasMoved) {
                const diffX = currentX - startX;
                const threshold = 100; // Minimum drag distance

                if (Math.abs(diffX) > threshold) {
                    if (diffX > 0) {
                        // Drag right - go to previous image
                        navigateModalGallery(-1);
                    } else {
                        // Drag left - go to next image
                        navigateModalGallery(1);
                    }
                }
                resetTransform();
            } else if (isDragging) {
                resetTransform();
            }
            isDragging = false;
            hasMoved = false;
            modalImageWrapper.style.cursor = 'grab';
        });

        modalImageWrapper.addEventListener('mouseleave', function(e) {
            if (isDragging) {
                resetTransform();
            }
            isDragging = false;
            hasMoved = false;
            modalImageWrapper.style.cursor = 'grab';
        });

        // Set initial cursor
        modalImageWrapper.style.cursor = 'grab';
    }

    // Swipe/Drag functionality for main image with smooth animation
    const mainImageWrapper = document.querySelector('.main-image-wrapper');
    const mainImageContainer = document.getElementById('mainImageContainer');
    if (mainImageWrapper && mainImageContainer) {
        let startX = 0;
        let startY = 0;
        let currentX = 0;
        let currentY = 0;
        let isDragging = false;
        let hasMoved = false;
        let currentTranslateX = 0;

        function updateMainTransform() {
            mainImageContainer.style.transform = `translateX(${currentTranslateX}px)`;
        }

        function resetMainTransform() {
            mainImageContainer.style.transition = 'transform 0.3s ease-out';
            currentTranslateX = 0;
            updateMainTransform();
            setTimeout(() => {
                mainImageContainer.style.transition = '';
            }, 300);
        }

        // Touch
        mainImageWrapper.addEventListener('touchstart', function(e) {
            if (e.touches.length === 1) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
                isDragging = true;
                hasMoved = false;
                currentTranslateX = 0;
                mainImageContainer.style.transition = '';
            }
        }, { passive: true });

        mainImageWrapper.addEventListener('touchmove', function(e) {
            if (isDragging && e.touches.length === 1) {
                currentX = e.touches[0].clientX;
                currentY = e.touches[0].clientY;
                const diffX = currentX - startX;
                const diffY = currentY - startY;
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 10) {
                    hasMoved = true;
                    currentTranslateX = diffX;
                    updateMainTransform();
                    e.preventDefault();
                }
            }
        }, { passive: false });

        mainImageWrapper.addEventListener('touchend', function(e) {
            if (isDragging && hasMoved) {
                const diffX = currentX - startX;
                const threshold = 100;
                if (Math.abs(diffX) > threshold) {
                    navigateGallery(diffX > 0 ? -1 : 1);
                }
                resetMainTransform();
            } else if (isDragging) {
                resetMainTransform();
            }
            isDragging = false;
            hasMoved = false;
        }, { passive: true });

        // Mouse
        mainImageWrapper.addEventListener('mousedown', function(e) {
            startX = e.clientX;
            startY = e.clientY;
            isDragging = true;
            hasMoved = false;
            mainImageWrapper.style.cursor = 'grabbing';
        });

        mainImageWrapper.addEventListener('mousemove', function(e) {
            if (isDragging) {
                currentX = e.clientX;
                currentY = e.clientY;
                const diffX = currentX - startX;
                const diffY = currentY - startY;
                if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 10) {
                    hasMoved = true;
                }
            }
        });

        mainImageWrapper.addEventListener('mouseup', function(e) {
            if (isDragging && hasMoved) {
                const diffX = currentX - startX;
                const threshold = 50;
                if (Math.abs(diffX) > threshold) {
                    navigateModalGallery(diffX > 0 ? -1 : 1);
                }
            }
            isDragging = false;
            hasMoved = false;
            mainImageWrapper.style.cursor = 'grab';
        });

        mainImageWrapper.addEventListener('mouseleave', function(e) {
            isDragging = false;
            hasMoved = false;
            mainImageWrapper.style.cursor = 'grab';
        });

        mainImageWrapper.style.cursor = 'grab';
    }
});

// Đồng bộ ảnh chính khi đổi bằng modal
function syncMainImage(index) {
    if (galleryImages.length === 0) return;
    const imgPath = 'assets/images/' + galleryImages[index];
    changeMainImage(imgPath, null, index);
}

// Ngăn kéo ảnh mặc định
document.addEventListener('DOMContentLoaded', function() {
    const mainImg = document.getElementById('mainProductImg');
    const modalImg = document.getElementById('modalImage');
    if (mainImg) {
        mainImg.addEventListener('dragstart', (e) => e.preventDefault());
    }
    if (modalImg) {
        modalImg.addEventListener('dragstart', (e) => e.preventDefault());
    }
});

// Xử lý thêm vào giỏ hàng
function addToCart(productId) {
    const quantity = parseInt(document.getElementById('productQuantity').value) || 1;
    const weightOption = document.querySelector('input[name="weight"]:checked')?.value || null;
    const addToCartBtn = document.getElementById('addToCartBtn');
    
    if (addToCartBtn) {
        addToCartBtn.disabled = true;
        addToCartBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang xử lý...';
    }
    
    fetch('ajax/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}&weight_option=${weightOption || ''}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật số lượng giỏ hàng
            updateCartCount();
            
            // Hiển thị thông báo
            alert(data.message);
            
            if (addToCartBtn) {
                addToCartBtn.disabled = false;
                addToCartBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Add to cart';
            }
        } else {
            alert('Lỗi: ' + data.message);
            if (addToCartBtn) {
                addToCartBtn.disabled = false;
                addToCartBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Add to cart';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi thêm vào giỏ hàng');
        if (addToCartBtn) {
            addToCartBtn.disabled = false;
            addToCartBtn.innerHTML = '<i class="bi bi-cart-plus"></i> Add to cart';
        }
    });
}

// Xử lý mua ngay
function buyNow(productId) {
    const quantity = parseInt(document.getElementById('productQuantity').value) || 1;
    const weightOption = document.querySelector('input[name="weight"]:checked')?.value || null;
    
    // Thêm vào giỏ hàng trước
    fetch('ajax/cart.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=add&product_id=${productId}&quantity=${quantity}&weight_option=${weightOption || ''}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Chuyển đến trang thanh toán
            window.location.href = 'checkout.php';
        } else {
            alert('Lỗi: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra');
    });
}

// Cập nhật số lượng giỏ hàng
function updateCartCount() {
    fetch('ajax/cart.php?action=get_count')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const cartCountEl = document.querySelector('.cart-count');
                if (cartCountEl) {
                    cartCountEl.textContent = data.cart_count;
                    cartCountEl.style.display = data.cart_count > 0 ? 'inline' : 'none';
                }
            }
        })
        .catch(error => console.error('Error:', error));
}

// Gán sự kiện cho nút
document.addEventListener('DOMContentLoaded', function() {
    const addToCartBtn = document.getElementById('addToCartBtn');
    const buyNowBtn = document.getElementById('buyNowBtn');
    
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            addToCart(<?= (int)$product['id'] ?>);
        });
    }
    
    if (buyNowBtn) {
        buyNowBtn.addEventListener('click', function() {
            buyNow(<?= (int)$product['id'] ?>);
        });
    }
    
    // Cập nhật số lượng giỏ hàng khi tải trang
    updateCartCount();
});
</script>

