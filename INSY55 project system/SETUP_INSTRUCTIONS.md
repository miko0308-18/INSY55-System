# Setup Instructions – Come N' Meatshop Inventory System

## 1. Prerequisites
- **Laragon** (recommended; Apache/Nginx + MySQL/MariaDB + PHP 8.x + phpMyAdmin/HeidiSQL). XAMPP also works with equivalent paths/URLs.
- **Python 3.9+** (optional but recommended for report/data/validation scripts)
- Web browser (Chrome/Edge/Firefox)

## 2. Project location (Laragon)
Place/keep the project folder under Laragon's web root, for example:
```
C:\laragon\www\clone\INSY55-System\INSY55 project system
```
Default app URL (localhost path): `http://localhost/clone/INSY55-System/INSY55%20project%20system/`  
If you use a Laragon virtual host/pretty URL (e.g. `http://cursor-project-system.test`), set `base_url` in `config/app.php` accordingly (e.g. empty string or `/` depending on your host root). If links/assets break, fix `base_url` first.

## 3. Database setup (phpMyAdmin / MySQL)
1. In Laragon, click **Start All** (or ensure Apache/Nginx and MySQL are running).
2. Open your DB tool from Laragon: **phpMyAdmin** (`http://localhost/phpmyadmin`, if enabled) or **HeidiSQL** (Laragon → Database).
3. Create/import the database `come_n_meatshop_db`:
   - **Option A (recommended):** Import `database/come_n_meatshop_db_schema.sql`, then import `database/come_n_meatshop_db_sample_data.sql`.
   - **Option B:** Run schema first, then generate sample data via Python: `python python/generate_sample_data.py --output database/come_n_meatshop_db_sample_data_generated.sql` and import it.
4. Confirm tables exist (users, roles, products, sales, notifications, backups, etc.).

### Database configuration (PHP)
Edit `config/database.php` if your MySQL credentials differ (Laragon defaults usually match):
- `host` (default `127.0.0.1`)
- `port` (default `3306`)
- `dbname` (`come_n_meatshop_db`)
- `username` (default `root`)
- `password` (default empty on many Laragon installs; change if you set one)

## 4. Run the web application
1. Ensure Laragon services are running (Apache/Nginx + MySQL).
2. Open: `http://localhost/clone/INSY55-System/INSY55%20project%20system/login.php` (or your Laragon virtual host URL).
3. Log in with default accounts (from sample data):
   - **Admin:** `admin` / `Admin@123` (placeholder/demo)
   - **Staff:** `staff` / `Staff@123` (placeholder/demo)
   - **Staff:** `staff.maria` / `Staff@123` (placeholder/demo)
4. Change passwords after first login (Admin via Users/Profile; Staff via Profile). If pages/styles don't load correctly, confirm `base_url` in `config/app.php`.

## 5. Python setup (reports, sample data, backup validation)
Python scripts connect to the **same MySQL database** used by PHP.

1. Install Python 3.9+ and ensure `python`/`pip` are on PATH.
2. From the `python/` folder:
   ```powershell
   cd "C:\laragon\www\clone\INSY55-System\INSY55 project system\python"
   pip install -r requirements.txt
   ```
3. Configure database access via environment variables (defaults match typical Laragon root/no password):
   - `DB_HOST` (default `127.0.0.1`)
   - `DB_PORT` (default `3306`)
   - `DB_USER` (default `root`)
   - `DB_PASSWORD` (default empty)
   - `DB_NAME` (default `come_n_meatshop_db`)

   PowerShell example:
   ```powershell
   $env:DB_HOST="127.0.0.1"; $env:DB_USER="root"; $env:DB_PASSWORD=""; $env:DB_NAME="come_n_meatshop_db"
   ```

### Script usage
- **Sample data (SQL generator):**
  ```bash
  python generate_sample_data.py --output ..\database\come_n_meatshop_db_sample_data_generated.sql
  ```
- **Sales report (CSV/Excel/HTML):**
  ```bash
  python generate_sales_report.py --from 2026-01-01 --to 2026-12-31 --format csv --output sales_report.csv
  python generate_sales_report.py --from 2026-01-01 --to 2026-12-31 --format excel --output sales_report.xlsx
  python generate_sales_report.py --from 2026-01-01 --to 2026-12-31 --format html --output sales_report.html
  ```
  HTML output is printable/PDF-ready via browser Print → Save as PDF.
- **Inventory report:**
  ```bash
  python generate_inventory_report.py --format csv --output inventory_report.csv
  python generate_inventory_report.py --near-expiry-days 3 --format html --output inventory_report.html
  ```
- **Backup validator (before restore):**
  ```bash
  python backup_validator.py ..\uploads\backups\backup_xxx.sql
  ```
  Exit code `0` = valid, `1` = invalid.

## 6. Backups and restore
- Admin can create/download backups in **Backup & Restore**. The app tries `mysqldump` (common Laragon/XAMPP paths and PATH, including auto-detected Laragon MySQL/MariaDB versions) and falls back to a PHP SQL export if unavailable.
- Restore supports **full database** and **selected tables** (parsed/executed from the uploaded SQL file). Uploaded files are validated; a safety backup is created before restore when enabled.
- Backup files are stored locally in `uploads/backups/` and recorded in the `backups` table. Ensure the folder is writable by PHP/Apache.

## 7. Language support
- Use the header language selector (English / Filipino). Preference is stored in session; simple translations live in `assets/lang/en.php` and `assets/lang/fil.php`.

## 8. Troubleshooting
- **404 / wrong paths / broken CSS/links:** Check `base_url` in `config/app.php` matches how you access the app (for this workspace, `/clone/INSY55-System/INSY55%20project%20system/`; spaces encoded as `%20`).
- **Database connection error:** Verify MySQL is running in Laragon and credentials/port in `config/database.php` (try `127.0.0.1`, correct port if changed).
- **Blank page / PHP errors:** Enable error display in Laragon's PHP settings (`php.ini`) for local dev or check Apache/Nginx/PHP logs from Laragon.
- **Permission issues (uploads/backups):** Ensure `uploads/` subfolders are writable by the web server/PHP.
- **mysqldump not found:** PHP fallback export will be used automatically (backup still works).
- **Chart not showing:** Ensure internet access for CDN assets (Bootstrap/Chart.js) or replace with local assets if required.

## 9. Security reminders
- Use only in a local/trusted Laragon environment for coursework/demo.
- Change default passwords, replace placeholders, and review permissions before any real deployment.
- Backups may contain sensitive data; protect `uploads/backups/`.
