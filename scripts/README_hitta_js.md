# Hitta.se Scraper - Node.js Version

This is a Node.js/JavaScript version of the `hitta.py` scraper that performs exactly the same tasks and saves the same results data.

## Features

- Scrapes person data from hitta.se search results
- Extracts: name, age, gender, address, postal code, city, phone numbers, map links, profile links
- Reveals hidden phone numbers by clicking buttons
- Saves results to CSV files (all results + results with phone numbers)
- Saves results to Laravel database via API
- Handles consent/cookie overlays
- Uses Playwright for browser automation

## Usage

```bash
# Basic usage
node scripts/hitta.mjs "search query"

# With options
node scripts/hitta.mjs "search query" --no-missing --no-db --api-url "http://localhost:8000" --api-token "your-token"
```

### Options

- `query` - Search query (required)
- `--no-missing` - Do not create separate CSV for missing phone numbers
- `--no-db` - Do not save to database
- `--api-url <url>` - Laravel API URL (default: http://localhost:8000)
- `--api-token <token>` - API authentication token

## Output Files

The script saves CSV files to `scripts/data/`:

- `hitta_se_{query}_alla_{total}.csv` - All results
- `hitta_se_{query}_visa_{count}.csv` - Results with phone numbers (unless `--no-missing`)

## CSV Format

The CSV files contain the same columns as the Python version:
- personnamn
- alder
- kon
- gatuadress
- postnummer
- postort
- telefon
- karta
- link

No headers are included in the CSV files (same as Python version).

## Requirements

- Node.js
- Playwright with system Chrome browser
- commander package

## Installation

```bash
# Install dependencies
npm install commander

# The script uses system Chrome browser at /usr/bin/google-chrome
# Make sure Chrome is installed on your system
```

## Differences from Python Version

- Uses Playwright instead of Selenium
- Written in JavaScript ES modules (.mjs)
- Same functionality and output format
- Same command-line interface
- Same data structure and CSV format

## Example

```bash
# Search for "John Doe" and save to CSV and database
node scripts/hitta.mjs "John Doe"

# Search but only save to CSV, no database
node scripts/hitta.mjs "John Doe" --no-db

# Search with custom API
node scripts/hitta.mjs "John Doe" --api-url "https://api.example.com" --api-token "abc123"
```

The Node.js version produces identical results to the Python version, making it a drop-in replacement.