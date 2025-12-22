-- ============================================
-- DATABASE HOÀN CHỈNH - BÁCH HÓA XANH
-- Bao gồm TẤT CẢ các bảng: categories, products, users, orders, cart, reviews, news, recruitment, contact
-- ============================================

-- Xóa các bảng cũ nếu tồn tại (CẨN THẬN: Sẽ xóa dữ liệu cũ)
DROP TABLE IF EXISTS `review_helpful`;
DROP TABLE IF EXISTS `recruitment_applications`;
DROP TABLE IF EXISTS `contact`;
DROP TABLE IF EXISTS `recruitment`;
DROP TABLE IF EXISTS `news`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `cart`;
DROP TABLE IF EXISTS `order_items`;
DROP TABLE IF EXISTS `orders`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;

-- ============================================
-- 1. BẢNG CATEGORIES (DANH MỤC)
-- ============================================
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `parent_id` int(11) DEFAULT NULL COMMENT 'ID danh mục cha (NULL nếu là danh mục cha)',
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL COMMENT 'Ảnh danh mục',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `name` (`name`),
  KEY `parent_id` (`parent_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 2. BẢNG PRODUCTS (SẢN PHẨM)
-- ============================================
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `price_min` decimal(10,2) DEFAULT NULL COMMENT 'Giá tối thiểu (khoảng giá)',
  `price_max` decimal(10,2) DEFAULT NULL COMMENT 'Giá tối đa (khoảng giá)',
  `weight_options` varchar(255) DEFAULT NULL COMMENT 'Các lựa chọn trọng lượng, ví dụ: 1kg,2kg,3kg',
  `promo_heading` varchar(255) DEFAULT NULL COMMENT 'Tiêu đề khuyến mãi',
  `promo_content` text DEFAULT NULL COMMENT 'Nội dung khuyến mãi',
  `sku` varchar(100) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `category` varchar(100) DEFAULT NULL COMMENT 'Tên danh mục (để match với categories.name)',
  `image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL COMMENT 'JSON array các ảnh trong gallery',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `featured` tinyint(1) NOT NULL DEFAULT 0 COMMENT 'Sản phẩm nổi bật',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`),
  KEY `category` (`category`),
  KEY `featured` (`featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 3. BẢNG USERS (NGƯỜI DÙNG)
-- ============================================
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('admin','customer') NOT NULL DEFAULT 'customer',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=active, 0=inactive',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`),
  KEY `status` (`status`),
  KEY `role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- 4. BẢNG ORDERS (ĐƠN HÀNG)
-- ============================================
CREATE TABLE `orders` (
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
-- 5. BẢNG ORDER_ITEMS (CHI TIẾT ĐƠN HÀNG)
-- ============================================
CREATE TABLE `order_items` (
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
-- 6. BẢNG CART (GIỎ HÀNG - Database backup)
-- ============================================
CREATE TABLE `cart` (
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
-- 7. BẢNG REVIEWS (ĐÁNH GIÁ SẢN PHẨM)
-- ============================================
CREATE TABLE `reviews` (
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
-- 8. BẢNG NEWS (TIN TỨC)
-- ============================================
CREATE TABLE `news` (
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
-- 9. BẢNG RECRUITMENT (TUYỂN DỤNG)
-- ============================================
CREATE TABLE `recruitment` (
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
-- 10. BẢNG RECRUITMENT_APPLICATIONS (ĐƠN XIN VIỆC)
-- ============================================
CREATE TABLE `recruitment_applications` (
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
-- 11. BẢNG CONTACT (LIÊN HỆ)
-- ============================================
CREATE TABLE `contact` (
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
-- 12. BẢNG REVIEW_HELPFUL (ĐÁNH GIÁ HỮU ÍCH)
-- ============================================
CREATE TABLE `review_helpful` (
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
-- DỮ LIỆU MẪU
-- ============================================

-- 1. DANH MỤC
-- Danh mục cha: "Thịt – Cá – Trứng"
INSERT INTO `categories` (`name`, `slug`, `description`, `image`, `status`, `parent_id`) VALUES
('Thịt – Cá – Trứng', 'thit-ca-trung', 'Thịt, cá, trứng tươi sống', 'icon_ns.png', 'active', NULL);

-- Lấy ID của danh mục cha
SET @thit_ca_trung_id = LAST_INSERT_ID();

-- Danh mục con: Thịt, Cá, Trứng
INSERT INTO `categories` (`name`, `slug`, `description`, `image`, `status`, `parent_id`) VALUES
('Thịt', 'thit', 'Các loại thịt tươi', NULL, 'active', @thit_ca_trung_id),
('Cá', 'ca', 'Các loại cá tươi sống', NULL, 'active', @thit_ca_trung_id),
('Trứng', 'trung', 'Trứng gà, vịt, cút', NULL, 'active', @thit_ca_trung_id);

-- 2. SẢN PHẨM
-- Sản phẩm cho danh mục "Thịt" (4 sản phẩm)
INSERT INTO `products` (`name`, `slug`, `description`, `short_description`, `price`, `sale_price`, `price_min`, `price_max`, `weight_options`, `promo_heading`, `promo_content`, `sku`, `stock`, `category`, `image`, `gallery`, `status`, `featured`) VALUES
('Thịt heo ba chỉ', 'thit-heo-ba-chi', 'Thịt heo ba chỉ tươi ngon, được chọn lọc kỹ càng. Thịt có lớp mỡ vàng đẹp, thịt nạc đỏ tươi, đảm bảo chất lượng và an toàn thực phẩm.', 'Thịt heo ba chỉ tươi ngon', 120000.00, 99000.00, 90000.00, 130000.00, '500g,1kg,1.5kg', 'KHUYẾN MÃI ĐẶC BIỆT', '1. Mua 2kg tặng 200g\n2. Freeship cho đơn hàng trên 500.000₫', 'TH001', 50, 'Thịt', '1.jpg', '["1.jpg", "2.jpg", "1766125189_11c9c31d8b6080ad1c0ebdc9894752c9.jpg", "1766125911_a360abeba5bcb9f75b9d11834c237b3d.jpg", "1766134306_2174068d8a26238f6bc6938f391b784c.jpg"]', 'active', 1),
('Thịt bò xay', 'thit-bo-xay', 'Thịt bò xay tươi, chất lượng cao, không pha trộn. Thịt bò được xay nhuyễn từ thịt bò tươi, đảm bảo vệ sinh an toàn thực phẩm. Giàu protein và sắt, tốt cho sức khỏe.', 'Thịt bò xay tươi', 250000.00, 220000.00, 200000.00, 250000.00, '500g,1kg', 'GIẢM 12%', '1. Thịt bò tươi nhập khẩu\n2. Đảm bảo chất lượng cao cấp', 'TH002', 30, 'Thịt', '2.jpg', '["2.jpg", "4.jpg", "1766134322_2174068d8a26238f6bc6938f391b784c.jpg", "1766134333_2174068d8a26238f6bc6938f391b784c.jpg", "1766134339_2174068d8a26238f6bc6938f391b784c.jpg"]', 'active', 1),
('Thịt gà nguyên con', 'thit-ga-nguyen-con', 'Thịt gà ta nguyên con tươi sống, thịt chắc ngon. Gà được nuôi thả tự nhiên, thịt dai ngon, thơm đậm đà. Thích hợp cho các món luộc, nướng, rang.', 'Thịt gà nguyên con', 180000.00, NULL, 160000.00, 200000.00, '1kg,1.5kg,2kg', NULL, NULL, 'TH003', 25, 'Thịt', '4.jpg', '["4.jpg", "5.jpg", "1766134348_2174068d8a26238f6bc6938f391b784c.jpg", "1766134358_2174068d8a26238f6bc6938f391b784c.jpg"]', 'active', 0),
('Thịt heo nạc vai', 'thit-heo-nac-vai', 'Thịt heo nạc vai tươi, ít mỡ, thịt thơm ngon. Phần vai là phần thịt ngon nhất của heo, có độ dai vừa phải, thích hợp cho nhiều món ăn khác nhau.', 'Thịt heo nạc vai', 140000.00, 120000.00, 110000.00, 150000.00, '500g,1kg', 'GIẢM GIÁ SỐC', 'Mua ngay hôm nay để nhận ưu đãi tốt nhất', 'TH004', 40, 'Thịt', '5.jpg', '["5.jpg", "1.jpg", "1766134396_2174068d8a26238f6bc6938f391b784c.jpg", "1766134404_2174068d8a26238f6bc6938f391b784c.jpg", "1766134727_a360abeba5bcb9f75b9d11834c237b3d.jpg"]', 'active', 0);

-- Sản phẩm cho danh mục "Cá" (4 sản phẩm)
INSERT INTO `products` (`name`, `slug`, `description`, `short_description`, `price`, `sale_price`, `price_min`, `price_max`, `weight_options`, `promo_heading`, `promo_content`, `sku`, `stock`, `category`, `image`, `gallery`, `status`, `featured`) VALUES
('Cá hồi fillet', 'ca-hoi-fillet', 'Cá hồi fillet tươi, giàu Omega-3, tốt cho sức khỏe. Cá hồi được nhập khẩu từ Na Uy, thịt mềm, thơm ngon, giàu dinh dưỡng. Thích hợp cho các món nướng, áp chảo, hoặc làm sushi.', 'Cá hồi fillet tươi', 450000.00, 399000.00, 380000.00, 450000.00, '300g,500g,1kg', 'SẢN PHẨM CAO CẤP', '1. Cá hồi nhập khẩu Na Uy\n2. Đảm bảo tươi sống 100%', 'CA001', 20, 'Cá', '1.jpg', '["1.jpg", "1766134675_chanh.jpg", "1766134693_dua.jpg", "1766134702_sau.jpg", "1766134747_2174068d8a26238f6bc6938f391b784c.jpg"]', 'active', 1),
('Cá basa tươi', 'ca-basa-tuoi', 'Cá basa tươi sống, thịt trắng ngon, ít xương. Cá basa được nuôi trong môi trường sạch, thịt thơm ngon, giàu protein. Thích hợp cho các món kho, chiên, nướng.', 'Cá basa tươi', 80000.00, 69000.00, 60000.00, 90000.00, '500g,1kg,1.5kg', 'GIÁ TỐT NHẤT', '1. Cá basa đảm bảo tươi sống\n2. Freeship cho đơn hàng trên 300.000₫', 'CA002', 60, 'Cá', '2.jpg', '["2.jpg", "banner1.jpg", "banner3.jpg", "banner4.jpg"]', 'active', 0),
('Cá thu một nắng', 'ca-thu-mot-nang', 'Cá thu một nắng thơm ngon, đậm đà hương vị biển. Cá thu được phơi một nắng giữ nguyên hương vị đặc trưng của biển. Thịt cá dai ngon, thơm lừng.', 'Cá thu một nắng', 180000.00, 160000.00, 150000.00, 200000.00, '500g,1kg', 'ĐẶC SẢN BIỂN', '1. Cá thu Phan Thiết\n2. Đặc sản miền biển', 'CA003', 35, 'Cá', '4.jpg', '["4.jpg", "right1.jpg", "left1-1.jpg", "lesterblur__2.jpg"]', 'active', 0),
('Cá chép tươi sống', 'ca-chep-tuoi-song', 'Cá chép tươi sống, thịt chắc, ngon ngọt. Cá chép được nuôi trong môi trường sạch, thịt thơm ngon, giàu dinh dưỡng. Thích hợp cho các món chưng, kho, nấu canh.', 'Cá chép tươi sống', 90000.00, NULL, 80000.00, 100000.00, '500g,1kg,1.5kg', NULL, NULL, 'CA004', 45, 'Cá', '5.jpg', '["5.jpg", "1-1.webp", "380700650_10162533193146729_2379134611963304810_n.jpg", "thuxu-huong-am-thuc.jpg"]', 'active', 0);

-- Sản phẩm cho danh mục "Trứng" (4 sản phẩm)
INSERT INTO `products` (`name`, `slug`, `description`, `short_description`, `price`, `sale_price`, `price_min`, `price_max`, `weight_options`, `promo_heading`, `promo_content`, `sku`, `stock`, `category`, `image`, `gallery`, `status`, `featured`) VALUES
('Trứng gà ta', 'trung-ga-ta', 'Trứng gà ta tươi, giàu dinh dưỡng, trứng đỏ đậm. Gà ta được nuôi thả tự nhiên, trứng to, lòng đỏ đậm, thơm ngon. Giàu protein, vitamin và khoáng chất tốt cho sức khỏe.', 'Trứng gà ta', 35000.00, 32000.00, 30000.00, 40000.00, '10 quả,20 quả,30 quả', 'TRỨNG TƯƠI HÀNG NGÀY', '1. Gà ta nuôi thả tự nhiên\n2. Đảm bảo tươi ngon mỗi ngày', 'TR001', 100, 'Trứng', '1.jpg', '["1.jpg", "p1.png", "p2.png", "p3.png", "p4.png"]', 'active', 1),
('Trứng vịt muối', 'trung-vit-muoi', 'Trứng vịt muối đặc sản, béo ngậy, đậm đà. Trứng vịt muối Bắc Ninh nổi tiếng với hương vị đặc trưng, lòng đỏ béo ngậy, thơm lừng. Thích hợp để ăn kèm cháo, cơm.', 'Trứng vịt muối', 25000.00, 22000.00, 20000.00, 30000.00, '10 quả,20 quả', 'ĐẶC SẢN VIỆT NAM', '1. Trứng vịt muối Bắc Ninh\n2. Đặc sản truyền thống', 'TR002', 80, 'Trứng', '2.jpg', '["2.jpg", "banner_prduct1.png", "banner_prduct2.png", "banner_prduct3.png"]', 'active', 0),
('Trứng cút', 'trung-cut', 'Trứng cút tươi, nhỏ gọn, giàu protein. Trứng cút có kích thước nhỏ, dễ ăn, giàu dinh dưỡng. Thích hợp cho trẻ em và người lớn, có thể luộc, chiên, hoặc làm salad.', 'Trứng cút', 20000.00, NULL, 18000.00, 25000.00, '20 quả,30 quả,50 quả', NULL, NULL, 'TR003', 120, 'Trứng', '4.jpg', '["4.jpg", "1766134322_2174068d8a26238f6bc6938f391b784c.jpg", "1766134333_2174068d8a26238f6bc6938f391b784c.jpg"]', 'active', 0),
('Trứng gà công nghiệp', 'trung-ga-cong-nghiep', 'Trứng gà công nghiệp, giá tốt, chất lượng đảm bảo. Trứng gà công nghiệp tươi ngon, giá cả hợp lý, phù hợp cho mọi gia đình. Đảm bảo an toàn vệ sinh thực phẩm.', 'Trứng gà công nghiệp', 28000.00, 25000.00, 22000.00, 32000.00, '10 quả,20 quả,30 quả', 'GIÁ TỐT', '1. Trứng tươi hàng ngày\n2. Giá cả hợp lý', 'TR004', 150, 'Trứng', '5.jpg', '["5.jpg", "1766134348_2174068d8a26238f6bc6938f391b784c.jpg", "1766134358_2174068d8a26238f6bc6938f391b784c.jpg", "1766134396_2174068d8a26238f6bc6938f391b784c.jpg", "1766134404_2174068d8a26238f6bc6938f391b784c.jpg"]', 'active', 0);

-- 3. USERS (NGƯỜI DÙNG)
-- Password: admin123 (đã hash bằng password_hash)
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `phone`, `role`, `status`) VALUES
('admin', 'admin@bachhoaxanh.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản Trị Viên', '0123456789', 'admin', 1);

-- Password: customer123 (đã hash bằng password_hash)
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `phone`, `address`, `role`, `status`) VALUES
('customer', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Khách Hàng Mẫu', '0987654321', '123 Đường ABC, Quận XYZ, TP.HCM', 'customer', 1);

-- 4. SAMPLE NEWS (TIN TỨC MẪU)
INSERT INTO `news` (`title`, `slug`, `excerpt`, `content`, `featured_image`, `category`, `status`, `featured`, `published_at`) VALUES
('Khuyến mãi đặc biệt tháng 12 - Giảm giá lên đến 50%', 'khuyen-mai-dac-biet-thang-12', 'Chương trình khuyến mãi lớn nhất trong năm với hàng ngàn sản phẩm giảm giá sâu.', '<p>Nội dung chi tiết về chương trình khuyến mãi tháng 12. Chúng tôi mang đến cho khách hàng những ưu đãi hấp dẫn nhất trong năm với hàng ngàn sản phẩm được giảm giá từ 20% đến 50%.</p><p>Áp dụng cho tất cả các danh mục sản phẩm: thịt, cá, trứng, rau củ quả, đồ khô, đồ uống...</p>', 'banner1.jpg', 'Khuyến mãi', 'published', 1, NOW()),
('Cách bảo quản thực phẩm tươi ngon trong tủ lạnh', 'cach-bao-quan-thuc-pham-tuoi-ngon', 'Hướng dẫn chi tiết cách bảo quản thực phẩm để giữ được độ tươi ngon và dinh dưỡng.', '<p>Bảo quản thực phẩm đúng cách là điều quan trọng để giữ được độ tươi ngon và giá trị dinh dưỡng. Bài viết này sẽ hướng dẫn bạn các mẹo bảo quản thực phẩm hiệu quả.</p><h3>1. Phân loại thực phẩm</h3><p>Mỗi loại thực phẩm cần được bảo quản ở nhiệt độ và độ ẩm phù hợp.</p><h3>2. Sử dụng hộp kín</h3><p>Đựng thực phẩm trong hộp kín để tránh mùi và vi khuẩn.</p>', 'banner2.jpg', 'Mẹo vặt', 'published', 0, NOW()),
('Top 10 sản phẩm bán chạy nhất tuần', 'top-10-san-pham-ban-chay-nhat-tuan', 'Danh sách các sản phẩm được khách hàng yêu thích nhất trong tuần qua.', '<p>Tuần qua, chúng tôi đã tổng hợp danh sách 10 sản phẩm được khách hàng mua nhiều nhất. Đây là những sản phẩm chất lượng cao, giá cả hợp lý và được đánh giá tốt.</p><ul><li>Thịt heo ba chỉ</li><li>Cá hồi fillet</li><li>Trứng gà ta</li></ul>', 'banner3.jpg', 'Sản phẩm', 'published', 1, NOW());

-- 5. SAMPLE RECRUITMENT (TUYỂN DỤNG MẪU)
INSERT INTO `recruitment` (`title`, `slug`, `position`, `department`, `location`, `employment_type`, `salary_display`, `description`, `requirements`, `benefits`, `status`, `deadline`) VALUES
('Tuyển Nhân viên bán hàng', 'tuyen-nhan-vien-ban-hang', 'Nhân viên bán hàng', 'Kinh doanh', 'Hà Nội', 'fulltime', '8-12 triệu', '<p>Chúng tôi đang tìm kiếm nhân viên bán hàng nhiệt tình, có kinh nghiệm trong ngành thực phẩm. Công việc bao gồm:</p><ul><li>Tư vấn và bán hàng cho khách hàng</li><li>Sắp xếp và quản lý hàng hóa</li><li>Chăm sóc khách hàng</li></ul>', '<ul><li>Tốt nghiệp THPT trở lên</li><li>Có kinh nghiệm bán hàng ưu tiên</li><li>Giao tiếp tốt, thân thiện</li><li>Chăm chỉ, trung thực</li></ul>', '<ul><li>Lương thưởng hấp dẫn</li><li>Bảo hiểm đầy đủ</li><li>Môi trường làm việc thân thiện</li><li>Cơ hội thăng tiến</li></ul>', 'open', DATE_ADD(NOW(), INTERVAL 30 DAY)),
('Tuyển Tài xế giao hàng', 'tuyen-tai-xe-giao-hang', 'Tài xế giao hàng', 'Vận chuyển', 'Hà Nội', 'fulltime', '10-15 triệu', '<p>Tuyển tài xế có bằng lái xe B2, có kinh nghiệm giao hàng. Công việc bao gồm:</p><ul><li>Giao hàng đến khách hàng</li><li>Kiểm tra và bảo quản hàng hóa</li><li>Thu tiền COD</li></ul>', '<ul><li>Bằng lái xe B2</li><li>Sức khỏe tốt</li><li>Trung thực, có trách nhiệm</li><li>Có kinh nghiệm giao hàng ưu tiên</li></ul>', '<ul><li>Lương cứng + phụ cấp xăng</li><li>Thưởng theo đơn hàng</li><li>Bảo hiểm đầy đủ</li><li>Làm việc linh hoạt</li></ul>', 'open', DATE_ADD(NOW(), INTERVAL 20 DAY)),
('Tuyển Nhân viên kho', 'tuyen-nhan-vien-kho', 'Nhân viên kho', 'Kho vận', 'Hà Nội', 'fulltime', '7-10 triệu', '<p>Tuyển nhân viên quản lý kho, sắp xếp hàng hóa. Công việc bao gồm:</p><ul><li>Nhập và xuất hàng</li><li>Sắp xếp hàng hóa trong kho</li><li>Kiểm kê hàng tồn kho</li></ul>', '<ul><li>Có kinh nghiệm làm kho</li><li>Sức khỏe tốt</li><li>Cẩn thận, tỉ mỉ</li><li>Biết đọc viết</li></ul>', '<ul><li>Lương ổn định</li><li>Làm việc trong môi trường sạch sẽ</li><li>Bảo hiểm đầy đủ</li><li>Ca làm việc linh hoạt</li></ul>', 'open', DATE_ADD(NOW(), INTERVAL 25 DAY));

-- 6. SAMPLE CONTACT (LIÊN HỆ MẪU)
INSERT INTO `contact` (`name`, `email`, `phone`, `subject`, `message`, `status`) VALUES
('Nguyễn Văn A', 'nguyenvana@email.com', '0987654321', 'Hỏi về sản phẩm', 'Tôi muốn hỏi về sản phẩm thịt heo ba chỉ có còn hàng không?', 'new'),
('Trần Thị B', 'tranthib@email.com', '0912345678', 'Góp ý dịch vụ', 'Dịch vụ giao hàng của các bạn rất tốt, cảm ơn!', 'read'),
('Lê Văn C', 'levanc@email.com', '0901234567', 'Khiếu nại', 'Đơn hàng của tôi bị giao sai sản phẩm, mong được giải quyết.', 'new');

-- 7. SAMPLE REVIEWS (ĐÁNH GIÁ MẪU)
INSERT INTO `reviews` (`product_id`, `user_id`, `customer_name`, `customer_email`, `rating`, `title`, `comment`, `status`) VALUES
(1, 2, 'Nguyễn Thị D', 'nguyenthid@email.com', 5, 'Sản phẩm rất tươi ngon', 'Thịt heo ba chỉ rất tươi, mỡ vàng đẹp, thịt đỏ tươi. Giao hàng nhanh, đóng gói cẩn thận. Sẽ mua lại!', 'approved'),
(1, NULL, 'Trần Văn E', 'tranvane@email.com', 4, 'Chất lượng tốt', 'Sản phẩm chất lượng tốt, giá cả hợp lý. Nhưng giao hàng hơi chậm một chút.', 'approved'),
(5, 2, 'Lê Thị F', 'lethif@email.com', 5, 'Cá hồi tuyệt vời', 'Cá hồi rất tươi, thịt mềm, thơm ngon. Đúng là hàng nhập khẩu Na Uy. Rất hài lòng!', 'approved');

-- ============================================
-- HOÀN THÀNH
-- ============================================
-- Database đã được tạo hoàn chỉnh với:
-- - 1 danh mục cha: "Thịt – Cá – Trứng"
-- - 3 danh mục con: Thịt, Cá, Trứng
-- - 12 sản phẩm (mỗi danh mục con có 4 sản phẩm)
-- - 2 users: admin và customer
-- - 3 tin tức mẫu
-- - 3 bài tuyển dụng mẫu
-- - 3 liên hệ mẫu
-- - 3 đánh giá mẫu
-- - Các bảng: orders, order_items, cart, reviews, news, recruitment, recruitment_applications, contact, review_helpful

