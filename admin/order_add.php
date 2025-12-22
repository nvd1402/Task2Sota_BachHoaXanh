<?php
session_start();
require_once '../includes/auth.php';
require_once '../config/database.php';

// Kiểm tra quyền admin
requireAdmin();

$pageTitle = "Thêm Đơn hàng - Admin Dashboard";

// Kết nối database
$conn = connectDB();

// Lấy danh sách sản phẩm và người dùng
$productsSql = "SELECT id, name, price, sale_price, image, sku FROM products WHERE status = 'active' ORDER BY name ASC";
$productsResult = $conn->query($productsSql);
$products = [];
while ($row = $productsResult->fetch_assoc()) {
    $products[] = $row;
}

$usersSql = "SELECT id, username, full_name, email FROM users WHERE status = 1 ORDER BY username ASC";
$usersResult = $conn->query($usersSql);
$users = [];
while ($row = $usersResult->fetch_assoc()) {
    $users[] = $row;
}

// Xử lý thêm đơn hàng
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_order'])) {
    $userId = !empty($_POST['user_id']) ? (int)$_POST['user_id'] : null;
    $customerName = trim($_POST['customer_name'] ?? '');
    $customerEmail = trim($_POST['customer_email'] ?? '');
    $customerPhone = trim($_POST['customer_phone'] ?? '');
    $customerAddress = trim($_POST['customer_address'] ?? '');
    $customerCity = trim($_POST['customer_city'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'cod');
    $paymentStatus = trim($_POST['payment_status'] ?? 'pending');
    $shippingMethod = trim($_POST['shipping_method'] ?? 'standard');
    $shippingFee = isset($_POST['shipping_fee']) ? (float)$_POST['shipping_fee'] : 0;
    $discount = isset($_POST['discount']) ? (float)$_POST['discount'] : 0;
    $status = trim($_POST['status'] ?? 'pending');
    $notes = trim($_POST['notes'] ?? '');
    
    // Lấy sản phẩm từ form
    $orderItems = [];
    if (isset($_POST['items']) && is_array($_POST['items'])) {
        foreach ($_POST['items'] as $item) {
            if (!empty($item['product_id']) && !empty($item['quantity']) && $item['quantity'] > 0) {
                $orderItems[] = [
                    'product_id' => (int)$item['product_id'],
                    'quantity' => (int)$item['quantity'],
                    'weight_option' => $item['weight_option'] ?? null
                ];
            }
        }
    }
    
    // Validation
    if (empty($customerName) || empty($customerEmail) || empty($customerPhone) || empty($customerAddress)) {
        $errors[] = "Vui lòng điền đầy đủ thông tin khách hàng";
    }
    
    if (empty($orderItems)) {
        $errors[] = "Vui lòng thêm ít nhất một sản phẩm vào đơn hàng";
    }
    
    // Tính tổng tiền
    $subtotal = 0;
    foreach ($orderItems as $item) {
        $productSql = "SELECT price, sale_price FROM products WHERE id = ?";
        $productStmt = $conn->prepare($productSql);
        $productStmt->bind_param("i", $item['product_id']);
        $productStmt->execute();
        $productResult = $productStmt->get_result();
        $product = $productResult->fetch_assoc();
        $productStmt->close();
        
        if ($product) {
            $price = $product['sale_price'] ?? $product['price'];
            $subtotal += $price * $item['quantity'];
        }
    }
    
    $total = $subtotal + $shippingFee - $discount;
    
    // Nếu không có lỗi, thêm đơn hàng
    if (empty($errors)) {
        // Tạo mã đơn hàng
        $orderNumber = 'ORD' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Thêm đơn hàng
        $insertSql = "INSERT INTO orders (
            user_id, order_number, customer_name, customer_email, customer_phone,
            customer_address, customer_city, payment_method, payment_status,
            shipping_method, shipping_fee, subtotal, discount, total, status, notes
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("issssssssddddsss",
            $userId, $orderNumber, $customerName, $customerEmail, $customerPhone,
            $customerAddress, $customerCity, $paymentMethod, $paymentStatus,
            $shippingMethod, $shippingFee, $subtotal, $discount, $total, $status, $notes
        );
        
        if ($insertStmt->execute()) {
            $orderId = $insertStmt->insert_id;
            
            // Thêm chi tiết đơn hàng
            foreach ($orderItems as $item) {
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
                    $unitPrice = $product['sale_price'] ?? $product['price'];
                    $itemSubtotal = $unitPrice * $quantity;
                    
                    $itemStmt->bind_param("iisssisdd",
                        $orderId, $item['product_id'], $productName, $productImage, $productSku,
                        $quantity, $weightOption, $unitPrice, $itemSubtotal
                    );
                    $itemStmt->execute();
                    $itemStmt->close();
                }
            }
            
            $insertStmt->close();
            closeDB($conn);
            header('Location: order_detail.php?id=' . $orderId . '&success=1');
            exit();
        } else {
            $errors[] = "Có lỗi xảy ra khi thêm đơn hàng";
        }
    }
}

closeDB($conn);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?= $pageTitle ?></title>
  <link href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700,800" rel="stylesheet" />
  <link href="../assets/css/nucleo-icons.css" rel="stylesheet" />
  <link href="../assets/css/nucleo-svg.css" rel="stylesheet" />
  <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
  <link id="pagestyle" href="../assets/css/soft-ui-dashboard.css?v=1.1.0" rel="stylesheet" />
</head>

<body class="g-sidenav-show bg-gray-100">
  <?php $currentPage = 'orders'; include 'includes/sidebar.php'; ?>

  <main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg">
    <?php include 'includes/navbar.php'; ?>

    <div class="container-fluid py-4">
      <?php if (!empty($errors)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Lỗi!</strong>
        <ul class="mb-0">
          <?php foreach ($errors as $error): ?>
          <li><?= htmlspecialchars($error) ?></li>
          <?php endforeach; ?>
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header pb-0">
              <div class="row">
                <div class="col-lg-6">
                  <h6>Thêm Đơn hàng</h6>
                </div>
                <div class="col-lg-6 text-end">
                  <a href="orders.php" class="btn bg-gradient-secondary btn-sm mb-0">Quay lại</a>
                </div>
              </div>
            </div>
            <div class="card-body">
              <form method="POST" id="orderForm">
                <div class="row">
                  <div class="col-md-6">
                    <h6 class="mb-3">Thông tin khách hàng</h6>
                    <div class="mb-3">
                      <label class="form-label">Người dùng (tùy chọn)</label>
                      <select name="user_id" id="user_id" class="form-select">
                        <option value="">Chọn người dùng</option>
                        <?php foreach ($users as $user): ?>
                        <option value="<?= $user['id'] ?>" 
                                data-name="<?= htmlspecialchars($user['full_name'] ?? $user['username']) ?>"
                                data-email="<?= htmlspecialchars($user['email']) ?>">
                          <?= htmlspecialchars($user['full_name'] ?? $user['username']) ?> (<?= htmlspecialchars($user['email']) ?>)
                        </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Tên khách hàng *</label>
                      <input type="text" name="customer_name" id="customer_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Email *</label>
                      <input type="email" name="customer_email" id="customer_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Điện thoại *</label>
                      <input type="tel" name="customer_phone" id="customer_phone" class="form-control" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Địa chỉ *</label>
                      <textarea name="customer_address" id="customer_address" class="form-control" rows="2" required></textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Thành phố *</label>
                      <input type="text" name="customer_city" id="customer_city" class="form-control" required>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <h6 class="mb-3">Thông tin đơn hàng</h6>
                    <div class="mb-3">
                      <label class="form-label">Phương thức thanh toán</label>
                      <select name="payment_method" class="form-select">
                        <option value="cod">COD</option>
                        <option value="bank_transfer">Chuyển khoản</option>
                        <option value="e_wallet">Ví điện tử</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Trạng thái thanh toán</label>
                      <select name="payment_status" class="form-select">
                        <option value="pending">Chờ thanh toán</option>
                        <option value="paid">Đã thanh toán</option>
                        <option value="failed">Thất bại</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Phương thức vận chuyển</label>
                      <input type="text" name="shipping_method" class="form-control" value="standard">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Phí vận chuyển (₫)</label>
                      <input type="number" name="shipping_fee" class="form-control" step="0.01" value="0">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Giảm giá (₫)</label>
                      <input type="number" name="discount" class="form-control" step="0.01" value="0">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Trạng thái</label>
                      <select name="status" class="form-select">
                        <option value="pending">Chờ xử lý</option>
                        <option value="processing">Đang xử lý</option>
                        <option value="shipped">Đang giao</option>
                        <option value="delivered">Đã giao</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Ghi chú</label>
                      <textarea name="notes" class="form-control" rows="3"></textarea>
                    </div>
                  </div>
                </div>

                <hr class="my-4">

                <h6 class="mb-3">Sản phẩm trong đơn hàng</h6>
                <div id="orderItems">
                  <div class="order-item-row mb-3 border rounded p-3">
                    <div class="row">
                      <div class="col-md-5">
                        <label class="form-label">Sản phẩm *</label>
                        <select name="items[0][product_id]" class="form-select product-select" required>
                          <option value="">Chọn sản phẩm</option>
                          <?php foreach ($products as $product): ?>
                          <option value="<?= $product['id'] ?>" 
                                  data-price="<?= $product['sale_price'] ?? $product['price'] ?>"
                                  data-name="<?= htmlspecialchars($product['name']) ?>">
                            <?= htmlspecialchars($product['name']) ?> - <?= number_format($product['sale_price'] ?? $product['price'], 0, ',', '.') ?>₫
                          </option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Số lượng *</label>
                        <input type="number" name="items[0][quantity]" class="form-control quantity-input" min="1" value="1" required>
                      </div>
                      <div class="col-md-3">
                        <label class="form-label">Đơn giá</label>
                        <input type="text" class="form-control unit-price" readonly value="0₫">
                      </div>
                      <div class="col-md-1">
                        <label class="form-label">&nbsp;</label>
                        <button type="button" class="btn btn-danger btn-sm w-100 remove-item" style="display: none;">
                          <i class="fas fa-trash"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
                <button type="button" class="btn btn-sm bg-gradient-info mb-3" id="addItemBtn">
                  <i class="fas fa-plus"></i> Thêm sản phẩm
                </button>

                <div class="mt-4">
                  <div class="row">
                    <div class="col-md-6">
                      <h6>Tổng cộng</h6>
                      <p class="text-sm">Tạm tính: <span id="subtotal">0</span>₫</p>
                      <p class="text-sm">Phí vận chuyển: <span id="shippingFee">0</span>₫</p>
                      <p class="text-sm">Giảm giá: <span id="discount">0</span>₫</p>
                      <p class="h5">Tổng: <span id="total">0</span>₫</p>
                    </div>
                  </div>
                </div>

                <div class="mt-4">
                  <button type="submit" name="add_order" class="btn bg-gradient-primary">Thêm đơn hàng</button>
                  <a href="orders.php" class="btn bg-gradient-secondary">Hủy</a>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <script src="../assets/js/core/popper.min.js"></script>
  <script src="../assets/js/core/bootstrap.min.js"></script>
  <script src="../assets/js/plugins/perfect-scrollbar.min.js"></script>
  <script src="../assets/js/soft-ui-dashboard.min.js?v=1.1.0"></script>
  <script>
    let itemIndex = 1;

    // Auto-fill customer info when user is selected
    document.getElementById('user_id').addEventListener('change', function() {
      const option = this.options[this.selectedIndex];
      if (option.value) {
        document.getElementById('customer_name').value = option.dataset.name || '';
        document.getElementById('customer_email').value = option.dataset.email || '';
      }
    });

    // Add new item row
    document.getElementById('addItemBtn').addEventListener('click', function() {
      const itemsContainer = document.getElementById('orderItems');
      const newRow = itemsContainer.firstElementChild.cloneNode(true);
      
      // Update input names
      newRow.querySelectorAll('input, select').forEach(input => {
        if (input.name) {
          input.name = input.name.replace(/\[0\]/, '[' + itemIndex + ']');
        }
        if (input.classList.contains('quantity-input')) {
          input.value = 1;
        }
        if (input.classList.contains('unit-price')) {
          input.value = '0₫';
        }
      });
      
      // Show remove button
      newRow.querySelector('.remove-item').style.display = 'block';
      
      itemsContainer.appendChild(newRow);
      itemIndex++;
      
      // Attach event listeners
      attachItemListeners(newRow);
    });

    // Remove item row
    document.addEventListener('click', function(e) {
      if (e.target.closest('.remove-item')) {
        const row = e.target.closest('.order-item-row');
        if (document.getElementById('orderItems').children.length > 1) {
          row.remove();
          calculateTotal();
        }
      }
    });

    // Attach listeners to item row
    function attachItemListeners(row) {
      const productSelect = row.querySelector('.product-select');
      const quantityInput = row.querySelector('.quantity-input');
      const unitPriceInput = row.querySelector('.unit-price');

      productSelect.addEventListener('change', function() {
        const option = this.options[this.selectedIndex];
        const price = parseFloat(option.dataset.price) || 0;
        unitPriceInput.value = new Intl.NumberFormat('vi-VN').format(price) + '₫';
        calculateTotal();
      });

      quantityInput.addEventListener('input', calculateTotal);
    }

    // Calculate total
    function calculateTotal() {
      let subtotal = 0;
      
      document.querySelectorAll('.order-item-row').forEach(row => {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        
        if (productSelect.value && quantityInput.value) {
          const option = productSelect.options[productSelect.selectedIndex];
          const price = parseFloat(option.dataset.price) || 0;
          const quantity = parseFloat(quantityInput.value) || 0;
          subtotal += price * quantity;
        }
      });
      
      const shippingFee = parseFloat(document.querySelector('input[name="shipping_fee"]').value) || 0;
      const discount = parseFloat(document.querySelector('input[name="discount"]').value) || 0;
      const total = subtotal + shippingFee - discount;
      
      document.getElementById('subtotal').textContent = new Intl.NumberFormat('vi-VN').format(subtotal);
      document.getElementById('shippingFee').textContent = new Intl.NumberFormat('vi-VN').format(shippingFee);
      document.getElementById('discount').textContent = new Intl.NumberFormat('vi-VN').format(discount);
      document.getElementById('total').textContent = new Intl.NumberFormat('vi-VN').format(total);
    }

    // Attach listeners to existing items
    document.querySelectorAll('.order-item-row').forEach(row => {
      attachItemListeners(row);
    });

    // Listen to shipping fee and discount changes
    document.querySelector('input[name="shipping_fee"]').addEventListener('input', calculateTotal);
    document.querySelector('input[name="discount"]').addEventListener('input', calculateTotal);
  </script>
</body>
</html>

