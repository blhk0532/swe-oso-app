#!/usr/bin/env python3
"""Import ratsit totals into postnummer.sqlite

Adds columns `ratsit_personer_total` and `ratsit_foretag_total` to the
`post_nummer` table if missing, then updates each row by matching
`post_nummer` (both with and without spaces) against the two JSON files
in `database/import/`.

Usage: python3 scripts/import_ratsit_totals.py
"""
import json
import sqlite3
import sys
from pathlib import Path

BASE = Path(__file__).resolve().parents[1]
# Default DB at repo root; can be overridden by providing a DB path as the
# first command-line argument when invoking the script.
DB_PATH = BASE / "postnummer.sqlite"
PERSON_JSON = BASE / "database" / "import" / "ratsit_person_postorter.json"
FORETAG_JSON = BASE / "database" / "import" / "ratsit_foretag_postorter.json"


def ensure_columns(conn: sqlite3.Connection) -> None:
    cur = conn.cursor()
    cur.execute("PRAGMA table_info('post_nummer')")
    cols = {row[1] for row in cur.fetchall()}  # name is at index 1

    if 'ratsit_personer_total' not in cols:
        cur.execute("ALTER TABLE post_nummer ADD COLUMN ratsit_personer_total INTEGER DEFAULT 0")
        print("Added column ratsit_personer_total")
    else:
        print("Column ratsit_personer_total already exists")

    if 'ratsit_foretag_total' not in cols:
        cur.execute("ALTER TABLE post_nummer ADD COLUMN ratsit_foretag_total INTEGER DEFAULT 0")
        print("Added column ratsit_foretag_total")
    else:
        print("Column ratsit_foretag_total already exists")

    conn.commit()


def load_totals(path: Path, key_field: str, total_field_name: str) -> dict:
    """Load mapping normalized_post_nummer -> total

    Normalized form removes spaces so matching can be done both ways.
    If multiple entries exist for the same normalized key, the totals are summed.
    """
    mapping: dict[str, int] = {}
    if not path.exists():
        print(f"WARN: {path} not found. Skipping {total_field_name} import.")
        return mapping

    with path.open('r', encoding='utf-8') as fh:
        data = json.load(fh)


    for obj in data:
        pn = obj.get('post_nummer') or obj.get('postnummer') or ''
        norm = pn.replace(' ', '')

        # Try a list of likely fields to find the total value (foretag uses post_nummer_count)
        candidates = [total_field_name, 'post_nummer_count', 'postnummer_count', 'ratsit_personer_total', 'ratsit_foretag_total', 'count', 'total']
        total = 0
        for cand in candidates:
            if cand in obj and obj.get(cand) is not None:
                try:
                    total = int(obj.get(cand) or 0)
                except Exception:
                    total = 0
                break

        mapping[norm] = mapping.get(norm, 0) + total

    return mapping


def update_db(conn: sqlite3.Connection, person_map: dict, foretag_map: dict) -> tuple[int,int,int]:
    cur = conn.cursor()
    cur.execute("SELECT id, post_nummer FROM post_nummer")
    rows = cur.fetchall()

    updated_person = 0
    updated_foretag = 0
    no_match = 0

    for row in rows:
        row_id, pn = row
        if pn is None:
            no_match += 1
            continue

        norm = pn.replace(' ', '')

        person_total = person_map.get(norm)
        foretag_total = foretag_map.get(norm)

        if person_total is None and foretag_total is None:
            # try matching the other way (if DB has no spaces but JSON had spaces)
            alt = pn
            alt_norm = alt.replace(' ', '')
            person_total = person_map.get(alt_norm)
            foretag_total = foretag_map.get(alt_norm)

        if person_total is None and foretag_total is None:
            no_match += 1
            continue

        # Build update parts
        updates = []
        params = []
        if person_total is not None:
            updates.append("ratsit_personer_total = ?")
            params.append(person_total)
        if foretag_total is not None:
            updates.append("ratsit_foretag_total = ?")
            params.append(foretag_total)

        params.append(row_id)
        sql = f"UPDATE post_nummer SET {', '.join(updates)} WHERE id = ?"
        cur.execute(sql, params)

        if person_total is not None:
            updated_person += 1
        if foretag_total is not None:
            updated_foretag += 1

    conn.commit()
    return updated_person, updated_foretag, no_match


def main() -> None:
    # Allow overriding DB path: python3 import_ratsit_totals.py /path/to/postnummer.sqlite
    db_to_use = DB_PATH
    if len(sys.argv) > 1:
        db_to_use = Path(sys.argv[1])

    print("DB:", db_to_use)
    conn = sqlite3.connect(str(db_to_use))

    ensure_columns(conn)

    person_map = load_totals(PERSON_JSON, 'post_nummer', 'ratsit_personer_total')
    print(f"Loaded person totals for {len(person_map)} normalized post_nummer entries")

    foretag_map = load_totals(FORETAG_JSON, 'post_nummer', 'ratsit_foretag_total')
    print(f"Loaded foretag totals for {len(foretag_map)} normalized post_nummer entries")

    up_person, up_foretag, no_match = update_db(conn, person_map, foretag_map)

    print("Import complete:")
    print(f"  Updated person totals on {up_person} rows")
    print(f"  Updated foretag totals on {up_foretag} rows")
    print(f"  Rows with no matching ratsit postnummer: {no_match}")


if __name__ == '__main__':
    main()
