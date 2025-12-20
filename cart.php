<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = "Giỏ hàng - Bách Hóa Xanh";

// Kết nối database
$conn = connectDB();

// Lấy giỏ hàng từ session/database
$cartItems = getCartItems($conn);
$subtotal = calculateCartTotal($cartItems);
$total = $subtotal;

include 'includes/header.php';
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
                            <img src="assets/images/<?= htmlspecialchars($item['img'] ?? '1.jpg') ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                        </div>
                        <div class="cart-item-name">
                            <?= htmlspecialchars($item['name']) ?>
                            <?php if (!empty($item['weight_option'])): ?>
                                <span class="text-muted">(<?= htmlspecialchars($item['weight_option']) ?>)</span>
                            <?php endif; ?>
                        </div>
                        <div class="cart-item-price"><?= number_format($item['price'], 0, ',', '.') ?>₫</div>
                        <div class="cart-item-quantity">
                            <button class="qty-btn qty-minus" type="button" data-product-id="<?= $item['product_id'] ?>">-</button>
                            <input type="number" class="qty-input" value="<?= $item['quantity'] ?>" min="1" data-product-id="<?= $item['product_id'] ?>" data-price="<?= $item['price'] ?>">
                            <button class="qty-btn qty-plus" type="button" data-product-id="<?= $item['product_id'] ?>">+</button>
                        </div>
                        <div class="cart-item-subtotal"><?= number_format($itemSubtotal, 0, ',', '.') ?>₫</div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (empty($cartItems)): ?>
                        <div style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                            <p>Giỏ hàng của bạn đang trống.</p>
                            <a href="products.php" class="btn btn-primary mt-3">Tiếp tục mua sắm</a>
                        </div>
                    <?php endif; ?>
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

<?php 
// Đóng kết nối database
if (isset($conn)) {
    closeDB($conn);
}
include 'includes/footer.php'; 
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý cập nhật số lượng
    const qtyInputs = document.querySelectorAll('.qty-input');
    const qtyMinusBtns = document.querySelectorAll('.qty-minus');
    const qtyPlusBtns = document.querySelectorAll('.qty-plus');
    const removeBtns = document.querySelectorAll('.cart-remove-btn');
    const updateCartBtn = document.querySelector('.btn-update-cart');
    
    // Nút tăng/giảm số lượng
    qtyPlusBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.qty-input');
            const currentValue = parseInt(input.value) || 1;
            input.value = currentValue + 1;
            updateCartItem(input);
        });
    });
    
    qtyMinusBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.qty-input');
            const currentValue = parseInt(input.value) || 1;
            if (currentValue > 1) {
                input.value = currentValue - 1;
                updateCartItem(input);
            }
        });
    });
    
    // Thay đổi số lượng trực tiếp
    qtyInputs.forEach(input => {
        input.addEventListener('change', function() {
            updateCartItem(this);
        });
    });
    
    // Xóa sản phẩm
    removeBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const cartItem = this.closest('.cart-item');
            const input = cartItem.querySelector('.qty-input');
            const productId = input.getAttribute('data-product-id');
            
            if (confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?')) {
                removeCartItem(productId, cartItem);
            }
        });
    });
    
    // Cập nhật giỏ hàng
    if (updateCartBtn) {
        updateCartBtn.addEventListener('click', function() {
            location.reload();
        });
    }
    
    // Hàm cập nhật số lượng sản phẩm
    function updateCartItem(input) {
        const productId = input.getAttribute('data-product-id');
        const quantity = parseInt(input.value) || 1;
        const cartItem = input.closest('.cart-item');
        
        fetch('ajax/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=update&product_id=${productId}&quantity=${quantity}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Cập nhật subtotal của item
                const price = parseFloat(input.getAttribute('data-price'));
                const subtotal = price * quantity;
                const subtotalEl = cartItem.querySelector('.cart-item-subtotal');
                if (subtotalEl) {
                    subtotalEl.textContent = formatPrice(subtotal);
                }
                
                // Cập nhật tổng tiền
                updateCartTotals(data.subtotal, data.total);
            } else {
                alert('Lỗi: ' + data.message);
                location.reload();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra');
        });
    }
    
    // Hàm xóa sản phẩm
    function removeCartItem(productId, cartItem) {
        fetch('ajax/cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=remove&product_id=${productId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cartItem.remove();
                
                // Cập nhật tổng tiền
                updateCartTotals(data.subtotal, data.total);
                
                // Kiểm tra nếu giỏ hàng trống
                const cartItems = document.querySelectorAll('.cart-item');
                if (cartItems.length === 0) {
                    location.reload();
                }
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Có lỗi xảy ra');
        });
    }
    
    // Cập nhật tổng tiền
    function updateCartTotals(subtotal, total) {
        const subtotalEl = document.querySelector('.totals-value:not(.totals-total)');
        const totalEl = document.querySelector('.totals-total');
        
        if (subtotalEl) {
            subtotalEl.textContent = formatPrice(subtotal);
        }
        if (totalEl) {
            totalEl.textContent = formatPrice(total);
        }
    }
    
    // Format giá
    function formatPrice(price) {
        return new Intl.NumberFormat('vi-VN').format(price) + '₫';
    }
});
</script>

