#!/usr/bin/env python3
"""CSV to SQLite importer

Usage:
  python3 scripts/csv_to_sqlite.py <input.csv> <output.sqlite> <table_name> [--overwrite]

Creates a SQLite database (or uses existing) and a table with columns inferred
from the CSV header. It will attempt simple type inference (INTEGER if all
values in column parse as int, otherwise TEXT).
"""
import csv
import sqlite3
import sys
from pathlib import Path


def infer_types(rows, headers):
    types = {h: 'INTEGER' for h in headers}
    for r in rows:
        for h in headers:
            v = r.get(h, '')
            if v == '' or v is None:
                continue
            try:
                int(v)
            except Exception:
                types[h] = 'TEXT'
    return types


def create_table(conn, table, headers, types, overwrite=False):
    cur = conn.cursor()
    if overwrite:
        cur.execute(f"DROP TABLE IF EXISTS {table}")
    cols_sql = ', '.join(f'"{h}" {types[h]}' for h in headers)
    sql = f'CREATE TABLE IF NOT EXISTS {table} ({cols_sql})'
    cur.execute(sql)
    conn.commit()


def insert_rows(conn, table, headers, rows):
    cur = conn.cursor()
    placeholders = ','.join('?' for _ in headers)
    sql = f'INSERT INTO {table} ({",".join([f"\"{h}\"" for h in headers])}) VALUES ({placeholders})'
    vals = [[(r.get(h) if r.get(h) != '' else None) for h in headers] for r in rows]
    cur.executemany(sql, vals)
    conn.commit()


def main():
    if len(sys.argv) < 4:
        print(__doc__)
        sys.exit(2)

    input_csv = Path(sys.argv[1])
    out_sqlite = Path(sys.argv[2])
    table = sys.argv[3]
    overwrite = '--overwrite' in sys.argv

    if not input_csv.exists():
        print(f'Input CSV not found: {input_csv}')
        sys.exit(1)

    with input_csv.open(encoding='utf-8') as fh:
        reader = csv.DictReader(fh)
        rows = list(reader)
        headers = reader.fieldnames or []

    if not headers:
        print('No headers found in CSV')
        sys.exit(1)

    types = infer_types(rows, headers)

    # ensure output dir
    out_sqlite.parent.mkdir(parents=True, exist_ok=True)

    conn = sqlite3.connect(str(out_sqlite))
    create_table(conn, table, headers, types, overwrite=overwrite)
    insert_rows(conn, table, headers, rows)
    cur = conn.cursor()
    cur.execute(f'SELECT COUNT(*) FROM {table}')
    cnt = cur.fetchone()[0]
    print(f'Wrote {cnt} rows to {out_sqlite} table {table}')
    conn.close()


if __name__ == '__main__':
    main()
