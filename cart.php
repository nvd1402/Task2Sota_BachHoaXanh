<?php
session_start();
$pageTitle = "Giỏ hàng - Bách Hóa Xanh";
include 'includes/header.php';

// Mock cart data
$cartItems = [
    [
        'id' => 1,
        'img' => '4.jpg',
        'name' => 'Thực phẩm hữu cơ sạch - 3KG',
        'price' => 130000,
        'quantity' => 1
    ],
    [
        'id' => 2,
        'img' => '5.jpg',
        'name' => 'Thực phẩm hữu cơ sạch - 2KG',
        'price' => 110000,
        'quantity' => 1
    ]
];

$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$total = $subtotal;
?>

<main class="cart-page">
    <!-- Breadcrumb -->
    <div class="cart-breadcrumb">
        <div class="container">
            <div class="breadcrumb-content">
                <span class="breadcrumb-current">SHOPPING CART</span>
                <span class="breadcrumb-separator">></span>
                <span class="breadcrumb-next">CHECKOUT DETAILS</span>
                <span class="breadcrumb-separator">></span>
                <span class="breadcrumb-next">ORDER COMPLETE</span>
            </div>
        </div>
    </div>

    <!-- Cart Content -->
    <div class="container">
        <div class="cart-layout">
            <!-- Left: Product List -->
            <div class="cart-products-section">
                <div class="cart-table-header">
                    <div class="cart-col-product">PRODUCT</div>
                    <div class="cart-col-price">PRICE</div>
                    <div class="cart-col-quantity">QUANTITY</div>
                    <div class="cart-col-subtotal">SUBTOTAL</div>
                </div>

                <div class="cart-items">
                    <?php foreach ($cartItems as $item): 
                        $itemSubtotal = $item['price'] * $item['quantity'];
                    ?>
                    <div class="cart-item">
                        <button class="cart-remove-btn" type="button" aria-label="Remove item">
                            <i class="bi bi-x"></i>
                        </button>
                        <div class="cart-item-image">
                            <img src="assets/images/<?= htmlspecialchars($item['img']) ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        </div>
                        <div class="cart-item-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="cart-item-price"><?= number_format($item['price'], 0, ',', '.') ?>₫</div>
                        <div class="cart-item-quantity">
                            <button class="qty-btn qty-minus" type="button">-</button>
                            <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" min="1" data-price="<?= $item['price'] ?>">
                            <button class="qty-btn qty-plus" type="button">+</button>
                        </div>
                        <div class="cart-item-subtotal"><?= number_format($itemSubtotal, 0, ',', '.') ?>₫</div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="cart-actions">
                    <a href="products.php" class="btn-continue-shopping">
                        <i class="bi bi-arrow-left"></i>
                        CONTINUE SHOPPING
                    </a>
                    <button type="button" class="btn-update-cart">UPDATE CART</button>
                </div>
            </div>

            <!-- Right: Cart Totals & Coupon -->
            <div class="cart-sidebar">
                <!-- Cart Totals -->
                <div class="cart-totals-box">
                    <h3 class="cart-totals-title">CART TOTALS</h3>
                    <div class="cart-totals-row">
                        <span class="totals-label">Subtotal</span>
                        <span class="totals-value"><?= number_format($subtotal, 0, ',', '.') ?>₫</span>
                    </div>
                    <div class="cart-totals-row">
                        <span class="totals-label">Total</span>
                        <span class="totals-value totals-total"><?= number_format($total, 0, ',', '.') ?>₫</span>
                    </div>
                    <a href="checkout.php" class="btn-proceed-checkout">PROCEED TO CHECKOUT</a>
                </div>

                <!-- Coupon Section -->
                <div class="cart-coupon-box">
                    <h3 class="coupon-title">
                        <i class="bi bi-tag"></i>
                        Coupon
                    </h3>
                    <div class="coupon-form">
                        <input type="text" class="coupon-input" placeholder="Coupon code">
                        <button type="button" class="btn-apply-coupon">Apply coupon</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

