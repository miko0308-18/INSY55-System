# Folder Structure

This document outlines the folder and file structure of the `INSY55 project system` project.

```text
INSY55 project system/
├── api/                         # API endpoints (currently empty)
├── assets/                      # Static assets
│   ├── css/
│   │   └── app.css              # Main stylesheet
│   ├── images/
│   │   ├── icons/               # Icon assets
│   │   └── placeholders/        # Placeholder images
│   │       ├── avatar-placeholder.svg
│   │       ├── logo-placeholder.svg
│   │       ├── product-placeholder.svg
│   │       └── receipt-placeholder.svg
│   ├── js/
│   │   ├── app.js               # Main client-side scripts
│   │   └── sales.js             # Sales-related scripts
│   └── lang/
│       ├── en.php               # English language strings
│       └── fil.php              # Filipino language strings
├── config/                      # Application configuration
│   ├── app.php
│   ├── constants.php
│   ├── database.php
│   └── session.php
├── database/                    # Database schema and sample data
│   ├── come_n_meatshop_db_sample_data.sql
│   └── come_n_meatshop_db_schema.sql
├── docs/                        # Project documentation
│   ├── MANUAL_TEST_CASES.md
│   └── PROJECT_DOCUMENTATION.md
├── includes/                    # Shared PHP includes/helpers
│   ├── auth_check.php           # Authentication guard
│   ├── bootstrap.php            # Application bootstrap
│   ├── csrf.php                 # CSRF protection helpers
│   ├── db.php                   # Database connection/helpers
│   ├── flash.php                # Flash message helpers
│   ├── footer.php               # Common footer template
│   ├── functions.php            # General helper functions
│   ├── header.php               # Common header template
│   ├── lang.php                 # Localization helpers
│   ├── notifications_helper.php # Notification helpers
│   └── sidebar.php              # Sidebar layout/template
├── modules/                     # Feature modules
│   ├── analytics/
│   │   └── index.php
│   ├── auth/                    # Authentication module (currently empty)
│   ├── backup_restore/
│   │   ├── actions.php
│   │   ├── backup_helper.php
│   │   ├── download.php
│   │   └── index.php
│   ├── dashboard/
│   │   └── index.php
│   ├── inventory/
│   │   ├── actions.php
│   │   ├── form.php
│   │   ├── index.php
│   │   └── stock.php
│   ├── notifications/
│   │   ├── actions.php
│   │   ├── index.php
│   │   └── open.php
│   ├── reports/
│   │   ├── inventory.php
│   │   └── sales.php
│   ├── sales/
│   │   ├── actions.php
│   │   ├── create.php
│   │   ├── history.php
│   │   ├── index.php
│   │   ├── receipt.php
│   │   ├── review.php
│   │   └── sales_helper.php
│   ├── settings/
│   │   ├── profile.php
│   │   └── set_language.php
│   └── users/
│       ├── actions.php
│       ├── form.php
│       └── index.php
├── python/                      # Python utility scripts
│   ├── backup_validator.py
│   ├── generate_inventory_report.py
│   ├── generate_sales_report.py
│   ├── generate_sample_data.py
│   └── requirements.txt
├── uploads/                     # User/system uploaded files
│   ├── backups/                 # Backup files
│   ├── products/                # Product-related uploads
│   └── receipts/                # Receipt files/uploads
├── index.php                    # Entry point; redirects based on login state
├── login.php                    # Login page
├── logout.php                   # Logout handler
├── README.md                    # Project overview/readme
└── SETUP_INSTRUCTIONS.md        # Setup instructions
```
