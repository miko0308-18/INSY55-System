# Web-Based Inventory Management System for Come N' Meatshop

A local web application for **Come N' Meatshop** (placeholder business) built with **HTML5, CSS3, JavaScript, PHP, MySQL/MariaDB, and Python** for a local **Laragon** environment (also compatible with XAMPP).

> **Placeholders:** Logo, business name/address/contact, receipt design text, staff/customer names, and sample product images are placeholders and clearly marked for easy replacement.

## Tech stack
- PHP 8+ (PDO, sessions), MySQL/MariaDB (phpMyAdmin/HeidiSQL)
- Bootstrap 5 (CDN), Bootstrap Icons (CDN), Chart.js (CDN)
- Vanilla JavaScript, custom CSS (orange theme)
- Python 3 scripts for sample data/report generation and backup validation

No Laravel, Node.js, Composer, npm, Docker, or external cloud services.

## Roles
- **Admin / Owner**: full access (dashboard, analytics, inventory, sales, notifications, reports, users, backup & restore, settings/profile)
- **Staff**: dashboard, inventory (view/search/add stock), sales (record/view history/edit own sales as correction), notifications, settings/profile. No access to Admin-only pages/actions.

## Default accounts (sample data)
After importing `database/come_n_meatshop_db_schema.sql` and `database/come_n_meatshop_db_sample_data.sql`:

| Role  | Username | Email | Password |
|-------|----------|-------|----------|
| Admin / Owner | `admin` | admin@comenmeatshop.com | `Admin@123` (placeholder/demo - change after first login) |
| Staff | `staff` | staff@comenmeatshop.com | `Staff@123` (placeholder/demo - change after first login) |
| Staff | `staff.maria` | staff.maria.placeholder@comenmeatshop.com | `Staff@123` (placeholder/demo - change after first login) |

> Credentials are documented for local demo/setup only. Passwords are stored using `password_hash()` and verified with `password_verify()`. Change default passwords immediately after first login. If credentials differ in your dataset, reset via Admin → Users or re-import the provided sample SQL.

## Quick start (Laragon)
1. Start **Laragon** and click **Start All** (Apache/Nginx + MySQL).
2. Ensure the project is in Laragon's web root. This workspace is at `C:\laragon\www\clone\INSY55-System\INSY55 project system`.
3. Open phpMyAdmin/HeidiSQL (Laragon menu → Database) and import into database `come_n_meatshop_db`:
   - Import `database/come_n_meatshop_db_schema.sql`
   - Import `database/come_n_meatshop_db_sample_data.sql`
4. Verify DB config in `config/database.php` (defaults: host `127.0.0.1`, port `3306`, user `root`, empty password, db `come_n_meatshop_db`) and `base_url` in `config/app.php` (default `/clone/INSY55-System/INSY55%20project%20system/` for this workspace).
5. Open: `http://localhost/clone/INSY55-System/INSY55%20project%20system/login.php`
6. Log in with default Admin/Staff accounts. If you use a Laragon virtual host/pretty URL, update `base_url` in `config/app.php` to match.

See [SETUP_INSTRUCTIONS.md](SETUP_INSTRUCTIONS.md) for details, Python setup, and troubleshooting.

## Key modules
- **Auth**: login/logout, session-based auth, role-based access control
- **Dashboard**: sales/inventory/low-stock summaries (Admin & Staff)
- **Inventory**: products, categories, stock movements, low/near-expiry/expired alerts, archive/restore/delete (Admin actions guarded)
- **Sales**: record sale with review-before-save, stock deduction, receipt/print, sales history, edit corrections (Staff own sales / Admin), Admin void/delete with stock restoration
- **Notifications**: in-app role-specific alerts (receive/open/read/clear/filter/history); optional email structure disabled by default
- **Reports** (Admin): sales & inventory reports on-screen, print (Save as PDF via browser), CSV/Excel downloads
- **Analytics** (Admin): Chart.js trends, top-selling products/categories, period comparison, date range, inventory value & low-stock
- **Users** (Admin): create/edit/deactivate/archive/delete Staff accounts; Admin profile management
- **Backup & Restore** (Admin): PHP SQL export (mysqldump fallback to PHP), download backups/history, restore full or selected tables with validation and safety backup
- **Settings**: profile editing, language switch (English / Filipino)

## Python scripts (`python/`)
- `generate_sample_data.py` – generates realistic sample SQL data (placeholders)
- `generate_sales_report.py` – sales report from MySQL (CSV/Excel/HTML print-to-PDF), date-range filtering
- `generate_inventory_report.py` – inventory report (low/out/near-expiry/expired, movements)
- `backup_validator.py` – validates SQL backup before restore
- `requirements.txt` – dependencies

Configure DB via environment variables if needed: `DB_HOST`, `DB_PORT`, `DB_USER`, `DB_PASSWORD`, `DB_NAME`.

## Documentation
- [SETUP_INSTRUCTIONS.md](SETUP_INSTRUCTIONS.md) – installation, DB, Python, and running the app
- [docs/PROJECT_DOCUMENTATION.md](docs/PROJECT_DOCUMENTATION.md) – modules, database tables, roles, workflows
- [docs/MANUAL_TEST_CASES.md](docs/MANUAL_TEST_CASES.md) – manual test cases

## Security notes
- PDO prepared statements, session auth, role checks, CSRF tokens on forms, `password_hash`/`password_verify`, input validation/sanitization, confirmation dialogs for destructive actions.
- Uploaded backup files are validated before restore; safety backup attempted before restore.

## Notes
- PDF export uses browser **Print → Save as PDF** (no external PDF library dependency).
- Payment-method filtering is not included in sales reports by design.
- `base_url` in `config/app.php` must match how you access the app in Laragon (localhost subpath or virtual host). Update it if links/assets redirect incorrectly.
