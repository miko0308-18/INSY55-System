#!/usr/bin/env python3
"""
Generate realistic sample data for Come N' Meatshop (SQL insert statements).

Outputs a ready-to-import SQL file with placeholder images/business info.
Uses the same schema as database/come_n_meatshop_db_schema.sql.

Usage:
  python generate_sample_data.py --output ../database/come_n_meatshop_db_sample_data_generated.sql
"""

from __future__ import annotations

import argparse
import datetime as dt
import random
from pathlib import Path


def esc(value):
    if value is None:
        return "NULL"
    if isinstance(value, (int, float)):
        return str(value)
    s = str(value).replace("\\", "\\\\").replace("'", "''")
    return f"'{s}'"


def main() -> int:
    parser = argparse.ArgumentParser(description="Generate sample SQL data for Come N' Meatshop")
    parser.add_argument("--output", default="sample_data_generated.sql", help="Output .sql file path")
    args = parser.parse_args()

    random.seed(42)
    today = dt.date.today()

    categories = [
        (1, "Beef", "Beef cuts and products"),
        (2, "Pork", "Pork cuts and products"),
        (3, "Chicken", "Chicken cuts and products"),
        (4, "Processed", "Processed meat products"),
    ]
    products = [
        (1, "Beef Ribeye Steak", 1, "Beef", "Premium ribeye steak cut [Placeholder].", 520.00, "kg", 18.5, 10, today + dt.timedelta(days=5), "assets/images/placeholders/product-placeholder.svg", None, "[Supplier Placeholder]", "Active"),
        (2, "Pork Belly Slab", 2, "Pork", "Fresh pork belly slab [Placeholder].", 340.00, "kg", 9.0, 10, today + dt.timedelta(days=2), "assets/images/placeholders/product-placeholder.svg", None, "[Supplier Placeholder]", "Active"),
        (3, "Whole Chicken", 3, "Chicken", "Dressed whole chicken [Placeholder].", 210.00, "piece", 25.0, 10, today + dt.timedelta(days=4), "assets/images/placeholders/product-placeholder.svg", None, "[Supplier Placeholder]", "Active"),
        (4, "Chicken Breast Fillet", 3, "Chicken", "Skinless chicken breast fillet [Placeholder].", 280.00, "kg", 6.0, 10, today - dt.timedelta(days=1), "assets/images/placeholders/product-placeholder.svg", None, "[Supplier Placeholder]", "Active"),
        (5, "Ground Beef", 1, "Beef", "Fresh ground beef [Placeholder].", 360.00, "kg", 0.0, 10, today + dt.timedelta(days=1), "assets/images/placeholders/product-placeholder.svg", None, "[Supplier Placeholder]", "Active"),
        (6, "Pork Tocino Pack", 4, "Processed", "Sweet cured pork tocino pack [Placeholder].", 165.00, "pack", 30.0, 10, today + dt.timedelta(days=20), "assets/images/placeholders/product-placeholder.svg", None, "[Supplier Placeholder]", "Active"),
    ]

    users = [
        (1, 1, "admin", "admin@comenmeatshop.local", "$2y$12$Rch56vMYC1jTuH7qAuoL..oJjcbh4L7el6atykzfuR4N5m5aO7A5i", "Shop Owner [Placeholder]", "[Admin Phone Placeholder]", "assets/images/placeholders/avatar-placeholder.svg", "Active"),
        (2, 2, "staff1", "staff1@comenmeatshop.local", "$2y$12$F42fEjYY4pBG5w4eDp5z6ekrC6evWykRWtoUEO9wzkuIHQG3fTX3S", "Staff One [Placeholder]", "[Staff Phone Placeholder]", "assets/images/placeholders/avatar-placeholder.svg", "Active"),
        (3, 2, "staff2", "staff2@comenmeatshop.local", "$2y$12$F42fEjYY4pBG5w4eDp5z6ekrC6evWykRWtoUEO9wzkuIHQG3fTX3S", "Staff Two [Placeholder]", "[Staff Phone Placeholder]", "assets/images/placeholders/avatar-placeholder.svg", "Active"),
    ]

    out = []
    out.append("-- Generated sample data for Come N' Meatshop [Business Placeholder]")
    out.append(f"-- Generated: {dt.datetime.now().isoformat(timespec='seconds')}")
    out.append("SET FOREIGN_KEY_CHECKS=0;")
    out.append("")

    out.append("INSERT INTO product_categories (id, name, description, status, created_by, updated_by) VALUES")
    out.append(",\n".join([f"({c[0]},{esc(c[1])},{esc(c[2])},'Active',1,1)" for c in categories]) + ";")
    out.append("")

    out.append("INSERT INTO products (id, name, category_id, meat_type, description, price, unit, stock_quantity, min_stock_level, expiration_date, image_path, barcode, supplier, status, created_by, updated_by) VALUES")
    prod_rows = []
    for p in products:
        prod_rows.append(
            f"({p[0]},{esc(p[1])},{p[2]},{esc(p[3])},{esc(p[4])},{p[5]},{esc(p[6])},{p[7]},{p[8]},{esc(p[9].isoformat())},{esc(p[10])},{esc(p[11])},{esc(p[12])},{esc(p[13])},1,1)"
        )
    out.append(",\n".join(prod_rows) + ";")
    out.append("")

    out.append("INSERT INTO users (id, role_id, username, email, password_hash, full_name, phone, avatar_path, status) VALUES")
    out.append(",\n".join([
        f"({u[0]},{u[1]},{esc(u[2])},{esc(u[3])},{esc(u[4])},{esc(u[5])},{esc(u[6])},{esc(u[7])},{esc(u[8])})" for u in users
    ]) + ";")
    out.append("")

    # Stock movements, sales, sale_items, payments, notifications, reports (simple realistic samples)
    out.append("INSERT INTO stock_movements (id, product_id, movement_type, quantity_change, quantity_before, quantity_after, reference_type, remarks, performed_by) VALUES")
    out.append("(1,1,'initial_stock',18.500,0.000,18.500,'product','Initial stock [Placeholder]',1),")
    out.append("(2,2,'initial_stock',9.000,0.000,9.000,'product','Initial stock [Placeholder]',1),")
    out.append("(3,3,'initial_stock',25.000,0.000,25.000,'product','Initial stock [Placeholder]',1),")
    out.append("(4,4,'initial_stock',6.000,0.000,6.000,'product','Initial stock [Placeholder]',1),")
    out.append("(5,5,'initial_stock',0.000,0.000,0.000,'product','Initial stock [Placeholder]',1),")
    out.append("(6,6,'initial_stock',30.000,0.000,30.000,'product','Initial stock [Placeholder]',1);")
    out.append("")

    now = dt.datetime.now()
    out.append("INSERT INTO sales (id, receipt_number, sale_datetime, cashier_user_id, customer_name, subtotal, discount_percent, discount_amount, vat_rate_percent, vat_amount, total_amount, payment_method, amount_paid, change_due, status, review_confirmed_at, created_by, updated_by) VALUES")
    out.append(f"(1,'CNM-{now.strftime('%Y%m%d')}-0001',{esc(now.strftime('%Y-%m-%d 09:15:00'))},2,'[Customer Placeholder]',860.00,0.00,0.00,12.00,103.20,963.20,'Cash',1000.00,36.80,'Completed',NOW(),2,2);")
    out.append("")
    out.append("INSERT INTO sale_items (id, sale_id, product_id, product_name_snapshot, meat_type_snapshot, unit_snapshot, unit_price, quantity, line_subtotal) VALUES")
    out.append("(1,1,1,'Beef Ribeye Steak','Beef','kg',520.00,1.000,520.00),")
    out.append("(2,1,2,'Pork Belly Slab','Pork','kg',340.00,1.000,340.00);")
    out.append("")
    out.append("INSERT INTO payments (id, sale_id, payment_method, amount_tendered, change_amount, payment_status, paid_at) VALUES")
    out.append("(1,1,'Cash',1000.00,36.80,'Paid',NOW());")
    out.append("")

    out.append("INSERT INTO notifications (id, notification_type, title, message, severity, module, related_entity_type, related_entity_id, target_role, action_url, created_by) VALUES")
    out.append("(1,'low_stock','Low stock: Pork Belly Slab','Pork Belly Slab has low stock [Placeholder].','warning','inventory','product','2','All','modules/inventory/index.php',1),")
    out.append("(2,'sale_recorded','New sale recorded','A sample sale was recorded [Placeholder].','success','sales','sale','1','All','modules/sales/receipt.php?id=1',2);")
    out.append("")
    out.append("INSERT INTO user_notifications (notification_id, user_id, is_read) VALUES (1,1,0),(1,2,0),(2,1,0),(2,2,0);")
    out.append("")
    out.append("INSERT INTO reports (id, report_type, report_name, file_path, file_format, parameters_json, generated_by) VALUES")
    out.append("(1,'sales','Sample Sales Report [Placeholder]',NULL,'csv',JSON_OBJECT('from',DATE_FORMAT(CURDATE(),'%Y-%m-01'),'to',CURDATE()),1);")
    out.append("")
    out.append("SET FOREIGN_KEY_CHECKS=1;")
    out.append("")

    output_path = Path(args.output)
    output_path.parent.mkdir(parents=True, exist_ok=True)
    output_path.write_text("\n".join(out), encoding="utf-8")
    print(f"Sample SQL written to: {output_path}")
    print("Note: Replace placeholder business details/images and adjust hashes/data as needed.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
