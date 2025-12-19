-- Tạo bảng products
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `short_description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `sku` varchar(100) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `category` varchar(100) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `gallery` text DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `status` (`status`),
  KEY `category` (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu
INSERT INTO `products` (`name`, `slug`, `description`, `short_description`, `price`, `sale_price`, `sku`, `stock`, `category`, `image`, `status`, `featured`) VALUES
('Sữa tươi Vinamilk 100%', 'sua-tuoi-vinamilk-100', 'Sữa tươi nguyên chất 100% từ Vinamilk, giàu dinh dưỡng', 'Sữa tươi nguyên chất 100%', 25000.00, 22000.00, 'SKU001', 100, 'Đồ uống', '4.jpg', 'active', 1),
('Bánh mì sandwich', 'banh-mi-sandwich', 'Bánh mì sandwich tươi ngon, mềm mịn', 'Bánh mì sandwich tươi ngon', 15000.00, NULL, 'SKU002', 50, 'Bánh kẹo', '5.jpg', 'active', 1);

