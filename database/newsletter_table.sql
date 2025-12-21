-- ============================================
-- BẢNG NEWSLETTER_SUBSCRIPTIONS
-- Lưu trữ email đăng ký nhận khuyến mãi
-- ============================================

CREATE TABLE IF NOT EXISTS `newsletter_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `status` enum('active','unsubscribed') NOT NULL DEFAULT 'active' COMMENT 'Trạng thái: active = đang đăng ký, unsubscribed = đã hủy đăng ký',
  `ip_address` varchar(45) DEFAULT NULL COMMENT 'Địa chỉ IP khi đăng ký',
  `user_agent` text DEFAULT NULL COMMENT 'Thông tin trình duyệt',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Ngày đăng ký',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Ngày cập nhật',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

