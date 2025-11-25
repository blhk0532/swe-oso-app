#!/usr/bin/env python3
"""Update ratsit_foretag_total in post_nummer tables from ratsit_foretag_postorter.json

This script:
 - loads database/import/ratsit_foretag_postorter.json
 - builds a mapping normalized_postnummer -> summed ratsit_foretag_total
 - ensures the target sqlite files have a ratsit_foretag_total column
 - updates rows in table `post_nummer` where REPLACE(post_nummer,' ','') matches
 - reports counts updated and unmatched keys

Run: python3 scripts/update_ratsit_foretag_totals.py [--db dbpath]
If --db is not provided, it will update both database/postnummer.sqlite and postnummer.sqlite in repo root if present.
"""
import json
import sqlite3
from pathlib import Path
import argparse

ROOT = Path('/home/baba/WORK/ekoll.se/filament')
JSON_PATH = ROOT / 'database' / 'import' / 'ratsit_foretag_postorter.json'

def load_totals(json_path: Path):
    data = json.loads(json_path.read_text(encoding='utf-8'))
    totals = {}
    for obj in data:
        pn = (obj.get('post_nummer') or '').replace(' ', '')
        try:
            v = int(obj.get('ratsit_foretag_total') or obj.get('post_nummer_count') or 0)
        except Exception:
            try:
                v = int(float(obj.get('ratsit_foretag_total') or 0))
            except Exception:
                v = 0
        if not pn:
            continue
        totals[pn] = totals.get(pn, 0) + v
    return totals

def ensure_column(conn: sqlite3.Connection, col: str):
    cur = conn.cursor()
    cur.execute("PRAGMA table_info('post_nummer')")
    cols = [r[1] for r in cur.fetchall()]
    if col not in cols:
        cur.execute(f"ALTER TABLE post_nummer ADD COLUMN {col} INTEGER DEFAULT 0")
        conn.commit()

def update_db(db_path: Path, totals: dict):
    if not db_path.exists():
        print(f"DB not found: {db_path}")
        return {'db': str(db_path), 'updated': 0, 'matched_keys': 0, 'missing_keys': len(totals)}

    conn = sqlite3.connect(str(db_path))
    cur = conn.cursor()

    # verify table exists
    cur.execute("SELECT name FROM sqlite_master WHERE type='table' AND name='post_nummer'")
    if not cur.fetchone():
        print(f"No table `post_nummer` in {db_path}")
        conn.close()
        return {'db': str(db_path), 'updated': 0, 'matched_keys': 0, 'missing_keys': len(totals)}

    ensure_column(conn, 'ratsit_foretag_total')

    updated = 0
    matched_keys = 0
    for pn, total in totals.items():
        cur.execute("UPDATE post_nummer SET ratsit_foretag_total = ? WHERE REPLACE(post_nummer, ' ', '') = ?", (total, pn))
        updated += cur.rowcount
        if cur.rowcount > 0:
            matched_keys += 1
    conn.commit()

    # compute unmatched count
    missing_keys = len(totals) - matched_keys

    # report
    print(f"DB: {db_path} - updated rows: {updated}, matched post_nummer keys: {matched_keys}, missing keys: {missing_keys}")
    conn.close()
    return {'db': str(db_path), 'updated': updated, 'matched_keys': matched_keys, 'missing_keys': missing_keys}

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--db', help='Optional sqlite DB to update (absolute or relative path)')
    args = parser.parse_args()

    if not JSON_PATH.exists():
        print(f"Source JSON not found: {JSON_PATH}")
        return

    totals = load_totals(JSON_PATH)
    print(f"Loaded {len(totals)} normalized post_nummer totals from {JSON_PATH}")

    targets = []
    if args.db:
        targets = [Path(args.db)]
    else:
        # default targets
        targets = [ROOT / 'database' / 'postnummer.sqlite', ROOT / 'postnummer.sqlite']

    results = []
    for t in targets:
        results.append(update_db(t, totals))

    print('\nSummary:')
    for r in results:
        print(r)

if __name__ == '__main__':
    main()
