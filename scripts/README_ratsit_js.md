# Ratsit.se Scraper - Node.js Version

This is a Node.js/JavaScript version of `ratsit.py` scraper that performs exactly the same tasks and saves the same results data.

## Features

- Scrapes person data from ratsit.se search results
- Extracts detailed person information from individual detail pages
- Handles special personnummer format with XXXX placeholder
- Extracts phone numbers from tel: links
- Maps Swedish gender values to API format (M, F, O)
- Saves results to timestamped CSV files
- Saves results to Laravel database via API
- Uses Playwright with multiple browser fallback options

## Usage

```bash
# Basic usage
node scripts/ratsit.mjs "search query"

# With API options
node scripts/ratsit.mjs "Andersson" --api-url "http://localhost:8000" --api-token "your-token"
```

### Options

- `query` - Search query (person name, etc.) (required)
- `--api-url <url>` - Laravel API base URL
- `--api-token <token>` - Sanctum authentication token

## Output Files

The script saves CSV files to `scripts/data/` with timestamp:
- `ratsit_export_YYYYMMDD_HHMMSS.csv`

## Data Fields Extracted

The scraper extracts the following fields from each person's detail page:

### Personal Information (ps_ prefix)
- `ps_personnummer` - Personal identification number (with XXXX placeholder)
- `ps_alder` - Age
- `ps_fodelsedag` - Birth date
- `ps_kon` - Gender (M, F, O format)
- `ps_telefon` - Phone number(s) (array format)
- `ps_personnamn` - Full name
- `ps_fornamn` - First name
- `ps_efternamn` - Last name

### Address Information (bo_ prefix)
- `bo_gatuadress` - Street address
- `bo_postnummer` - Postal code
- `bo_postort` - City

## CSV Format

The CSV files contain all extracted fields:
- Dynamic column order based on available data
- Arrays converted to JSON strings for CSV compatibility
- No headers included (same as Python version)
- UTF-8 encoding

## Browser Compatibility

The script uses multiple fallback options for browser launching:
1. Google Chrome (if installed)
2. Playwright-managed Chromium
3. System Chrome executable
4. Default Playwright resolution

## Requirements

- Node.js
- Playwright
- commander package
- Google Chrome or Chromium browser

## Installation

```bash
# Install dependencies
npm install commander

# The script uses system Chrome browser at /usr/bin/google-chrome
# Make sure Chrome is installed on your system
```

## Differences from Python Version

- Uses Playwright instead of Python Playwright sync API
- Written in JavaScript ES modules (.mjs)
- Same functionality and output format
- Same command-line interface
- Same data structure and CSV format
- Same browser fallback logic

## Example

```bash
# Search for "Andersson" and save to CSV and database
node scripts/ratsit.mjs "Andersson" --api-url "https://api.example.com" --api-token "abc123"

# Search without database save
node scripts/ratsit.mjs "Andersson"
```

## Process Flow

1. **Search**: Searches ratsit.se for the query
2. **Extract Links**: Gets all person detail page URLs from search results
3. **Scrape Details**: Visits each person's page and extracts all available data
4. **Save to Database**: Sends data to Laravel API (if token provided)
5. **Save to CSV**: Creates timestamped CSV file with all collected data

## Special Features

### Personnummer Handling
- Extracts partial personnummer with XXXX placeholder
- Handles HTML parsing for masked personal numbers
- Falls back to text extraction if HTML parsing fails

### Gender Mapping
- Maps Swedish gender terms to standard format:
  - "man" → "M"
  - "kvinna/kvinno" → "F" 
  - "other/annat" → "O"

### Phone Number Extraction
- Extracts from `tel:` links in the page
- Returns as array format for API compatibility

The Node.js version produces identical results to Python version, making it a drop-in replacement.