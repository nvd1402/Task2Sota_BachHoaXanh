<?php
session_start();
$pageTitle = "Thanh toán - Bách Hóa Xanh";
include 'includes/header.php';

// Mock cart data (same as cart page)
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

<main class="checkout-page">
    <!-- Breadcrumb -->
    <div class="checkout-breadcrumb">
        <div class="container">
            <div class="breadcrumb-content">
                <span class="breadcrumb-prev">SHOPPING CART</span>
                <span class="breadcrumb-separator">></span>
                <span class="breadcrumb-current">CHECKOUT DETAILS</span>
                <span class="breadcrumb-separator">></span>
                <span class="breadcrumb-next">ORDER COMPLETE</span>
            </div>
        </div>
    </div>

    <!-- Checkout Content -->
    <div class="container">
        <!-- Coupon Section -->
        <div class="checkout-coupon-section">
            <p class="coupon-link">
                Have a coupon? <a href="#" id="toggle-coupon" class="coupon-toggle-link">Click here to enter your code</a>
            </p>

            <div class="coupon-form-box" id="coupon-form-box" style="display: none;">
                <p class="coupon-instruction">If you have a coupon code, please apply it below.</p>
                <div class="coupon-input-group">
                    <input type="text" class="coupon-code-input" placeholder="Coupon code">
                    <button type="button" class="btn-apply-coupon-checkout">APPLY COUPON</button>
                </div>
            </div>
        </div>

        <div class="checkout-layout">
            <!-- Left: Billing Details -->
            <div class="checkout-billing-section">
                <h2 class="billing-title">BILLING DETAILS</h2>

                <form class="billing-form" action="#" method="post">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="first-name">First name *</label>
                            <input type="text" id="first-name" name="first_name" required>
                        </div>
                        <div class="form-group">
                            <label for="last-name">Last name *</label>
                            <input type="text" id="last-name" name="last_name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="company-name">Company name (optional)</label>
                        <input type="text" id="company-name" name="company_name">
                    </div>

                    <div class="form-group">
                        <label for="country">Country / Region *</label>
                        <select id="country" name="country" required>
                            <option value="vietnam" selected>Vietnam</option>
                            <option value="afghanistan">Afghanistan</option>
                            <option value="albania">Albania</option>
                            <option value="algeria">Algeria</option>
                            <option value="argentina">Argentina</option>
                            <option value="australia">Australia</option>
                            <option value="austria">Austria</option>
                            <option value="bangladesh">Bangladesh</option>
                            <option value="belgium">Belgium</option>
                            <option value="brazil">Brazil</option>
                            <option value="cambodia">Cambodia</option>
                            <option value="canada">Canada</option>
                            <option value="china">China</option>
                            <option value="denmark">Denmark</option>
                            <option value="egypt">Egypt</option>
                            <option value="finland">Finland</option>
                            <option value="france">France</option>
                            <option value="germany">Germany</option>
                            <option value="greece">Greece</option>
                            <option value="hongkong">Hong Kong</option>
                            <option value="india">India</option>
                            <option value="indonesia">Indonesia</option>
                            <option value="iran">Iran</option>
                            <option value="iraq">Iraq</option>
                            <option value="ireland">Ireland</option>
                            <option value="israel">Israel</option>
                            <option value="italy">Italy</option>
                            <option value="japan">Japan</option>
                            <option value="kenya">Kenya</option>
                            <option value="korea">Korea</option>
                            <option value="laos">Laos</option>
                            <option value="malaysia">Malaysia</option>
                            <option value="mexico">Mexico</option>
                            <option value="myanmar">Myanmar</option>
                            <option value="netherlands">Netherlands</option>
                            <option value="newzealand">New Zealand</option>
                            <option value="norway">Norway</option>
                            <option value="pakistan">Pakistan</option>
                            <option value="philippines">Philippines</option>
                            <option value="poland">Poland</option>
                            <option value="portugal">Portugal</option>
                            <option value="russia">Russia</option>
                            <option value="singapore">Singapore</option>
                            <option value="southafrica">South Africa</option>
                            <option value="spain">Spain</option>
                            <option value="sweden">Sweden</option>
                            <option value="switzerland">Switzerland</option>
                            <option value="taiwan">Taiwan</option>
                            <option value="thailand">Thailand</option>
                            <option value="turkey">Turkey</option>
                            <option value="ukraine">Ukraine</option>
                            <option value="unitedkingdom">United Kingdom</option>
                            <option value="usa">United States</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="address-1">Street address *</label>
                        <input type="text" id="address-1" name="address_1" placeholder="House number and street name" value="19b Đường Số 9" required>
                        <input type="text" id="address-2" name="address_2" placeholder="Apartment, suite, unit, etc. (optional)" value="suite" class="mt-2">
                    </div>

                    <div class="form-group">
                        <label for="postcode">Postcode / ZIP (optional)</label>
                        <input type="text" id="postcode" name="postcode" value="00700">
                    </div>

                    <div class="form-group">
                        <label for="city">Town / City *</label>
                        <input type="text" id="city" name="city" value="Bình Chiểu" required>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone *</label>
                        <input type="tel" id="phone" name="phone" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email address *</label>
                        <input type="email" id="email" name="email" required>
                    </div>

                    <h3 class="additional-title">ADDITIONAL INFORMATION</h3>

                    <div class="form-group">
                        <label for="order-notes">Order notes (optional)</label>
                        <textarea id="order-notes" name="order_notes" rows="4" placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
                    </div>
                </form>
            </div>

            <!-- Right: Order Summary -->
            <div class="checkout-order-section">
                <div class="order-summary-box">
                    <h3 class="order-title">YOUR ORDER</h3>

                    <div class="order-items">
                        <?php foreach ($cartItems as $item): 
                            $itemSubtotal = $item['price'] * $item['quantity'];
                        ?>
                        <div class="order-item">
                            <span class="order-item-name"><?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?></span>
                            <span class="order-item-price"><?= number_format($itemSubtotal, 0, ',', '.') ?>₫</span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-totals">
                        <div class="order-total-row">
                            <span class="total-label">Subtotal</span>
                            <span class="total-value"><?= number_format($subtotal, 0, ',', '.') ?>₫</span>
                        </div>
                        <div class="order-total-row">
                            <span class="total-label">Total</span>
                            <span class="total-value total-final"><?= number_format($total, 0, ',', '.') ?>₫</span>
                        </div>
                    </div>

                    <div class="payment-method">
                        <h4 class="payment-title">Trả tiền mặt khi nhận hàng</h4>
                        <p class="payment-description">Trả tiền mặt khi giao hàng</p>
                    </div>

                    <button type="submit" class="btn-place-order" id="place-order-btn" disabled>PLACE ORDER</button>

                    <p class="privacy-notice">
                        Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our privacy policy.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle coupon form
    const toggleCoupon = document.getElementById('toggle-coupon');
    const couponFormBox = document.getElementById('coupon-form-box');
    
    if (toggleCoupon && couponFormBox) {
        toggleCoupon.addEventListener('click', function(e) {
            e.preventDefault();
            if (couponFormBox.style.display === 'none') {
                couponFormBox.style.display = 'block';
            } else {
                couponFormBox.style.display = 'none';
            }
        });
    }

    // Form validation and submit
    const billingForm = document.querySelector('.billing-form');
    const placeOrderBtn = document.getElementById('place-order-btn');
    const requiredFields = billingForm.querySelectorAll('[required]');

    function validateForm() {
        let allFilled = true;
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                allFilled = false;
            }
        });
        
        if (allFilled) {
            placeOrderBtn.disabled = false;
            placeOrderBtn.style.opacity = '1';
            placeOrderBtn.style.cursor = 'pointer';
        } else {
            placeOrderBtn.disabled = true;
            placeOrderBtn.style.opacity = '0.6';
            placeOrderBtn.style.cursor = 'not-allowed';
        }
    }

    // Check on input change
    requiredFields.forEach(function(field) {
        field.addEventListener('input', validateForm);
        field.addEventListener('change', validateForm);
    });

    // Initial check
    validateForm();

    // Handle form submit
    if (billingForm) {
        billingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Check if all required fields are filled
            let allFilled = true;
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    allFilled = false;
                    field.style.borderColor = '#d0021b';
                } else {
                    field.style.borderColor = '#ddd';
                }
            });

            if (allFilled) {
                // Redirect to order complete page
                window.location.href = 'order-complete.php';
            } else {
                alert('Vui lòng điền đầy đủ các trường bắt buộc.');
            }
        });
    }

    // Handle place order button click
    if (placeOrderBtn) {
        placeOrderBtn.addEventListener('click', function(e) {
            e.preventDefault();
            billingForm.dispatchEvent(new Event('submit'));
        });
    }
});
</script>

