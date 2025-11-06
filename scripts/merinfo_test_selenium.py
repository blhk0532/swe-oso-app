#!/usr/bin/env python3
"""
Merinfo.se test using Selenium + undetected-chromedriver
Opens homepage, fills input.search-field-input, submits, waits for results.
"""

import argparse
import json
import os
from pathlib import Path
from time import sleep

import undetected_chromedriver as uc
from selenium.webdriver.common.by import By
from selenium.webdriver.common.keys import Keys
from selenium.webdriver.support.ui import WebDriverWait
from selenium.webdriver.support import expected_conditions as EC
from selenium.common.exceptions import TimeoutException, NoSuchElementException


def run(query: str, profile_dir: str | None = None, headless: bool = False):
    # Configure Chrome options
    options = uc.ChromeOptions()
    options.add_argument("--no-sandbox")
    options.add_argument("--disable-dev-shm-usage")
    options.add_argument("--disable-blink-features=AutomationControlled")
    options.add_argument("--disable-web-security")
    options.add_argument("--disable-features=IsolateOrigins,site-per-process")
    options.add_argument("--lang=sv-SE")

    if profile_dir:
        Path(profile_dir).mkdir(parents=True, exist_ok=True)
        options.add_argument(f"--user-data-dir={profile_dir}")

    if headless:
        options.add_argument("--headless=new")

    driver = uc.Chrome(options=options)

    try:
        wait = WebDriverWait(driver, 60)

        # 1) Go to homepage
        print("Step 1: Navigating to merinfo.se homepage ...")
        driver.get("https://www.merinfo.se/")

        # If Cloudflare shows up, allow time and user intervention (browser is visible by default)
        try:
            wait.until(lambda d: "Vänta" not in d.title and "Just a moment" not in d.title)
        except TimeoutException:
            print("  Cloudflare wait exceeded. If a challenge is visible, solve it manually in the browser.")
            input("  Press Enter here when the page is loaded...")

        print(f"  Page title: {driver.title}")

        # 2) Find search input and type query
        print("Step 2: Filling search input ...")
        try:
            search = wait.until(EC.presence_of_element_located((By.CSS_SELECTOR, "input.search-field-input")))
        except TimeoutException:
            # Try an alternative known search field selector
            try:
                search = driver.find_element(By.CSS_SELECTOR, "input[name='q']")
            except NoSuchElementException:
                raise TimeoutException("Search input not found")

        search.click()
        sleep(0.3)
        search.clear()
        for ch in query:
            search.send_keys(ch)
            sleep(0.05)
        sleep(0.3)
        search.send_keys(Keys.ENTER)

        # 3) Wait for results page
        print("Step 3: Waiting for results page ...")
        try:
            wait.until(EC.url_contains("/search?q="))
            print(f"  ✓ Results page: {driver.current_url}")
        except TimeoutException:
            print(f"  ✗ Did not navigate to results. Current URL: {driver.current_url}")

        # Save a screenshot for verification
        out_dir = Path(__file__).parent / "data"
        out_dir.mkdir(exist_ok=True)
        shot = out_dir / "selenium_results.png"
        driver.save_screenshot(str(shot))
        print(f"  Screenshot saved: {shot}")

    finally:
        print("Done. Close the browser window if it's still open.")
        # Keep the browser open if not headless so the user can see
        if headless:
            driver.quit()


if __name__ == "__main__":
    parser = argparse.ArgumentParser(description="Merinfo.se test via undetected-chromedriver")
    parser.add_argument("query", help="Search query, e.g. '733 32 Sala'")
    parser.add_argument("--profile-dir", help="Chrome user data dir to persist session", default=None)
    parser.add_argument("--headless", action="store_true", help="Run browser headless")

    args = parser.parse_args()
    run(args.query, profile_dir=args.profile_dir, headless=args.headless)
