#!/usr/bin/env python3
"""
Ratsit.se scraper script
Scrapes person data from ratsit.se and saves to database and CSV
"""

import argparse
import csv
import json
import os
import re
from datetime import datetime
from pathlib import Path
from typing import Optional
from urllib.parse import quote

import requests
from playwright.sync_api import sync_playwright, TimeoutError as PlaywrightTimeoutError


class RatsitScraper:
    """Scraper for ratsit.se person search results"""

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

    def extract_text_after_label(self, page, label_text: str) -> Optional[str]:
        """Extract text value after a label span"""
        try:
            # Find the label span
            label_selector = f'span.color--gray5:has-text("{label_text}")'
            label_element = page.query_selector(label_selector)
            
            if not label_element:
                return None
            
            # Get the parent paragraph using element.evaluate
            parent_text = label_element.evaluate('''(el) => {
                const p = el.closest("p");
                return p ? p.innerText : null;
            }''')
            
            if not parent_text:
                return None
            
            # Remove the label text and clean up
            text = parent_text.replace(label_text, '', 1).strip()
            # Remove any tooltip text that might be present
            text = re.sub(r'\s*Visas för medlemmar.*', '', text, flags=re.IGNORECASE)
            
            return text if text else None
        except Exception as e:
            print(f"Error extracting {label_text}: {e}")
            return None

    def extract_personnummer(self, page) -> Optional[str]:
        """Extract personnummer (special handling for XXXX placeholder)"""
        try:
            label_selector = 'span.color--gray5:has-text("Personnummer:")'
            label_element = page.query_selector(label_selector)
            
            if not label_element:
                return None
            
            # Get the HTML content to find the XXXX pattern
            html = label_element.evaluate('''(el) => {
                const p = el.closest("p");
                return p ? p.innerHTML : null;
            }''')
            
            if not html:
                return None
            
            # Extract text before the link and the XXXX part
            # Pattern: "19601110- " + <a>...<strong>XXXX</strong>...
            match = re.search(r'Personnummer:\s*([0-9-]+)\s*.*?<strong>XXXX</strong>', html, re.IGNORECASE | re.DOTALL)
            if match:
                return match.group(1).strip() + 'XXXX'
            
            # Fallback: try to get just the text value
            text = self.extract_text_after_label(page, "Personnummer:")
            if text:
                # Clean up any remaining HTML entities
                text = re.sub(r'<[^>]+>', '', text)
                text = text.strip()
                # Check if XXXX is in the HTML
                if 'XXXX' in html.upper() or 'xxxx' in html:
                    if not text.endswith('XXXX') and not text.endswith('xxxx'):
                        text = text.rstrip('-').rstrip() + 'XXXX'
                return text
            
            return None
        except Exception as e:
            print(f"Error extracting personnummer: {e}")
            return None

    def extract_telefon(self, page) -> Optional[str]:
        """Extract telefon number from href tel: link"""
        try:
            label_selector = 'span.color--gray5:has-text("Telefon:")'
            label_element = page.query_selector(label_selector)
            
            if not label_element:
                return None
            
            # Find the tel: link using element.evaluate
            tel_href = label_element.evaluate('''(el) => {
                const p = el.closest("p");
                if (!p) return null;
                const telLink = p.querySelector('a[href^="tel:"]');
                return telLink ? telLink.getAttribute('href') : null;
            }''')
            
            if tel_href and tel_href.startswith('tel:'):
                return tel_href.replace('tel:', '')
            
            return None
        except Exception as e:
            print(f"Error extracting telefon: {e}")
            return None

    def map_kon_value(self, value: Optional[str]) -> Optional[str]:
        """Map Swedish gender values to API format (M, F, O)"""
        if not value:
            return None
        
        value_lower = value.lower().strip()
        mapping = {
            'man': 'M',
            'kvinna': 'F',
            'kvinno': 'F',
            'm': 'M',
            'f': 'F',
            'o': 'O',
            'other': 'O',
            'annat': 'O',
        }
        
        return mapping.get(value_lower, value)

    def scrape_person_page(self, page, url: str) -> dict:
        """Scrape a single person's detail page"""
        print(f"  Scraping: {url}")
        
        try:
            page.goto(url, wait_until='networkidle', timeout=30000)
            page.wait_for_timeout(2000)  # Wait for dynamic content
            
            # Extract raw values
            raw_kon = self.extract_text_after_label(page, "Juridiskt kön:")
            
            data = {
                'ps_personnummer': self.extract_personnummer(page),
                'ps_alder': self.extract_text_after_label(page, "Ålder:"),
                'ps_fodelsedag': self.extract_text_after_label(page, "Födelsedag:"),
                'ps_kon': self.map_kon_value(raw_kon),
                'ps_telefon': self.extract_telefon(page),
                'ps_personnamn': self.extract_text_after_label(page, "Personnamn:"),
                'ps_fornamn': self.extract_text_after_label(page, "Förnamn:"),
                'ps_efternamn': self.extract_text_after_label(page, "Efternamn:"),
                'bo_gatuadress': self.extract_text_after_label(page, "Gatuadress:"),
                'bo_postnummer': self.extract_text_after_label(page, "Postnummer:"),
                'bo_postort': self.extract_text_after_label(page, "Postort:"),
            }
            
            # Convert telefon to array format if present
            if data['ps_telefon']:
                data['ps_telefon'] = [data['ps_telefon']]
            else:
                data['ps_telefon'] = []
            
            # Clean up None values
            data = {k: v for k, v in data.items() if v is not None and v != ''}
            
            return data
            
        except PlaywrightTimeoutError:
            print(f"  Timeout loading: {url}")
            return {}
        except Exception as e:
            print(f"  Error scraping {url}: {e}")
            return {}

    def get_search_result_links(self, page, search_url: str) -> list:
        """Get all person detail page links from search results"""
        print(f"Searching: {search_url}")
        
        try:
            page.goto(search_url, wait_until='networkidle', timeout=30000)
            page.wait_for_timeout(2000)  # Wait for dynamic content
            
            # Find all links in the search results
            links = []
            result_list = page.query_selector('ul.search-result-list')
            
            if result_list:
                # Find all li elements with links to ratsit.se
                link_elements = result_list.query_selector_all('li a[href^="https://www.ratsit.se/"]')
                
                for link_element in link_elements:
                    href = link_element.get_attribute('href')
                    if href and href.startswith('https://www.ratsit.se/'):
                        links.append(href)
            
            print(f"Found {len(links)} results")
            return links
            
        except Exception as e:
            print(f"Error getting search results: {e}")
            return []

    def save_to_database(self, data: dict) -> bool:
        """Save data to Laravel API"""
        if not self.api_token:
            print("  No API token provided, skipping database save")
            return False
        
        try:
            url = f"{self.api_url}/api/ratsit-data"
            headers = {
                'Authorization': f'Bearer {self.api_token}',
                'Content-Type': 'application/json',
                'Accept': 'application/json',
            }
            
            # Prepare data for API (only include fields that are present)
            api_data = {k: v for k, v in data.items() if v is not None and v != ''}
            
            response = requests.post(url, json=api_data, headers=headers, timeout=10)

            # 201 Created for new, 200 OK for updated (upsert behavior)
            if response.status_code in (200, 201):
                action = 'created' if response.status_code == 201 else 'updated'
                print(f"  ✓ Saved to database ({action})")
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
        csv_path = self.data_dir / f"ratsit_export_{timestamp}.csv"
        
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

    def scrape(self, query: str):
        """Main scraping method"""
        # URL encode the query parameter
        encoded_query = quote(query, safe='')
        search_url = f"https://www.ratsit.se/sok/person?vem={encoded_query}"
        
        with sync_playwright() as p:
            # Prefer system Google Chrome channel first (more reliable on some Linux distros),
            # then try Playwright-managed Chromium, and finally system Chromium as a last resort.
            try:
                # 1) Try Google Chrome if installed
                browser = p.chromium.launch(channel="chrome", headless=True)
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
                user_agent='Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
            )
            page = context.new_page()
            
            try:
                # Step 1: Get search result links
                links = self.get_search_result_links(page, search_url)
                
                if not links:
                    print("No search results found")
                    return
                
                # Step 2: Scrape each person's detail page
                print(f"\nScraping {len(links)} person pages...")
                for i, link in enumerate(links, 1):
                    print(f"[{i}/{len(links)}]", end=' ')
                    data = self.scrape_person_page(page, link)
                    
                    if data:
                        self.results.append(data)
                        
                        # Step 3: Save to database (if token provided)
                        if self.api_token:
                            self.save_to_database(data)
                    
                    # Small delay between requests
                    page.wait_for_timeout(1000)
                
                # Step 4: Save all results to CSV
                if self.results:
                    self.save_to_csv(self.results)
                    print(f"\n✓ Scraping complete: {len(self.results)} records collected")
                else:
                    print("\n✗ No data collected")
                    
            finally:
                browser.close()


def main():
    parser = argparse.ArgumentParser(description='Scrape person data from ratsit.se')
    parser.add_argument('query', help='Search query (person name, etc.)')
    parser.add_argument('--api-url', help='Laravel API base URL', default=None)
    parser.add_argument('--api-token', help='Sanctum authentication token', default=None)
    
    args = parser.parse_args()
    
    scraper = RatsitScraper(api_url=args.api_url, api_token=args.api_token)
    scraper.scrape(args.query)


if __name__ == '__main__':
    main()

