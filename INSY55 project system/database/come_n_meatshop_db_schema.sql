-- Come N’ Meatshop Inventory Management System
-- Full PRD-aligned MySQL/MariaDB schema
-- Database: come_n_meatshop_db
-- Import in XAMPP/phpMyAdmin or MySQL CLI before seed.sql

SET NAMES utf8mb4;
SET time_zone = '+08:00';
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

CREATE DATABASE IF NOT EXISTS `come_n_meatshop_db`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;
USE `come_n_meatshop_db`;

-- ---------------------------------------------------------------------------
-- Roles and users
-- ---------------------------------------------------------------------------
DROP TABLE IF EXISTS `user_notifications`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `restore_logs`;
DROP TABLE IF EXISTS `backups`;
DROP TABLE IF EXISTS `reports`;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `payments`;
DROP TABLE IF EXISTS `sale_items`;
DROP TABLE IF EXISTS `sales`;
DROP TABLE IF EXISTS `stock_movements`;
DROP TABLE IF EXISTS `products`;
DROP TABLE IF EXISTS `product_categories`;
DROP TABLE IF EXISTS `settings`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(30) NOT NULL,
  `slug` VARCHAR(30) NOT NULL,
  `description` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_roles_name` (`name`),
  UNIQUE KEY `uk_roles_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `role_id` TINYINT UNSIGNED NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NULL,
  `avatar_path` VARCHAR(255) NULL,
  `language_preference` ENUM('en','fil') NOT NULL DEFAULT 'en',
  `status` ENUM('Active','Inactive','Archived','Deleted') NOT NULL DEFAULT 'Active',
  `is_owner` TINYINT(1) NOT NULL DEFAULT 0,
  `last_login_at` DATETIME NULL,
  `created_by` INT UNSIGNED NULL,
  `updated_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archived_at` DATETIME NULL,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_users_username` (`username`),
  UNIQUE KEY `uk_users_email` (`email`),
  KEY `idx_users_role_status` (`role_id`, `status`),
  KEY `idx_users_status` (`status`),
  CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_users_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_users_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Inventory
-- ---------------------------------------------------------------------------
CREATE TABLE `product_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(255) NULL,
  `status` ENUM('Active','Inactive','Archived') NOT NULL DEFAULT 'Active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_product_categories_name` (`name`),
  KEY `idx_product_categories_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `products` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NULL,
  `name` VARCHAR(150) NOT NULL,
  `meat_type` VARCHAR(100) NULL,
  `description` TEXT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `unit` VARCHAR(30) NOT NULL DEFAULT 'kg',
  `stock_quantity` DECIMAL(12,3) NOT NULL DEFAULT 0.000,
  `min_stock_level` DECIMAL(12,3) NOT NULL DEFAULT 10.000,
  `expiration_date` DATE NULL,
  `image_path` VARCHAR(255) NULL,
  `barcode` VARCHAR(100) NULL,
  `supplier` VARCHAR(150) NULL,
  `status` ENUM('Active','Inactive','Archived','Deleted') NOT NULL DEFAULT 'Active',
  `created_by` INT UNSIGNED NULL,
  `updated_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `archived_at` DATETIME NULL,
  `deleted_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_products_barcode` (`barcode`),
  KEY `idx_products_category_status` (`category_id`, `status`),
  KEY `idx_products_name` (`name`),
  KEY `idx_products_status` (`status`),
  KEY `idx_products_expiration` (`expiration_date`),
  KEY `idx_products_stock` (`stock_quantity`),
  CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_products_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_products_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `chk_products_price_nonneg` CHECK (`price` >= 0),
  CONSTRAINT `chk_products_stock_nonneg` CHECK (`stock_quantity` >= 0),
  CONSTRAINT `chk_products_min_stock_nonneg` CHECK (`min_stock_level` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `stock_movements` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` INT UNSIGNED NOT NULL,
  `movement_type` ENUM('stock_in','stock_out','sale_deduction','sale_restore','adjustment','void_restore','edit_adjustment','initial') NOT NULL,
  `quantity_change` DECIMAL(12,3) NOT NULL,
  `quantity_before` DECIMAL(12,3) NULL,
  `quantity_after` DECIMAL(12,3) NULL,
  `reference_type` VARCHAR(50) NULL,
  `reference_id` INT UNSIGNED NULL,
  `remarks` VARCHAR(255) NULL,
  `performed_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_stock_movements_product_created` (`product_id`, `created_at`),
  KEY `idx_stock_movements_type` (`movement_type`),
  KEY `idx_stock_movements_reference` (`reference_type`, `reference_id`),
  CONSTRAINT `fk_stock_movements_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_stock_movements_user` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Sales
-- ---------------------------------------------------------------------------
CREATE TABLE `sales` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `receipt_number` VARCHAR(50) NOT NULL,
  `sale_datetime` DATETIME NOT NULL,
  `cashier_user_id` INT UNSIGNED NOT NULL,
  `customer_name` VARCHAR(150) NULL,
  `subtotal` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `vat_rate_percent` DECIMAL(5,2) NOT NULL DEFAULT 12.00,
  `vat_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `total_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `payment_method` ENUM('Cash','GCash') NOT NULL,
  `amount_paid` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `change_due` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('Completed','Edited','Voided','Deleted') NOT NULL DEFAULT 'Completed',
  `review_confirmed_at` DATETIME NULL,
  `remarks` VARCHAR(255) NULL,
  `void_reason` VARCHAR(255) NULL,
  `created_by` INT UNSIGNED NULL,
  `updated_by` INT UNSIGNED NULL,
  `voided_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `voided_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_sales_receipt_number` (`receipt_number`),
  KEY `idx_sales_datetime` (`sale_datetime`),
  KEY `idx_sales_cashier_datetime` (`cashier_user_id`, `sale_datetime`),
  KEY `idx_sales_status` (`status`),
  KEY `idx_sales_payment_method` (`payment_method`),
  CONSTRAINT `fk_sales_cashier` FOREIGN KEY (`cashier_user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  CONSTRAINT `fk_sales_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_sales_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_sales_voided_by` FOREIGN KEY (`voided_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `chk_sales_totals_nonneg` CHECK (`subtotal` >= 0 AND `discount_amount` >= 0 AND `vat_amount` >= 0 AND `total_amount` >= 0 AND `amount_paid` >= 0 AND `change_due` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `sale_items` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sale_id` BIGINT UNSIGNED NOT NULL,
  `product_id` INT UNSIGNED NULL,
  `product_name_snapshot` VARCHAR(150) NOT NULL,
  `meat_type_snapshot` VARCHAR(100) NULL,
  `unit_snapshot` VARCHAR(30) NOT NULL,
  `unit_price` DECIMAL(12,2) NOT NULL,
  `quantity` DECIMAL(12,3) NOT NULL,
  `line_subtotal` DECIMAL(12,2) NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sale_items_sale` (`sale_id`),
  KEY `idx_sale_items_product` (`product_id`),
  CONSTRAINT `fk_sale_items_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_sale_items_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `chk_sale_items_qty_positive` CHECK (`quantity` > 0),
  CONSTRAINT `chk_sale_items_amounts_nonneg` CHECK (`unit_price` >= 0 AND `line_subtotal` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `payments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `sale_id` BIGINT UNSIGNED NOT NULL,
  `payment_method` ENUM('Cash','GCash') NOT NULL,
  `amount_tendered` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `change_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `reference_number` VARCHAR(100) NULL,
  `payment_status` ENUM('Pending','Paid','Failed','Refunded','Voided') NOT NULL DEFAULT 'Paid',
  `paid_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payments_sale` (`sale_id`),
  KEY `idx_payments_method_status` (`payment_method`, `payment_status`),
  CONSTRAINT `fk_payments_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `chk_payments_amounts_nonneg` CHECK (`amount_tendered` >= 0 AND `change_amount` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Notifications (event + per-user state for role-specific read/clear/history)
-- ---------------------------------------------------------------------------
CREATE TABLE `notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `notification_type` VARCHAR(50) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `severity` ENUM('info','success','warning','danger') NOT NULL DEFAULT 'info',
  `module` VARCHAR(50) NULL,
  `related_entity_type` VARCHAR(50) NULL,
  `related_entity_id` VARCHAR(50) NULL,
  `target_role` ENUM('Admin','Staff','All') NULL,
  `action_url` VARCHAR(255) NULL,
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notifications_type_created` (`notification_type`, `created_at`),
  KEY `idx_notifications_target_role` (`target_role`),
  KEY `idx_notifications_related` (`related_entity_type`, `related_entity_id`),
  CONSTRAINT `fk_notifications_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `user_notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `notification_id` BIGINT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `read_at` DATETIME NULL,
  `is_cleared` TINYINT(1) NOT NULL DEFAULT 0,
  `cleared_at` DATETIME NULL,
  `opened_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_user_notifications` (`notification_id`, `user_id`),
  KEY `idx_user_notifications_user_state` (`user_id`, `is_cleared`, `is_read`, `created_at`),
  CONSTRAINT `fk_user_notifications_notification` FOREIGN KEY (`notification_id`) REFERENCES `notifications` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT `fk_user_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------------
-- Settings, backups/restore, reports, activity logs
-- ---------------------------------------------------------------------------
CREATE TABLE `settings` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
  `setting_key` VARCHAR(100) NOT NULL,
  `setting_value` TEXT NULL,
  `value_type` ENUM('string','number','boolean','json') NOT NULL DEFAULT 'string',
  `description` VARCHAR(255) NULL,
  `is_editable` TINYINT(1) NOT NULL DEFAULT 1,
  `updated_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_settings_key` (`setting_key`),
  KEY `idx_settings_group` (`setting_group`),
  CONSTRAINT `fk_settings_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `backups` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `backup_type` ENUM('full','selected') NOT NULL DEFAULT 'full',
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size_bytes` BIGINT UNSIGNED NULL,
  `included_tables` TEXT NULL,
  `status` ENUM('pending','completed','failed') NOT NULL DEFAULT 'completed',
  `notes` VARCHAR(255) NULL,
  `created_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `downloaded_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  KEY `idx_backups_type_created` (`backup_type`, `created_at`),
  KEY `idx_backups_status` (`status`),
  CONSTRAINT `fk_backups_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `restore_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `backup_id` BIGINT UNSIGNED NULL,
  `restore_type` ENUM('full','selected') NOT NULL DEFAULT 'full',
  `source_file_name` VARCHAR(255) NOT NULL,
  `source_file_path` VARCHAR(500) NULL,
  `selected_tables` TEXT NULL,
  `validation_status` ENUM('pending','passed','failed') NOT NULL DEFAULT 'pending',
  `validation_message` VARCHAR(255) NULL,
  `safety_backup_id` BIGINT UNSIGNED NULL,
  `status` ENUM('pending','completed','failed','cancelled') NOT NULL DEFAULT 'pending',
  `notes` VARCHAR(255) NULL,
  `performed_by` INT UNSIGNED NULL,
  `started_at` DATETIME NULL,
  `completed_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_restore_logs_backup` (`backup_id`),
  KEY `idx_restore_logs_status_created` (`status`, `created_at`),
  CONSTRAINT `fk_restore_logs_backup` FOREIGN KEY (`backup_id`) REFERENCES `backups` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_restore_logs_safety_backup` FOREIGN KEY (`safety_backup_id`) REFERENCES `backups` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL,
  CONSTRAINT `fk_restore_logs_user` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `reports` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `report_type` ENUM('sales','inventory') NOT NULL,
  `output_format` ENUM('screen','print','pdf','csv','excel') NOT NULL DEFAULT 'screen',
  `date_from` DATE NULL,
  `date_to` DATE NULL,
  `parameters_json` JSON NULL,
  `file_path` VARCHAR(500) NULL,
  `status` ENUM('generated','failed','archived') NOT NULL DEFAULT 'generated',
  `summary_text` VARCHAR(255) NULL,
  `generated_by` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reports_type_created` (`report_type`, `created_at`),
  KEY `idx_reports_dates` (`date_from`, `date_to`),
  CONSTRAINT `fk_reports_generated_by` FOREIGN KEY (`generated_by`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `activity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `event_type` VARCHAR(80) NOT NULL,
  `module` VARCHAR(50) NOT NULL,
  `entity_type` VARCHAR(50) NULL,
  `entity_id` VARCHAR(50) NULL,
  `description` TEXT NOT NULL,
  `old_values_json` JSON NULL,
  `new_values_json` JSON NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` VARCHAR(255) NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_activity_logs_user_created` (`user_id`, `created_at`),
  KEY `idx_activity_logs_module_event` (`module`, `event_type`),
  KEY `idx_activity_logs_entity` (`entity_type`, `entity_id`),
  CONSTRAINT `fk_activity_logs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
    ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- Notes:
-- 1) Language/i18n is prepared via config/constants and user/settings language preferences (language via PHP translation files).
-- 2) Role-specific notifications are stored as notification events + per-user recipient state in user_notifications.
-- 3) Sales edits/voids/deletes should adjust stock via stock_movements and record audit/activity entries in later phases.
-- 4) All application SQL access must use PDO prepared statements and password_hash/password_verify.
