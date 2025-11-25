#!/usr/bin/env python3
import json
from pathlib import Path

# Assumption: 'id' should be set to post_nummer with spaces removed, e.g. "449 50" -> "44950".

ROOT = Path('/home/baba/WORK/ekoll.se/filament')
INPUT = ROOT / 'database' / 'import' / 'ratsit_person_postorter.json'
OUT_DIR = ROOT / 'database' / 'import'
NUM_PARTS = 10

with INPUT.open('r', encoding='utf-8') as f:
    data = json.load(f)

n = len(data)
per = (n + NUM_PARTS - 1) // NUM_PARTS

parts = []
for i in range(NUM_PARTS):
    start = i * per
    end = min(start + per, n)
    parts.append(data[start:end])

written = []
for i, part in enumerate(parts, start=1):
    # modify ids
    for obj in part:
        post_nummer = obj.get('post_nummer') or ''
        # remove spaces to form id
        new_id = post_nummer.replace(' ', '')
        # fallback: if empty, keep original id
        if new_id == '':
            new_id = str(obj.get('id'))
        obj['id'] = new_id
    out_path = OUT_DIR / f'ratsit_person_postorter_part_{i}.json'
    with out_path.open('w', encoding='utf-8') as f:
        json.dump(part, f, ensure_ascii=False, indent=2)
    written.append((out_path, len(part)))

print('Total entries:', n)
for p, cnt in written:
    print(p, cnt)

# also print a small sample of first 3 modified entries
print('\nSample modified entries from part 1:')
for obj in parts[0][:3]:
    print(obj)
