-- Come N’ Meatshop Inventory Management System
-- Phase 1: Representative sample/seed data (placeholder/demo only)
-- Import after database/schema.sql

SET NAMES utf8mb4;
SET time_zone = '+08:00';
SET FOREIGN_KEY_CHECKS = 0;

USE `come_n_meatshop_db`;

-- ---------------------------------------------------------------------------
-- Roles and users (passwords hashed with PHP password_hash)
-- Demo credentials (for setup/docs only):
--   Admin: username=admin, email=admin@comenmeatshop.com, password=Admin@123
--   Staff: username=staff, email=staff@comenmeatshop.com, password=Staff@123
-- ---------------------------------------------------------------------------
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Admin', 'admin', 'Admin/Owner role with full system access', '2026-01-05 08:00:00', '2026-01-05 08:00:00'),
(2, 'Staff', 'staff', 'Staff role with operational access based on RBAC rules', '2026-01-05 08:00:00', '2026-01-05 08:00:00');

INSERT INTO `users` (
  `id`, `role_id`, `username`, `email`, `password_hash`, `full_name`, `phone`, `avatar_path`,
  `language_preference`, `status`, `is_owner`, `last_login_at`, `created_by`, `updated_by`,
  `created_at`, `updated_at`, `archived_at`, `deleted_at`
) VALUES
(1, 1, 'admin', 'admin@comenmeatshop.com', '$2y$10$78wbN/XvQiw.1sG4Pv8RYex0nE50ZrYGrPNzGVbwJBOU187cSdXRO', 'Demo Admin Owner', '[Contact Number Placeholder]', 'assets/images/placeholders/avatar-placeholder.svg', 'en', 'Active', 1, '2026-11-24 08:10:00', NULL, 1, '2026-01-05 08:05:00', '2026-11-24 08:10:00', NULL, NULL),
(2, 2, 'staff', 'staff@comenmeatshop.com', '$2y$10$8UyPXZiSiwDmLjr9Uj/p4uuCFh6x2lICZkEF5RVChqpLvePXWucQW', 'Demo Staff One', '[Contact Number Placeholder]', 'assets/images/placeholders/avatar-placeholder.svg', 'en', 'Active', 0, '2026-11-24 08:20:00', 1, 1, '2026-01-06 09:00:00', '2026-11-24 08:20:00', NULL, NULL),
(3, 2, 'staff.maria', 'staff.maria.placeholder@comenmeatshop.com', '$2y$10$8UyPXZiSiwDmLjr9Uj/p4uuCFh6x2lICZkEF5RVChqpLvePXWucQW', 'Maria Demo Staff', NULL, 'assets/images/placeholders/avatar-placeholder.svg', 'fil', 'Active', 0, '2026-11-23 17:45:00', 1, 1, '2026-02-10 10:15:00', '2026-11-23 17:45:00', NULL, NULL),
(4, 2, 'staff.archived', 'archived.staff.placeholder@comenmeatshop.com', '$2y$10$8UyPXZiSiwDmLjr9Uj/p4uuCFh6x2lICZkEF5RVChqpLvePXWucQW', 'Archived Demo Staff', NULL, 'assets/images/placeholders/avatar-placeholder.svg', 'en', 'Archived', 0, '2026-08-15 14:00:00', 1, 1, '2026-03-01 08:30:00', '2026-09-01 09:00:00', '2026-09-01 09:00:00', NULL);

-- ---------------------------------------------------------------------------
-- Product categories and products (representative demo inventory)
-- ---------------------------------------------------------------------------
INSERT INTO `product_categories` (`id`, `name`, `description`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Beef', 'Fresh and frozen beef cuts (placeholder)', 'Active', '2026-01-10 08:00:00', '2026-01-10 08:00:00'),
(2, 'Pork', 'Pork cuts and portions (placeholder)', 'Active', '2026-01-10 08:05:00', '2026-01-10 08:05:00'),
(3, 'Chicken', 'Chicken cuts and whole items (placeholder)', 'Active', '2026-01-10 08:10:00', '2026-01-10 08:10:00'),
(4, 'Processed Meats', 'Tocino, longganisa, and similar items (placeholder)', 'Active', '2026-01-10 08:15:00', '2026-01-10 08:15:00'),
(5, 'Offal & Specialty', 'Special cuts and assorted items (placeholder)', 'Active', '2026-01-10 08:20:00', '2026-01-10 08:20:00');

INSERT INTO `products` (
  `id`, `category_id`, `name`, `meat_type`, `description`, `price`, `unit`, `stock_quantity`, `min_stock_level`,
  `expiration_date`, `image_path`, `barcode`, `supplier`, `status`, `created_by`, `updated_by`,
  `created_at`, `updated_at`, `archived_at`, `deleted_at`
) VALUES
(1, 1, 'Beef Brisket (Sample)', 'Beef', 'Placeholder beef brisket cut for demo.', 420.00, 'kg', 28.500, 10.000, '2026-12-05', 'assets/images/placeholders/product-placeholder.svg', 'BCN-BEEF-001', 'Sample Supplier A', 'Active', 1, 1, '2026-01-12 09:00:00', '2026-11-24 07:30:00', NULL, NULL),
(2, 1, 'Beef Ribeye Slice (Sample)', 'Beef', 'Placeholder premium beef slices.', 680.00, 'kg', 6.000, 10.000, '2026-11-28', 'assets/images/placeholders/product-placeholder.svg', 'BCN-BEEF-002', 'Sample Supplier A', 'Active', 1, 1, '2026-01-12 09:10:00', '2026-11-24 07:30:00', NULL, NULL),
(3, 1, 'Ground Beef (Sample)', 'Beef', 'Placeholder ground beef pack.', 360.00, 'pack', 0.000, 10.000, '2026-11-26', 'assets/images/placeholders/product-placeholder.svg', 'BCN-BEEF-003', 'Sample Supplier B', 'Active', 1, 1, '2026-01-12 09:20:00', '2026-11-23 16:00:00', NULL, NULL),
(4, 2, 'Pork Belly Slab (Sample)', 'Pork', 'Placeholder pork belly slab.', 390.00, 'kg', 34.000, 10.000, '2026-12-03', 'assets/images/placeholders/product-placeholder.svg', 'BCN-PORK-001', 'Sample Supplier C', 'Active', 1, 1, '2026-01-13 08:30:00', '2026-11-24 07:40:00', NULL, NULL),
(5, 2, 'Pork Chop (Sample)', 'Pork', 'Placeholder pork chop cuts.', 320.00, 'kg', 8.500, 10.000, '2026-11-27', 'assets/images/placeholders/product-placeholder.svg', 'BCN-PORK-002', 'Sample Supplier C', 'Active', 1, 1, '2026-01-13 08:40:00', '2026-11-24 07:40:00', NULL, NULL),
(6, 2, 'Pork Kasim (Sample)', 'Pork', 'Placeholder pork shoulder cut.', 310.00, 'kg', 18.000, 10.000, '2026-11-20', 'assets/images/placeholders/product-placeholder.svg', 'BCN-PORK-003', 'Sample Supplier C', 'Active', 1, 1, '2026-01-13 08:50:00', '2026-11-22 15:00:00', NULL, NULL),
(7, 3, 'Whole Chicken (Sample)', 'Chicken', 'Placeholder whole dressed chicken.', 220.00, 'piece', 25.000, 10.000, '2026-11-30', 'assets/images/placeholders/product-placeholder.svg', 'BCN-CHKN-001', 'Sample Supplier D', 'Active', 1, 1, '2026-01-14 09:00:00', '2026-11-24 07:50:00', NULL, NULL),
(8, 3, 'Chicken Breast Fillet (Sample)', 'Chicken', 'Placeholder boneless chicken breast.', 280.00, 'kg', 12.000, 10.000, '2026-11-27', 'assets/images/placeholders/product-placeholder.svg', 'BCN-CHKN-002', 'Sample Supplier D', 'Active', 1, 1, '2026-01-14 09:10:00', '2026-11-24 07:50:00', NULL, NULL),
(9, 3, 'Chicken Wings (Sample)', 'Chicken', 'Placeholder chicken wings tray.', 260.00, 'tray', 4.000, 10.000, '2026-11-29', 'assets/images/placeholders/product-placeholder.svg', 'BCN-CHKN-003', 'Sample Supplier D', 'Active', 1, 1, '2026-01-14 09:20:00', '2026-11-24 07:50:00', NULL, NULL),
(10, 4, 'Pork Tocino Pack (Sample)', 'Processed Meat', 'Placeholder sweet cured pork tocino.', 145.00, 'pack', 40.000, 10.000, '2026-12-20', 'assets/images/placeholders/product-placeholder.svg', 'BCN-PROC-001', 'Sample Supplier E', 'Active', 1, 1, '2026-01-15 10:00:00', '2026-11-24 08:00:00', NULL, NULL),
(11, 4, 'Chicken Longganisa Pack (Sample)', 'Processed Meat', 'Placeholder chicken longganisa.', 135.00, 'pack', 36.000, 10.000, '2026-12-18', 'assets/images/placeholders/product-placeholder.svg', 'BCN-PROC-002', 'Sample Supplier E', 'Active', 1, 1, '2026-01-15 10:10:00', '2026-11-24 08:00:00', NULL, NULL),
(12, 4, 'Beef Tapa Pack (Sample)', 'Processed Meat', 'Placeholder marinated beef tapa.', 165.00, 'pack', 22.000, 10.000, '2026-12-15', 'assets/images/placeholders/product-placeholder.svg', 'BCN-PROC-003', 'Sample Supplier E', 'Active', 1, 1, '2026-01-15 10:20:00', '2026-11-24 08:00:00', NULL, NULL),
(13, 1, 'Beef Short Ribs (Sample)', 'Beef', 'Placeholder beef short ribs.', 540.00, 'kg', 14.000, 10.000, '2026-12-01', 'assets/images/placeholders/product-placeholder.svg', 'BCN-BEEF-004', 'Sample Supplier A', 'Active', 1, 1, '2026-02-01 09:00:00', '2026-11-24 08:00:00', NULL, NULL),
(14, 2, 'Pork Liempo Strips (Sample)', 'Pork', 'Placeholder sliced pork liempo.', 375.00, 'kg', 16.500, 10.000, '2026-11-30', 'assets/images/placeholders/product-placeholder.svg', 'BCN-PORK-004', 'Sample Supplier C', 'Active', 1, 1, '2026-02-01 09:15:00', '2026-11-24 08:00:00', NULL, NULL),
(15, 3, 'Chicken Drumsticks (Sample)', 'Chicken', 'Placeholder chicken drumsticks.', 240.00, 'kg', 20.000, 10.000, '2026-11-29', 'assets/images/placeholders/product-placeholder.svg', 'BCN-CHKN-004', 'Sample Supplier D', 'Active', 1, 1, '2026-02-01 09:30:00', '2026-11-24 08:00:00', NULL, NULL),
(16, 5, 'Beef Liver (Sample)', 'Offal', 'Placeholder beef liver item.', 210.00, 'kg', 7.000, 10.000, '2026-11-26', 'assets/images/placeholders/product-placeholder.svg', 'BCN-OFFAL-001', 'Sample Supplier F', 'Active', 1, 1, '2026-02-05 08:00:00', '2026-11-24 08:00:00', NULL, NULL),
(17, 5, 'Pork Blood Cubes (Sample)', 'Offal', 'Placeholder pork blood cubes tray.', 95.00, 'tray', 9.000, 10.000, '2026-11-25', 'assets/images/placeholders/product-placeholder.svg', 'BCN-OFFAL-002', 'Sample Supplier F', 'Active', 1, 1, '2026-02-05 08:10:00', '2026-11-24 08:00:00', NULL, NULL),
(18, 4, 'Smoked Ham Slices (Sample)', 'Processed Meat', 'Placeholder sliced ham pack.', 190.00, 'pack', 15.000, 10.000, '2026-12-10', 'assets/images/placeholders/product-placeholder.svg', 'BCN-PROC-004', 'Sample Supplier E', 'Active', 1, 1, '2026-02-05 08:20:00', '2026-11-24 08:00:00', NULL, NULL),
(19, 1, 'Beef Sirloin Cubes (Sample)', 'Beef', 'Placeholder cubed beef sirloin.', 520.00, 'kg', 11.000, 10.000, '2026-11-21', 'assets/images/placeholders/product-placeholder.svg', 'BCN-BEEF-005', 'Sample Supplier A', 'Active', 1, 1, '2026-02-10 09:00:00', '2026-11-22 11:00:00', NULL, NULL),
(20, 2, 'Pork Ribs Rack (Sample)', 'Pork', 'Placeholder pork ribs rack.', 410.00, 'kg', 13.000, 10.000, '2026-12-02', 'assets/images/placeholders/product-placeholder.svg', 'BCN-PORK-005', 'Sample Supplier C', 'Active', 1, 1, '2026-02-10 09:10:00', '2026-11-24 08:00:00', NULL, NULL),
(21, 3, 'Chicken Thigh Fillet (Sample)', 'Chicken', 'Placeholder chicken thigh fillet.', 295.00, 'kg', 5.000, 10.000, '2026-11-28', 'assets/images/placeholders/product-placeholder.svg', 'BCN-CHKN-005', 'Sample Supplier D', 'Active', 1, 1, '2026-02-10 09:20:00', '2026-11-24 08:00:00', NULL, NULL),
(22, 4, 'Skinless Longganisa (Sample)', 'Processed Meat', 'Placeholder skinless longganisa pack.', 150.00, 'pack', 30.000, 10.000, '2027-01-05', 'assets/images/placeholders/product-placeholder.svg', 'BCN-PROC-005', 'Sample Supplier E', 'Active', 1, 1, '2026-03-01 08:00:00', '2026-11-24 08:00:00', NULL, NULL),
(23, 2, 'Pork Maskara (Sample)', 'Pork', 'Placeholder specialty pork cut.', 180.00, 'kg', 3.000, 5.000, '2026-12-08', 'assets/images/placeholders/product-placeholder.svg', 'BCN-PORK-006', 'Sample Supplier C', 'Inactive', 1, 1, '2026-03-10 08:00:00', '2026-10-01 10:00:00', NULL, NULL),
(24, 1, 'Beef Shank (Sample, Archived)', 'Beef', 'Placeholder archived product record.', 350.00, 'kg', 0.000, 8.000, '2026-10-15', 'assets/images/placeholders/product-placeholder.svg', 'BCN-BEEF-006', 'Sample Supplier B', 'Archived', 1, 1, '2026-03-15 08:00:00', '2026-10-20 09:00:00', '2026-10-20 09:00:00', NULL);

-- ---------------------------------------------------------------------------
-- Stock movements (initial + adjustments + sales deductions/restores)
-- ---------------------------------------------------------------------------
INSERT INTO `stock_movements` (
  `id`, `product_id`, `movement_type`, `quantity_change`, `quantity_before`, `quantity_after`,
  `reference_type`, `reference_id`, `remarks`, `performed_by`, `created_at`
) VALUES
(1, 1, 'initial', 40.000, 0.000, 40.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 08:00:00'),
(2, 2, 'initial', 15.000, 0.000, 15.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 08:05:00'),
(3, 3, 'initial', 20.000, 0.000, 20.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 08:10:00'),
(4, 4, 'initial', 50.000, 0.000, 50.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 08:15:00'),
(5, 5, 'initial', 20.000, 0.000, 20.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 08:20:00'),
(6, 6, 'initial', 25.000, 0.000, 25.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 08:25:00'),
(7, 7, 'initial', 40.000, 0.000, 40.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 08:30:00'),
(8, 8, 'initial', 20.000, 0.000, 20.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 08:35:00'),
(9, 9, 'initial', 18.000, 0.000, 18.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 08:40:00'),
(10, 10, 'initial', 60.000, 0.000, 60.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 08:45:00'),
(11, 11, 'initial', 50.000, 0.000, 50.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 08:50:00'),
(12, 12, 'initial', 35.000, 0.000, 35.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 08:55:00'),
(13, 13, 'initial', 20.000, 0.000, 20.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 09:00:00'),
(14, 14, 'initial', 25.000, 0.000, 25.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 09:05:00'),
(15, 15, 'initial', 30.000, 0.000, 30.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 09:10:00'),
(16, 16, 'initial', 15.000, 0.000, 15.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 09:15:00'),
(17, 17, 'initial', 15.000, 0.000, 15.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 09:20:00'),
(18, 18, 'initial', 25.000, 0.000, 25.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 09:25:00'),
(19, 19, 'initial', 18.000, 0.000, 18.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 09:30:00'),
(20, 20, 'initial', 20.000, 0.000, 20.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 09:35:00'),
(21, 21, 'initial', 12.000, 0.000, 12.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 09:40:00'),
(22, 22, 'initial', 40.000, 0.000, 40.000, 'setup', NULL, 'Initial stock (placeholder)', 1, '2026-09-01 09:45:00'),
(23, 4, 'stock_in', 10.000, 40.000, 50.000, 'manual', NULL, 'Restock delivery (placeholder)', 1, '2026-10-10 10:00:00'),
(24, 10, 'stock_in', 20.000, 40.000, 60.000, 'manual', NULL, 'Restock delivery (placeholder)', 1, '2026-10-10 10:10:00'),
(25, 3, 'adjustment', -5.000, 5.000, 0.000, 'manual', NULL, 'Spoilage/disposal adjustment (placeholder)', 1, '2026-11-23 16:00:00'),
(26, 1, 'sale_deduction', -2.000, 30.500, 28.500, 'sale', 30, 'Sale deduction (placeholder)', 2, '2026-11-24 07:30:00'),
(27, 4, 'sale_deduction', -1.500, 35.500, 34.000, 'sale', 30, 'Sale deduction (placeholder)', 2, '2026-11-24 07:30:00'),
(28, 6, 'sale_restore', 2.000, 16.000, 18.000, 'sale_void', 28, 'Stock restored after void (placeholder)', 1, '2026-11-22 15:30:00'),
(29, 9, 'sale_deduction', -2.000, 6.000, 4.000, 'sale', 29, 'Sale deduction leading to low stock (placeholder)', 3, '2026-11-23 17:10:00'),
(30, 2, 'sale_deduction', -1.500, 7.500, 6.000, 'sale', 27, 'Sale deduction leading to low stock (placeholder)', 2, '2026-11-21 11:20:00');

-- ---------------------------------------------------------------------------
-- Sales, sale items, payments (30 records across dates/statuses)
-- Totals shown in PHP; VAT configurable (default 12%). Placeholder/demo values.
-- ---------------------------------------------------------------------------
INSERT INTO `sales` (
  `id`, `receipt_number`, `sale_datetime`, `cashier_user_id`, `customer_name`,
  `subtotal`, `discount_percent`, `discount_amount`, `vat_rate_percent`, `vat_amount`, `total_amount`,
  `payment_method`, `amount_paid`, `change_due`, `status`, `review_confirmed_at`, `remarks`,
  `void_reason`, `created_by`, `updated_by`, `voided_by`, `created_at`, `updated_at`, `voided_at`
) VALUES
(1, 'CNM-20260902-0001', '2026-09-02 09:15:00', 2, 'Walk-in Sample', 760.00, 0.00, 0.00, 12.00, 91.20, 851.20, 'Cash', 900.00, 48.80, 'Completed', '2026-09-02 09:14:30', 'Sample completed sale', NULL, 2, 2, NULL, '2026-09-02 09:15:00', '2026-09-02 09:15:00', NULL),
(2, 'CNM-20260904-0002', '2026-09-04 11:40:00', 3, NULL, 430.00, 0.00, 0.00, 12.00, 51.60, 481.60, 'GCash', 481.60, 0.00, 'Completed', '2026-09-04 11:39:40', 'Sample GCash sale', NULL, 3, 3, NULL, '2026-09-04 11:40:00', '2026-09-04 11:40:00', NULL),
(3, 'CNM-20260906-0003', '2026-09-06 15:05:00', 2, 'Walk-in Sample', 540.00, 0.00, 0.00, 12.00, 64.80, 604.80, 'Cash', 700.00, 95.20, 'Completed', '2026-09-06 15:04:20', NULL, NULL, 2, 2, NULL, '2026-09-06 15:05:00', '2026-09-06 15:05:00', NULL),
(4, 'CNM-20260910-0004', '2026-09-10 10:20:00', 3, NULL, 615.00, 0.00, 0.00, 12.00, 73.80, 688.80, 'Cash', 700.00, 11.20, 'Completed', '2026-09-10 10:19:40', NULL, NULL, 3, 3, NULL, '2026-09-10 10:20:00', '2026-09-10 10:20:00', NULL),
(5, 'CNM-20260912-0005', '2026-09-12 13:50:00', 2, 'Walk-in Sample', 920.00, 5.00, 46.00, 12.00, 104.88, 978.88, 'Cash', 1000.00, 21.12, 'Completed', '2026-09-12 13:49:30', 'Sample discounted sale', NULL, 2, 2, NULL, '2026-09-12 13:50:00', '2026-09-12 13:50:00', NULL),
(6, 'CNM-20260918-0006', '2026-09-18 16:10:00', 3, NULL, 355.00, 0.00, 0.00, 12.00, 42.60, 397.60, 'GCash', 397.60, 0.00, 'Completed', '2026-09-18 16:09:30', NULL, NULL, 3, 3, NULL, '2026-09-18 16:10:00', '2026-09-18 16:10:00', NULL),
(7, 'CNM-20260922-0007', '2026-09-22 09:35:00', 2, 'Walk-in Sample', 780.00, 0.00, 0.00, 12.00, 93.60, 873.60, 'Cash', 900.00, 26.40, 'Completed', '2026-09-22 09:34:20', NULL, NULL, 2, 2, NULL, '2026-09-22 09:35:00', '2026-09-22 09:35:00', NULL),
(8, 'CNM-20260925-0008', '2026-09-25 14:25:00', 3, NULL, 450.00, 0.00, 0.00, 12.00, 54.00, 504.00, 'Cash', 550.00, 46.00, 'Completed', '2026-09-25 14:24:45', NULL, NULL, 3, 3, NULL, '2026-09-25 14:25:00', '2026-09-25 14:25:00', NULL),
(9, 'CNM-20261001-0009', '2026-10-01 10:05:00', 2, 'Walk-in Sample', 1060.00, 0.00, 0.00, 12.00, 127.20, 1187.20, 'Cash', 1200.00, 12.80, 'Completed', '2026-10-01 10:04:30', NULL, NULL, 2, 2, NULL, '2026-10-01 10:05:00', '2026-10-01 10:05:00', NULL),
(10, 'CNM-20261003-0010', '2026-10-03 12:15:00', 3, NULL, 520.00, 0.00, 0.00, 12.00, 62.40, 582.40, 'GCash', 582.40, 0.00, 'Completed', '2026-10-03 12:14:30', NULL, NULL, 3, 3, NULL, '2026-10-03 12:15:00', '2026-10-03 12:15:00', NULL),
(11, 'CNM-20261005-0011', '2026-10-05 17:00:00', 2, 'Walk-in Sample', 640.00, 0.00, 0.00, 12.00, 76.80, 716.80, 'Cash', 800.00, 83.20, 'Completed', '2026-10-05 16:59:30', NULL, NULL, 2, 2, NULL, '2026-10-05 17:00:00', '2026-10-05 17:00:00', NULL),
(12, 'CNM-20261008-0012', '2026-10-08 09:50:00', 3, NULL, 875.00, 0.00, 0.00, 12.00, 105.00, 980.00, 'Cash', 1000.00, 20.00, 'Completed', '2026-10-08 09:49:20', NULL, NULL, 3, 3, NULL, '2026-10-08 09:50:00', '2026-10-08 09:50:00', NULL),
(13, 'CNM-20261011-0013', '2026-10-11 14:40:00', 2, 'Walk-in Sample', 700.00, 0.00, 0.00, 12.00, 84.00, 784.00, 'Cash', 800.00, 16.00, 'Edited', '2026-10-11 14:39:20', 'Corrected quantity before finalization (placeholder edited record)', NULL, 2, 1, NULL, '2026-10-11 14:40:00', '2026-10-11 15:05:00', NULL),
(14, 'CNM-20261014-0014', '2026-10-14 11:30:00', 3, NULL, 330.00, 0.00, 0.00, 12.00, 39.60, 369.60, 'GCash', 369.60, 0.00, 'Completed', '2026-10-14 11:29:40', NULL, NULL, 3, 3, NULL, '2026-10-14 11:30:00', '2026-10-14 11:30:00', NULL),
(15, 'CNM-20261018-0015', '2026-10-18 16:45:00', 2, 'Walk-in Sample', 990.00, 0.00, 0.00, 12.00, 118.80, 1108.80, 'Cash', 1200.00, 91.20, 'Completed', '2026-10-18 16:44:30', NULL, NULL, 2, 2, NULL, '2026-10-18 16:45:00', '2026-10-18 16:45:00', NULL),
(16, 'CNM-20261022-0016', '2026-10-22 10:25:00', 3, NULL, 560.00, 0.00, 0.00, 12.00, 67.20, 627.20, 'Cash', 650.00, 22.80, 'Completed', '2026-10-22 10:24:40', NULL, NULL, 3, 3, NULL, '2026-10-22 10:25:00', '2026-10-22 10:25:00', NULL),
(17, 'CNM-20261026-0017', '2026-10-26 13:05:00', 2, 'Walk-in Sample', 430.00, 0.00, 0.00, 12.00, 51.60, 481.60, 'GCash', 481.60, 0.00, 'Completed', '2026-10-26 13:04:30', NULL, NULL, 2, 2, NULL, '2026-10-26 13:05:00', '2026-10-26 13:05:00', NULL),
(18, 'CNM-20261029-0018', '2026-10-29 15:55:00', 3, NULL, 1215.00, 0.00, 0.00, 12.00, 145.80, 1360.80, 'Cash', 1400.00, 39.20, 'Completed', '2026-10-29 15:54:20', NULL, NULL, 3, 3, NULL, '2026-10-29 15:55:00', '2026-10-29 15:55:00', NULL),
(19, 'CNM-20261102-0019', '2026-11-02 09:10:00', 2, 'Walk-in Sample', 735.00, 0.00, 0.00, 12.00, 88.20, 823.20, 'Cash', 900.00, 76.80, 'Completed', '2026-11-02 09:09:30', NULL, NULL, 2, 2, NULL, '2026-11-02 09:10:00', '2026-11-02 09:10:00', NULL),
(20, 'CNM-20261104-0020', '2026-11-04 12:35:00', 3, NULL, 600.00, 0.00, 0.00, 12.00, 72.00, 672.00, 'GCash', 672.00, 0.00, 'Completed', '2026-11-04 12:34:30', NULL, NULL, 3, 3, NULL, '2026-11-04 12:35:00', '2026-11-04 12:35:00', NULL),
(21, 'CNM-20261106-0021', '2026-11-06 16:20:00', 2, 'Walk-in Sample', 840.00, 0.00, 0.00, 12.00, 100.80, 940.80, 'Cash', 1000.00, 59.20, 'Completed', '2026-11-06 16:19:30', NULL, NULL, 2, 2, NULL, '2026-11-06 16:20:00', '2026-11-06 16:20:00', NULL),
(22, 'CNM-20261108-0022', '2026-11-08 10:45:00', 3, NULL, 480.00, 0.00, 0.00, 12.00, 57.60, 537.60, 'Cash', 600.00, 62.40, 'Completed', '2026-11-08 10:44:30', NULL, NULL, 3, 3, NULL, '2026-11-08 10:45:00', '2026-11-08 10:45:00', NULL),
(23, 'CNM-20261110-0023', '2026-11-10 14:15:00', 2, 'Walk-in Sample', 1020.00, 0.00, 0.00, 12.00, 122.40, 1142.40, 'Cash', 1200.00, 57.60, 'Completed', '2026-11-10 14:14:30', NULL, NULL, 2, 2, NULL, '2026-11-10 14:15:00', '2026-11-10 14:15:00', NULL),
(24, 'CNM-20261112-0024', '2026-11-12 11:05:00', 3, NULL, 665.00, 0.00, 0.00, 12.00, 79.80, 744.80, 'GCash', 744.80, 0.00, 'Completed', '2026-11-12 11:04:30', NULL, NULL, 3, 3, NULL, '2026-11-12 11:05:00', '2026-11-12 11:05:00', NULL),
(25, 'CNM-20261114-0025', '2026-11-14 17:25:00', 2, 'Walk-in Sample', 390.00, 0.00, 0.00, 12.00, 46.80, 436.80, 'Cash', 500.00, 63.20, 'Deleted', '2026-11-14 17:24:30', 'Deleted demo record (placeholder)', 'Incorrect test entry removed by admin', 2, 1, 1, '2026-11-14 17:25:00', '2026-11-14 18:00:00', NULL),
(26, 'CNM-20261116-0026', '2026-11-16 09:40:00', 3, NULL, 910.00, 0.00, 0.00, 12.00, 109.20, 1019.20, 'Cash', 1100.00, 80.80, 'Completed', '2026-11-16 09:39:30', NULL, NULL, 3, 3, NULL, '2026-11-16 09:40:00', '2026-11-16 09:40:00', NULL),
(27, 'CNM-20261118-0027', '2026-11-18 13:30:00', 2, 'Walk-in Sample', 860.00, 0.00, 0.00, 12.00, 103.20, 963.20, 'Cash', 1000.00, 36.80, 'Completed', '2026-11-18 13:29:30', NULL, NULL, 2, 2, NULL, '2026-11-18 13:30:00', '2026-11-18 13:30:00', NULL),
(28, 'CNM-20261120-0028', '2026-11-20 15:10:00', 3, NULL, 620.00, 0.00, 0.00, 12.00, 74.40, 694.40, 'GCash', 694.40, 0.00, 'Voided', '2026-11-20 15:09:30', 'Voided demo sale (placeholder)', 'Customer cancellation before pickup', 3, 1, 1, '2026-11-20 15:10:00', '2026-11-20 15:40:00', '2026-11-20 15:40:00'),
(29, 'CNM-20261122-0029', '2026-11-22 11:20:00', 2, 'Walk-in Sample', 1040.00, 0.00, 0.00, 12.00, 124.80, 1164.80, 'Cash', 1200.00, 35.20, 'Completed', '2026-11-22 11:19:30', NULL, NULL, 2, 2, NULL, '2026-11-22 11:20:00', '2026-11-22 11:20:00', NULL),
(30, 'CNM-20261124-0030', '2026-11-24 07:30:00', 2, 'Walk-in Sample', 1425.00, 0.00, 0.00, 12.00, 171.00, 1596.00, 'Cash', 1600.00, 4.00, 'Completed', '2026-11-24 07:29:30', 'Recent sale for dashboard/trends demo', NULL, 2, 2, NULL, '2026-11-24 07:30:00', '2026-11-24 07:30:00', NULL);

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `product_name_snapshot`, `meat_type_snapshot`, `unit_snapshot`, `unit_price`, `quantity`, `line_subtotal`, `created_at`, `updated_at`) VALUES
(1, 1, 4, 'Pork Belly Slab (Sample)', 'Pork', 'kg', 390.00, 1.000, 390.00, '2026-09-02 09:15:00', '2026-09-02 09:15:00'),
(2, 1, 10, 'Pork Tocino Pack (Sample)', 'Processed Meat', 'pack', 145.00, 2.000, 290.00, '2026-09-02 09:15:00', '2026-09-02 09:15:00'),
(3, 1, 17, 'Pork Blood Cubes (Sample)', 'Offal', 'tray', 95.00, 0.842, 80.00, '2026-09-02 09:15:00', '2026-09-02 09:15:00'),
(4, 2, 7, 'Whole Chicken (Sample)', 'Chicken', 'piece', 220.00, 1.000, 220.00, '2026-09-04 11:40:00', '2026-09-04 11:40:00'),
(5, 2, 11, 'Chicken Longganisa Pack (Sample)', 'Processed Meat', 'pack', 135.00, 1.000, 135.00, '2026-09-04 11:40:00', '2026-09-04 11:40:00'),
(6, 2, 18, 'Smoked Ham Slices (Sample)', 'Processed Meat', 'pack', 190.00, 0.395, 75.00, '2026-09-04 11:40:00', '2026-09-04 11:40:00'),
(7, 3, 1, 'Beef Brisket (Sample)', 'Beef', 'kg', 420.00, 1.000, 420.00, '2026-09-06 15:05:00', '2026-09-06 15:05:00'),
(8, 3, 12, 'Beef Tapa Pack (Sample)', 'Processed Meat', 'pack', 165.00, 0.727, 120.00, '2026-09-06 15:05:00', '2026-09-06 15:05:00'),
(9, 4, 14, 'Pork Liempo Strips (Sample)', 'Pork', 'kg', 375.00, 1.000, 375.00, '2026-09-10 10:20:00', '2026-09-10 10:20:00'),
(10, 4, 15, 'Chicken Drumsticks (Sample)', 'Chicken', 'kg', 240.00, 1.000, 240.00, '2026-09-10 10:20:00', '2026-09-10 10:20:00'),
(11, 5, 13, 'Beef Short Ribs (Sample)', 'Beef', 'kg', 540.00, 1.000, 540.00, '2026-09-12 13:50:00', '2026-09-12 13:50:00'),
(12, 5, 4, 'Pork Belly Slab (Sample)', 'Pork', 'kg', 390.00, 0.974, 380.00, '2026-09-12 13:50:00', '2026-09-12 13:50:00'),
(13, 6, 10, 'Pork Tocino Pack (Sample)', 'Processed Meat', 'pack', 145.00, 1.000, 145.00, '2026-09-18 16:10:00', '2026-09-18 16:10:00'),
(14, 6, 11, 'Chicken Longganisa Pack (Sample)', 'Processed Meat', 'pack', 135.00, 1.556, 210.00, '2026-09-18 16:10:00', '2026-09-18 16:10:00'),
(15, 7, 1, 'Beef Brisket (Sample)', 'Beef', 'kg', 420.00, 1.000, 420.00, '2026-09-22 09:35:00', '2026-09-22 09:35:00'),
(16, 7, 8, 'Chicken Breast Fillet (Sample)', 'Chicken', 'kg', 280.00, 1.286, 360.00, '2026-09-22 09:35:00', '2026-09-22 09:35:00'),
(17, 8, 7, 'Whole Chicken (Sample)', 'Chicken', 'piece', 220.00, 1.000, 220.00, '2026-09-25 14:25:00', '2026-09-25 14:25:00'),
(18, 8, 15, 'Chicken Drumsticks (Sample)', 'Chicken', 'kg', 240.00, 0.958, 230.00, '2026-09-25 14:25:00', '2026-09-25 14:25:00'),
(19, 9, 2, 'Beef Ribeye Slice (Sample)', 'Beef', 'kg', 680.00, 1.000, 680.00, '2026-10-01 10:05:00', '2026-10-01 10:05:00'),
(20, 9, 4, 'Pork Belly Slab (Sample)', 'Pork', 'kg', 390.00, 0.974, 380.00, '2026-10-01 10:05:00', '2026-10-01 10:05:00'),
(21, 10, 14, 'Pork Liempo Strips (Sample)', 'Pork', 'kg', 375.00, 1.000, 375.00, '2026-10-03 12:15:00', '2026-10-03 12:15:00'),
(22, 10, 10, 'Pork Tocino Pack (Sample)', 'Processed Meat', 'pack', 145.00, 1.000, 145.00, '2026-10-03 12:15:00', '2026-10-03 12:15:00'),
(23, 11, 5, 'Pork Chop (Sample)', 'Pork', 'kg', 320.00, 1.000, 320.00, '2026-10-05 17:00:00', '2026-10-05 17:00:00'),
(24, 11, 8, 'Chicken Breast Fillet (Sample)', 'Chicken', 'kg', 280.00, 1.143, 320.00, '2026-10-05 17:00:00', '2026-10-05 17:00:00'),
(25, 12, 1, 'Beef Brisket (Sample)', 'Beef', 'kg', 420.00, 1.250, 525.00, '2026-10-08 09:50:00', '2026-10-08 09:50:00'),
(26, 12, 20, 'Pork Ribs Rack (Sample)', 'Pork', 'kg', 410.00, 0.854, 350.00, '2026-10-08 09:50:00', '2026-10-08 09:50:00'),
(27, 13, 13, 'Beef Short Ribs (Sample)', 'Beef', 'kg', 540.00, 1.000, 540.00, '2026-10-11 14:40:00', '2026-10-11 15:05:00'),
(28, 13, 12, 'Beef Tapa Pack (Sample)', 'Processed Meat', 'pack', 165.00, 0.970, 160.00, '2026-10-11 14:40:00', '2026-10-11 15:05:00'),
(29, 14, 11, 'Chicken Longganisa Pack (Sample)', 'Processed Meat', 'pack', 135.00, 1.000, 135.00, '2026-10-14 11:30:00', '2026-10-14 11:30:00'),
(30, 14, 22, 'Skinless Longganisa (Sample)', 'Processed Meat', 'pack', 150.00, 1.300, 195.00, '2026-10-14 11:30:00', '2026-10-14 11:30:00'),
(31, 15, 4, 'Pork Belly Slab (Sample)', 'Pork', 'kg', 390.00, 1.500, 585.00, '2026-10-18 16:45:00', '2026-10-18 16:45:00'),
(32, 15, 7, 'Whole Chicken (Sample)', 'Chicken', 'piece', 220.00, 1.841, 405.00, '2026-10-18 16:45:00', '2026-10-18 16:45:00'),
(33, 16, 15, 'Chicken Drumsticks (Sample)', 'Chicken', 'kg', 240.00, 1.000, 240.00, '2026-10-22 10:25:00', '2026-10-22 10:25:00'),
(34, 16, 5, 'Pork Chop (Sample)', 'Pork', 'kg', 320.00, 1.000, 320.00, '2026-10-22 10:25:00', '2026-10-22 10:25:00'),
(35, 17, 10, 'Pork Tocino Pack (Sample)', 'Processed Meat', 'pack', 145.00, 1.000, 145.00, '2026-10-26 13:05:00', '2026-10-26 13:05:00'),
(36, 17, 8, 'Chicken Breast Fillet (Sample)', 'Chicken', 'kg', 280.00, 1.018, 285.00, '2026-10-26 13:05:00', '2026-10-26 13:05:00'),
(37, 18, 2, 'Beef Ribeye Slice (Sample)', 'Beef', 'kg', 680.00, 1.000, 680.00, '2026-10-29 15:55:00', '2026-10-29 15:55:00'),
(38, 18, 13, 'Beef Short Ribs (Sample)', 'Beef', 'kg', 540.00, 0.991, 535.00, '2026-10-29 15:55:00', '2026-10-29 15:55:00'),
(39, 19, 4, 'Pork Belly Slab (Sample)', 'Pork', 'kg', 390.00, 1.000, 390.00, '2026-11-02 09:10:00', '2026-11-02 09:10:00'),
(40, 19, 14, 'Pork Liempo Strips (Sample)', 'Pork', 'kg', 375.00, 0.920, 345.00, '2026-11-02 09:10:00', '2026-11-02 09:10:00'),
(41, 20, 7, 'Whole Chicken (Sample)', 'Chicken', 'piece', 220.00, 1.000, 220.00, '2026-11-04 12:35:00', '2026-11-04 12:35:00'),
(42, 20, 18, 'Smoked Ham Slices (Sample)', 'Processed Meat', 'pack', 190.00, 2.000, 380.00, '2026-11-04 12:35:00', '2026-11-04 12:35:00'),
(43, 21, 1, 'Beef Brisket (Sample)', 'Beef', 'kg', 420.00, 1.000, 420.00, '2026-11-06 16:20:00', '2026-11-06 16:20:00'),
(44, 21, 4, 'Pork Belly Slab (Sample)', 'Pork', 'kg', 390.00, 1.077, 420.00, '2026-11-06 16:20:00', '2026-11-06 16:20:00'),
(45, 22, 15, 'Chicken Drumsticks (Sample)', 'Chicken', 'kg', 240.00, 1.000, 240.00, '2026-11-08 10:45:00', '2026-11-08 10:45:00'),
(46, 22, 11, 'Chicken Longganisa Pack (Sample)', 'Processed Meat', 'pack', 135.00, 1.778, 240.00, '2026-11-08 10:45:00', '2026-11-08 10:45:00'),
(47, 23, 13, 'Beef Short Ribs (Sample)', 'Beef', 'kg', 540.00, 1.000, 540.00, '2026-11-10 14:15:00', '2026-11-10 14:15:00'),
(48, 23, 4, 'Pork Belly Slab (Sample)', 'Pork', 'kg', 390.00, 1.231, 480.00, '2026-11-10 14:15:00', '2026-11-10 14:15:00'),
(49, 24, 8, 'Chicken Breast Fillet (Sample)', 'Chicken', 'kg', 280.00, 1.250, 350.00, '2026-11-12 11:05:00', '2026-11-12 11:05:00'),
(50, 24, 20, 'Pork Ribs Rack (Sample)', 'Pork', 'kg', 410.00, 0.768, 315.00, '2026-11-12 11:05:00', '2026-11-12 11:05:00'),
(51, 25, 4, 'Pork Belly Slab (Sample)', 'Pork', 'kg', 390.00, 1.000, 390.00, '2026-11-14 17:25:00', '2026-11-14 17:25:00'),
(52, 26, 1, 'Beef Brisket (Sample)', 'Beef', 'kg', 420.00, 1.000, 420.00, '2026-11-16 09:40:00', '2026-11-16 09:40:00'),
(53, 26, 10, 'Pork Tocino Pack (Sample)', 'Processed Meat', 'pack', 145.00, 2.000, 290.00, '2026-11-16 09:40:00', '2026-11-16 09:40:00'),
(54, 26, 12, 'Beef Tapa Pack (Sample)', 'Processed Meat', 'pack', 165.00, 1.212, 200.00, '2026-11-16 09:40:00', '2026-11-16 09:40:00'),
(55, 27, 2, 'Beef Ribeye Slice (Sample)', 'Beef', 'kg', 680.00, 0.500, 340.00, '2026-11-18 13:30:00', '2026-11-18 13:30:00'),
(56, 27, 14, 'Pork Liempo Strips (Sample)', 'Pork', 'kg', 375.00, 1.387, 520.00, '2026-11-18 13:30:00', '2026-11-18 13:30:00'),
(57, 28, 6, 'Pork Kasim (Sample)', 'Pork', 'kg', 310.00, 2.000, 620.00, '2026-11-20 15:10:00', '2026-11-20 15:10:00'),
(58, 29, 4, 'Pork Belly Slab (Sample)', 'Pork', 'kg', 390.00, 1.000, 390.00, '2026-11-22 11:20:00', '2026-11-22 11:20:00'),
(59, 29, 9, 'Chicken Wings (Sample)', 'Chicken', 'tray', 260.00, 2.000, 520.00, '2026-11-22 11:20:00', '2026-11-22 11:20:00'),
(60, 29, 16, 'Beef Liver (Sample)', 'Offal', 'kg', 210.00, 0.619, 130.00, '2026-11-22 11:20:00', '2026-11-22 11:20:00'),
(61, 30, 1, 'Beef Brisket (Sample)', 'Beef', 'kg', 420.00, 2.000, 840.00, '2026-11-24 07:30:00', '2026-11-24 07:30:00'),
(62, 30, 4, 'Pork Belly Slab (Sample)', 'Pork', 'kg', 390.00, 1.500, 585.00, '2026-11-24 07:30:00', '2026-11-24 07:30:00');

INSERT INTO `payments` (`id`, `sale_id`, `payment_method`, `amount_tendered`, `change_amount`, `reference_number`, `payment_status`, `paid_at`, `created_at`, `updated_at`) VALUES
(1, 1, 'Cash', 900.00, 48.80, NULL, 'Paid', '2026-09-02 09:15:00', '2026-09-02 09:15:00', '2026-09-02 09:15:00'),
(2, 2, 'GCash', 481.60, 0.00, 'GCASH-SAMPLE-0904', 'Paid', '2026-09-04 11:40:00', '2026-09-04 11:40:00', '2026-09-04 11:40:00'),
(3, 3, 'Cash', 700.00, 95.20, NULL, 'Paid', '2026-09-06 15:05:00', '2026-09-06 15:05:00', '2026-09-06 15:05:00'),
(4, 4, 'Cash', 700.00, 11.20, NULL, 'Paid', '2026-09-10 10:20:00', '2026-09-10 10:20:00', '2026-09-10 10:20:00'),
(5, 5, 'Cash', 1000.00, 21.12, NULL, 'Paid', '2026-09-12 13:50:00', '2026-09-12 13:50:00', '2026-09-12 13:50:00'),
(6, 6, 'GCash', 397.60, 0.00, 'GCASH-SAMPLE-0918', 'Paid', '2026-09-18 16:10:00', '2026-09-18 16:10:00', '2026-09-18 16:10:00'),
(7, 7, 'Cash', 900.00, 26.40, NULL, 'Paid', '2026-09-22 09:35:00', '2026-09-22 09:35:00', '2026-09-22 09:35:00'),
(8, 8, 'Cash', 550.00, 46.00, NULL, 'Paid', '2026-09-25 14:25:00', '2026-09-25 14:25:00', '2026-09-25 14:25:00'),
(9, 9, 'Cash', 1200.00, 12.80, NULL, 'Paid', '2026-10-01 10:05:00', '2026-10-01 10:05:00', '2026-10-01 10:05:00'),
(10, 10, 'GCash', 582.40, 0.00, 'GCASH-SAMPLE-1003', 'Paid', '2026-10-03 12:15:00', '2026-10-03 12:15:00', '2026-10-03 12:15:00'),
(11, 11, 'Cash', 800.00, 83.20, NULL, 'Paid', '2026-10-05 17:00:00', '2026-10-05 17:00:00', '2026-10-05 17:00:00'),
(12, 12, 'Cash', 1000.00, 20.00, NULL, 'Paid', '2026-10-08 09:50:00', '2026-10-08 09:50:00', '2026-10-08 09:50:00'),
(13, 13, 'Cash', 800.00, 16.00, NULL, 'Paid', '2026-10-11 14:40:00', '2026-10-11 14:40:00', '2026-10-11 15:05:00'),
(14, 14, 'GCash', 369.60, 0.00, 'GCASH-SAMPLE-1014', 'Paid', '2026-10-14 11:30:00', '2026-10-14 11:30:00', '2026-10-14 11:30:00'),
(15, 15, 'Cash', 1200.00, 91.20, NULL, 'Paid', '2026-10-18 16:45:00', '2026-10-18 16:45:00', '2026-10-18 16:45:00'),
(16, 16, 'Cash', 650.00, 22.80, NULL, 'Paid', '2026-10-22 10:25:00', '2026-10-22 10:25:00', '2026-10-22 10:25:00'),
(17, 17, 'GCash', 481.60, 0.00, 'GCASH-SAMPLE-1026', 'Paid', '2026-10-26 13:05:00', '2026-10-26 13:05:00', '2026-10-26 13:05:00'),
(18, 18, 'Cash', 1400.00, 39.20, NULL, 'Paid', '2026-10-29 15:55:00', '2026-10-29 15:55:00', '2026-10-29 15:55:00'),
(19, 19, 'Cash', 900.00, 76.80, NULL, 'Paid', '2026-11-02 09:10:00', '2026-11-02 09:10:00', '2026-11-02 09:10:00'),
(20, 20, 'GCash', 672.00, 0.00, 'GCASH-SAMPLE-1104', 'Paid', '2026-11-04 12:35:00', '2026-11-04 12:35:00', '2026-11-04 12:35:00'),
(21, 21, 'Cash', 1000.00, 59.20, NULL, 'Paid', '2026-11-06 16:20:00', '2026-11-06 16:20:00', '2026-11-06 16:20:00'),
(22, 22, 'Cash', 600.00, 62.40, NULL, 'Paid', '2026-11-08 10:45:00', '2026-11-08 10:45:00', '2026-11-08 10:45:00'),
(23, 23, 'Cash', 1200.00, 57.60, NULL, 'Paid', '2026-11-10 14:15:00', '2026-11-10 14:15:00', '2026-11-10 14:15:00'),
(24, 24, 'GCash', 744.80, 0.00, 'GCASH-SAMPLE-1112', 'Paid', '2026-11-12 11:05:00', '2026-11-12 11:05:00', '2026-11-12 11:05:00'),
(25, 25, 'Cash', 500.00, 63.20, NULL, 'Refunded', '2026-11-14 17:25:00', '2026-11-14 17:25:00', '2026-11-14 18:00:00'),
(26, 26, 'Cash', 1100.00, 80.80, NULL, 'Paid', '2026-11-16 09:40:00', '2026-11-16 09:40:00', '2026-11-16 09:40:00'),
(27, 27, 'Cash', 1000.00, 36.80, NULL, 'Paid', '2026-11-18 13:30:00', '2026-11-18 13:30:00', '2026-11-18 13:30:00'),
(28, 28, 'GCash', 694.40, 0.00, 'GCASH-SAMPLE-1120', 'Voided', '2026-11-20 15:10:00', '2026-11-20 15:10:00', '2026-11-20 15:40:00'),
(29, 29, 'Cash', 1200.00, 35.20, NULL, 'Paid', '2026-11-22 11:20:00', '2026-11-22 11:20:00', '2026-11-22 11:20:00'),
(30, 30, 'Cash', 1600.00, 4.00, NULL, 'Paid', '2026-11-24 07:30:00', '2026-11-24 07:30:00', '2026-11-24 07:30:00');

-- ---------------------------------------------------------------------------
-- Notifications and per-user recipient states
-- ---------------------------------------------------------------------------
INSERT INTO `notifications` (`id`, `notification_type`, `title`, `message`, `severity`, `module`, `related_entity_type`, `related_entity_id`, `target_role`, `action_url`, `created_by`, `created_at`) VALUES
(1, 'low_stock', 'Low stock alert', 'Beef Ribeye Slice (Sample) is low on stock (6.000 kg remaining, minimum 10.000 kg).', 'warning', 'inventory', 'product', '2', 'All', 'modules/inventory/products.php', 1, '2026-11-24 07:35:00'),
(2, 'out_of_stock', 'Out of stock alert', 'Ground Beef (Sample) is out of stock.', 'danger', 'inventory', 'product', '3', 'All', 'modules/inventory/products.php', 1, '2026-11-23 16:05:00'),
(3, 'near_expiry', 'Near-expiry product alert', 'Pork Blood Cubes (Sample) will expire on 2026-11-25.', 'warning', 'inventory', 'product', '17', 'All', 'modules/inventory/products.php', 1, '2026-11-24 06:00:00'),
(4, 'expired_product', 'Expired product alert', 'Pork Kasim (Sample) expired on 2026-11-20.', 'danger', 'inventory', 'product', '6', 'All', 'modules/inventory/products.php', 1, '2026-11-21 06:00:00'),
(5, 'sale_recorded', 'New sale recorded', 'Sale CNM-20261124-0030 was recorded by Demo Staff One.', 'success', 'sales', 'sale', '30', 'All', 'modules/sales/history.php', 2, '2026-11-24 07:30:10'),
(6, 'sale_edited', 'Sale edited', 'Sale CNM-20261011-0013 was edited by Demo Admin Owner.', 'info', 'sales', 'sale', '13', 'Admin', 'modules/sales/history.php', 1, '2026-10-11 15:05:00'),
(7, 'sale_voided', 'Sale voided', 'Sale CNM-20261120-0028 was voided by Demo Admin Owner.', 'warning', 'sales', 'sale', '28', 'Admin', 'modules/sales/history.php', 1, '2026-11-20 15:40:00'),
(8, 'sale_deleted', 'Sale deleted', 'Sale CNM-20261114-0025 was deleted by Demo Admin Owner.', 'warning', 'sales', 'sale', '25', 'Admin', 'modules/sales/history.php', 1, '2026-11-14 18:00:00'),
(9, 'staff_account_created', 'Staff account created', 'Staff account for Maria Demo Staff was created.', 'success', 'users', 'user', '3', 'Admin', 'modules/users/index.php', 1, '2026-02-10 10:15:00'),
(10, 'staff_account_updated', 'Staff account updated', 'Profile/settings for Maria Demo Staff were updated.', 'info', 'users', 'user', '3', 'Admin', 'modules/users/index.php', 1, '2026-11-23 17:45:00'),
(11, 'staff_account_deleted', 'Staff account archived/deleted event', 'Archived Demo Staff account was archived (placeholder lifecycle event).', 'warning', 'users', 'user', '4', 'Admin', 'modules/users/index.php', 1, '2026-09-01 09:00:00'),
(12, 'backup_completed', 'Backup completed', 'Full database backup backup_full_20261124_010000.sql was completed.', 'success', 'backup', 'backup', '3', 'Admin', 'modules/backup_restore/index.php', 1, '2026-11-24 01:00:30'),
(13, 'restore_completed', 'Restore completed', 'Selected-table restore completed using backup_selected_20261110_020000.sql (placeholder).', 'warning', 'backup', 'restore_log', '2', 'Admin', 'modules/backup_restore/index.php', 1, '2026-11-10 02:15:00'),
(14, 'system_activity', 'System activity summary', 'Daily system activity summary generated (placeholder).', 'info', 'system', 'activity', NULL, 'All', 'modules/dashboard/index.php', 1, '2026-11-24 06:30:00');

INSERT INTO `user_notifications` (`id`, `notification_id`, `user_id`, `is_read`, `read_at`, `is_cleared`, `cleared_at`, `opened_at`, `created_at`) VALUES
(1, 1, 1, 0, NULL, 0, NULL, NULL, '2026-11-24 07:35:00'),
(2, 1, 2, 1, '2026-11-24 07:50:00', 0, NULL, '2026-11-24 07:49:00', '2026-11-24 07:35:00'),
(3, 1, 3, 0, NULL, 0, NULL, NULL, '2026-11-24 07:35:00'),
(4, 2, 1, 1, '2026-11-23 16:20:00', 0, NULL, '2026-11-23 16:19:00', '2026-11-23 16:05:00'),
(5, 2, 2, 1, '2026-11-23 16:30:00', 1, '2026-11-23 17:00:00', '2026-11-23 16:29:00', '2026-11-23 16:05:00'),
(6, 2, 3, 0, NULL, 0, NULL, NULL, '2026-11-23 16:05:00'),
(7, 3, 1, 0, NULL, 0, NULL, NULL, '2026-11-24 06:00:00'),
(8, 3, 2, 0, NULL, 0, NULL, NULL, '2026-11-24 06:00:00'),
(9, 3, 3, 1, '2026-11-24 06:30:00', 0, NULL, '2026-11-24 06:29:00', '2026-11-24 06:00:00'),
(10, 4, 1, 1, '2026-11-21 07:00:00', 0, NULL, '2026-11-21 06:59:00', '2026-11-21 06:00:00'),
(11, 4, 2, 0, NULL, 0, NULL, NULL, '2026-11-21 06:00:00'),
(12, 4, 3, 0, NULL, 0, NULL, NULL, '2026-11-21 06:00:00'),
(13, 5, 1, 1, '2026-11-24 07:31:00', 0, NULL, '2026-11-24 07:31:00', '2026-11-24 07:30:10'),
(14, 5, 2, 1, '2026-11-24 07:30:20', 1, '2026-11-24 07:45:00', '2026-11-24 07:30:20', '2026-11-24 07:30:10'),
(15, 5, 3, 0, NULL, 0, NULL, NULL, '2026-11-24 07:30:10'),
(16, 6, 1, 1, '2026-10-11 15:10:00', 0, NULL, '2026-10-11 15:09:00', '2026-10-11 15:05:00'),
(17, 7, 1, 1, '2026-11-20 15:45:00', 0, NULL, '2026-11-20 15:44:00', '2026-11-20 15:40:00'),
(18, 8, 1, 1, '2026-11-14 18:05:00', 0, NULL, '2026-11-14 18:04:00', '2026-11-14 18:00:00'),
(19, 9, 1, 1, '2026-02-10 10:20:00', 1, '2026-02-10 11:00:00', '2026-02-10 10:19:00', '2026-02-10 10:15:00'),
(20, 10, 1, 0, NULL, 0, NULL, NULL, '2026-11-23 17:45:00'),
(21, 11, 1, 1, '2026-09-01 09:10:00', 1, '2026-09-01 09:30:00', '2026-09-01 09:09:00', '2026-09-01 09:00:00'),
(22, 12, 1, 1, '2026-11-24 01:05:00', 0, NULL, '2026-11-24 01:04:00', '2026-11-24 01:00:30'),
(23, 13, 1, 1, '2026-11-10 02:20:00', 0, NULL, '2026-11-10 02:19:00', '2026-11-10 02:15:00'),
(24, 14, 1, 0, NULL, 0, NULL, NULL, '2026-11-24 06:30:00'),
(25, 14, 2, 0, NULL, 0, NULL, NULL, '2026-11-24 06:30:00'),
(26, 14, 3, 1, '2026-11-24 06:45:00', 0, NULL, '2026-11-24 06:44:00', '2026-11-24 06:30:00');

-- ---------------------------------------------------------------------------
-- Settings (business placeholders and configurable defaults)
-- ---------------------------------------------------------------------------
INSERT INTO `settings` (`id`, `setting_group`, `setting_key`, `setting_value`, `value_type`, `description`, `is_editable`, `updated_by`, `created_at`, `updated_at`) VALUES
(1, 'business', 'business_name', 'Come N’ Meatshop', 'string', 'Business display name (placeholder allowed)', 1, 1, '2026-01-05 08:10:00', '2026-01-05 08:10:00'),
(2, 'business', 'business_address', '[Business Address Placeholder]', 'string', 'Business address placeholder', 1, 1, '2026-01-05 08:10:00', '2026-01-05 08:10:00'),
(3, 'business', 'business_contact', '[Contact Number Placeholder]', 'string', 'Business contact number placeholder', 1, 1, '2026-01-05 08:10:00', '2026-01-05 08:10:00'),
(4, 'business', 'business_email', '[Business Email Placeholder]', 'string', 'Business email placeholder', 1, 1, '2026-01-05 08:10:00', '2026-01-05 08:10:00'),
(5, 'business', 'business_logo', '[Logo Placeholder]', 'string', 'Logo path/text placeholder', 1, 1, '2026-01-05 08:10:00', '2026-01-05 08:10:00'),
(6, 'receipt', 'receipt_footer', 'Thank you for shopping at Come N’ Meatshop!', 'string', 'Receipt footer text', 1, 1, '2026-01-05 08:10:00', '2026-01-05 08:10:00'),
(7, 'localization', 'currency_code', 'PHP', 'string', 'Currency code', 1, 1, '2026-01-05 08:10:00', '2026-01-05 08:10:00'),
(8, 'localization', 'currency_symbol', '₱', 'string', 'Currency symbol (Philippine Peso)', 1, 1, '2026-01-05 08:10:00', '2026-01-05 08:10:00'),
(9, 'localization', 'default_language', 'en', 'string', 'Default language (en/fil)', 1, 1, '2026-01-05 08:10:00', '2026-01-05 08:10:00'),
(10, 'localization', 'supported_languages', '["en","fil"]', 'json', 'Supported languages for config-based bilingual approach', 1, 1, '2026-01-05 08:10:00', '2026-01-05 08:10:00'),
(11, 'sales', 'vat_rate_percent', '12.00', 'number', 'Default VAT/tax rate (configurable)', 1, 1, '2026-01-05 08:10:00', '2026-01-05 08:10:00'),
(12, 'sales', 'default_discount_percent', '0.00', 'number', 'Default discount; discounts allowed per sale', 1, 1, '2026-01-05 08:10:00', '2026-01-05 08:10:00'),
(13, 'inventory', 'default_min_stock_level', '10', 'number', 'Default minimum stock level (editable per product)', 1, 1, '2026-01-05 08:10:00', '2026-01-05 08:10:00'),
(14, 'sales', 'payment_methods', '["Cash","GCash"]', 'json', 'Enabled payment methods', 1, 1, '2026-01-05 08:10:00', '2026-01-05 08:10:00'),
(15, 'notifications', 'email_notifications_enabled', 'false', 'boolean', 'Optional email notification structure (disabled by default)', 1, 1, '2026-01-05 08:10:00', '2026-01-05 08:10:00');

-- ---------------------------------------------------------------------------
-- Backups, restore logs, reports, activity logs (history/demo records)
-- ---------------------------------------------------------------------------
INSERT INTO `backups` (`id`, `backup_type`, `file_name`, `file_path`, `file_size_bytes`, `included_tables`, `status`, `notes`, `created_by`, `created_at`, `downloaded_at`) VALUES
(1, 'full', 'backup_full_20261015_010000.sql', 'uploads/backups/backup_full_20261015_010000.sql', 245760, 'ALL', 'completed', 'Scheduled full backup (placeholder)', 1, '2026-10-15 01:00:20', '2026-10-15 08:00:00'),
(2, 'selected', 'backup_selected_20261110_020000.sql', 'uploads/backups/backup_selected_20261110_020000.sql', 98304, 'products,stock_movements,sales,sale_items', 'completed', 'Selected-table backup before demo restore (placeholder)', 1, '2026-11-10 02:00:15', NULL),
(3, 'full', 'backup_full_20261124_010000.sql', 'uploads/backups/backup_full_20261124_010000.sql', 262144, 'ALL', 'completed', 'Latest full backup (placeholder)', 1, '2026-11-24 01:00:30', NULL);

INSERT INTO `restore_logs` (`id`, `backup_id`, `restore_type`, `source_file_name`, `source_file_path`, `selected_tables`, `validation_status`, `validation_message`, `safety_backup_id`, `status`, `notes`, `performed_by`, `started_at`, `completed_at`, `created_at`) VALUES
(1, 1, 'full', 'backup_full_20261015_010000.sql', 'uploads/backups/backup_full_20261015_010000.sql', 'ALL', 'passed', 'Validation passed (placeholder)', 2, 'completed', 'Test restore in demo workflow (placeholder)', 1, '2026-10-15 02:00:00', '2026-10-15 02:03:00', '2026-10-15 02:03:00'),
(2, 2, 'selected', 'backup_selected_20261110_020000.sql', 'uploads/backups/backup_selected_20261110_020000.sql', 'products,stock_movements', 'passed', 'Selected tables validated (placeholder)', 3, 'completed', 'Selected-table restore demo record', 1, '2026-11-10 02:10:00', '2026-11-10 02:15:00', '2026-11-10 02:15:00');

INSERT INTO `reports` (`id`, `report_type`, `output_format`, `date_from`, `date_to`, `parameters_json`, `file_path`, `status`, `summary_text`, `generated_by`, `created_at`) VALUES
(1, 'sales', 'screen', '2026-09-01', '2026-09-30', '{"grouping":"daily,weekly,monthly","include_chart":true}', NULL, 'generated', 'September sales report summary (placeholder)', 1, '2026-10-01 08:00:00'),
(2, 'inventory', 'csv', NULL, NULL, '{"include_low_stock":true,"include_expiry":true,"include_movements":true}', 'uploads/reports/inventory_20261015.csv', 'generated', 'Inventory snapshot/export (placeholder)', 1, '2026-10-15 08:30:00'),
(3, 'sales', 'pdf', '2026-10-01', '2026-10-31', '{"grouping":"daily,weekly,monthly","by_staff":true}', 'uploads/reports/sales_20261031.pdf', 'generated', 'October sales report PDF/print-ready (placeholder)', 1, '2026-11-01 08:00:00'),
(4, 'inventory', 'print', NULL, NULL, '{"include_archived":true,"include_categories":true}', NULL, 'generated', 'Printable inventory report (placeholder)', 1, '2026-11-20 09:00:00'),
(5, 'sales', 'excel', '2026-11-01', '2026-11-24', '{"grouping":"daily,weekly,monthly","top_products":true}', 'uploads/reports/sales_20261124.xlsx', 'generated', 'Current period sales export (placeholder)', 1, '2026-11-24 07:00:00');

INSERT INTO `activity_logs` (`id`, `user_id`, `event_type`, `module`, `entity_type`, `entity_id`, `description`, `old_values_json`, `new_values_json`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'user.login', 'auth', 'user', '1', 'Demo Admin Owner logged in (placeholder).', NULL, '{"username":"admin"}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-11-24 08:10:00'),
(2, 2, 'user.login', 'auth', 'user', '2', 'Demo Staff One logged in (placeholder).', NULL, '{"username":"staff"}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-11-24 08:20:00'),
(3, 1, 'product.create', 'inventory', 'product', '22', 'Created product Skinless Longganisa (Sample).', NULL, '{"name":"Skinless Longganisa (Sample)","stock_quantity":30}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-03-01 08:05:00'),
(4, 1, 'stock.add', 'inventory', 'stock_movement', '23', 'Added 10.000 kg stock to Pork Belly Slab (Sample).', '{"stock_quantity":40.000}', '{"stock_quantity":50.000}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-10-10 10:00:00'),
(5, 2, 'sale.create', 'sales', 'sale', '30', 'Recorded sale CNM-20261124-0030 (Completed).', NULL, '{"receipt_number":"CNM-20261124-0030","total_amount":1596.00}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-11-24 07:30:00'),
(6, 1, 'sale.edit', 'sales', 'sale', '13', 'Edited sale CNM-20261011-0013 to correct quantities (placeholder).', '{"status":"Completed"}', '{"status":"Edited"}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-10-11 15:05:00'),
(7, 1, 'sale.void', 'sales', 'sale', '28', 'Voided sale CNM-20261120-0028 and restored stock (placeholder).', '{"status":"Completed"}', '{"status":"Voided"}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-11-20 15:40:00'),
(8, 1, 'sale.delete', 'sales', 'sale', '25', 'Deleted sale CNM-20261114-0025 (placeholder).', '{"status":"Completed"}', '{"status":"Deleted"}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-11-14 18:00:00'),
(9, 1, 'user.create', 'users', 'user', '3', 'Created staff account for Maria Demo Staff.', NULL, '{"username":"staff.maria","role":"Staff"}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-02-10 10:15:00'),
(10, 1, 'user.update', 'users', 'user', '3', 'Updated staff account/profile for Maria Demo Staff (placeholder).', '{"language_preference":"en"}', '{"language_preference":"fil"}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-11-23 17:45:00'),
(11, 1, 'user.archive', 'users', 'user', '4', 'Archived staff account Archived Demo Staff (placeholder).', '{"status":"Active"}', '{"status":"Archived"}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-09-01 09:00:00'),
(12, 1, 'notification.read', 'notifications', 'notification', '2', 'Opened/read out-of-stock notification (placeholder).', NULL, '{"is_read":true}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-11-23 16:20:00'),
(13, 1, 'backup.create', 'backup', 'backup', '3', 'Created full backup backup_full_20261124_010000.sql.', NULL, '{"backup_type":"full","status":"completed"}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-11-24 01:00:30'),
(14, 1, 'restore.complete', 'backup', 'restore_log', '2', 'Completed selected-table restore (placeholder).', NULL, '{"restore_type":"selected","status":"completed"}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-11-10 02:15:00'),
(15, 1, 'report.generate', 'reports', 'report', '5', 'Generated sales Excel report for 2026-11-01 to 2026-11-24 (placeholder).', NULL, '{"report_type":"sales","output_format":"excel"}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-11-24 07:00:00'),
(16, 2, 'alert.low_stock', 'inventory', 'product', '9', 'Low-stock condition detected for Chicken Wings (Sample) (placeholder).', NULL, '{"stock_quantity":4.000,"min_stock_level":10.000}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-11-23 17:10:00'),
(17, 1, 'settings.update', 'settings', 'setting', '11', 'Confirmed VAT default setting (12%).', '{"vat_rate_percent":"12.00"}', '{"vat_rate_percent":"12.00"}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-11-01 09:00:00'),
(18, 3, 'sale.create', 'sales', 'sale', '29', 'Recorded sale CNM-20261122-0029 (Completed).', NULL, '{"receipt_number":"CNM-20261122-0029","total_amount":1164.80}', '127.0.0.1', 'Placeholder Browser/1.0', '2026-11-22 11:20:00');

SET FOREIGN_KEY_CHECKS = 1;
