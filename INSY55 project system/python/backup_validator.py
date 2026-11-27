#!/usr/bin/env python3
"""
Validate a SQL backup file before restore for Come N' Meatshop.

Checks:
- file exists/readable and has .sql extension
- basic size sanity
- contains SQL statements (CREATE/INSERT/DROP)
- contains required application table markers
- flags obviously unsafe tokens (e.g. LOAD_FILE, INTO OUTFILE)

Usage:
  python backup_validator.py path/to/backup.sql
Exit code 0 = valid, 1 = invalid.
"""

from __future__ import annotations

import argparse
import sys
from pathlib import Path

REQUIRED_TABLES = [
    'users', 'roles', 'products', 'product_categories', 'stock_movements',
    'sales', 'sale_items', 'payments', 'notifications', 'user_notifications',
    'settings', 'backups', 'activity_logs', 'reports',
]
UNSAFE_TOKENS = ['LOAD_FILE', 'INTO OUTFILE', 'INTO DUMPFILE']


def validate(path: Path):
    errors = []
    warnings = []
    if not path.exists():
        return False, [f'File does not exist: {path}'], warnings
    if not path.is_file():
        return False, [f'Not a file: {path}'], warnings
    if path.suffix.lower() != '.sql':
        errors.append('Only .sql backup files are allowed.')
    size = path.stat().st_size
    if size <= 0:
        errors.append('Backup file is empty.')
    if size > 50 * 1024 * 1024:
        errors.append('Backup file is too large (max 50 MB).')

    try:
        content = path.read_text(encoding='utf-8', errors='replace')
    except Exception as e:
        return False, [f'Unable to read file: {e}'], warnings

    lower = content.lower()
    has_sql = ('create table' in lower) or ('insert into' in lower) or ('drop table' in lower)
    if not has_sql:
        errors.append('File does not look like a valid SQL backup (missing CREATE/INSERT/DROP).')

    for token in UNSAFE_TOKENS:
        if token.lower() in lower:
            errors.append(f'Backup contains unsafe SQL token: {token}')

    found = 0
    for table in REQUIRED_TABLES:
        if f'`{table}`' in lower or f' {table} ' in lower or f'\n{table}\n' in lower:
            found += 1
    if found < 5:
        errors.append(f'Backup does not appear to contain required application tables/structure (found {found} markers).')
    elif found < len(REQUIRED_TABLES):
        warnings.append(f'Only {found}/{len(REQUIRED_TABLES)} expected table markers found; backup may be partial.')

    if 'set foreign_key_checks' not in lower:
        warnings.append('No FOREIGN_KEY_CHECKS handling detected (may still be valid).')

    return len(errors) == 0, errors, warnings


def main() -> int:
    ap = argparse.ArgumentParser(description="Validate Come N' Meatshop SQL backup file")
    ap.add_argument('file', help='Path to .sql backup file')
    args = ap.parse_args()
    ok, errors, warnings = validate(Path(args.file))
    if ok:
        print('VALID: backup file passed basic validation checks.')
        for w in warnings:
            print(f'WARNING: {w}')
        return 0
    print('INVALID: backup file failed validation.')
    for e in errors:
        print(f'ERROR: {e}')
    for w in warnings:
        print(f'WARNING: {w}')
    return 1


if __name__ == '__main__':
    raise SystemExit(main())
