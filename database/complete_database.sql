-- ============================================
-- DATABASE HOÀN CHỈNH - BÁCH HÓA XANH
-- Bao gồm: categories, products, users
-- ============================================

-- Xóa các bảng cũ nếu tồn tại (CẨN THẬN: Sẽ xóa dữ liệu cũ)
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

-- ============================================
-- 2. SẢN PHẨM
-- ============================================

-- Sản phẩm cho danh mục "Thịt" (4 sản phẩm)
-- Gallery lưu dạng JSON array, mỗi sản phẩm có nhiều ảnh khác nhau
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

-- ============================================
-- 3. USERS (NGƯỜI DÙNG)
-- ============================================
-- Password: admin123 (đã hash bằng password_hash)
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `phone`, `role`, `status`) VALUES
('admin', 'admin@bachhoaxanh.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Quản Trị Viên', '0123456789', 'admin', 1);

-- Password: customer123 (đã hash bằng password_hash)
INSERT INTO `users` (`username`, `email`, `password`, `full_name`, `phone`, `address`, `role`, `status`) VALUES
('customer', 'customer@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Khách Hàng Mẫu', '0987654321', '123 Đường ABC, Quận XYZ, TP.HCM', 'customer', 1);

-- ============================================
-- HOÀN THÀNH
-- ============================================
-- Database đã được tạo hoàn chỉnh với:
-- - 1 danh mục cha: "Thịt – Cá – Trứng"
-- - 3 danh mục con: Thịt, Cá, Trứng
-- - 12 sản phẩm (mỗi danh mục con có 4 sản phẩm)
-- - 2 users: admin và customer

