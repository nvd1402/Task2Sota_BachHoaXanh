/**
 * Checkout JavaScript
 * Xử lý form checkout và thanh toán
 */

// Xử lý thanh toán
function processPayment(orderId, paymentMethod) {
    if (!orderId || !paymentMethod) {
        alert('Thông tin thanh toán không hợp lệ');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'process_payment');
    formData.append('order_id', orderId);
    formData.append('payment_method', paymentMethod);
    
    return fetch('ajax/payment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            return data.data;
        } else {
            throw new Error(data.message || 'Có lỗi xảy ra khi xử lý thanh toán');
        }
    });
}

// Xác minh thanh toán (cho bank transfer)
function verifyPayment(orderId, transactionCode) {
    if (!orderId || !transactionCode) {
        alert('Vui lòng nhập mã giao dịch');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'verify_payment');
    formData.append('order_id', orderId);
    formData.append('transaction_code', transactionCode);
    
    return fetch('ajax/payment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            return data.data;
        } else {
            throw new Error(data.message || 'Có lỗi xảy ra khi xác minh');
        }
    });
}

// Validate checkout form
function validateCheckoutForm() {
    const form = document.querySelector('.billing-form');
    if (!form) return false;
    
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    let allFilled = true;
    
    requiredFields.forEach(field => {
        const value = field.value.trim();
        
        if (!value) {
            isValid = false;
            allFilled = false;
            field.style.borderColor = '#d0021b';
            field.classList.add('is-invalid');
        } else {
            field.style.borderColor = '#ddd';
            field.classList.remove('is-invalid');
            
            // Validate email
            if (field.type === 'email' && value) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    isValid = false;
                    field.style.borderColor = '#d0021b';
                    field.classList.add('is-invalid');
                }
            }
            
            // Validate phone
            if (field.type === 'tel' && value) {
                const phoneRegex = /^[\d\s\+\-\(\)]{10,15}$/;
                if (!phoneRegex.test(value)) {
                    isValid = false;
                    field.style.borderColor = '#d0021b';
                    field.classList.add('is-invalid');
                }
            }
        }
    });
    
    // Payment method is always COD, no need to check
    
    return isValid && allFilled;
}

// Handle form submit
document.addEventListener('DOMContentLoaded', function() {
    const billingForm = document.querySelector('.billing-form');
    const placeOrderBtn = document.getElementById('place-order-btn');
    
    if (billingForm && placeOrderBtn) {
        // Real-time validation
        const requiredFields = billingForm.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('blur', function() {
                validateCheckoutForm();
            });
            
            field.addEventListener('input', function() {
                this.classList.remove('is-invalid');
                if (validateCheckoutForm()) {
                    placeOrderBtn.disabled = false;
                }
            });
        });
        
        // Payment method is always COD, no need to handle change
        
        // Enable button nếu form hợp lệ
        function updateButtonState() {
            const isValid = validateCheckoutForm();
            if (isValid) {
                placeOrderBtn.disabled = false;
                placeOrderBtn.style.opacity = '1';
                placeOrderBtn.style.cursor = 'pointer';
            } else {
                placeOrderBtn.disabled = true;
                placeOrderBtn.style.opacity = '0.6';
                placeOrderBtn.style.cursor = 'not-allowed';
            }
        }
        
        // Update button state on input
        requiredFields.forEach(field => {
            field.addEventListener('input', updateButtonState);
            field.addEventListener('change', updateButtonState);
        });
        
        // Initial button state - check after a short delay to allow form to render
        setTimeout(updateButtonState, 100);
        
        // Form submit - chỉ validate, KHÔNG preventDefault nếu hợp lệ
        billingForm.addEventListener('submit', function(e) {
            console.log('Form submit triggered');
            
            // Validate form
            const isValid = validateCheckoutForm();
            console.log('Form validation result:', isValid);
            
            if (!isValid) {
                e.preventDefault();
                alert('Vui lòng điền đầy đủ thông tin hợp lệ');
                return false;
            }
            
            console.log('Form is valid, allowing submit');
            
            // Disable button to prevent double submit
            placeOrderBtn.disabled = true;
            placeOrderBtn.textContent = 'Đang xử lý...';
            
            // KHÔNG preventDefault - để form submit bình thường đến checkout.php
            // PHP sẽ xử lý và redirect đến order-complete.php
            // Form sẽ submit với POST và place_order=1
        });
    }
    
    // Coupon toggle
    const toggleCoupon = document.getElementById('toggle-coupon');
    const couponFormBox = document.getElementById('coupon-form-box');
    
    if (toggleCoupon && couponFormBox) {
        toggleCoupon.addEventListener('click', function(e) {
            e.preventDefault();
            couponFormBox.style.display = couponFormBox.style.display === 'none' ? 'block' : 'none';
        });
    }
    
    // Apply coupon
    const applyCouponBtn = document.querySelector('.btn-apply-coupon-checkout');
    if (applyCouponBtn) {
        applyCouponBtn.addEventListener('click', function() {
            const couponCode = document.querySelector('.coupon-code-input').value.trim();
            if (!couponCode) {
                alert('Vui lòng nhập mã giảm giá');
                return;
            }
            
            // TODO: Implement coupon validation
            alert('Tính năng áp dụng mã giảm giá đang được phát triển');
        });
    }
});

