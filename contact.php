<?php
// Bắt đầu output buffering ngay từ đầu để bắt mọi output
ob_start();

// Xử lý AJAX request TRƯỚC khi include bất kỳ file nào để tránh output HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    // Kiểm tra nhiều cách để xác định AJAX request
    $isAjax = (
        (isset($_POST['ajax']) && $_POST['ajax'] === '1') ||
        (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
        (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false)
    );
    
    if ($isAjax) {
        // Xóa mọi output đã bắt được
        ob_end_clean();
        
        session_start();
        require_once 'config/database.php';
        
        // Set header JSON
        header('Content-Type: application/json; charset=utf-8');
    
    // Kết nối database
    $conn = connectDB();
    
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $message = trim($_POST['message'] ?? '');
    
    // Validate
    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập họ và tên']);
        exit();
    }
    
    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập email']);
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email không hợp lệ']);
        exit();
    }
    
    if (empty($message)) {
        echo json_encode(['success' => false, 'message' => 'Vui lòng nhập nội dung liên hệ']);
        exit();
    }
    
    // Lấy IP address
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
    
    // Lưu vào database
    try {
        $sql = "INSERT INTO contact (name, email, phone, subject, message, ip_address, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'new')";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $subject = 'Liên hệ từ website';
        $stmt->bind_param("ssssss", $name, $email, $phone, $subject, $message, $ipAddress);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.']);
        } else {
            throw new Exception('Execute failed: ' . $stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        error_log('Contact form error: ' . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Có lỗi xảy ra khi gửi liên hệ. Vui lòng thử lại sau.']);
    } finally {
        if (isset($conn)) {
            closeDB($conn);
        }
    }
    exit();
    }
}

// Nếu không phải AJAX request, tiếp tục load trang bình thường
// Xóa output buffer và tiếp tục
ob_end_flush();

session_start();
require_once 'config/database.php';

$pageTitle = "Liên hệ - Bách Hóa Xanh";

// Kết nối database
$conn = connectDB();

include 'includes/header.php';
?>

<main class="contact-page">

    <!-- ================= MAP SECTION ================= -->
    <div class="contact-map-section">
        <div class="container">
            <div class="map-container">
                <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3725.727471746137!2d105.82941152189149!3d20.963456786090013!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3135ac5526be0f83%3A0x8bd2ffe68188acfd!2zQ2h1bmcgY8awIFZQNSBMaW5oIMSQw6Bt!5e0!3m2!1svi!2s!4v1766109793147!5m2!1svi!2s"
                        width="100%"
                        height="100%"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>

    <!-- ================= CONTACT CONTENT ================= -->
    <div class="container">
        <div class="contact-content">

            <!-- LEFT: INFO -->
            <div class="contact-info-col">
                <h2 class="contact-heading">GREEN FOOD</h2>

                <p class="contact-intro">
                    Mọi thắc mắc quý khách vui lòng liên hệ tới chúng tôi thông qua thông tin bên dưới
                    hoặc điền thông tin vào form bên cạnh. Chúng tôi sẽ phản hồi trong thời gian sớm nhất.
                </p>

                <div class="contact-details">
                    <div class="contact-item">
                        <i class="bi bi-geo-alt-fill contact-icon"></i>
                        <span class="contact-text">
                            Địa chỉ: Chung cư VP5 Linh Đàm, P. Hoàng Liệt, Q. Hoàng Mai, Hà Nội
                        </span>
                    </div>

                    <div class="contact-item">
                        <i class="bi bi-envelope-fill contact-icon"></i>
                        <span class="contact-text">Email: webdemo@gmail.com</span>
                    </div>

                    <div class="contact-item">
                        <i class="bi bi-telephone-fill contact-icon"></i>
                        <span class="contact-text">Điện thoại: 0387 969 xxx</span>
                    </div>
                </div>

                <div class="contact-badge">
                    <img src="assets/images/Untitled-7.png" alt="Đã thông báo Bộ Công Thương">
                </div>
            </div>

            <!-- RIGHT: FORM -->
            <div class="contact-form-col">
                <form class="contact-form" action="contact.php" method="post" id="contactForm">
                    <div class="form-group">
                        <input type="text" name="name" class="form-control" placeholder="Họ và tên" 
                               value="<?= isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <input type="email" name="email" class="form-control" placeholder="Địa chỉ email" 
                               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <input type="tel" name="phone" class="form-control" placeholder="Số điện thoại" 
                               value="<?= isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '' ?>" required>
                    </div>

                    <div class="form-group">
                        <textarea name="message" class="form-control" rows="6"
                                  placeholder="Nhập nội dung liên hệ" required><?= isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '' ?></textarea>
                    </div>

                    <button type="submit" name="submit_contact" class="contact-submit-btn">
                        GỬI LIÊN HỆ
                    </button>
                </form>
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

<!-- Contact Form Handler with Toast -->
<style>
.contact-form {
    transition: opacity 0.3s ease;
}

.contact-form.reset-animation {
    animation: formReset 0.5s ease;
}

@keyframes formReset {
    0% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
        transform: translateY(-5px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.contact-form input:focus,
.contact-form textarea:focus {
    outline: none;
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contactForm');
    
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form trước khi gửi
            if (!contactForm.checkValidity()) {
                contactForm.reportValidity();
                return;
            }
            
            const formData = new FormData(contactForm);
            formData.append('ajax', '1'); // Đánh dấu là AJAX request
            formData.append('submit_contact', '1'); // Đảm bảo submit_contact được gửi
            
            // Debug: log form data
            console.log('Form data:', {
                name: formData.get('name'),
                email: formData.get('email'),
                phone: formData.get('phone'),
                message: formData.get('message'),
                ajax: formData.get('ajax'),
                submit_contact: formData.get('submit_contact')
            });
            
            const submitBtn = contactForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.textContent;
            
            // Disable button while submitting
            submitBtn.disabled = true;
            submitBtn.textContent = 'Đang gửi...';
            
            // Send AJAX request với header
            fetch('contact.php', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(response => {
                // Kiểm tra response status
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                // Kiểm tra content type
                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    return response.text().then(text => {
                        console.error('Response is not JSON:', text);
                        throw new Error('Response is not JSON');
                    });
                }
                return response.json();
            })
            .then(data => {
                if (data && data.success) {
                    showToast(data.message, 'success');
                    
                    // Reset form hoàn toàn
                    contactForm.reset();
                    
                    // Reset validation states và styles
                    const inputs = contactForm.querySelectorAll('input, textarea');
                    inputs.forEach(input => {
                        input.classList.remove('is-invalid', 'is-valid');
                        input.style.borderColor = '';
                        input.style.backgroundColor = '';
                    });
                    
                    // Thêm hiệu ứng reset animation
                    contactForm.classList.add('reset-animation');
                    setTimeout(() => {
                        contactForm.classList.remove('reset-animation');
                    }, 500);
                    
                    // Focus vào trường đầu tiên để có thể gửi tiếp ngay
                    const firstInput = contactForm.querySelector('input[type="text"]');
                    if (firstInput) {
                        setTimeout(() => {
                            firstInput.focus();
                            // Scroll to form nếu cần (nếu form không trong viewport)
                            firstInput.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        }, 500);
                    }
                } else {
                    showToast(data && data.message ? data.message : 'Có lỗi xảy ra. Vui lòng thử lại.', 'error');
                }
            })
            .catch(error => {
                console.error('Error details:', error);
                showToast('Có lỗi xảy ra khi gửi liên hệ. Vui lòng kiểm tra lại thông tin và thử lại.', 'error');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        });
    }
    
    // Hàm showToast (sử dụng chung với newsletter)
    function showToast(message, type) {
        // Container đã có trong footer.php, chỉ cần lấy
        const container = document.getElementById('toast-container');
        if (!container) {
            console.error('Toast container not found');
            return;
        }
        
        // Tạo toast element
        const toast = document.createElement('div');
        toast.className = `toast-notification ${type}`;
        
        // Icon và nội dung
        const icon = type === 'success' ? '✓' : '✕';
        const title = type === 'success' ? 'Thành công!' : 'Lỗi!';
        
        toast.innerHTML = `
            <div class="toast-icon">${icon}</div>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                <div class="toast-message">${message}</div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()">&times;</button>
        `;
        
        // Thêm vào container
        container.appendChild(toast);
        
        // Tự động xóa sau 5 giây
        setTimeout(() => {
            toast.classList.add('hiding');
            setTimeout(() => {
                if (toast.parentElement) {
                    toast.remove();
                }
            }, 300);
        }, 5000);
    }
});
</script>
