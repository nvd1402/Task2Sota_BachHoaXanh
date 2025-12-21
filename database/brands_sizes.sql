-- ============================================
-- BẢNG BRANDS (THƯƠNG HIỆU)
-- ============================================
CREATE TABLE IF NOT EXISTS `brands` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL COMMENT 'Tên thương hiệu',
  `slug` varchar(100) NOT NULL COMMENT 'Slug thương hiệu',
  `description` text DEFAULT NULL COMMENT 'Mô tả thương hiệu',
  `logo` varchar(255) DEFAULT NULL COMMENT 'Logo thương hiệu',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `name` (`name`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- BẢNG SIZES (KÍCH THƯỚC)
-- ============================================
CREATE TABLE IF NOT EXISTS `sizes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL COMMENT 'Tên kích thước (S, M, L, XL, XXL, etc.)',
  `slug` varchar(50) NOT NULL COMMENT 'Slug kích thước',
  `description` text DEFAULT NULL COMMENT 'Mô tả kích thước',
  `status` enum('active','inactive') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  UNIQUE KEY `name` (`name`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- CẬP NHẬT BẢNG PRODUCTS
-- ============================================
-- Thêm cột brand_id và size_id vào bảng products
ALTER TABLE `products` 
ADD COLUMN `brand_id` int(11) DEFAULT NULL COMMENT 'ID thương hiệu' AFTER `category`,
ADD COLUMN `size_id` int(11) DEFAULT NULL COMMENT 'ID kích thước' AFTER `brand_id`,
ADD KEY `brand_id` (`brand_id`),
ADD KEY `size_id` (`size_id`),
ADD CONSTRAINT `fk_products_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
ADD CONSTRAINT `fk_products_size` FOREIGN KEY (`size_id`) REFERENCES `sizes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ============================================
-- DỮ LIỆU MẪU BRANDS
-- ============================================
INSERT INTO `brands` (`name`, `slug`, `status`) VALUES
('5TFOODS', '5tfoods', 'active'),
('HAIHACO', 'haihaco', 'active'),
('KIDO', 'kido', 'active'),
('Nutifood', 'nutifood', 'active'),
('Vissan', 'vissan', 'active'),
('Acecook', 'acecook', 'active'),
('Vifon', 'vifon', 'active'),
('Coca Cola', 'coca-cola', 'active'),
('Pepsi', 'pepsi', 'active'),
('Vinamilk', 'vinamilk', 'active');

-- ============================================
-- DỮ LIỆU MẪU SIZES
-- ============================================
INSERT INTO `sizes` (`name`, `slug`, `status`) VALUES
('S', 's', 'active'),
('M', 'm', 'active'),
('L', 'l', 'active'),
('XL', 'xl', 'active'),
('XXL', 'xxl', 'active'),
('500g', '500g', 'active'),
('1kg', '1kg', 'active'),
('2kg', '2kg', 'active'),
('500ml', '500ml', 'active'),
('1L', '1l', 'active');

