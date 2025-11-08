# Ratsit Data SQLite Database

## Overview

The `hitta_ratsit.mjs` script now saves Ratsit data directly to the main SQLite database file instead of making API calls to the Laravel backend.

## Database Location

- **File**: `/home/baba/WORK/ekoll.se/filament/database/database.sqlite`
- **Table**: `ratsit_data`
- **Type**: SQLite 3 database with WAL (Write-Ahead Logging) mode enabled

## Database Schema

The `ratsit_data` table contains the following fields:

### Personal Information
- `id` - Primary key (auto-increment)
- `ps_personnummer` - Personal number
- `ps_alder` - Age
- `ps_fodelsedag` - Birth date
- `ps_kon` - Gender (M/F/O)
- `ps_civilstand` - Marital status
- `ps_fornamn` - First name
- `ps_efternamn` - Last name
- `ps_personnamn` - Full name
- `ps_telefon` - Phone numbers (JSON array)
- `ps_epost_adress` - Email addresses (JSON array)
- `ps_bolagsengagemang` - Company engagements (JSON array)

### Address Information
- `bo_gatuadress` - Street address
- `bo_postnummer` - Postal code
- `bo_postort` - City
- `bo_forsamling` - Parish
- `bo_kommun` - Municipality
- `bo_lan` - County

### Property Information
- `bo_agandeform` - Ownership form
- `bo_bostadstyp` - Property type
- `bo_boarea` - Living area
- `bo_byggar` - Build year
- `bo_fastighet` - Property designation
- `bo_personer` - People at address (JSON array)
- `bo_foretag` - Companies at address (JSON array)
- `bo_grannar` - Neighbors (JSON array)
- `bo_fordon` - Vehicles (JSON array)
- `bo_hundar` - Dogs (JSON array)
- `bo_longitude` - Longitude coordinate
- `bo_latitud` - Latitude coordinate

### System Fields
- `is_active` - Active status (1/0)
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

### Unique Constraint
Records are unique by combination of: `ps_personnummer`, `bo_gatuadress`, `bo_postnummer`

## Usage

### Running the Scraper
```bash
node hitta_ratsit.mjs "search query"
```

The script will:
1. Search Hitta.se for people matching the query
2. When a person with a phone number is found, trigger Ratsit scraping
3. Save Ratsit data directly to the SQLite database
4. Create/update records with upsert logic (INSERT OR REPLACE)

### Querying the Database

Using SQLite command line:
```bash
# Open the database
sqlite3 database/database.sqlite

# Count total records
SELECT COUNT(*) FROM ratsit_data;

# View recent records
SELECT ps_personnamn, bo_gatuadress, bo_postort, created_at 
FROM ratsit_data 
ORDER BY created_at DESC 
LIMIT 10;

# Search by name
SELECT * FROM ratsit_data WHERE ps_personnamn LIKE '%name%';

# Search by city
SELECT * FROM ratsit_data WHERE bo_postort = 'Stockholm';
```

Using Node.js:
```javascript
import Database from 'better-sqlite3';

const db = new Database('database/database.sqlite');
const rows = db.prepare('SELECT * FROM ratsit_data').all();
console.log(rows);
db.close();
```

## Features

### Automatic Table Creation

The table already exists in Laravel's database.sqlite file - no need to create it.

### Upsert Logic
- If a record with the same `ps_personnummer`, `bo_gatuadress`, and `bo_postnummer` exists, it will be updated
- Otherwise, a new record is created

### Performance
- WAL mode enabled for better concurrent access
- Foreign keys enabled
- Prepared statements for safe and fast inserts

## Viewing Database Contents

You can use any SQLite viewer:

- **Command line**: `sqlite3 database/database.sqlite`
- **GUI tools**: DB Browser for SQLite, DBeaver, etc.
- **VS Code extension**: SQLite Viewer

## Backup

To backup the database:

```bash
# Create a backup copy
cp database/database.sqlite database/database.backup.sqlite

# Or export to SQL
sqlite3 database/database.sqlite .dump > database_backup.sql
```

## Migration to Laravel

If you need to import this data into your Laravel MySQL database later:

```bash
# Export as CSV
sqlite3 -header -csv database/database.sqlite "SELECT * FROM ratsit_data;" > ratsit_export.csv

# Then import to MySQL using Laravel's database seeder or import tool
```
