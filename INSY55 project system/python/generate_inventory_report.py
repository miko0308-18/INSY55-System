#!/usr/bin/env python3
"""
Generate inventory report from MySQL for Come N' Meatshop.

Includes low-stock, out-of-stock, near-expiry, and expired products, plus
stock movement history. Supports CSV, Excel (if openpyxl available), and
printable HTML (browser Print → Save as PDF).

Usage:
  python generate_inventory_report.py --format csv --output inventory_report.csv
  python generate_inventory_report.py --near-expiry-days 3 --format html --output inventory_report.html
"""

from __future__ import annotations

import argparse
import csv
import os
from datetime import date
from pathlib import Path

try:
    import mysql.connector  # type: ignore
except Exception:
    mysql = None  # type: ignore
    mysql_connector = None
else:
    mysql_connector = mysql.connector

DB_CONFIG = {
    "host": os.getenv("DB_HOST", "127.0.0.1"),
    "port": int(os.getenv("DB_PORT", "3306")),
    "user": os.getenv("DB_USER", "root"),
    "password": os.getenv("DB_PASSWORD", ""),
    "database": os.getenv("DB_NAME", "come_n_meatshop_db"),
}


def get_conn():
    if mysql_connector is None:
        raise RuntimeError("mysql-connector-python is not installed. Run: pip install -r requirements.txt")
    return mysql_connector.connect(**DB_CONFIG)


def fetch(conn, near_days: int):
    cur = conn.cursor(dictionary=True)
    cur.execute(
        """
        SELECT p.*, c.name AS category_name
        FROM products p LEFT JOIN product_categories c ON c.id = p.category_id
        ORDER BY p.status, p.name
        """
    )
    products = cur.fetchall()
    cur.execute(
        """
        SELECT sm.*, p.name AS product_name, u.full_name AS performed_by_name
        FROM stock_movements sm
        INNER JOIN products p ON p.id = sm.product_id
        LEFT JOIN users u ON u.id = sm.performed_by
        ORDER BY sm.created_at DESC
        LIMIT 300
        """
    )
    movements = cur.fetchall()
    cur.close()

    today = date.today()
    low = [p for p in products if p.get('status') == 'Active' and float(p.get('stock_quantity') or 0) > 0 and float(p.get('stock_quantity') or 0) <= float(p.get('min_stock_level') or 0)]
    out = [p for p in products if p.get('status') == 'Active' and float(p.get('stock_quantity') or 0) <= 0]
    expired = []
    near = []
    for p in products:
        if p.get('status') != 'Active' or not p.get('expiration_date'):
            continue
        exp = p['expiration_date']
        if hasattr(exp, 'date'):
            exp = exp.date()
        if exp < today:
            expired.append(p)
        elif exp <= today.fromordinal(today.toordinal() + near_days):
            near.append(p)
    return products, movements, low, out, near, expired


def write_csv(path, products, movements, low, out, near, expired):
    with path.open("w", newline="", encoding="utf-8") as f:
        w = csv.writer(f)
        w.writerow(["Inventory Report", str(date.today())])
        w.writerow(["Summary", f"Low:{len(low)}", f"Out:{len(out)}", f"Near expiry:{len(near)}", f"Expired:{len(expired)}"])
        w.writerow([])
        w.writerow(["Current Inventory"])
        w.writerow(["Name","Category","Meat Type","Price","Unit","Stock","Min","Expiration","Status"])
        for p in products:
            w.writerow([p.get('name'), p.get('category_name'), p.get('meat_type'), p.get('price'), p.get('unit'), p.get('stock_quantity'), p.get('min_stock_level'), p.get('expiration_date'), p.get('status')])
        w.writerow([])
        w.writerow(["Stock Movements (latest 300)"])
        w.writerow(["Date","Product","Type","Change","Before","After","Reference","Remarks","By"])
        for m in movements:
            ref = f"{m.get('reference_type') or ''}:{m.get('reference_id') or ''}"
            w.writerow([m.get('created_at'), m.get('product_name'), m.get('movement_type'), m.get('quantity_change'), m.get('quantity_before'), m.get('quantity_after'), ref, m.get('remarks'), m.get('performed_by_name')])


def write_excel(path, products, movements, low, out, near, expired):
    try:
        from openpyxl import Workbook  # type: ignore
    except Exception:
        csv_path = path.with_suffix('.csv')
        write_csv(csv_path, products, movements, low, out, near, expired)
        print(f"openpyxl not installed; wrote CSV instead: {csv_path}")
        return
    wb = Workbook()
    ws = wb.active
    ws.title = "Inventory"
    ws.append(["Name","Category","Meat Type","Price","Unit","Stock","Min","Expiration","Status"])
    for p in products:
        ws.append([p.get('name'), p.get('category_name'), p.get('meat_type'), p.get('price'), p.get('unit'), p.get('stock_quantity'), p.get('min_stock_level'), str(p.get('expiration_date') or ''), p.get('status')])
    ws2 = wb.create_sheet("Alerts")
    ws2.append(["Type","Name","Stock","Min","Expiration"])
    for p in low: ws2.append(["Low stock", p.get('name'), p.get('stock_quantity'), p.get('min_stock_level'), str(p.get('expiration_date') or '')])
    for p in out: ws2.append(["Out of stock", p.get('name'), p.get('stock_quantity'), p.get('min_stock_level'), str(p.get('expiration_date') or '')])
    for p in near: ws2.append(["Near expiry", p.get('name'), p.get('stock_quantity'), p.get('min_stock_level'), str(p.get('expiration_date') or '')])
    for p in expired: ws2.append(["Expired", p.get('name'), p.get('stock_quantity'), p.get('min_stock_level'), str(p.get('expiration_date') or '')])
    ws3 = wb.create_sheet("Movements")
    ws3.append(["Date","Product","Type","Change","Before","After","Reference","Remarks","By"])
    for m in movements:
        ws3.append([str(m.get('created_at')), m.get('product_name'), m.get('movement_type'), m.get('quantity_change'), m.get('quantity_before'), m.get('quantity_after'), f"{m.get('reference_type') or ''}:{m.get('reference_id') or ''}", m.get('remarks'), m.get('performed_by_name')])
    wb.save(path)


def write_html(path, products, movements, low, out, near, expired):
    def rows(items, cols):
        return "".join("<tr>" + "".join(f"<td>{i.get(c,'')}</td>" for c in cols) + "</tr>" for i in items)
    cols_p = ["name","category_name","meat_type","price","unit","stock_quantity","min_stock_level","expiration_date","status"]
    html = f"""<!DOCTYPE html><html><head><meta charset='utf-8'><title>Inventory Report</title>
<style>body{{font-family:Arial,sans-serif;margin:24px}} table{{border-collapse:collapse;width:100%;margin-bottom:16px}} th,td{{border:1px solid #ccc;padding:6px}}</style>
</head><body>
<h1>Inventory Report</h1><p>{date.today()}</p>
<p><em>Printable/PDF-ready: browser Print → Save as PDF.</em></p>
<h2>Summary</h2><ul><li>Low stock: {len(low)}</li><li>Out of stock: {len(out)}</li><li>Near expiry: {len(near)}</li><li>Expired: {len(expired)}</li></ul>
<h2>Current Inventory</h2><table><thead><tr>{''.join(f'<th>{c}</th>' for c in cols_p)}</tr></thead><tbody>{rows(products, cols_p)}</tbody></table>
<h2>Stock Movements (latest 300)</h2><table><thead><tr><th>created_at</th><th>product_name</th><th>movement_type</th><th>quantity_change</th><th>quantity_before</th><th>quantity_after</th><th>remarks</th><th>performed_by_name</th></tr></thead><tbody>{rows(movements, ['created_at','product_name','movement_type','quantity_change','quantity_before','quantity_after','remarks','performed_by_name'])}</tbody></table>
</body></html>"""
    path.write_text(html, encoding='utf-8')


def main() -> int:
    ap = argparse.ArgumentParser()
    ap.add_argument('--format', choices=['csv','excel','html'], default='csv')
    ap.add_argument('--output', required=True)
    ap.add_argument('--near-expiry-days', type=int, default=3)
    args = ap.parse_args()
    conn = get_conn()
    try:
        products, movements, low, out, near, expired = fetch(conn, args.near_expiry_days)
    finally:
        conn.close()
    path = Path(args.output)
    path.parent.mkdir(parents=True, exist_ok=True)
    if args.format == 'csv': write_csv(path, products, movements, low, out, near, expired)
    elif args.format == 'excel': write_excel(path, products, movements, low, out, near, expired)
    else: write_html(path, products, movements, low, out, near, expired)
    print(f'Report written to: {path}')
    return 0


if __name__ == '__main__':
    raise SystemExit(main())
