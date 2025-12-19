<?php
session_start();
$pageTitle = "Hoàn tất đơn hàng - Bách Hóa Xanh";
include 'includes/header.php';

// Mock order data
$orderNumber = 1078;
$orderDate = "Tháng 12 19, 2025";
$paymentMethod = "Trả tiền mặt khi nhận hàng";

$orderItems = [
    [
        'id' => 1,
        'name' => 'Thực phẩm hữu cơ sạch - 3KG',
        'weight' => '3KG',
        'quantity' => 1,
        'price' => 130000
    ],
    [
        'id' => 2,
        'name' => 'Thực phẩm hữu cơ sạch - 2KG',
        'weight' => '2KG',
        'quantity' => 1,
        'price' => 110000
    ]
];

$subtotal = 0;
foreach ($orderItems as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$total = $subtotal;
?>

<main class="order-complete-page">
    <!-- Breadcrumb -->
    <div class="order-complete-breadcrumb">
        <div class="container">
            <div class="breadcrumb-content">
                <span class="breadcrumb-prev">SHOPPING CART</span>
                <span class="breadcrumb-separator">></span>
                <span class="breadcrumb-prev">CHECKOUT DETAILS</span>
                <span class="breadcrumb-separator">></span>
                <span class="breadcrumb-current">ORDER COMPLETE</span>
            </div>
        </div>
    </div>

    <!-- Order Complete Content -->
    <div class="container">
        <div class="order-complete-layout">
            <!-- Left: Order Details -->
            <div class="order-details-section">
                <p class="payment-method-text">Trả tiền mặt khi giao hàng</p>
                
                <h2 class="order-details-title">Order details</h2>

                <div class="order-details-content">
                    <div class="order-details-header-row">
                        <span class="order-header-product">PRODUCT</span>
                        <span class="order-header-total">TOTAL</span>
                    </div>

                    <div class="order-details-items">
                        <?php foreach ($orderItems as $item): 
                            $itemTotal = $item['price'] * $item['quantity'];
                        ?>
                        <div class="order-details-item-row">
                            <div class="order-item-info">
                                <span class="order-item-name"><?= htmlspecialchars($item['name']) ?> x <?= $item['quantity'] ?></span>
                                <span class="order-item-weight">Trọng lượng: <?= htmlspecialchars($item['weight']) ?></span>
                            </div>
                            <span class="order-item-total"><?= number_format($itemTotal, 0, ',', '.') ?>₫</span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-details-summary">
                        <div class="order-summary-row">
                            <span class="summary-label">Subtotal:</span>
                            <span class="summary-value"><?= number_format($subtotal, 0, ',', '.') ?>₫</span>
                        </div>
                        <div class="order-summary-row">
                            <span class="summary-label">Payment method:</span>
                            <span class="summary-value"><?= htmlspecialchars($paymentMethod) ?></span>
                        </div>
                        <div class="order-summary-row">
                            <span class="summary-label">Total:</span>
                            <span class="summary-value summary-total"><?= number_format($total, 0, ',', '.') ?>₫</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Order Confirmation -->
            <div class="order-confirmation-section">
                <div class="confirmation-box">
                    <p class="confirmation-message">Thank you. Your order has been received.</p>
                    
                    <ul class="confirmation-details">
                        <li>
                            <span class="confirmation-label">Order number</span>
                            <span class="confirmation-value"><?= $orderNumber ?></span>
                        </li>
                        <li>
                            <span class="confirmation-label">Date</span>
                            <span class="confirmation-value"><?= htmlspecialchars($orderDate) ?></span>
                        </li>
                        <li>
                            <span class="confirmation-label">Total</span>
                            <span class="confirmation-value confirmation-total"><?= number_format($total, 0, ',', '.') ?>₫</span>
                        </li>
                        <li>
                            <span class="confirmation-label">Payment method</span>
                            <span class="confirmation-value"><?= htmlspecialchars($paymentMethod) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

