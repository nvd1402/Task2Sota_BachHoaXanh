/**
 * Order Management JavaScript
 * Xử lý cập nhật trạng thái đơn hàng qua AJAX
 */

// Cập nhật trạng thái đơn hàng
function updateOrderStatus(orderId, status, paymentStatus) {
    if (!orderId || !status || !paymentStatus) {
        alert('Vui lòng điền đầy đủ thông tin');
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'update_status');
    formData.append('order_id', orderId);
    formData.append('status', status);
    formData.append('payment_status', paymentStatus);
    
    fetch('../ajax/order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Hiển thị thông báo thành công
            showNotification('success', 'Cập nhật trạng thái thành công');
            // Reload trang sau 1 giây
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification('error', data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Có lỗi xảy ra khi kết nối server');
    });
}

// Hủy đơn hàng
function cancelOrder(orderId, reason) {
    if (!orderId) {
        alert('ID đơn hàng không hợp lệ');
        return;
    }
    
    if (!reason || reason.trim() === '') {
        reason = prompt('Vui lòng nhập lý do hủy đơn hàng:');
        if (!reason || reason.trim() === '') {
            return;
        }
    }
    
    if (!confirm('Bạn có chắc chắn muốn hủy đơn hàng này?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'cancel_order');
    formData.append('order_id', orderId);
    formData.append('reason', reason);
    
    fetch('../ajax/order.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('success', 'Đơn hàng đã được hủy');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            showNotification('error', data.message || 'Có lỗi xảy ra');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('error', 'Có lỗi xảy ra khi kết nối server');
    });
}

// Lấy thông tin đơn hàng
function getOrderDetails(orderId) {
    return fetch(`../ajax/order.php?action=get_order&order_id=${orderId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                return data.data;
            } else {
                throw new Error(data.message || 'Không thể lấy thông tin đơn hàng');
            }
        });
}

// Hiển thị thông báo
function showNotification(type, message) {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Xóa thông báo cũ nếu có
    const existingAlert = document.querySelector('.alert[role="alert"]');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    document.body.insertAdjacentHTML('beforeend', alertHtml);
    
    // Tự động ẩn sau 5 giây
    setTimeout(() => {
        const alert = document.querySelector('.alert[role="alert"]');
        if (alert) {
            alert.remove();
        }
    }, 5000);
}

// Quick update status buttons
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý quick update buttons nếu có
    document.querySelectorAll('.quick-update-status').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const orderId = this.dataset.orderId;
            const status = this.dataset.status;
            const paymentStatus = this.dataset.paymentStatus || 'pending';
            
            updateOrderStatus(orderId, status, paymentStatus);
        });
    });
    
    // Xử lý cancel order buttons
    document.querySelectorAll('.cancel-order-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const orderId = this.dataset.orderId;
            cancelOrder(orderId);
        });
    });
});

