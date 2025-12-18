<?php
session_start();
$pageTitle = "Chi tiết sản phẩm - Bách Hóa Xanh";
include 'includes/header.php';
?>

<main class="product-detail-page">
    <div class="container">
        <div class="product-detail-layout">
            
            <!-- LEFT COLUMN - PRODUCT DETAILS -->
            <div class="product-detail-main">
                
                <!-- Product Image -->
                <div class="product-image-section">
                    <div class="main-image-wrapper">
                        <img src="assets/images/2.jpg" alt="Thực phẩm hữu cơ sạch" class="main-product-img" id="mainProductImg">
                        <button class="zoom-btn" type="button">
                            <i class="bi bi-arrows-fullscreen"></i>
                        </button>
                    </div>
                    
                    <!-- Thumbnail Images -->
                    <div class="thumbnail-images">
                        <div class="thumbnail-item active" onclick="changeMainImage('assets/images/2.jpg', this)">
                            <img src="assets/images/2.jpg" alt="Thumbnail 1">
                        </div>
                        <div class="thumbnail-item" onclick="changeMainImage('assets/images/4.jpg', this)">
                            <img src="assets/images/4.jpg" alt="Thumbnail 2">
                        </div>
                        <div class="thumbnail-item" onclick="changeMainImage('assets/images/1.jpg', this)">
                            <img src="assets/images/1.jpg" alt="Thumbnail 3">
                        </div>
                        <div class="thumbnail-item" onclick="changeMainImage('assets/images/5.jpg', this)">
                            <img src="assets/images/5.jpg" alt="Thumbnail 4">
                        </div>
                    </div>
                </div>

                <!-- Product Info -->
                <div class="product-info-section">
                    <div class="product-badge">-20%</div>
                    <h1 class="product-title">Thực phẩm hữu cơ sạch</h1>
                    <div class="product-price">120,000₫ – 170,000₫</div>
                    
                    <!-- Features -->
                    <ul class="product-features">
                        <li>Đạt chuẩn an toàn Viet Gap</li>
                        <li>Hàng tươi mới trong ngày</li>
                    </ul>

                    <!-- Promotion Box -->
                    <div class="promotion-box">
                        <div class="promotion-title">KHUYẾN MÃI TRỊ GIÁ 300.000₫</div>
                        <div class="promotion-items">
                            <div class="promotion-item">1. Tặng kèm 1 hộp táo đỏ</div>
                            <div class="promotion-item">2. Tặng 1 hộp đường phèn <a href="#" class="promotion-link">(click xem chi tiết)</a></div>
                        </div>
                    </div>

                    <!-- Size Selection -->
                    <div class="size-selection">
                        <label class="size-label">Kích thước :</label>
                        <div class="size-buttons">
                            <button class="size-btn" data-size="M">M</button>
                            <button class="size-btn" data-size="L">L</button>
                            <button class="size-btn active" data-size="S">S</button>
                            <button class="size-btn" data-size="XL">XL</button>
                            <button class="size-btn" data-size="XXL">XXL</button>
                        </div>
                    </div>

                    <!-- Quantity Selection -->
                    <div class="quantity-selection">
                        <label class="quantity-label">Số lượng :</label>
                        <div class="quantity-controls">
                            <button class="qty-btn minus" type="button">-</button>
                            <input type="number" class="qty-input" value="1" min="1" id="productQuantity">
                            <button class="qty-btn plus" type="button">+</button>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button class="btn-add-cart" type="button">
                            <i class="bi bi-cart-plus"></i> Add to cart
                        </button>
                        <button class="btn-buy-now" type="button">BUY NOW</button>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN - FEATURED PRODUCTS -->
            <aside class="product-detail-sidebar">
                <div class="sidebar-featured">
                    <h3 class="sidebar-featured-title">SẢN PHẨM NỔI BẬT</h3>
                    <div class="sidebar-products">
                        <?php
                        $featured = [
                            ['name' => 'Thực phẩm hữu cơ sạch', 'price' => '90,000₫ – 130,000₫', 'img' => '5.jpg'],
                            ['name' => 'Thực phẩm hữu cơ sạch', 'price' => '90,000₫ – 130,000₫', 'img' => '2.jpg'],
                            ['name' => 'Thực phẩm hữu cơ sạch', 'price' => '120,000₫ – 170,000₫', 'img' => '2.jpg'],
                            ['name' => 'Thực phẩm hữu cơ sạch', 'price' => '120,000₫ – 170,000₫', 'img' => '5.jpg'],
                            ['name' => 'Thực phẩm hữu cơ sạch', 'price' => '120,000₫ – 170,000₫', 'img' => '4.jpg'],
                        ];
                        foreach ($featured as $item):
                        ?>
                            <a href="product-detail.php" class="sidebar-product-item">
                                <div class="sidebar-product-img">
                                    <img src="assets/images/<?= $item['img'] ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                </div>
                                <div class="sidebar-product-info">
                                    <h4 class="sidebar-product-name"><?= htmlspecialchars($item['name']) ?></h4>
                                    <p class="sidebar-product-price"><?= htmlspecialchars($item['price']) ?></p>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>

        </div>
    </div>
</main>

<!-- Product Detail Tabs -->
<section class="product-tabs-section">
    <div class="container">
        <div class="product-tabs">
            <button class="tab-btn active" data-tab="desc">Description</button>
            <button class="tab-btn" data-tab="add-info">Additional Information</button>
            <button class="tab-btn" data-tab="reviews">Reviews (0)</button>
        </div>
        <div class="tab-panels">
            <div class="tab-panel active" id="desc">
                <p>
                    Hơn năm ngàn năm về trước, những nhà nông Trung Hoa đã khám phá và trồng một loại cây đậu mà sau đó đã trở thành một loại thực phẩm thiết yếu cho các dân tộc Á châu và thế giới ngày nay. Cây đậu này được biết đến là đậu nành, cũng còn gọi là cây đậu tương.
                </p>
                <p>
                    Trong suốt những thiên niên kỷ sau đó, đậu nành đã vượt biên sang các nước lân bang như Nhật Bản, Đại Hàn, Việt Nam, Nam Dương và Mã Lai. Đến Nhật Bản vào thế kỷ thứ 8 sau Tây lịch và khoảng một ngàn năm sau đó mới qua đến Âu châu.
                </p>
                <div class="tab-image">
                    <img src="assets/images/banner3.jpg" alt="Mô tả sản phẩm">
                </div>
            </div>
            <div class="tab-panel" id="add-info">
                <div class="info-row">
                    <span class="info-label">Trọng lượng</span>
                    <span class="info-value">1kg, 2kg, 3kg, 4kg, 5kg</span>
                </div>
            </div>
            <div class="tab-panel" id="reviews">
                <p class="no-review">There are no reviews yet.</p>
                <form class="review-form">
                    <h4 class="review-title">Be the first to review “Thực phẩm hữu cơ sạch”</h4>
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
                    <?php
                    $related = [
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'90,000₫ – 130,000₫','off'=>'19%','img'=>'1.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'120,000₫ – 170,000₫','off'=>'20%','img'=>'4.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'90,000₫ – 130,000₫','off'=>'19%','img'=>'2.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'120,000₫ – 170,000₫','off'=>'20%','img'=>'5.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'90,000₫ – 130,000₫','off'=>'19%','img'=>'1.jpg'],
                        ['name'=>'Thực phẩm hữu cơ sạch','price'=>'120,000₫ – 170,000₫','off'=>'20%','img'=>'4.jpg'],
                    ];
                    foreach ($related as $item): ?>
                    <a href="product-detail.php" class="related-card">
                        <div class="related-thumb">
                            <img src="assets/images/bg_sale.png" class="sale-badge" alt="Sale">
                            <span class="sale-text">-<?= htmlspecialchars($item['off']) ?></span>
                            <img src="assets/images/<?= $item['img'] ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        </div>
                        <div class="related-body">
                            <p class="related-name"><?= htmlspecialchars($item['name']) ?></p>
                            <p class="related-price"><?= htmlspecialchars($item['price']) ?></p>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <button id="relatedNext" class="related-nav-btn" type="button" aria-label="Next">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
</section>

<script>
function changeMainImage(src, element) {
    document.getElementById('mainProductImg').src = src;
    // Update active thumbnail
    document.querySelectorAll('.thumbnail-item').forEach(item => {
        item.classList.remove('active');
    });
    if (element) {
        element.classList.add('active');
    }
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

<?php include 'includes/footer.php'; ?>

