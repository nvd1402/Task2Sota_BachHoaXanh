<?php
session_start();
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = "Thanh toán - Bách Hóa Xanh";

// Kết nối database
$conn = connectDB();

// Lấy giỏ hàng từ session/database
$cartItems = getCartItems($conn);
$subtotal = calculateCartTotal($cartItems);
$total = $subtotal;

// Nếu giỏ hàng trống, chuyển về trang giỏ hàng
if (empty($cartItems)) {
    header('Location: cart.php');
    exit();
}

// Xử lý đặt hàng
$orderPlaced = false;
$orderNumber = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Lấy thông tin từ form và làm sạch dữ liệu
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $companyName = trim($_POST['company_name'] ?? '');
    $country = trim($_POST['country'] ?? 'vietnam');
    $address1 = trim($_POST['address_1'] ?? '');
    $address2 = trim($_POST['address_2'] ?? '');
    $postcode = trim($_POST['postcode'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $orderNotes = trim($_POST['order_notes'] ?? '');
    
    // Validate dữ liệu đầu vào
    $errors = [];
    
    // Kiểm tra các trường bắt buộc
    if (empty($firstName)) {
        $errors[] = 'Vui lòng nhập tên';
    } elseif (strlen($firstName) < 2) {
        $errors[] = 'Tên phải có ít nhất 2 ký tự';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Vui lòng nhập họ';
    } elseif (strlen($lastName) < 2) {
        $errors[] = 'Họ phải có ít nhất 2 ký tự';
    }
    
    if (empty($address1)) {
        $errors[] = 'Vui lòng nhập địa chỉ';
    }
    
    if (empty($city)) {
        $errors[] = 'Vui lòng nhập thành phố';
    }
    
    // Validate số điện thoại (chỉ số và khoảng trắng, dấu +, dấu -)
    if (empty($phone)) {
        $errors[] = 'Vui lòng nhập số điện thoại';
    } elseif (!preg_match('/^[\d\s\+\-\(\)]{10,15}$/', $phone)) {
        $errors[] = 'Số điện thoại không hợp lệ';
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = 'Vui lòng nhập email';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email không hợp lệ';
    }
    
    if (empty($errors)) {
        // Tạo mã đơn hàng
        $orderNumber = 'ORD' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Tính tổng tiền
        $subtotal = calculateCartTotal($cartItems);
        $shippingFee = 30000; // Phí vận chuyển mặc định
        $discount = 0;
        $total = $subtotal + $shippingFee - $discount;
        
        // Lấy user_id nếu đã đăng nhập
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        
        // Lấy phương thức thanh toán
        $paymentMethod = trim($_POST['payment_method'] ?? 'cod');
        $paymentStatus = ($paymentMethod === 'bank_transfer' || $paymentMethod === 'e_wallet') ? 'pending' : 'pending';
        
        // Tạo đơn hàng
        $customerName = $firstName . ' ' . $lastName;
        $fullAddress = $address1;
        if (!empty($address2)) {
            $fullAddress .= ', ' . $address2;
        }
        
        $sql = "INSERT INTO orders (
            user_id, order_number, customer_name, customer_email, customer_phone,
            customer_address, customer_city, customer_district, customer_ward,
            payment_method, payment_status, shipping_method, shipping_fee,
            subtotal, discount, total, status, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $shippingMethod = 'standard';
        $orderStatus = 'pending';
        $district = '';
        $ward = '';
        
        $stmt->bind_param(
            "issssssssssidddsss",
            $userId, $orderNumber, $customerName, $email, $phone,
            $fullAddress, $city, $district, $ward,
            $paymentMethod, $paymentStatus, $shippingMethod, $shippingFee,
            $subtotal, $discount, $total, $orderStatus, $orderNotes
        );
        
        if ($stmt->execute()) {
            $orderId = $stmt->insert_id;
            
            // Thêm chi tiết đơn hàng
            foreach ($cartItems as $item) {
                // Lấy thông tin sản phẩm
                $productSql = "SELECT * FROM products WHERE id = ?";
                $productStmt = $conn->prepare($productSql);
                $productStmt->bind_param("i", $item['product_id']);
                $productStmt->execute();
                $productResult = $productStmt->get_result();
                $product = $productResult->fetch_assoc();
                $productStmt->close();
                
                if ($product) {
                    $itemSql = "INSERT INTO order_items (
                        order_id, product_id, product_name, product_image, product_sku,
                        quantity, weight_option, unit_price, subtotal
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    $itemStmt = $conn->prepare($itemSql);
                    $productName = $product['name'];
                    $productImage = $product['image'];
                    $productSku = $product['sku'] ?? '';
                    $quantity = $item['quantity'];
                    $weightOption = $item['weight_option'] ?? null;
                    $unitPrice = $item['price'];
                    $itemSubtotal = $unitPrice * $quantity;
                    
                    $itemStmt->bind_param(
                        "iisssisdd",
                        $orderId, $item['product_id'], $productName, $productImage, $productSku,
                        $quantity, $weightOption, $unitPrice, $itemSubtotal
                    );
                    $itemStmt->execute();
                    $itemStmt->close();
                }
            }
            
            // Xóa giỏ hàng
            $_SESSION['cart'] = [];
            
            $orderPlaced = true;
            $stmt->close();
            
            // Chuyển đến trang hoàn tất đơn hàng
            header('Location: order-complete.php?order=' . urlencode($orderNumber));
            exit();
        } else {
            $error = 'Có lỗi xảy ra khi tạo đơn hàng. Vui lòng thử lại.';
            $stmt->close();
        }
    } else {
        // Hiển thị tất cả lỗi validation
        $error = implode('<br>', $errors);
    }
}

include 'includes/header.php';
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
        <!-- Error/Success Messages -->
        <?php if (!empty($error)): ?>
        <div class="alert alert-error" style="background-color: #fee; border: 1px solid #fcc; color: #c33; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
            <strong>Lỗi:</strong> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
        <div class="alert alert-success" style="background-color: #efe; border: 1px solid #cfc; color: #3c3; padding: 15px; margin-bottom: 20px; border-radius: 5px;">
            <strong>Thành công:</strong> <?= htmlspecialchars($success) ?>
        </div>
        <?php endif; ?>
        
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

                <form class="billing-form" action="checkout.php" method="post">
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
                        <input type="tel" id="phone" name="phone" pattern="[\d\s\+\-\(\)]{10,15}" placeholder="VD: 0901234567" required>
                        <small style="color: #666; font-size: 12px;">Nhập số điện thoại hợp lệ (10-15 số)</small>
                    </div>

                    <div class="form-group">
                        <label for="email">Email address *</label>
                        <input type="email" id="email" name="email" placeholder="example@email.com" required>
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
                        <h4 class="payment-title">Phương thức thanh toán</h4>
                        <div class="payment-options">
                            <div class="payment-option">
                                <input type="radio" id="payment-cod" name="payment_method" value="cod" checked>
                                <label for="payment-cod">
                                    <strong>Trả tiền mặt khi nhận hàng (COD)</strong>
                                    <span class="payment-desc">Thanh toán khi nhận được hàng</span>
                                </label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="payment-bank" name="payment_method" value="bank_transfer">
                                <label for="payment-bank">
                                    <strong>Chuyển khoản ngân hàng</strong>
                                    <span class="payment-desc">Chuyển khoản qua tài khoản ngân hàng</span>
                                </label>
                            </div>
                            <div class="payment-option">
                                <input type="radio" id="payment-wallet" name="payment_method" value="e_wallet">
                                <label for="payment-wallet">
                                    <strong>Ví điện tử</strong>
                                    <span class="payment-desc">Thanh toán qua ví điện tử (MoMo, ZaloPay...)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="place_order" class="btn-place-order" id="place-order-btn" disabled>PLACE ORDER</button>

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
        let isValid = true;
        
        requiredFields.forEach(function(field) {
            const value = field.value.trim();
            
            // Kiểm tra trường bắt buộc
            if (!value) {
                allFilled = false;
                field.style.borderColor = '#d0021b';
            } else {
                // Validate email
                if (field.type === 'email' && value) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(value)) {
                        isValid = false;
                        field.style.borderColor = '#d0021b';
                    } else {
                        field.style.borderColor = '#ddd';
                    }
                }
                // Validate phone
                else if (field.type === 'tel' && value) {
                    const phoneRegex = /^[\d\s\+\-\(\)]{10,15}$/;
                    if (!phoneRegex.test(value)) {
                        isValid = false;
                        field.style.borderColor = '#d0021b';
                    } else {
                        field.style.borderColor = '#ddd';
                    }
                }
                // Các trường khác
                else {
                    field.style.borderColor = '#ddd';
                }
            }
        });
        
        if (allFilled && isValid) {
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

            // Validate email format
            const emailField = billingForm.querySelector('[type="email"]');
            let emailValid = true;
            if (emailField && emailField.value.trim()) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(emailField.value.trim())) {
                    emailValid = false;
                    emailField.style.borderColor = '#d0021b';
                }
            }
            
            // Validate phone format
            const phoneField = billingForm.querySelector('[type="tel"]');
            let phoneValid = true;
            if (phoneField && phoneField.value.trim()) {
                const phoneRegex = /^[\d\s\+\-\(\)]{10,15}$/;
                if (!phoneRegex.test(phoneField.value.trim())) {
                    phoneValid = false;
                    phoneField.style.borderColor = '#d0021b';
                }
            }

            if (allFilled && emailValid && phoneValid) {
                // Submit form
                billingForm.submit();
            } else {
                let errorMsg = 'Vui lòng điền đầy đủ các trường bắt buộc.';
                if (!emailValid) {
                    errorMsg += '\n- Email không hợp lệ.';
                }
                if (!phoneValid) {
                    errorMsg += '\n- Số điện thoại không hợp lệ (10-15 số).';
                }
                alert(errorMsg);
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

