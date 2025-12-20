-- ============================================
-- DATABASE BỔ SUNG - BÁCH HÓA XANH
-- Bao gồm: orders, order_items, cart, reviews, news, recruitment, contact
-- ============================================

-- ============================================
-- 1. BẢNG ORDERS (ĐƠN HÀNG)
-- ============================================
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'ID người dùng (NULL nếu khách hàng không đăng nhập)',
  `order_number` varchar(50) NOT NULL COMMENT 'Mã đơn hàng',
  `customer_name` varchar(150) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_phone` varchar(20) NOT NULL,
  `customer_address` text NOT NULL,
  `customer_city` varchar(100) DEFAULT NULL,
  `customer_district` varchar(100) DEFAULT NULL,
  `customer_ward` varchar(100) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT 'cod' COMMENT 'Phương thức thanh toán: cod, bank_transfer, e_wallet',
  `payment_status` enum('pending','paid','failed','refunded') NOT NULL DEFAULT 'pending',
  `shipping_method` varchar(50) DEFAULT 'standard' COMMENT 'Phương thức vận chuyển',
  `shipping_fee` decimal(10,2) DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Tổng tiền sản phẩm',
  `discount` decimal(10,2) DEFAULT 0.00 COMMENT 'Giảm giá',
  `total` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Tổng tiền cuối cùng',
  `status` enum('pending','processing','shipped','delivered','cancelled') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL COMMENT 'Ghi chú của khách hàng',
  `admin_notes` text DEFAULT NULL COMMENT 'Ghi chú của admin',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_number` (`order_number`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `payment_status` (`payment_status`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. BẢNG ORDER_ITEMS (CHI TIẾT ĐƠN HÀNG)
-- ============================================
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL COMMENT 'Tên sản phẩm tại thời điểm đặt hàng',
  `product_image` varchar(255) DEFAULT NULL,
  `product_sku` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `weight_option` varchar(50) DEFAULT NULL COMMENT 'Lựa chọn trọng lượng/kích thước',
  `unit_price` decimal(10,2) NOT NULL COMMENT 'Giá đơn vị tại thời điểm đặt hàng',
  `subtotal` decimal(10,2) NOT NULL COMMENT 'Tổng tiền = quantity * unit_price',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. BẢNG CART (GIỎ HÀNG - Database backup)
-- ============================================
CREATE TABLE IF NOT EXISTS `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL COMMENT 'ID người dùng (NULL nếu guest, dùng session_id)',
  `session_id` varchar(100) DEFAULT NULL COMMENT 'Session ID cho khách hàng chưa đăng nhập',
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `weight_option` varchar(50) DEFAULT NULL COMMENT 'Lựa chọn trọng lượng/kích thước',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `session_id` (`session_id`),
  KEY `product_id` (`product_id`),
  UNIQUE KEY `unique_cart_item` (`user_id`, `session_id`, `product_id`, `weight_option`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. BẢNG REVIEWS (ĐÁNH GIÁ SẢN PHẨM)
-- ============================================
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'ID người dùng (NULL nếu khách hàng không đăng nhập)',
  `customer_name` varchar(150) NOT NULL COMMENT 'Tên khách hàng',
  `customer_email` varchar(150) NOT NULL,
  `rating` tinyint(1) NOT NULL DEFAULT 5 COMMENT 'Điểm đánh giá từ 1-5',
  `title` varchar(255) DEFAULT NULL COMMENT 'Tiêu đề đánh giá',
  `comment` text NOT NULL COMMENT 'Nội dung đánh giá',
  `images` text DEFAULT NULL COMMENT 'JSON array: ["image1.jpg", "image2.jpg"]',
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `helpful_count` int(11) DEFAULT 0 COMMENT 'Số lượt hữu ích',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `product_id` (`product_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `rating` (`rating`),
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 5. BẢNG NEWS (TIN TỨC)
-- ============================================
CREATE TABLE IF NOT EXISTS `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` text DEFAULT NULL COMMENT 'Tóm tắt',
  `content` longtext NOT NULL COMMENT 'Nội dung bài viết',
  `featured_image` varchar(255) DEFAULT NULL COMMENT 'Ảnh đại diện',
  `gallery` text DEFAULT NULL COMMENT 'JSON array: ["img1.jpg", "img2.jpg"]',
  `author_id` int(11) DEFAULT NULL COMMENT 'ID người viết (admin)',
  `category` varchar(100) DEFAULT NULL COMMENT 'Danh mục tin tức',
  `tags` varchar(255) DEFAULT NULL COMMENT 'Tags phân cách bằng dấu phẩy',
  `views` int(11) DEFAULT 0 COMMENT 'Số lượt xem',
  `status` enum('draft','published','archived') NOT NULL DEFAULT 'draft',
  `featured` tinyint(1) DEFAULT 0 COMMENT 'Tin nổi bật',
  `published_at` datetime DEFAULT NULL COMMENT 'Ngày xuất bản',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `author_id` (`author_id`),
  KEY `status` (`status`),
  KEY `featured` (`featured`),
  KEY `published_at` (`published_at`),
  FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 6. BẢNG RECRUITMENT (TUYỂN DỤNG)
-- ============================================
CREATE TABLE IF NOT EXISTS `recruitment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL COMMENT 'Tiêu đề vị trí tuyển dụng',
  `slug` varchar(255) NOT NULL,
  `position` varchar(150) NOT NULL COMMENT 'Vị trí công việc',
  `department` varchar(100) DEFAULT NULL COMMENT 'Phòng ban',
  `location` varchar(150) DEFAULT NULL COMMENT 'Địa điểm làm việc',
  `employment_type` varchar(50) DEFAULT NULL COMMENT 'Loại hình: fulltime, parttime, contract',
  `salary_min` decimal(10,2) DEFAULT NULL COMMENT 'Mức lương tối thiểu',
  `salary_max` decimal(10,2) DEFAULT NULL COMMENT 'Mức lương tối đa',
  `salary_display` varchar(100) DEFAULT NULL COMMENT 'Hiển thị lương: "Thỏa thuận", "10-15 triệu", etc.',
  `description` text NOT NULL COMMENT 'Mô tả công việc',
  `requirements` text DEFAULT NULL COMMENT 'Yêu cầu ứng viên',
  `benefits` text DEFAULT NULL COMMENT 'Quyền lợi',
  `deadline` date DEFAULT NULL COMMENT 'Hạn nộp hồ sơ',
  `quantity` int(11) DEFAULT 1 COMMENT 'Số lượng cần tuyển',
  `status` enum('draft','open','closed','filled') NOT NULL DEFAULT 'draft',
  `views` int(11) DEFAULT 0 COMMENT 'Số lượt xem',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`),
  KEY `position` (`position`),
  KEY `deadline` (`deadline`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 7. BẢNG RECRUITMENT_APPLICATIONS (ĐƠN XIN VIỆC)
-- ============================================
CREATE TABLE IF NOT EXISTS `recruitment_applications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recruitment_id` int(11) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `cv_file` varchar(255) DEFAULT NULL COMMENT 'File CV đính kèm',
  `cover_letter` text DEFAULT NULL COMMENT 'Thư xin việc',
  `experience` text DEFAULT NULL COMMENT 'Kinh nghiệm',
  `education` text DEFAULT NULL COMMENT 'Học vấn',
  `status` enum('pending','reviewing','interviewed','accepted','rejected') NOT NULL DEFAULT 'pending',
  `notes` text DEFAULT NULL COMMENT 'Ghi chú của HR',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `recruitment_id` (`recruitment_id`),
  KEY `status` (`status`),
  KEY `email` (`email`),
  FOREIGN KEY (`recruitment_id`) REFERENCES `recruitment`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 8. BẢNG CONTACT (LIÊN HỆ)
-- ============================================
CREATE TABLE IF NOT EXISTS `contact` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL COMMENT 'Chủ đề',
  `message` text NOT NULL,
  `status` enum('new','read','replied','archived') NOT NULL DEFAULT 'new',
  `reply` text DEFAULT NULL COMMENT 'Phản hồi từ admin',
  `replied_by` int(11) DEFAULT NULL COMMENT 'ID admin phản hồi',
  `replied_at` datetime DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `status` (`status`),
  KEY `email` (`email`),
  KEY `created_at` (`created_at`),
  FOREIGN KEY (`replied_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 9. BẢNG REVIEW_HELPFUL (ĐÁNH GIÁ HỮU ÍCH)
-- ============================================
CREATE TABLE IF NOT EXISTS `review_helpful` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `review_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL COMMENT 'ID người dùng (NULL nếu dùng IP)',
  `ip_address` varchar(45) DEFAULT NULL,
  `is_helpful` tinyint(1) DEFAULT 1 COMMENT '1 = hữu ích, 0 = không hữu ích',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_helpful` (`review_id`, `user_id`, `ip_address`),
  KEY `review_id` (`review_id`),
  FOREIGN KEY (`review_id`) REFERENCES `reviews`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- SAMPLE DATA (DỮ LIỆU MẪU)
-- ============================================

-- Sample News
INSERT INTO `news` (`title`, `slug`, `excerpt`, `content`, `featured_image`, `category`, `status`, `featured`, `published_at`) VALUES
('Khuyến mãi đặc biệt tháng 12 - Giảm giá lên đến 50%', 'khuyen-mai-dac-biet-thang-12', 'Chương trình khuyến mãi lớn nhất trong năm với hàng ngàn sản phẩm giảm giá sâu.', 'Nội dung chi tiết về chương trình khuyến mãi...', 'banner1.jpg', 'Khuyến mãi', 'published', 1, NOW()),
('Cách bảo quản thực phẩm tươi ngon trong tủ lạnh', 'cach-bao-quan-thuc-pham-tuoi-ngon', 'Hướng dẫn chi tiết cách bảo quản thực phẩm để giữ được độ tươi ngon và dinh dưỡng.', 'Nội dung hướng dẫn chi tiết...', 'banner2.jpg', 'Mẹo vặt', 'published', 0, NOW()),
('Top 10 sản phẩm bán chạy nhất tuần', 'top-10-san-pham-ban-chay-nhat-tuan', 'Danh sách các sản phẩm được khách hàng yêu thích nhất trong tuần qua.', 'Nội dung về các sản phẩm bán chạy...', 'banner3.jpg', 'Sản phẩm', 'published', 1, NOW());

-- Sample Recruitment
INSERT INTO `recruitment` (`title`, `slug`, `position`, `department`, `location`, `employment_type`, `salary_display`, `description`, `requirements`, `benefits`, `status`, `deadline`) VALUES
('Tuyển Nhân viên bán hàng', 'tuyen-nhan-vien-ban-hang', 'Nhân viên bán hàng', 'Kinh doanh', 'Hà Nội', 'fulltime', '8-12 triệu', 'Tìm kiếm nhân viên bán hàng nhiệt tình, có kinh nghiệm trong ngành thực phẩm.', 'Tốt nghiệp THPT trở lên, có kinh nghiệm bán hàng ưu tiên.', 'Lương thưởng hấp dẫn, bảo hiểm đầy đủ, môi trường làm việc thân thiện.', 'open', DATE_ADD(NOW(), INTERVAL 30 DAY)),
('Tuyển Tài xế giao hàng', 'tuyen-tai-xe-giao-hang', 'Tài xế giao hàng', 'Vận chuyển', 'Hà Nội', 'fulltime', '10-15 triệu', 'Tuyển tài xế có bằng lái xe B2, có kinh nghiệm giao hàng.', 'Bằng lái xe B2, sức khỏe tốt, trung thực, có trách nhiệm.', 'Lương cứng + phụ cấp xăng + thưởng theo đơn hàng.', 'open', DATE_ADD(NOW(), INTERVAL 20 DAY)),
('Tuyển Nhân viên kho', 'tuyen-nhan-vien-kho', 'Nhân viên kho', 'Kho vận', 'Hà Nội', 'fulltime', '7-10 triệu', 'Tuyển nhân viên quản lý kho, sắp xếp hàng hóa.', 'Có kinh nghiệm làm kho, sức khỏe tốt, cẩn thận.', 'Lương ổn định, làm việc trong môi trường sạch sẽ.', 'open', DATE_ADD(NOW(), INTERVAL 25 DAY));

-- Sample Contact
INSERT INTO `contact` (`name`, `email`, `phone`, `subject`, `message`, `status`) VALUES
('Nguyễn Văn A', 'nguyenvana@email.com', '0987654321', 'Hỏi về sản phẩm', 'Tôi muốn hỏi về sản phẩm thịt heo ba chỉ có còn hàng không?', 'new'),
('Trần Thị B', 'tranthib@email.com', '0912345678', 'Góp ý dịch vụ', 'Dịch vụ giao hàng của các bạn rất tốt, cảm ơn!', 'read'),
('Lê Văn C', 'levanc@email.com', '0901234567', 'Khiếu nại', 'Đơn hàng của tôi bị giao sai sản phẩm, mong được giải quyết.', 'new');

