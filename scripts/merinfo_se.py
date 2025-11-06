#!/usr/bin/env python3
"""
Merinfo.se scraper script
Scrapes person data from merinfo.se and saves to database and CSV

IMPORTANT: Merinfo.se uses Cloudflare protection which may block automated scraping.
If the script fails with "Cloudflare challenge did not resolve", try:
1. Running with --no-headless flag to manually solve the challenge
2. Using a VPN or different IP address
3. Waiting a few minutes between runs
4. Running from a residential IP (not datacenter/VPS)

Usage:
    ./merinfo_se.py "search query"
    ./merinfo_se.py "733 32 Sala" --api-token "your-token"
    ./merinfo_se.py "733 32 Sala" --no-headless
"""

import argparse
import csv
import json
import os
import re
import random
from datetime import datetime
from pathlib import Path
from typing import Optional
from urllib.parse import quote

import requests
from playwright.sync_api import sync_playwright, TimeoutError as PlaywrightTimeoutError
from playwright_stealth import Stealth


class MerinfoScraper:
    """Scraper for merinfo.se person search results"""

    def __init__(self, api_url: Optional[str] = None, api_token: Optional[str] = None):
        """
        Initialize the scraper

        Args:
            api_url: Base URL for the Laravel API (e.g., http://localhost:8000)
            api_token: Sanctum authentication token (optional)
        """
        self.api_url = api_url or os.getenv('LARAVEL_API_URL', 'http://localhost:8000')
        self.api_token = api_token or os.getenv('LARAVEL_API_TOKEN')
        self.data_dir = Path(__file__).parent / 'data'
        self.data_dir.mkdir(exist_ok=True)
        self.results = []

    def random_delay(self, min_ms: int = 500, max_ms: int = 2000):
        """Add random human-like delay"""
        delay = random.randint(min_ms, max_ms)
        return delay

    def human_like_mouse_move(self, page):
        """Simulate human-like mouse movements"""
        try:
            # Move mouse to random positions to simulate human behavior
            for _ in range(random.randint(2, 4)):
                x = random.randint(100, 800)
                y = random.randint(100, 600)
                page.mouse.move(x, y)
                page.wait_for_timeout(random.randint(100, 300))
        except Exception:
            pass  # If this fails, it's not critical

    def scrape_person_card(self, card_element) -> dict:
        """Scrape a single person's card from search results"""
        try:
            data = {}
            
            # Extract ps_personnamn from the link
            try:
                name_link = card_element.query_selector('a[href*="/person/"]')
                if name_link:
                    name_text = name_link.inner_text().strip()
                    # Clean up the name (remove extra whitespace)
                    name_text = ' '.join(name_text.split())
                    data['ps_personnamn'] = name_text
            except Exception as e:
                print(f"    Error extracting name: {e}")
            
            # Extract ps_personnummer
            try:
                # Look for the pattern: <span>YYYYMMDD-</span>
                personnummer_span = card_element.query_selector('span')
                if personnummer_span:
                    # Get all text content from the parent to catch both parts
                    parent_div = card_element.query_selector('div.mi-my-1')
                    if parent_div:
                        full_text = parent_div.inner_text()
                        # Match pattern like "19410902-XXXX" or "19410902- XXXX"
                        match = re.search(r'(\d{8}-)\s*(\w+)', full_text)
                        if match:
                            data['ps_personnummer'] = match.group(1) + match.group(2)
            except Exception as e:
                print(f"    Error extracting personnummer: {e}")
            
            # Extract address information
            try:
                address_element = card_element.query_selector('address')
                if address_element:
                    address_spans = address_element.query_selector_all('span')
                    
                    if len(address_spans) >= 1:
                        # First span is bo_gatuadress
                        data['bo_gatuadress'] = address_spans[0].inner_text().strip()
                    
                    if len(address_spans) >= 2:
                        # Second span contains postnummer and postort
                        full_location = address_spans[1].inner_text().strip()
                        # Extract first 6 chars (numbers and space) for postnummer
                        if len(full_location) >= 6:
                            data['bo_postnummer'] = full_location[:6].strip()
                            # Everything after first 6 chars is postort
                            data['bo_postort'] = full_location[6:].strip()
            except Exception as e:
                print(f"    Error extracting address: {e}")
            
            # Extract ps_telefon
            try:
                phone_link = card_element.query_selector('a[href^="tel:"]')
                if phone_link:
                    # Get the text content (the formatted phone number)
                    phone_text = phone_link.inner_text().strip()
                    # Remove any extra whitespace
                    phone_text = ' '.join(phone_text.split())
                    if phone_text:
                        data['ps_telefon'] = [phone_text]
            except Exception as e:
                print(f"    Error extracting telefon: {e}")
            
            # Clean up None values and empty strings
            data = {k: v for k, v in data.items() if v is not None and v != ''}
            
            return data
            
        except Exception as e:
            print(f"    Error scraping card: {e}")
            return {}

    def handle_cloudflare_challenge(self, page) -> bool:
        """Handle Cloudflare human verification challenge"""
        try:
            # Simulate human-like mouse movement
            self.human_like_mouse_move(page)
            
            page.wait_for_timeout(self.random_delay(2000, 4000))  # Initial wait for page to stabilize
            
            # Check if we're on a Cloudflare challenge page
            try:
                title = page.title()
            except Exception:
                # Page might be navigating, wait and try again
                page.wait_for_timeout(2000)
                title = page.title()
            
            if any(phrase in title for phrase in ["Just a moment", "Checking your browser", "Vänta", "Kontrollerar"]):
                print(f"  Detected Cloudflare challenge (title: {title}), waiting for resolution...")
                
                # Add more human-like mouse movements
                self.human_like_mouse_move(page)
                
                # Wait for the page to load after challenge (up to 50 seconds with human-like delays)
                max_wait = 50
                for i in range(max_wait):
                    try:
                        page.wait_for_timeout(self.random_delay(800, 1500))
                        current_title = page.title()
                        
                        if not any(phrase in current_title for phrase in ["Just a moment", "Checking your browser", "Vänta", "Kontrollerar"]):
                            print(f"  ✓ Cloudflare challenge passed after ~{i+1} seconds")
                            page.wait_for_timeout(self.random_delay(1500, 3000))  # Additional stabilization wait
                            self.human_like_mouse_move(page)
                            return True
                    except Exception:
                        # Page might still be navigating
                        continue
                
                print("  ✗ Cloudflare challenge did not resolve within timeout")
                return False
            
            return True  # No challenge detected
            
        except Exception as e:
            print(f"  Error handling Cloudflare challenge: {e}")
            return False

    def get_search_results(self, page, search_url: str) -> list:
        """Get all person data from search results page"""
        print(f"Searching: {search_url}")
        
        try:
            page.goto(search_url, wait_until='domcontentloaded', timeout=30000)
            page.wait_for_timeout(2000)  # Wait for initial load
            
            # Handle Cloudflare challenge if present
            if not self.handle_cloudflare_challenge(page):
                print("Failed to pass Cloudflare challenge")
                return []
            
            # Additional wait after challenge
            page.wait_for_timeout(2000)
            
            # Debug: Print page content
            print(f"Debug: Page title: {page.title()}")
            
            # Find the result list container
            result_list = page.query_selector('div.result-list')
            
            if not result_list:
                print("No result list found")
                return []
            
            # Find all person cards in the search results
            # Each card has classes: mi-text-sm mi-bg-white mi-shadow-dark-blue-20 mi-p-0 mi-mb-6 md:mi-rounded-lg
            cards = result_list.query_selector_all('div.mi-text-sm.mi-bg-white.mi-shadow-dark-blue-20.mi-p-0.mi-mb-6')
            
            if not cards:
                print("No person cards found")
                return []
            
            print(f"Found {len(cards)} results")
            
            results = []
            for i, card in enumerate(cards, 1):
                print(f"  [{i}/{len(cards)}] Extracting data...")
                data = self.scrape_person_card(card)
                if data:
                    results.append(data)
            
            return results
            
        except PlaywrightTimeoutError:
            print(f"  Timeout loading: {search_url}")
            return []
        except Exception as e:
            print(f"Error getting search results: {e}")
            return []

    def save_to_database(self, data: dict) -> bool:
        """Save data to Laravel API"""
        if not self.api_token:
            print("  No API token provided, skipping database save")
            return False
        
        try:
            url = f"{self.api_url}/api/data-private"
            headers = {
                'Authorization': f'Bearer {self.api_token}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
            
            # Prepare data for API (only include fields that are present)
            api_data = {k: v for k, v in data.items() if v is not None and v != ''}
            
            response = requests.post(url, json=api_data, headers=headers, timeout=10)
            
            if response.status_code == 201:
                print(f"  ✓ Saved to database")
                return True
            else:
                print(f"  ✗ Database save failed: {response.status_code} - {response.text[:200]}")
                return False
                
        except Exception as e:
            print(f"  ✗ Error saving to database: {e}")
            return False

    def save_to_csv(self, results: list):
        """Save results to CSV file with timestamp"""
        if not results:
            print("No results to save")
            return
        
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        csv_path = self.data_dir / f"merinfo_export_{timestamp}.csv"
        
        # Get all unique keys from all results
        all_keys = set()
        for result in results:
            all_keys.update(result.keys())
        
        # Sort keys for consistent column order
        fieldnames = sorted(all_keys)
        
        with open(csv_path, 'w', newline='', encoding='utf-8') as csvfile:
            writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
            # Write data without header (as per requirements)
            for result in results:
                # Convert arrays to JSON strings for CSV
                row = {}
                for key, value in result.items():
                    if isinstance(value, list):
                        row[key] = json.dumps(value, ensure_ascii=False)
                    else:
                        row[key] = value
                writer.writerow(row)
        
        print(f"\n✓ Saved {len(results)} records to {csv_path}")

    def scrape(self, query: str, headless: bool = True):
        """Main scraping method"""
        # URL encode the query parameter
        encoded_query = quote(query, safe='')
        search_url = f"https://www.merinfo.se/search?q={encoded_query}&page=1&d=p&ap=1"
        
        with sync_playwright() as p:
            # Prefer system Google Chrome channel first (more reliable on some Linux distros),
            # then try Playwright-managed Chromium, and finally system Chromium as a last resort.
            try:
                # 1) Try Google Chrome if installed
                browser = p.chromium.launch(
                    channel="chrome",
                    headless=headless,
                    slow_mo=50 if not headless else 100,  # Slow down operations to appear more human
                    args=[
                        '--disable-blink-features=AutomationControlled',
                        '--disable-dev-shm-usage',
                        '--no-sandbox',
                        '--disable-web-security',
                        '--disable-features=IsolateOrigins,site-per-process',
                    ]
                )
            except Exception:
                try:
                    # 2) Try Playwright-managed Chromium (if installed via `playwright install`)
                    browser = p.chromium.launch(channel="chromium", headless=True)
                except Exception:
                    try:
                        # 3) Fallback to system executables
                        import shutil
                        chrome_path = shutil.which('google-chrome') or shutil.which('chrome')
                        chromium_path = shutil.which('chromium-browser') or shutil.which('chromium')
                        if chrome_path:
                            browser = p.chromium.launch(executable_path=chrome_path, headless=True)
                        elif chromium_path:
                            browser = p.chromium.launch(executable_path=chromium_path, headless=True)
                        else:
                            # Last resort: try default Playwright resolution
                            browser = p.chromium.launch(headless=True)
                    except Exception as e:
                        print(f"Error launching browser: {e}")
                        print("Tips:\n - Ensure Google Chrome or Chromium is installed.\n - If using Playwright-managed browsers, run: playwright install chromium\n - On Linux, you may also need system deps: playwright install-deps (requires sudo)")
                        raise
            
            context = browser.new_context(
                user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                viewport={'width': 1920, 'height': 1080},
                locale='sv-SE',
                timezone_id='Europe/Stockholm',
                geolocation={'latitude': 59.3293, 'longitude': 18.0686},  # Stockholm coordinates
                permissions=['geolocation'],
                color_scheme='light',
                has_touch=False,
                java_script_enabled=True,
            )
            page = context.new_page()
            
            # Apply stealth mode to hide automation
            stealth = Stealth()
            stealth.apply_stealth_sync(page)
            
            # Add extra JavaScript to hide webdriver property
            page.add_init_script("""
                Object.defineProperty(navigator, 'webdriver', {
                    get: () => undefined
                });
                
                // Override the permissions API
                Object.defineProperty(navigator, 'permissions', {
                    get: () => ({
                        query: () => Promise.resolve({ state: 'granted' })
                    })
                });
                
                // Mock plugins to look more real
                Object.defineProperty(navigator, 'plugins', {
                    get: () => [1, 2, 3, 4, 5]
                });
                
                // Mock languages
                Object.defineProperty(navigator, 'languages', {
                    get: () => ['sv-SE', 'sv', 'en-US', 'en']
                });
                
                // Chrome runtime
                window.chrome = {
                    runtime: {}
                };
            """)
            
            try:
                # Get search results and extract data from all cards
                results = self.get_search_results(page, search_url)
                
                if not results:
                    print("No data found in search results")
                    return
                
                # Save each result to database (if token provided)
                if self.api_token:
                    print(f"\nSaving {len(results)} records to database...")
                    for i, data in enumerate(results, 1):
                        print(f"[{i}/{len(results)}]", end=' ')
                        self.save_to_database(data)
                
                # Store results
                self.results = results
                
                # Save all results to CSV
                if self.results:
                    self.save_to_csv(self.results)
                    print(f"\n✓ Scraping complete: {len(self.results)} records collected")
                else:
                    print("\n✗ No data collected")
                    
            finally:
                browser.close()


def main():
    parser = argparse.ArgumentParser(description='Scrape person data from merinfo.se')
    parser.add_argument('query', help='Search query (address, postal code, city, etc.)')
    parser.add_argument('--api-url', help='Laravel API base URL', default=None)
    parser.add_argument('--api-token', help='Sanctum authentication token', default=None)
    parser.add_argument('--no-headless', action='store_true', help='Run browser in visible mode (useful for debugging Cloudflare)')
    
    args = parser.parse_args()
    
    scraper = MerinfoScraper(api_url=args.api_url, api_token=args.api_token)
    scraper.scrape(args.query, headless=not args.no_headless)


if __name__ == '__main__':
    main()
