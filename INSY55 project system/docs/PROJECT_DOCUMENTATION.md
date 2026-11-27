# Project Documentation – Come N' Meatshop Inventory System

## 1. Overview
**System:** Web-Based Inventory Management System for Come N' Meatshop (placeholder business)  
**Environment:** XAMPP (Apache, PHP, MySQL/MariaDB, phpMyAdmin)  
**Stack:** HTML5, CSS3, JavaScript, PHP (PDO), MySQL/MariaDB, Python 3, Bootstrap 5 (CDN), Chart.js (CDN)

The system provides inventory management, sales recording with stock control, dashboards, analytics, reports, in-app notifications, user/role management, and database backup/restore.

## 2. User roles and access control
| Role | Description | Access |
|------|-------------|--------|
| **Admin / Owner** | Shop owner/administrator | Dashboard, Analytics, Inventory, Sales (including void/delete), Notifications, Reports, Users (Staff account management), Backup & Restore, Settings/Profile |
| **Staff** | Store staff/cashier | Dashboard, Inventory (search/view/add stock), Sales (record, view history, edit own sales corrections), Notifications, Settings/Profile |

- Authentication is session-based (`login.php`, `logout.php`, `includes/auth_check.php`).
- Role checks prevent Staff from accessing Admin-only pages/actions via direct URL.
- Passwords are hashed with `password_hash()` and verified with `password_verify()`.

## 3. Modules and workflows

### 3.1 Authentication
- **Login:** `login.php` validates credentials, checks active status, loads role, stores user session.
- **Logout:** `logout.php` clears session.
- **Role identification:** Admin vs Staff determined from `roles`/user role.

### 3.2 Dashboard (`modules/dashboard/`)
- Admin and Staff dashboards show sales summary, inventory summary, and low-stock items (plus inventory value/alerts context).
- Inventory alerts are checked on load (best-effort) to generate in-app notifications.

### 3.3 Inventory (`modules/inventory/`)
- **List/Search:** view products with filters/search.
- **Add/Edit product:** name, category/meat type, description, price, unit, stock, min stock (default 10), expiration, image placeholder, barcode/supplier (optional), status.
- **Add stock:** creates stock movement and updates quantity.
- **Archive/Restore/Delete:** product status management (Admin-guarded actions where applicable).
- **Alerts:** low stock, out of stock, near-expiry, expired tracked and surfaced via dashboard/reports/notifications.

### 3.4 Sales (`modules/sales/`)
- **Record sale:** select products, enter quantities, customer (optional), discount/VAT, payment method (Cash/GCash), amount paid.
- **Stock validation:** prevents insufficient-stock sales.
- **Review before save:** draft stored in session; user reviews totals and can **Make Changes** or **Confirm & Save**.
- **On save:** computes totals/change, saves sale/items/payment, deducts stock, records stock movements, generates receipt number, creates notifications/activity logs.
- **Receipt:** printable placeholder receipt view (`receipt.php`) with browser Print/PDF support.
- **History:** both roles can view history (Staff scoped appropriately; Admin sees full). Staff can edit their own sale corrections when permitted; Admin can edit/void/delete.
- **Edit/Void/Delete:** edits restore previous stock then reapply deductions; void/delete restore stock and update status/payment/logs/notifications.

### 3.5 Notifications (`modules/notifications/`)
- **Primary system:** in-app, role-specific notifications.
- **Optional email:** placeholder/disabled by default in config (`email_notifications_enabled`).
- **Events:** low stock, out of stock, near-expiry, expired, new sale, sale edited/voided/deleted, user/account actions, backup/restore, important activities.
- **Actions:** receive/open alert, mark read, mark all read, clear, clear read, filter unread/read/all, view history.

### 3.6 Reports (`modules/reports/`, Admin)
- **Sales report:** date range, summary totals/transactions, products sold/qty, sales by product and staff, daily/weekly/monthly trends, optional chart, printable layout. No payment-method filtering.
- **Inventory report:** current inventory, stock levels, low/out/near-expiry/expired, stock movement history, categories, archived/inactive items, printable layout.
- **Outputs:** on-screen, browser Print → Save as PDF, CSV download, Excel-friendly download.

### 3.7 Analytics (`modules/analytics/`, Admin)
- Descriptive analytics with Chart.js: sales trend (daily/weekly/monthly), top-selling meats/products, best-selling categories, period comparison (two date ranges), date-range selector, inventory value summary, low-stock overview.

### 3.8 Users (`modules/users/`, Admin)
- Admin can create, edit, deactivate/archive, and delete Staff accounts from within the system.
- Admin can manage own profile; Staff can edit own profile (`modules/settings/profile.php`).

### 3.9 Backup & Restore (`modules/backup_restore/`, Admin)
- **Backup:** downloadable SQL export (mysqldump if available, PHP fallback), history recorded, previous backups downloadable if stored locally.
- **Restore:** full database or selected tables, warning/confirmation, upload validation, safety backup before restore when possible, activity logging and notifications.
- **Validation:** basic SQL/required-table/unsafe-token checks in PHP helper and Python `backup_validator.py`.

### 3.10 Settings (`modules/settings/`)
- **Profile:** edit own profile information/password (per implementation).
- **Language:** English / Filipino session-based switch (`set_language.php`, `assets/lang/*.php`).
- **Logout:** available via header/menu.

## 4. Database design (core tables)
Database: `come_n_meatshop_db` (import `database/come_n_meatshop_db_schema.sql`).

| Table | Purpose |
|-------|---------|
| `roles` | Role definitions (Admin/Owner, Staff) |
| `users` | User accounts, role linkage, profile/status fields, password hashes |
| `product_categories` | Product/meat categories |
| `products` | Product catalog, pricing, stock, min stock, expiry, status, placeholders |
| `stock_movements` | Stock in/out adjustments, references (sale, restore, manual), audit trail |
| `sales` | Sale header: receipt, cashier, customer, totals, payment, status, timestamps |
| `sale_items` | Line items with product snapshots, qty, price, subtotal |
| `payments` | Payment records (Cash/GCash), amount tendered/change/status |
| `notifications` | Notification definitions/events (type, target role, module, entity, action URL) |
| `user_notifications` | Per-user delivery state (read/cleared timestamps) |
| `settings` | Key/value application settings |
| `backups` | Backup/restore history metadata and local file references |
| `activity_logs` | System/user activity audit trail |
| `reports` | Generated report metadata/history (as applicable) |

Design notes: foreign keys, indexes, timestamps, status/archive fields where needed, secure password hashes, and transaction-safe stock/sale operations.

## 5. Python scripts
Located in `python/` (connect to same MySQL DB via `DB_*` env vars):
- `generate_sample_data.py` – outputs SQL sample data with placeholders.
- `generate_sales_report.py` – date-range sales report (CSV/Excel/HTML print-to-PDF).
- `generate_inventory_report.py` – inventory/alerts/movements report (CSV/Excel/HTML).
- `backup_validator.py` – validates backup file before restore (structure/safety checks).

Suggested additional utility (optional): `check_alerts.py` to recompute/send inventory alerts from a scheduled task; not required for core app.

## 6. UI/UX notes
- Orange-based theme with neutral supporting colors; Metis-inspired layout/sidebar/cards/tables/modals adapted to Bootstrap 5 CDN.
- Responsive layout, toast/alert messages, confirmation dialogs for destructive actions.
- Placeholder logo/images/business details/receipt clearly marked and replaceable.
- Language support: English and Filipino/Tagalog (simple switch).

## 7. Known implementation notes
- PDF generation uses browser Print → Save as PDF; Excel downloads are Excel-friendly exports where practical (CSV/HTML-table .xls fallback) without heavy dependencies.
- Sales reports intentionally exclude payment-method filtering.
- Optional email notification structure exists but is disabled by default and not required.
- For XAMPP demos, ensure `uploads/` is writable for backups/uploads. Replace all placeholder data before any real-world use.
