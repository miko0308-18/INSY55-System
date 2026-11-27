#!/usr/bin/env python3
"""
Generate sales report from MySQL for Come N' Meatshop.

Supports date-range filtering and outputs CSV, Excel (if openpyxl available),
and a printable HTML file (can be saved/printed to PDF via browser).

Usage:
  python generate_sales_report.py --from 2026-01-01 --to 2026-01-31 --format csv --output sales_report.csv
  python generate_sales_report.py --from 2026-01-01 --to 2026-01-31 --format excel --output sales_report.xlsx
  python generate_sales_report.py --from 2026-01-01 --to 2026-01-31 --format html --output sales_report.html
"""

from __future__ import annotations

import argparse
import csv
import os
from datetime import date
from pathlib import Path

try:
    import mysql.connector  # type: ignore
except Exception:  # pragma: no cover
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


def fetch_report(conn, date_from: str, date_to: str):
    cur = conn.cursor(dictionary=True)
    cur.execute(
        """
        SELECT COUNT(*) AS transactions,
               COALESCE(SUM(total_amount),0) AS total_sales,
               COALESCE(SUM(discount_amount),0) AS total_discounts,
               COALESCE(SUM(vat_amount),0) AS total_vat
        FROM sales
        WHERE DATE(sale_datetime) BETWEEN %s AND %s AND status IN ('Completed','Edited')
        """,
        (date_from, date_to),
    )
    summary = cur.fetchone() or {}

    cur.execute(
        """
        SELECT si.product_name_snapshot AS product_name,
               si.meat_type_snapshot AS meat_type,
               SUM(si.quantity) AS qty_sold,
               SUM(si.line_subtotal) AS sales_amount,
               COUNT(DISTINCT s.id) AS transactions
        FROM sale_items si
        INNER JOIN sales s ON s.id = si.sale_id
        WHERE DATE(s.sale_datetime) BETWEEN %s AND %s AND s.status IN ('Completed','Edited')
        GROUP BY si.product_name_snapshot, si.meat_type_snapshot
        ORDER BY qty_sold DESC
        """,
        (date_from, date_to),
    )
    by_product = cur.fetchall()

    cur.execute(
        """
        SELECT u.full_name AS staff_name, COUNT(*) AS transactions, COALESCE(SUM(s.total_amount),0) AS total_sales
        FROM sales s
        INNER JOIN users u ON u.id = s.cashier_user_id
        WHERE DATE(s.sale_datetime) BETWEEN %s AND %s AND s.status IN ('Completed','Edited')
        GROUP BY u.id, u.full_name
        ORDER BY total_sales DESC
        """,
        (date_from, date_to),
    )
    by_staff = cur.fetchall()

    cur.execute(
        """
        SELECT DATE(sale_datetime) AS day, COUNT(*) AS transactions, COALESCE(SUM(total_amount),0) AS total_sales
        FROM sales
        WHERE DATE(sale_datetime) BETWEEN %s AND %s AND status IN ('Completed','Edited')
        GROUP BY day ORDER BY day
        """,
        (date_from, date_to),
    )
    daily = cur.fetchall()
    cur.close()
    return summary, by_product, by_staff, daily


def write_csv(path: Path, date_from, date_to, summary, by_product, by_staff, daily):
    with path.open("w", newline="", encoding="utf-8") as f:
        w = csv.writer(f)
        w.writerow(["Sales Report", date_from, "to", date_to])
        w.writerow([])
        w.writerow(["Summary"])
        for k in ["transactions", "total_sales", "total_discounts", "total_vat"]:
            w.writerow([k, summary.get(k, 0)])
        w.writerow([])
        w.writerow(["Sales by Product"])
        w.writerow(["Product", "Meat Type", "Qty Sold", "Sales Amount", "Transactions"])
        for r in by_product:
            w.writerow([r.get("product_name"), r.get("meat_type"), r.get("qty_sold"), r.get("sales_amount"), r.get("transactions")])
        w.writerow([])
        w.writerow(["Sales by Staff"])
        w.writerow(["Staff", "Transactions", "Total Sales"])
        for r in by_staff:
            w.writerow([r.get("staff_name"), r.get("transactions"), r.get("total_sales")])
        w.writerow([])
        w.writerow(["Daily Sales"])
        w.writerow(["Date", "Transactions", "Total Sales"])
        for r in daily:
            w.writerow([r.get("day"), r.get("transactions"), r.get("total_sales")])


def write_excel(path: Path, date_from, date_to, summary, by_product, by_staff, daily):
    try:
        from openpyxl import Workbook  # type: ignore
    except Exception:
        csv_path = path.with_suffix(".csv")
        write_csv(csv_path, date_from, date_to, summary, by_product, by_staff, daily)
        print(f"openpyxl not installed; wrote CSV instead: {csv_path}")
        return
    wb = Workbook()
    ws = wb.active
    ws.title = "Summary"
    ws.append(["Sales Report", date_from, "to", date_to])
    ws.append([])
    ws.append(["Metric", "Value"])
    for k in ["transactions", "total_sales", "total_discounts", "total_vat"]:
        ws.append([k, summary.get(k, 0)])
    ws2 = wb.create_sheet("By Product")
    ws2.append(["Product", "Meat Type", "Qty Sold", "Sales Amount", "Transactions"])
    for r in by_product:
        ws2.append([r.get("product_name"), r.get("meat_type"), r.get("qty_sold"), r.get("sales_amount"), r.get("transactions")])
    ws3 = wb.create_sheet("By Staff")
    ws3.append(["Staff", "Transactions", "Total Sales"])
    for r in by_staff:
        ws3.append([r.get("staff_name"), r.get("transactions"), r.get("total_sales")])
    ws4 = wb.create_sheet("Daily")
    ws4.append(["Date", "Transactions", "Total Sales"])
    for r in daily:
        ws4.append([str(r.get("day")), r.get("transactions"), r.get("total_sales")])
    wb.save(path)


def write_html(path: Path, date_from, date_to, summary, by_product, by_staff, daily):
    def table(headers, rows):
        th = "".join(f"<th>{h}</th>" for h in headers)
        trs = []
        for row in rows:
            trs.append("<tr>" + "".join(f"<td>{row.get(h,'')}</td>" for h in headers) + "</tr>")
        return f"<table border='1' cellpadding='6' cellspacing='0'><thead><tr>{th}</tr></thead><tbody>{''.join(trs)}</tbody></table>"

    html = f"""<!DOCTYPE html>
<html><head><meta charset='utf-8'><title>Sales Report</title>
<style>body{{font-family:Arial,sans-serif;margin:24px}} table{{border-collapse:collapse;width:100%;margin-bottom:16px}} h1,h2{{margin:0 0 8px}}</style>
</head><body>
<h1>Sales Report</h1>
<p>{date_from} to {date_to}</p>
<p><em>Printable/PDF-ready: use browser Print → Save as PDF.</em></p>
<h2>Summary</h2>
<ul>
  <li>Transactions: {summary.get('transactions',0)}</li>
  <li>Total Sales: {summary.get('total_sales',0)}</li>
  <li>Total Discounts: {summary.get('total_discounts',0)}</li>
  <li>Total VAT: {summary.get('total_vat',0)}</li>
</ul>
<h2>Sales by Product</h2>
{table(['product_name','meat_type','qty_sold','sales_amount','transactions'], by_product)}
<h2>Sales by Staff</h2>
{table(['staff_name','transactions','total_sales'], by_staff)}
<h2>Daily Sales</h2>
{table(['day','transactions','total_sales'], daily)}
</body></html>"""
    path.write_text(html, encoding="utf-8")


def main() -> int:
    ap = argparse.ArgumentParser(description="Generate sales report for Come N' Meatshop")
    ap.add_argument("--from", dest="date_from", default=date.today().replace(day=1).isoformat(), help="Start date YYYY-MM-DD")
    ap.add_argument("--to", dest="date_to", default=date.today().isoformat(), help="End date YYYY-MM-DD")
    ap.add_argument("--format", choices=["csv", "excel", "html"], default="csv")
    ap.add_argument("--output", required=True, help="Output file path")
    args = ap.parse_args()

    conn = get_conn()
    try:
        summary, by_product, by_staff, daily = fetch_report(conn, args.date_from, args.date_to)
    finally:
        conn.close()

    out = Path(args.output)
    out.parent.mkdir(parents=True, exist_ok=True)
    if args.format == "csv":
        write_csv(out, args.date_from, args.date_to, summary, by_product, by_staff, daily)
    elif args.format == "excel":
        write_excel(out, args.date_from, args.date_to, summary, by_product, by_staff, daily)
    else:
        write_html(out, args.date_from, args.date_to, summary, by_product, by_staff, daily)
    print(f"Report written to: {out}")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
