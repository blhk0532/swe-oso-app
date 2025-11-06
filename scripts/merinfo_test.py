#!/usr/bin/env python3
"""
Merinfo.se test scraper - navigates like a real user
Starts from homepage, fills search form, submits, then scrapes results
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

import requests
from playwright.sync_api import sync_playwright, TimeoutError as PlaywrightTimeoutError
from playwright_stealth import Stealth


class MerinfoTestScraper:
    """Test scraper for merinfo.se using real user navigation"""

    def __init__(self, api_url: Optional[str] = None, api_token: Optional[str] = None):
        self.api_url = api_url or os.getenv('LARAVEL_API_URL', 'http://localhost:8000')
        self.api_token = api_token or os.getenv('LARAVEL_API_TOKEN')
        self.data_dir = Path(__file__).parent / 'data'
        self.data_dir.mkdir(exist_ok=True)
        self.results = []

    def random_delay(self, min_ms: int = 500, max_ms: int = 2000):
        """Add random human-like delay"""
        return random.randint(min_ms, max_ms)

    def human_like_mouse_move(self, page):
        """Simulate human-like mouse movements"""
        try:
            for _ in range(random.randint(2, 4)):
                x = random.randint(100, 800)
                y = random.randint(100, 600)
                page.mouse.move(x, y)
                page.wait_for_timeout(random.randint(100, 300))
        except Exception:
            pass

    def navigate_to_homepage(self, page) -> bool:
        """Navigate to merinfo.se homepage"""
        print("Step 1: Navigating to merinfo.se homepage...")
        try:
            page.goto("https://www.merinfo.se/", wait_until='domcontentloaded', timeout=30000)
            page.wait_for_timeout(self.random_delay(2000, 4000))
            
            # Simulate human behavior
            self.human_like_mouse_move(page)
            
            # Check page title
            title = page.title()
            print(f"  Page title: {title}")
            
            # Wait for Cloudflare if present
            if any(phrase in title for phrase in ["Just a moment", "Vänta", "Checking"]):
                print("  Cloudflare challenge detected, waiting...")
                max_wait = 60
                for i in range(max_wait):
                    page.wait_for_timeout(self.random_delay(800, 1500))
                    current_title = page.title()
                    if not any(phrase in current_title for phrase in ["Just a moment", "Vänta", "Checking"]):
                        print(f"  ✓ Challenge passed after ~{i+1} seconds")
                        page.wait_for_timeout(self.random_delay(1500, 3000))
                        break
                else:
                    print("  ⚠ Challenge still active. Please complete the verification in the visible browser window, then press Enter here to continue...")
                    try:
                        input()
                    except EOFError:
                        pass
                    # Re-check after manual attempt
                    page.wait_for_timeout(self.random_delay(1000, 2000))
                    current_title = page.title()
                    if any(phrase in current_title for phrase in ["Just a moment", "Vänta", "Checking"]):
                        # Try a few gentle reloads with random delays
                        print("  Challenge not solved, attempting gentle reloads...")
                        for attempt in range(1, 6):
                            delay = self.random_delay(2000, 4000)
                            print(f"   - Reload attempt {attempt}/5 (waiting {delay}ms)...")
                            page.wait_for_timeout(delay)
                            page.reload(wait_until='domcontentloaded')
                            page.wait_for_timeout(self.random_delay(1000, 2000))
                            t = page.title()
                            if not any(phrase in t for phrase in ["Just a moment", "Vänta", "Checking"]):
                                print("   ✓ Challenge passed after reload")
                                break
                        else:
                            print("  ✗ Challenge not solved")
                            return False
            
            print("  ✓ Homepage loaded successfully")
            return True
            
        except Exception as e:
            print(f"  ✗ Error loading homepage: {e}")
            return False

    def fill_and_submit_search(self, page, query: str) -> bool:
        """Fill search form and submit"""
        print(f"Step 2: Filling search form with query: {query}")
        try:
            # Look for the search input field
            search_input = page.query_selector('input.search-field-input')
            
            if not search_input:
                print("  ✗ Search input field not found")
                # Try to take a screenshot for debugging
                screenshot_path = self.data_dir / f"debug_no_search_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png"
                page.screenshot(path=str(screenshot_path))
                print(f"  Screenshot saved: {screenshot_path}")
                return False
            
            print("  ✓ Found search input field")
            
            # Click the input field (human-like)
            search_input.click()
            page.wait_for_timeout(self.random_delay(500, 1000))
            
            # Type slowly like a human
            for char in query:
                search_input.type(char)
                page.wait_for_timeout(random.randint(50, 150))
            
            print(f"  ✓ Typed query: {query}")
            page.wait_for_timeout(self.random_delay(500, 1500))
            
            # Look for submit button or form
            # Try multiple possible selectors
            submit_button = (
                page.query_selector('button[type="submit"]') or
                page.query_selector('input[type="submit"]') or
                page.query_selector('button.search-button') or
                page.query_selector('.search-form button')
            )
            
            if submit_button:
                print("  ✓ Found submit button, clicking...")
                submit_button.click()
            else:
                # Try submitting the form by pressing Enter
                print("  ⚠ Submit button not found, trying Enter key...")
                search_input.press('Enter')
            
            page.wait_for_timeout(self.random_delay(2000, 3000))
            
            # Wait for navigation to results page
            try:
                page.wait_for_url('**/search?q=**', timeout=10000)
                print("  ✓ Navigated to search results page")
                return True
            except:
                # Check if we're on a results page anyway
                current_url = page.url
                if '/search' in current_url:
                    print(f"  ✓ On search results page: {current_url}")
                    return True
                else:
                    print(f"  ✗ Unexpected page: {current_url}")
                    return False
            
        except Exception as e:
            print(f"  ✗ Error during search: {e}")
            return False

    def scrape_person_card(self, card_element) -> dict:
        """Scrape a single person's card from search results"""
        try:
            data = {}
            
            # Extract ps_personnamn
            try:
                name_link = card_element.query_selector('a[href*="/person/"]')
                if name_link:
                    name_text = name_link.inner_text().strip()
                    name_text = ' '.join(name_text.split())
                    data['ps_personnamn'] = name_text
            except Exception as e:
                print(f"    Error extracting name: {e}")
            
            # Extract ps_personnummer
            try:
                personnummer_span = card_element.query_selector('span')
                if personnummer_span:
                    parent_div = card_element.query_selector('div.mi-my-1')
                    if parent_div:
                        full_text = parent_div.inner_text()
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
                        data['bo_gatuadress'] = address_spans[0].inner_text().strip()
                    
                    if len(address_spans) >= 2:
                        full_location = address_spans[1].inner_text().strip()
                        if len(full_location) >= 6:
                            data['bo_postnummer'] = full_location[:6].strip()
                            data['bo_postort'] = full_location[6:].strip()
            except Exception as e:
                print(f"    Error extracting address: {e}")
            
            # Extract ps_telefon
            try:
                phone_link = card_element.query_selector('a[href^="tel:"]')
                if phone_link:
                    phone_text = phone_link.inner_text().strip()
                    phone_text = ' '.join(phone_text.split())
                    if phone_text:
                        data['ps_telefon'] = [phone_text]
            except Exception as e:
                print(f"    Error extracting telefon: {e}")
            
            data = {k: v for k, v in data.items() if v is not None and v != ''}
            return data
            
        except Exception as e:
            print(f"    Error scraping card: {e}")
            return {}

    def scrape_results(self, page) -> list:
        """Scrape all results from the current page"""
        print("Step 3: Scraping search results...")
        
        try:
            page.wait_for_timeout(self.random_delay(2000, 3000))
            
            # Take screenshot for debugging
            screenshot_path = self.data_dir / f"results_{datetime.now().strftime('%Y%m%d_%H%M%S')}.png"
            page.screenshot(path=str(screenshot_path))
            print(f"  Screenshot saved: {screenshot_path}")
            
            # Find result cards
            result_list = page.query_selector('div.result-list')
            
            if not result_list:
                print("  ✗ Result list not found")
                print(f"  Current URL: {page.url}")
                print(f"  Page title: {page.title()}")
                return []
            
            cards = result_list.query_selector_all('div.mi-text-sm.mi-bg-white.mi-shadow-dark-blue-20.mi-p-0.mi-mb-6')
            
            if not cards:
                print("  ✗ No person cards found")
                return []
            
            print(f"  ✓ Found {len(cards)} result cards")
            
            results = []
            for i, card in enumerate(cards, 1):
                print(f"  [{i}/{len(cards)}] Extracting data...")
                data = self.scrape_person_card(card)
                if data:
                    results.append(data)
                    print(f"    ✓ Extracted: {data.get('ps_personnamn', 'N/A')}")
            
            return results
            
        except Exception as e:
            print(f"  ✗ Error scraping results: {e}")
            return []

    def save_to_database(self, data: dict) -> bool:
        """Save data to Laravel API"""
        if not self.api_token:
            return False
        
        try:
            url = f"{self.api_url}/api/data-private"
            headers = {
                'Authorization': f'Bearer {self.api_token}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
            
            api_data = {k: v for k, v in data.items() if v is not None and v != ''}
            response = requests.post(url, json=api_data, headers=headers, timeout=10)
            
            if response.status_code == 201:
                print(f"    ✓ Saved to database")
                return True
            else:
                print(f"    ✗ Database save failed: {response.status_code}")
                return False
                
        except Exception as e:
            print(f"    ✗ Error saving to database: {e}")
            return False

    def save_to_csv(self, results: list):
        """Save results to CSV file with timestamp"""
        if not results:
            print("\n✗ No results to save")
            return
        
        timestamp = datetime.now().strftime('%Y%m%d_%H%M%S')
        csv_path = self.data_dir / f"merinfo_test_export_{timestamp}.csv"
        
        all_keys = set()
        for result in results:
            all_keys.update(result.keys())
        
        fieldnames = sorted(all_keys)
        
        with open(csv_path, 'w', newline='', encoding='utf-8') as csvfile:
            writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
            for result in results:
                row = {}
                for key, value in result.items():
                    if isinstance(value, list):
                        row[key] = json.dumps(value, ensure_ascii=False)
                    else:
                        row[key] = value
                writer.writerow(row)
        
        print(f"\n✓ Saved {len(results)} records to {csv_path}")

    def scrape(self, query: str, headless: bool = True, profile_dir: Optional[str] = None, storage_state: Optional[str] = None, save_storage_state: Optional[str] = None):
        """Main scraping method"""
        with sync_playwright() as p:
            # Prepare launch options
            launch_args = [
                '--disable-blink-features=AutomationControlled',
                '--disable-dev-shm-usage',
                '--no-sandbox',
                '--disable-web-security',
                '--disable-features=IsolateOrigins,site-per-process',
            ]

            browser = None
            context = None

            try:
                if profile_dir:
                    # Use persistent context (re-uses Chrome profile to keep Cloudflare cookies)
                    context = p.chromium.launch_persistent_context(
                        user_data_dir=profile_dir,
                        channel="chrome",
                        headless=headless,
                        slow_mo=50 if not headless else 100,
                        args=launch_args,
                        viewport={'width': 1920, 'height': 1080},
                        locale='sv-SE',
                        timezone_id='Europe/Stockholm',
                        geolocation={'latitude': 59.3293, 'longitude': 18.0686},
                        permissions=['geolocation'],
                        color_scheme='light',
                        java_script_enabled=True,
                    )
                else:
                    # Regular browser + context
                    browser = p.chromium.launch(
                        channel="chrome",
                        headless=headless,
                        slow_mo=50 if not headless else 100,
                        args=launch_args,
                    )
                    context_kwargs = {
                        'user_agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                        'viewport': {'width': 1920, 'height': 1080},
                        'locale': 'sv-SE',
                        'timezone_id': 'Europe/Stockholm',
                        'geolocation': {'latitude': 59.3293, 'longitude': 18.0686},
                        'permissions': ['geolocation'],
                        'color_scheme': 'light',
                        'has_touch': False,
                        'java_script_enabled': True,
                    }
                    if storage_state and Path(storage_state).exists():
                        context_kwargs['storage_state'] = storage_state
                    context = browser.new_context(**context_kwargs)

            except Exception:
                # Fallbacks if chrome channel isn't available
                if profile_dir:
                    context = p.chromium.launch_persistent_context(
                        user_data_dir=profile_dir,
                        headless=headless,
                        args=launch_args,
                    )
                else:
                    browser = p.chromium.launch(headless=headless)
                    context = browser.new_context()

            page = context.new_page()
            
            # Apply stealth
            stealth = Stealth()
            stealth.apply_stealth_sync(page)
            
            # Add anti-detection scripts
            page.add_init_script("""
                Object.defineProperty(navigator, 'webdriver', {
                    get: () => undefined
                });
                Object.defineProperty(navigator, 'plugins', {
                    get: () => [1, 2, 3, 4, 5]
                });
                Object.defineProperty(navigator, 'languages', {
                    get: () => ['sv-SE', 'sv', 'en-US', 'en']
                });
                window.chrome = { runtime: {} };
            """)
            
            try:
                # Step 1: Navigate to homepage
                if not self.navigate_to_homepage(page):
                    print("\n✗ Failed to load homepage")
                    return
                
                # Step 2: Fill and submit search form
                if not self.fill_and_submit_search(page, query):
                    print("\n✗ Failed to submit search")
                    return
                
                # Step 3: Scrape results
                results = self.scrape_results(page)
                
                if not results:
                    print("\n✗ No data collected")
                    return
                
                self.results = results
                
                # Save to database if token provided
                if self.api_token:
                    print(f"\nStep 4: Saving {len(results)} records to database...")
                    for i, data in enumerate(results, 1):
                        print(f"  [{i}/{len(results)}]", end=' ')
                        self.save_to_database(data)
                
                # Save to CSV
                self.save_to_csv(self.results)
                print(f"\n✓ Scraping complete: {len(self.results)} records collected")

                # Optionally save storage state for future runs
                if save_storage_state:
                    try:
                        context.storage_state(path=save_storage_state)
                        print(f"\n✓ Session saved to {save_storage_state}")
                    except Exception as e:
                        print(f"\n⚠ Could not save session: {e}")
                
            finally:
                if not headless:
                    print("\nPress Enter to close browser...")
                    input()
                # Close context/browser appropriately
                try:
                    if context:
                        context.close()
                finally:
                    if browser:
                        browser.close()


def main():
    parser = argparse.ArgumentParser(description='Test scraper for merinfo.se using real user navigation')
    parser.add_argument('query', help='Search query (address, postal code, city, etc.)')
    parser.add_argument('--api-url', help='Laravel API base URL', default=None)
    parser.add_argument('--api-token', help='Sanctum authentication token', default=None)
    parser.add_argument('--no-headless', action='store_true', help='Run browser in visible mode')
    parser.add_argument('--profile-dir', help='Path to persistent Chrome profile directory (will be created if not exists)', default=None)
    parser.add_argument('--storage-state', help='Path to storage state JSON to load (cookies/session)', default=None)
    parser.add_argument('--save-storage-state', help='Path to save storage state JSON after run', default=None)
    parser.add_argument('--proxy', help='Proxy URL, e.g. http://user:pass@host:port', default=None)
    
    args = parser.parse_args()
    
    scraper = MerinfoTestScraper(api_url=args.api_url, api_token=args.api_token)

    # Pass proxy through environment variable that Playwright will read (alternative is to wire into launch kwargs)
    # If you want a stronger wiring, we can thread it into launch_persistent_context/launch as `proxy={server: ...}`.
    if args.proxy:
        os.environ['PLAYWRIGHT_PROXY'] = args.proxy

    scraper.scrape(
        args.query,
        headless=not args.no_headless,
        profile_dir=args.profile_dir,
        storage_state=args.storage_state,
        save_storage_state=args.save_storage_state,
    )


if __name__ == '__main__':
    main()
