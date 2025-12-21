<?php
// Bật output buffering để tránh lỗi header
if (ob_get_level() == 0) {
    ob_start();
}

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

// Debug: Log cart items
error_log('Checkout - Cart items count: ' . count($cartItems));
error_log('Checkout - Session cart: ' . print_r($_SESSION['cart'] ?? [], true));
error_log('Checkout - Cart items: ' . print_r($cartItems, true));

// Nếu giỏ hàng trống, chuyển về trang giỏ hàng
if (empty($cartItems)) {
    error_log('Checkout - Cart is empty, redirecting to cart.php');
    header('Location: cart.php');
    exit();
}

// Xử lý đặt hàng
$orderPlaced = false;
$orderNumber = '';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // Debug: Log POST data
    error_log('Checkout POST - place_order received');
    error_log('Checkout POST - Cart items before refresh: ' . count($cartItems));
    error_log('Checkout POST - Session cart: ' . print_r($_SESSION['cart'] ?? [], true));
    
    // Lấy lại giỏ hàng để đảm bảo có dữ liệu mới nhất
    $cartItems = getCartItems($conn);
    if (empty($cartItems)) {
        $error = 'Giỏ hàng của bạn đã trống. Vui lòng thêm sản phẩm vào giỏ hàng trước khi đặt hàng.';
        error_log('Checkout POST - Cart is empty after POST');
    } else {
        error_log('Checkout POST - Cart items after refresh: ' . count($cartItems));
    }
    
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
    
    // Kiểm tra giỏ hàng trước khi xử lý
    if (empty($cartItems)) {
        $errors[] = 'Giỏ hàng của bạn đã trống. Vui lòng thêm sản phẩm vào giỏ hàng trước khi đặt hàng.';
        error_log('Checkout POST - Cart is empty!');
        error_log('Checkout POST - Session cart: ' . print_r($_SESSION['cart'] ?? [], true));
    }
    
    if (empty($errors)) {
        error_log('Checkout POST - No validation errors, proceeding with order creation');
        // Tạo mã đơn hàng
        $orderNumber = 'ORD' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Tính tổng tiền
        $subtotal = calculateCartTotal($cartItems);
        if ($subtotal <= 0) {
            $errors[] = 'Tổng tiền đơn hàng không hợp lệ.';
        }
        
        $shippingFee = 30000; // Phí vận chuyển mặc định
        $discount = 0;
        $total = $subtotal + $shippingFee - $discount;
        
        // Lấy user_id nếu đã đăng nhập
        $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        
        // Phương thức thanh toán: chỉ COD
        $paymentMethod = 'cod';
        $paymentStatus = 'pending';
        
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
            error_log('Checkout - Order created with ID: ' . $orderId . ', Order number: ' . $orderNumber);
            error_log('Checkout - Processing ' . count($cartItems) . ' cart items');
            
            // Thêm chi tiết đơn hàng
            $itemCount = 0;
            foreach ($cartItems as $item) {
                $itemCount++;
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
                    if (!$itemStmt->execute()) {
                        error_log('Checkout - Error inserting order item: ' . $itemStmt->error);
                        throw new Exception('Có lỗi xảy ra khi thêm sản phẩm vào đơn hàng');
                    }
                    $itemStmt->close();
                    error_log('Checkout - Order item added: ' . $productName . ' x' . $quantity);
                } else {
                    error_log('Checkout - Product not found for ID: ' . $item['product_id']);
                }
            }
            
            error_log('Checkout - Total items processed: ' . $itemCount);
            
            // Xóa giỏ hàng từ database và session
            $userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
            $sessionId = session_id();
            
            // Xóa từ database
            if ($userId) {
                $deleteSql = "DELETE FROM cart WHERE user_id = ?";
                $deleteStmt = $conn->prepare($deleteSql);
                $deleteStmt->bind_param("i", $userId);
            } else {
                $deleteSql = "DELETE FROM cart WHERE session_id = ? AND user_id IS NULL";
                $deleteStmt = $conn->prepare($deleteSql);
                $deleteStmt->bind_param("s", $sessionId);
            }
            $deleteStmt->execute();
            $deleteStmt->close();
            
            // Xóa từ session
            $_SESSION['cart'] = [];
            
            error_log('Checkout - Cart cleared from database and session');
            
            $orderPlaced = true;
            $stmt->close();
            
            error_log('Checkout - Order created successfully. Order ID: ' . $orderId . ', Order Number: ' . $orderNumber);
            
            // Đảm bảo không có output trước header
            while (ob_get_level() > 0) {
                ob_end_clean();
            }
            
            // Chuyển đến trang hoàn tất đơn hàng
            $redirectUrl = 'order-complete.php?order=' . urlencode($orderNumber);
            error_log('Checkout - Redirecting to: ' . $redirectUrl);
            header('Location: ' . $redirectUrl);
            exit();
        } else {
            $errorMsg = 'Có lỗi xảy ra khi tạo đơn hàng: ' . $stmt->error;
            error_log('Checkout - Database error: ' . $errorMsg);
            error_log('Checkout - SQL error code: ' . $stmt->errno);
            $error = $errorMsg;
            $stmt->close();
        }
    } else {
        // Hiển thị tất cả lỗi validation
        $error = implode('<br>', $errors);
        // Debug: log errors
        error_log('Checkout validation errors: ' . $error);
        error_log('Checkout POST data: ' . print_r($_POST, true));
        error_log('Checkout cart items count: ' . count($cartItems));
        error_log('Checkout session cart: ' . print_r($_SESSION['cart'] ?? [], true));
    }
} else {
    // Debug: Kiểm tra xem có POST request không
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        error_log('POST request received but place_order not set.');
        error_log('POST keys: ' . implode(', ', array_keys($_POST)));
        error_log('POST data: ' . print_r($_POST, true));
        $error = 'Lỗi: Không nhận được yêu cầu đặt hàng. Vui lòng thử lại.';
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
        <script>
            console.error('Checkout Error:', <?= json_encode($error) ?>);
            console.error('POST Data:', <?= json_encode($_POST ?? []) ?>);
            console.error('Cart Items Count:', <?= count($cartItems ?? []) ?>);
            alert('Lỗi: <?= addslashes($error) ?>');
        </script>
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
                                <input type="hidden" name="payment_method" value="cod">
                                <label style="display: flex; align-items: center; gap: 10px; cursor: default;">
                                    <i class="bi bi-cash-coin" style="font-size: 24px; color: #3da04d;"></i>
                                    <div>
                                        <strong>Trả tiền mặt khi nhận hàng (COD)</strong>
                                        <span class="payment-desc" style="display: block; color: #666; font-size: 14px; margin-top: 5px;">Thanh toán khi nhận được hàng</span>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" name="place_order" value="1" class="btn-place-order" id="place-order-btn">PLACE ORDER</button>

                    <p class="privacy-notice">
                        Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our privacy policy.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<script src="assets/js/checkout.js"></script>

