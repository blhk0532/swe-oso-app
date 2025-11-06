#!/usr/bin/env python3
"""
Hitta.se scraper script
Scrapes person data from hitta.se and saves to CSV and database
"""

import argparse
import csv
import json
import os
import re
import time
from pathlib import Path
from typing import List, Dict, Optional
from urllib.parse import quote, urlparse, parse_qs

import requests
from selenium import webdriver
from selenium.webdriver.common.by import By
from selenium.webdriver.chrome.options import Options
from selenium.webdriver.chrome.service import Service
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import (
    TimeoutException,
    NoSuchElementException,
    ElementClickInterceptedException,
)
from webdriver_manager.chrome import ChromeDriverManager


class HittaSeScraper:
    """Scraper for hitta.se person search results"""

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
        self.base_url = "https://www.hitta.se"

    def scrape_search_results(self, query: str) -> List[Dict[str, str]]:
        """
        Scrape search results for a given query

        Args:
            query: Search query string

        Returns:
            List of person data dictionaries
        """
        self.results = []
        encoded_query = quote(query)
        search_url = f"{self.base_url}/s%C3%B6k?vad={encoded_query}&typ=prv"
        
        print(f"Searching for: {query}")
        print(f"URL: {search_url}")

        # Setup Chrome options
        chrome_options = Options()
        chrome_options.add_argument('--headless')
        chrome_options.add_argument('--no-sandbox')
        chrome_options.add_argument('--disable-dev-shm-usage')
        chrome_options.add_argument('--disable-gpu')
        chrome_options.add_argument('--window-size=1920,1080')
        chrome_options.add_argument('--user-agent=Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')

        driver = None
        try:
            service = Service(ChromeDriverManager().install())
            driver = webdriver.Chrome(service=service, options=chrome_options)
            driver.get(search_url)
            
            # Wait for search results to load
            try:
                WebDriverWait(driver, 10).until(
                    EC.presence_of_element_located((By.CSS_SELECTOR, 'li[data-test="person-item"]'))
                )
            except TimeoutException:
                print("No results found or timeout waiting for results")
                return []

            # Try to dismiss cookie/consent overlays once at start
            try:
                self._dismiss_consent_overlay(driver)
            except Exception:
                pass

            # Extract all person items
            person_items = driver.find_elements(By.CSS_SELECTOR, 'li[data-test="person-item"]')
            total = len(person_items)
            print(f"Found {total} results")

            for i in range(total):
                # Re-query items each iteration to avoid stale references after navigation
                try:
                    current_items = driver.find_elements(By.CSS_SELECTOR, 'li[data-test="person-item"]')
                    if i >= len(current_items):
                        break
                    item = current_items[i]
                    idx = i + 1
                    person_data = self.extract_person_data(item, driver)
                    if person_data:
                        self.results.append(person_data)
                        print(f"Extracted {idx}/{total}: {person_data.get('personnamn', 'Unknown')}")
                except Exception as e:
                    print(f"Error extracting person {i + 1}: {e}")
                    continue

        except Exception as e:
            print(f"Error during scraping: {e}")
        finally:
            if driver:
                driver.quit()

        return self.results

    def extract_person_data(self, item, driver) -> Optional[Dict[str, str]]:
        """
        Extract person data from a search result item

        Args:
            item: Selenium WebElement for person item
            driver: Selenium WebDriver instance

        Returns:
            Dictionary with person data
        """
        data = {
            'personnamn': None,
            'alder': None,
            'kon': None,
            'gatuadress': None,
            'postnummer': None,
            'postort': None,
            'telefon': None,
            'karta': None,
            'link': None,
        }

        try:
            # Extract name and age from h2 title
            try:
                title = item.find_element(By.CSS_SELECTOR, 'h2[data-test="search-result-title"]')
                title_text = title.text
                # Age is in a span with class style_age__ZgTHo
                try:
                    age_span = title.find_element(By.CSS_SELECTOR, 'span.style_age__ZgTHo')
                    data['alder'] = age_span.text.strip()
                    # Remove age from title to get name
                    data['personnamn'] = title_text.replace(data['alder'], '').strip()
                except NoSuchElementException:
                    data['personnamn'] = title_text.strip()
            except NoSuchElementException:
                pass

            # Extract gender and address from paragraph
            try:
                address_p = item.find_element(By.CSS_SELECTOR, 'p.text-body-long-sm-regular')
                address_lines = address_p.text.split('\n')
                for i, line in enumerate(address_lines):
                    line = line.strip()
                    if i == 0:
                        # First line is gender
                        try:
                            gender_span = address_p.find_element(By.CSS_SELECTOR, 'span.style_gender__hKSL0')
                            data['kon'] = gender_span.text.strip()
                        except NoSuchElementException:
                            data['kon'] = line
                    elif i == 1:
                        # Second line is street address
                        data['gatuadress'] = line
                    elif i == 2:
                        # Third line is postal code and city
                        parts = line.split(' ', 2)
                        if len(parts) >= 2:
                            data['postnummer'] = f"{parts[0]} {parts[1]}".strip()
                            if len(parts) >= 3:
                                data['postort'] = parts[2].strip()
            except NoSuchElementException:
                pass

            # Defer phone reveal until after we grab static links to avoid stale elements

            # Extract map link
            try:
                map_link = item.find_element(By.CSS_SELECTOR, 'a[data-test="show-on-map-button"]')
                href = map_link.get_attribute('href')
                if href:
                    data['karta'] = f"{self.base_url}{href}" if href.startswith('/') else href
            except NoSuchElementException:
                pass

            # Extract profile link
            try:
                profile_link = item.find_element(By.CSS_SELECTOR, 'a[data-test="search-list-link"]')
                href = profile_link.get_attribute('href')
                if href:
                    data['link'] = f"{self.base_url}{href}" if href.startswith('/') else href
            except NoSuchElementException:
                pass

            # Extract phone number - click button to reveal full number (after links to avoid stale)
            try:
                phone_button = item.find_element(By.CSS_SELECTOR, 'button[data-test="phone-link"]')
                phone_text = phone_button.text

                if "Lägg till telefonnummer" not in phone_text:
                    try:
                        # Ensure element is in view and try normal click
                        driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", phone_button)
                        WebDriverWait(driver, 5).until(
                            EC.element_to_be_clickable((By.CSS_SELECTOR, 'button[data-test="phone-link"]'))
                        )

                        current_url = driver.current_url
                        try:
                            phone_button.click()
                        except ElementClickInterceptedException:
                            # Attempt to close consent overlay then retry
                            self._dismiss_consent_overlay(driver)
                            try:
                                phone_button.click()
                            except ElementClickInterceptedException:
                                # Fallback: force click via JS
                                driver.execute_script("arguments[0].click();", phone_button)

                        # Wait briefly for potential navigation or reveal
                        time.sleep(0.8)

                        # Check if URL changed (redirect)
                        new_url = driver.current_url
                        if new_url != current_url and 'revealNumber' in new_url:
                            # Extract the full phone number from URL (first revealed) and then collect all numbers on profile
                            parsed_url = urlparse(new_url)
                            params = parse_qs(parsed_url.query)
                            first_phone = params.get('revealNumber', [None])[0]

                            numbers = []
                            try:
                                # Wait for either show-number buttons or tel: links
                                WebDriverWait(driver, 5).until(
                                    EC.presence_of_all_elements_located((By.CSS_SELECTOR, 'button[data-test="show-number"], a[href^="tel:"]'))
                                )

                                # Click all show-number buttons to reveal full numbers
                                buttons = driver.find_elements(By.CSS_SELECTOR, 'button[data-test="show-number"]')
                                for btn in buttons:
                                    try:
                                        if btn.is_displayed():
                                            driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", btn)
                                            time.sleep(0.1)
                                            try:
                                                btn.click()
                                            except ElementClickInterceptedException:
                                                driver.execute_script("arguments[0].click();", btn)
                                            time.sleep(0.2)
                                    except Exception:
                                        continue

                                # Collect from tel: links (most reliable)
                                tel_links = driver.find_elements(By.CSS_SELECTOR, 'a[href^="tel:"]')
                                for a in tel_links:
                                    href = a.get_attribute('href') or ''
                                    if href.lower().startswith('tel:'):
                                        num = href[4:].strip()
                                        if num:
                                            numbers.append(num)

                                # Fallback to any visible spans if no tel links found
                                if not numbers:
                                    spans = driver.find_elements(By.CSS_SELECTOR, 'button[data-test="show-number"] span')
                                    for sp in spans:
                                        txt = sp.text.strip()
                                        if txt:
                                            numbers.append(txt)
                            except Exception:
                                pass

                            # If none found via spans, fallback to the revealNumber param
                            if not numbers and first_phone:
                                numbers = [first_phone]

                            # De-duplicate while preserving order
                            seen = set()
                            deduped = []
                            for n in numbers:
                                if n not in seen:
                                    seen.add(n)
                                    deduped.append(n)

                            data['telefon'] = deduped
                            if deduped:
                                print(f"  → Revealed phone(s): {', '.join(deduped)}")

                            # Navigate back to search results
                            driver.back()
                            # Wait for results list to be available again (avoid stale)
                            WebDriverWait(driver, 10).until(
                                EC.presence_of_element_located((By.CSS_SELECTOR, 'li[data-test="person-item"]'))
                            )
                            time.sleep(0.2)
                        else:
                            # No redirect, try to extract inline by clicking and collecting tel: links
                            try:
                                driver.execute_script("arguments[0].scrollIntoView({block: 'center'});", phone_button)
                                try:
                                    phone_button.click()
                                except Exception:
                                    try:
                                        driver.execute_script("arguments[0].click();", phone_button)
                                    except Exception:
                                        pass
                                time.sleep(0.5)
                                tel_links = driver.find_elements(By.CSS_SELECTOR, 'a[href^="tel:"]')
                                inline_numbers = []
                                for a in tel_links:
                                    href = a.get_attribute('href') or ''
                                    if href.lower().startswith('tel:'):
                                        inline_numbers.append(href[4:].strip())
                                if inline_numbers:
                                    data['telefon'] = inline_numbers
                                else:
                                    fresh_text = item.find_element(By.CSS_SELECTOR, 'button[data-test="phone-link"]').text
                                    phone_matches = re.findall(r'(\+?\d[\d\s-]{7,})', fresh_text)
                                    if phone_matches:
                                        data['telefon'] = [m.strip() for m in phone_matches]
                            except Exception:
                                fresh_text = phone_text
                                phone_matches = re.findall(r'(\+?\d[\d\s-]{7,})', fresh_text)
                                if phone_matches:
                                    data['telefon'] = [m.strip() for m in phone_matches]
                    except Exception as e:
                        print(f"  → Error clicking phone button: {e}")
                        # Fallback to extracting from text
                        phone_matches = re.findall(r'(\+?\d[\d\s-]{7,})', phone_text)
                        if phone_matches:
                            data['telefon'] = [m.strip() for m in phone_matches]
                else:
                    data['telefon'] = []
            except NoSuchElementException:
                pass

        except Exception as e:
            print(f"Error extracting data: {e}")
            return None

        return data

    def _dismiss_consent_overlay(self, driver) -> None:
        """Best-effort dismissal of Gravito CMP / cookie overlays that block clicks."""
        # If a visible overlay exists, try clicking common accept buttons
        try:
            overlay = driver.find_elements(By.CSS_SELECTOR, '.gravitoCMP-background-overlay')
            if overlay:
                # Try common accept/approve buttons by text
                candidates = [
                    (By.XPATH, "//button[contains(., 'Godkänn') or contains(., 'Acceptera') or contains(., 'OK') or contains(., 'Jag förstår')]") ,
                    (By.CSS_SELECTOR, "button[data-test='uc-accept-all-button']"),
                    (By.CSS_SELECTOR, "button[aria-label*='Godkänn']"),
                ]
                for by, sel in candidates:
                    try:
                        btns = driver.find_elements(by, sel)
                        for btn in btns:
                            if btn.is_displayed():
                                driver.execute_script("arguments[0].click();", btn)
                                time.sleep(0.2)
                                return
                    except Exception:
                        continue

                # As a last resort, hide the overlay via JS
                driver.execute_script('''
                    document.querySelectorAll('.gravitoCMP-background-overlay, .gravitoCMP, [class*="consent"]').forEach(e => e.style.display='none');
                ''')
                time.sleep(0.1)
        except Exception:
            # Non-fatal; proceed regardless
            pass

    def save_to_csv(self, query: str, include_phone_missing: bool = False):
        """
        Save results to CSV file(s)

        Args:
            query: Search query for filename
            include_phone_missing: If True, create separate CSV for missing phones
        """
        if not self.results:
            print("No results to save")
            return

        total = len(self.results)
        safe_query = re.sub(r'[^\w\s-]', '', query).strip().replace(' ', '_')

        # Save all results
        all_filename = self.data_dir / f"hitta_se_{safe_query}_alla_{total}.csv"
        self.write_csv(all_filename, self.results)
        print(f"Saved all results to: {all_filename}")

        # Save results with phone numbers (not missing)
        if include_phone_missing:
            with_phone = [r for r in self.results if r.get('telefon') and r.get('telefon') != "Lägg till telefonnummer"]
            if with_phone:
                with_phone_total = len(with_phone)
                with_phone_filename = self.data_dir / f"hitta_se_{safe_query}_visa_{with_phone_total}.csv"
                self.write_csv(with_phone_filename, with_phone)
                print(f"Saved {with_phone_total} results with phone numbers to: {with_phone_filename}")

    def save_to_database(self) -> int:
        """
        Save results to Laravel database via API
        
        Returns:
            Number of records saved
        """
        if not self.results:
            print("No results to save to database")
            return 0
        
        saved_count = 0
        
        for record in self.results:
            try:
                # Handle phone numbers - convert to array or null
                telefon = record.get('telefon')
                if isinstance(telefon, list):
                    # If list is empty or contains "Lägg till telefonnummer", set to null
                    if not telefon or (len(telefon) == 1 and telefon[0] == "Lägg till telefonnummer"):
                        telefon = None
                elif telefon == "Lägg till telefonnummer" or not telefon:
                    telefon = None
                
                # Prepare data for database
                db_data = {
                    'personnamn': record.get('personnamn'),
                    'alder': record.get('alder'),
                    'kon': record.get('kon'),
                    'gatuadress': record.get('gatuadress'),
                    'postnummer': record.get('postnummer'),
                    'postort': record.get('postort'),
                    'telefon': telefon,
                    'karta': record.get('karta'),
                    'link': record.get('link'),
                    'is_active': True,
                    'is_telefon': telefon is not None and len(telefon) > 0 if isinstance(telefon, list) else False,
                    'is_ratsit': False,
                }
                
                # Send to API
                headers = {'Content-Type': 'application/json'}
                if self.api_token:
                    headers['Authorization'] = f'Bearer {self.api_token}'
                
                response = requests.post(
                    f"{self.api_url}/api/hitta-se",
                    json=db_data,
                    headers=headers,
                    timeout=10
                )
                
                if response.status_code in [200, 201]:
                    saved_count += 1
                else:
                    print(f"  ⚠ Failed to save {record.get('personnamn')}: {response.status_code}")
                    
            except Exception as e:
                print(f"  ⚠ Error saving {record.get('personnamn')}: {e}")
                continue
        
        print(f"\n✓ Saved {saved_count}/{len(self.results)} records to database")
        return saved_count

    def write_csv(self, filename: Path, data: List[Dict[str, str]]):
        """
        Write data to CSV file without headers

        Args:
            filename: Path to CSV file
            data: List of person data dictionaries
        """
        fieldnames = ['personnamn', 'alder', 'kon', 'gatuadress', 'postnummer', 
                      'postort', 'telefon', 'karta', 'link']
        
        with open(filename, 'w', newline='', encoding='utf-8') as csvfile:
            writer = csv.DictWriter(csvfile, fieldnames=fieldnames)
            # Do not write header as per requirements
            for row in data:
                # Convert telefon arrays to a single string for CSV output
                out = dict(row)
                tel = out.get('telefon')
                if isinstance(tel, list):
                    out['telefon'] = ' | '.join(tel)
                writer.writerow(out)


def main():
    """Main function"""
    parser = argparse.ArgumentParser(description='Scrape person data from hitta.se')
    parser.add_argument('query', type=str, help='Search query')
    parser.add_argument('--no-missing', action='store_true', 
                        help='Do not create separate CSV for missing phone numbers')
    parser.add_argument('--no-db', action='store_true',
                        help='Do not save to database')
    parser.add_argument('--api-url', type=str,
                        help='Laravel API URL (default: http://localhost:8000)')
    parser.add_argument('--api-token', type=str,
                        help='API authentication token')
    
    args = parser.parse_args()

    scraper = HittaSeScraper(api_url=args.api_url, api_token=args.api_token)
    
    # Scrape results
    results = scraper.scrape_search_results(args.query)
    
    if results:
        print(f"\nTotal results found: {len(results)}")
        
        # Save to CSV (include missing phone CSV by default)
        scraper.save_to_csv(args.query, include_phone_missing=not args.no_missing)
        
        # Save to database unless --no-db flag is set
        if not args.no_db:
            print("\nSaving to database...")
            scraper.save_to_database()
    else:
        print("No results found")


if __name__ == '__main__':
    main()
