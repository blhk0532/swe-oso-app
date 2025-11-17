#!/usr/bin/env node

/**
 * Hitta.se scraper script
 * Scrapes person data from hitta.se
 * Saves data to hitta_data table
 */

import { program } from 'commander';
import { promises as fs } from 'fs';
import path from 'path';
import { URL } from 'url';
import { chromium } from 'playwright';
import { spawn } from 'child_process';
import Database from 'better-sqlite3';
import axios from 'axios';

class HittaScraper {
  constructor(api_url, api_token) {
    this.api_url = api_url || process.env.LARAVEL_API_URL || 'http://localhost:8000';
    this.api_token = api_token || process.env.LARAVEL_API_TOKEN;

    this.data_dir = path.join(process.cwd(), 'scripts', 'data');
    this.results = [];
    this.base_url = 'https://www.hitta.se';

    // SQLite database connection - go up one level from scripts/ to project root
    this.db = null;
    this.dbPath = path.join(path.dirname(process.cwd()), 'database', 'database.sqlite');

    // Ensure data directory exists
    fs.mkdir(this.data_dir, { recursive: true }).catch(() => {});
    // Ensure database directory exists
    fs.mkdir(path.dirname(this.dbPath), { recursive: true }).catch(() => {});
  }

  getDbConnection() {
    /** Get or create SQLite database connection */
    if (this.db) {
      return this.db;
    }

    // Create SQLite database connection
    this.db = new Database(this.dbPath);
    
    // Enable foreign keys and WAL mode for better performance
    this.db.pragma('journal_mode = WAL');
    this.db.pragma('foreign_keys = ON');
    
    // Table already exists in Laravel's database.sqlite - no need to create
    console.log(`✓ Connected to SQLite database: ${this.dbPath}`);
    return this.db;
  }

  async saveHittaToDatabase(hittaData) {
    /**
     * Save Hitta data via API
     */
    try {
      const apiData = {
        personnamn: hittaData.personnamn || null,
        alder: hittaData.alder || null,
        kon: hittaData.kon || null,
        gatuadress: hittaData.gatuadress || null,
        postnummer: hittaData.postnummer || null,
        postort: hittaData.postort || null,
        telefon: Array.isArray(hittaData.telefon) ? hittaData.telefon.join(' | ') : null,
        karta: hittaData.karta || null,
        link: hittaData.link || null,
        bostadstyp: hittaData.bostadstyp || null,
        bostadspris: hittaData.bostadspris || null,
        is_active: true,
        is_telefon: Array.isArray(hittaData.telefon) && hittaData.telefon.length > 0,
        is_ratsit: false,
      };

      // Use API to save
      const response = await axios.post(`${this.api_url}/api/hitta-data`, apiData, {
        headers: {
          'Authorization': this.api_token ? `Bearer ${this.api_token}` : undefined,
          'Content-Type': 'application/json',
        },
      });

      console.log(`  ✓ Saved ${hittaData.personnamn} via API:`, response.data);
      return true;
    } catch (error) {
      console.log(`  ✗ Error saving ${hittaData.personnamn} via API:`, error.response?.data || error.message);
      return false;
    }
  }

  saveHittaToDataTable(hittaData) {
    /**
     * Save Hitta data to hitta_data table (only if has telefon and kon)
     * Uses the same structure as hitta_se table
     */
    try {
      const db = this.getDbConnection();
      
      const dbData = {
        personnamn: hittaData.personnamn || null,
        alder: hittaData.alder || null,
        kon: hittaData.kon || null,
        gatuadress: hittaData.gatuadress || null,
        postnummer: hittaData.postnummer || null,
        postort: hittaData.postort || null,
        telefon: Array.isArray(hittaData.telefon) ? JSON.stringify(hittaData.telefon) : '[]',
        karta: hittaData.karta || null,
        link: hittaData.link || null,
        bostadstyp: hittaData.bostadstyp || null,
        bostadspris: hittaData.bostadspris || null,
        is_active: 1,
        is_telefon: Array.isArray(hittaData.telefon) && hittaData.telefon.length > 0 ? 1 : 0,
        is_ratsit: 0,
      };

      // Check if record exists based on personnamn, gatuadress, and telefon
      const checkStmt = db.prepare(`
        SELECT id FROM hitta_data 
        WHERE personnamn = ? AND gatuadress = ? AND telefon = ?
      `);
      const existing = checkStmt.get(
        dbData.personnamn,
        dbData.gatuadress,
        dbData.telefon
      );

      let result;
      let action;

      if (existing) {
        const updateFields = Object.keys(dbData).map(f => `${f} = ?`).join(', ');
        const updateStmt = db.prepare(`
          UPDATE hitta_data 
          SET ${updateFields}, updated_at = datetime('now')
          WHERE id = ?
        `);
        result = updateStmt.run(...Object.values(dbData), existing.id);
        action = 'updated';
      } else {
        const fields = Object.keys(dbData);
        const placeholders = fields.map(() => '?').join(', ');
        const insertStmt = db.prepare(`
          INSERT INTO hitta_data (${fields.join(', ')}, created_at, updated_at)
          VALUES (${placeholders}, datetime('now'), datetime('now'))
        `);
        result = insertStmt.run(...Object.values(dbData));
        action = 'created';
      }
      
      if (result.changes > 0) {
        console.log(`  ✓ Hitta data also saved to hitta_data table (${action})`);
        return true;
      } else {
        console.log(`  ⚠ No changes made to hitta_data table`);
        return false;
      }
      
    } catch (error) {
      console.log('  ✗ Error saving Hitta data to hitta_data table:', error.message);
      return false;
    }
  }

  closeDbConnection() {
    /** Close SQLite database connection */
    if (this.db) {
      this.db.close();
      this.db = null;
    }
  }


  async scrapeSearchResults(query, maxResults = 50, startPage = 0, startIndex = 0) {
    this.results = [];
    const encodedQuery = encodeURIComponent(query);
    const searchUrl = `${this.base_url}/s%C3%B6k?vad=${encodedQuery}&typ=prv`;
    
    console.log(`Searching for: ${query} (max ${maxResults} results)`);
    console.log(`URL: ${searchUrl}`);
    
    if (startPage > 0 || startIndex > 0) {
      console.log(`Resume mode: starting from page ${startPage || 1}, index ${startIndex}`);
    }

    let browser = null;
    let currentPage = startPage > 0 ? startPage : 1;
    let totalPages = null;
    let hasMorePages = true;
    let globalIndex = startIndex; // Track global item count for resume
    
    try {
      // Launch browser
      browser = await chromium.launch({
        headless: true,
        executablePath: '/usr/bin/google-chrome',
        args: [
          '--no-sandbox',
          '--disable-dev-shm-usage',
          '--disable-gpu',
          '--window-size=1920,1080',
          '--user-agent=Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ]
      });

      const context = await browser.newContext();
      const page = await context.newPage();
      
  while (hasMorePages) {
        const pageUrl = currentPage === 1 ? searchUrl : `${searchUrl}&sida=${currentPage}`;
        console.log(`\nPage ${currentPage}: ${pageUrl}`);
        
        await page.goto(pageUrl);
        
        // Wait for search results to load
        try {
          await page.waitForSelector('li[data-test="person-item"]', { timeout: 10000 });
        } catch (error) {
          console.log('No results found or timeout waiting for results');
          break;
        }

        // Try to dismiss cookie/consent overlays (only on first page)
        if (currentPage === 1) {
          try {
            await this.dismissConsentOverlay(page);
          } catch (error) {
            // Ignore consent overlay errors
          }
        }

        // Get total results count on first page
        if (currentPage === 1) {
          try {
            const countElement = await page.$('span[data-test="search-results-count"]');
            if (countElement) {
              const countText = await countElement.textContent();
              const totalMatch = countText.match(/\d+/);
              if (totalMatch) {
                const totalResults = parseInt(totalMatch[0]);
                totalPages = Math.ceil(totalResults / 25); // 25 results per page
                console.log(`Total results: ${totalResults}`);
                console.log(`Total pages: ${totalPages}`);
              }
            }
          } catch (error) {
            console.log(`Could not determine total results: ${error}`);
          }
        }

        // Extract all person items on current page
        const personItems = await page.$$('li[data-test="person-item"]');
        const pageTotal = personItems.length;
        console.log(`Found ${pageTotal} results on page ${currentPage}`);

        const pageResults = [];
        let pendingToSave = [];
        
        // Determine starting index for this page
        let startIdx = 0;
        if (currentPage === startPage && startIndex > 0) {
          // Calculate items per page (assuming 25 per page)
          const itemsPerPage = 25;
          startIdx = startIndex % itemsPerPage;
          console.log(`Resuming from item ${startIdx + 1} on page ${currentPage}`);
        }
        
        for (let i = startIdx; i < pageTotal; i++) {
          try {
            // Re-query items each iteration to avoid stale references
            const currentItems = await page.$$('li[data-test="person-item"]');
            if (i >= currentItems.length) break;
            
            const item = currentItems[i];
            const idx = i + 1;
            const personData = await this.extractPersonData(item, page);
            
            if (personData) {
              pageResults.push(personData);
              this.results.push(personData);
              pendingToSave.push(personData);
              globalIndex++; // Increment global counter
              console.log(`[Page ${currentPage}] Extracted ${idx}/${pageTotal}: ${personData.personnamn || 'Unknown'}`);
              
              // If we have a phone number, flush pending results to DB
              const hasPhone = Array.isArray(personData.telefon)
                ? personData.telefon.some((n) => n && !String(n).includes('Lägg till telefonnummer'))
                : (typeof personData.telefon === 'string' ? personData.telefon && !personData.telefon.includes('Lägg till telefonnummer') : false);

              if (hasPhone) {
                try {
                  console.log(`  → Phone found for ${personData.personnamn}. Flushing ${pendingToSave.length} pending record(s) to DB...`);
                  await this.saveToDatabase(pendingToSave);
                  pendingToSave = [];
                } catch (e) {
                  console.log('  → Error flushing pending results to DB:', e);
                }
              }
            }
          } catch (error) {
            console.log(`Error extracting person ${i + 1}:`, error);
            continue;
          }
        }

        // After finishing this page: save any remaining pending results to DB
        if (pendingToSave.length > 0) {
          try {
            console.log(`\n→ Saving remaining ${pendingToSave.length} pending record(s) for page ${currentPage} to database...`);
            await this.saveToDatabase(pendingToSave);
            pendingToSave = [];
          } catch (e) {
            console.log(`→ Error saving remaining pending records for page ${currentPage}:`, e);
          }
        }

        // Check if we've reached max results
        if (this.results.length >= maxResults) {
          console.log(`\n→ Reached max results limit (${maxResults})`);
          hasMorePages = false;
        }
        // Check if there are more pages
        else if (pageTotal === 0) {
          console.log('\n→ No results on this page, stopping');
          hasMorePages = false;
        } else if (totalPages && currentPage < totalPages) {
          console.log(`\n→ Moving to page ${currentPage + 1} of ${totalPages}`);
          currentPage += 1;
          await page.waitForTimeout(1000); // Brief pause between pages
        } else if (!totalPages) {
          // If we don't know total pages, check for next button
          try {
            const nextButton = await page.$('button[data-ga4-action="next_page"]');
            if (nextButton && await nextButton.isVisible() && await nextButton.isEnabled()) {
              console.log(`\n→ Next page button found, moving to page ${currentPage + 1}`);
              currentPage += 1;
              await page.waitForTimeout(1000);
            } else {
              console.log('\n→ No more pages (next button not available)');
              hasMorePages = false;
            }
          } catch (error) {
            console.log('\n→ No more pages (next button not found)');
            hasMorePages = false;
          }
        } else {
          console.log(`\n→ Reached final page (${currentPage} of ${totalPages})`);
          hasMorePages = false;
        }
      }

    } catch (error) {
      console.log('Error during scraping:', error);
    } finally {
      if (browser) {
        await browser.close();
      }
    }

    return this.results;
  }

  async extractPersonData(item, page) {
    const data = {
      personnamn: null,
      alder: null,
      kon: null,
      gatuadress: null,
      postnummer: null,
      postort: null,
      telefon: null,
      karta: null,
      link: null,
      bostadstyp: null,
      bostadspris: null,
    };

    try {
      // Extract name and age from h2 title
      try {
        const title = await item.$('h2[data-test="search-result-title"]');
        if (title) {
          const titleText = await title.textContent();
          if (titleText) {
            // Age is in a span with class style_age__ZgTHo
            try {
              const ageSpan = await title.$('span.style_age__ZgTHo');
              if (ageSpan) {
                data.alder = (await ageSpan.textContent())?.trim() || null;
                // Remove age from title to get name
                data.personnamn = titleText.replace(data.alder || '', '').trim();
              } else {
                data.personnamn = titleText.trim();
              }
            } catch {
              data.personnamn = titleText.trim();
            }
          }
        }
      } catch {
        // Ignore errors
      }

      // Extract gender and address from paragraph
      try {
        const addressP = await item.$('p.text-body-long-sm-regular');
        if (addressP) {
          const addressText = await addressP.textContent();
          if (addressText) {
            // First try to extract gender from span
            try {
              const genderSpan = await addressP.$('span.style_gender__hKSL0');
              if (genderSpan) {
                data.kon = (await genderSpan.textContent())?.trim();
              }
            } catch {
              // Ignore
            }

            // Parse the address text - format is now single-line: "KvinnaKällvägen 4153 32 Järna"
            // Swedish postal codes are always 5 digits formatted as "XXX XX"
            // The format is: Gender + StreetAddress (no space) + PostalCode (XXX XX) + City
            // Strategy: Find the LAST occurrence of pattern "3digits 2digits" as postal code
            const postalMatch = addressText.match(/(\d{3})\s+(\d{2})\s+([^\d]+)$/);
            
            if (postalMatch) {
              // Postal code found
              data.postnummer = `${postalMatch[1]} ${postalMatch[2]}`.trim();
              data.postort = postalMatch[3].trim();
              
              // Everything before the postal code is gender + street
              const beforePostal = addressText.substring(0, addressText.indexOf(postalMatch[0]));
              
              // Extract gender from the beginning
              const genderMatch = beforePostal.match(/^(Kvinna|Man|Kvinno)/);
              if (genderMatch && !data.kon) {
                data.kon = genderMatch[1];
              }
              
              // Street is everything after gender
              const streetStart = genderMatch ? genderMatch[0].length : 0;
              const street = beforePostal.substring(streetStart).trim();
              if (street) {
                data.gatuadress = street;
              }
            } else {
              // No postal code pattern found - try legacy fallback for multi-line format
              const addressLines = addressText.split('\n').map(l => l.trim()).filter(l => l);
              if (addressLines.length >= 3) {
                if (!data.kon && addressLines[0]) {
                  data.kon = addressLines[0];
                }
                if (addressLines[1]) {
                  data.gatuadress = addressLines[1];
                }
                if (addressLines[2]) {
                  const parts = addressLines[2].split(' ').filter(p => p);
                  if (parts.length >= 2) {
                    data.postnummer = `${parts[0]} ${parts[1]}`.trim();
                    if (parts.length >= 3) {
                      data.postort = parts.slice(2).join(' ').trim();
                    }
                  }
                }
              }
            }
          }
        }
      } catch {
        // Ignore errors
      }

      // Extract map link
      try {
        const mapLink = await item.$('a[data-test="show-on-map-button"]');
        if (mapLink) {
          const href = await mapLink.getAttribute('href');
          if (href) {
            data.karta = href.startsWith('/') ? `${this.base_url}${href}` : href;
          }
        }
      } catch {
        // Ignore errors
      }

      // Extract profile link
      try {
        const profileLink = await item.$('a[data-test="search-list-link"]');
        if (profileLink) {
          const href = await profileLink.getAttribute('href');
          if (href) {
            data.link = href.startsWith('/') ? `${this.base_url}${href}` : href;
          }
        }
      } catch {
        // Ignore errors
      }

      // Extract phone number - click button to reveal full number
      try {
        const phoneButton = await item.$('button[data-test="phone-link"]');
        if (phoneButton) {
          const phoneText = await phoneButton.textContent();
          
          if (phoneText && !phoneText.includes('Lägg till telefonnummer')) {
            try {
              // Ensure element is in view
              await phoneButton.scrollIntoViewIfNeeded();
              await phoneButton.waitForElementState('stable', { timeout: 5000 });

              const currentUrl = page.url();
              
              try {
                await phoneButton.click();
              } catch {
                // Attempt to close consent overlay then retry
                await this.dismissConsentOverlay(page);
                try {
                  await phoneButton.click();
                } catch {
                  // Fallback: force click via JS
                  await page.evaluate((el) => el.click(), phoneButton);
                }
              }

              // Wait briefly for potential navigation or reveal
              await page.waitForTimeout(800);

              // Check if URL changed (redirect)
              const newUrl = page.url();
              if (newUrl !== currentUrl && newUrl.includes('revealNumber')) {
                // Extract the full phone number from URL
                const urlObj = new URL(newUrl);
                const firstPhone = urlObj.searchParams.get('revealNumber');

                const numbers = [];
                try {
                  await page.waitForSelector('button[data-test="show-number"] span', { timeout: 5000 });
                  const spans = await page.$$('button[data-test="show-number"] span');
                  for (const sp of spans) {
                    const txt = await sp.textContent();
                    if (txt) {
                      numbers.push(txt.trim());
                    }
                  }
                } catch {
                  // Ignore errors
                }

                // If none found via spans, fallback to the revealNumber param
                if (numbers.length === 0 && firstPhone) {
                  numbers.push(firstPhone);
                }

                // De-duplicate while preserving order
                const seen = new Set();
                const deduped = [];
                for (const n of numbers) {
                  if (!seen.has(n)) {
                    seen.add(n);
                    deduped.push(n);
                  }
                }

                data.telefon = deduped;
                if (deduped.length > 0) {
                  console.log(`  → Revealed phone(s): ${deduped.join(', ')}`);
                }

                // Extract house type and price information from person-intro-section
                try {
                  const introSpan = await page.$('span[data-test="person-intro-section"]');
                  if (introSpan) {
                    const introText = await introSpan.textContent();
                    
                    // Check if "Huset" or "en villa" appears in the text
                    if (introText && (introText.includes('Huset') || introText.includes('en villa'))) {
                      data.bostadstyp = 'Hus';
                      
                      // Extract price range (format: "2 800 000 – 4 200 000 kr" or similar)
                      // Pattern matches numbers with spaces and range indicators
                      const pricePattern = /(\d[\d\s]*\d)\s*[–-]\s*(\d[\d\s]*\d)\s*kr/;
                      const priceMatch = introText.match(pricePattern);
                      if (priceMatch) {
                        // Get the matched price range and clean it up
                        const minPrice = priceMatch[1].replace(/\s+/g, ' '); // Normalize spaces
                        const maxPrice = priceMatch[2].replace(/\s+/g, ' ');
                        data.bostadspris = `${minPrice} – ${maxPrice} kr`;
                        console.log(`  → House info: ${data.bostadstyp}, Price: ${data.bostadspris}`);
                      }
                    }
                  }
                } catch (error) {
                  console.log(`  → Error extracting house info:`, error);
                }

                // Navigate back to search results
                await page.goBack();
                // Wait for results list to be available again
                await page.waitForSelector('li[data-test="person-item"]', { timeout: 10000 });
                await page.waitForTimeout(200);
              } else {
                // No redirect, try to extract from updated button text
                try {
                  const freshButton = await item.$('button[data-test="phone-link"]');
                  const freshText = freshButton ? await freshButton.textContent() : phoneText;
                  const phoneMatches = freshText?.match(/(\+?\d[\d\s-]{7,})/g);
                  if (phoneMatches) {
                    data.telefon = phoneMatches.map(m => m.trim());
                  }
                } catch {
                  // Ignore errors
                }
              }
            } catch (error) {
              console.log(`  → Error clicking phone button:`, error);
              // Fallback to extracting from text
              const phoneMatches = phoneText?.match(/(\+?\d[\d\s-]{7,})/g);
              if (phoneMatches) {
                data.telefon = phoneMatches.map(m => m.trim());
              }
            }
          } else {
            data.telefon = [];
          }
        }
      } catch {
        // Ignore errors
      }

    } catch (error) {
      console.log('Error extracting data:', error);
      return null;
    }

    // Map kon to valid values
    const konMap = { 'man': 'M', 'kvinna': 'F', 'kvinno': 'F' };
    if (data.kon) {
      data.kon = konMap[data.kon.toLowerCase()] || data.kon;
    }

    return data;
  }

  async dismissConsentOverlay(page) {
    try {
      // Try to find and click consent buttons
      const selectors = [
        'button:has-text("Godkänn")',
        'button:has-text("Acceptera")',
        'button:has-text("OK")',
        'button:has-text("Jag förstår")',
        'button[data-test="uc-accept-all-button"]',
        'button[aria-label*="Godkänn"]'
      ];

      for (const selector of selectors) {
        try {
          const buttons = await page.$$(selector);
          for (const button of buttons) {
            if (await button.isVisible()) {
              await page.evaluate((el) => {
                if (el.click) el.click();
              }, button);
              await page.waitForTimeout(200);
              return;
            }
          }
        } catch {
          // Continue trying other selectors
        }
      }

      // As a last resort, hide overlays via JS
      await page.evaluate(() => {
        document.querySelectorAll('.gravitoCMP-background-overlay, .gravitoCMP, [class*="consent"]').forEach(e => {
          e.style.display = 'none';
        });
      });
      await page.waitForTimeout(100);
    } catch {
      // Non-fatal; proceed regardless
    }
  }

  async enrichAddressFromProfile(page, personData) {
    if (!personData.link) {
      return false;
    }
    let detailPage = null;
    try {
      const context = page.context();
      detailPage = await context.newPage();
      await detailPage.goto(personData.link, { waitUntil: 'domcontentloaded', timeout: 20000 });

      try { await this.dismissConsentOverlay(detailPage); } catch {}

      // Try to locate explicit address lines first
      // Heuristic approach: find two consecutive lines where second line matches Swedish postnummer + postort
      const bodyText = await detailPage.evaluate(() => document.body.innerText || '');
      const lines = bodyText.split('\n').map(l => l.trim()).filter(Boolean);

      let foundStreet = null;
      let foundZip = null;
      let foundCity = null;

      for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        const m = line.match(/^(\d{3})\s?(\d{2})\s+(.+)$/);
        if (m) {
          foundZip = `${m[1]} ${m[2]}`;
          foundCity = m[3].trim();
          // Prefer the previous non-empty line as street address
          if (i > 0) {
            const prev = lines[i - 1];
            if (prev && prev.length > 0 && !prev.match(/^Telefon|^Ålder|^Födelsedag/i)) {
              foundStreet = prev;
            }
          }
          break;
        }
      }

      let updated = false;
      if (foundStreet && !personData.gatuadress) { personData.gatuadress = foundStreet; updated = true; }
      if (foundZip && !personData.postnummer) { personData.postnummer = foundZip; updated = true; }
      if (foundCity && !personData.postort) { personData.postort = foundCity; updated = true; }

      await detailPage.close();
      return updated && !!(personData.gatuadress && personData.postort);
    } catch (e) {
      try { if (detailPage) await detailPage.close(); } catch {}
      return false;
    }
  }

  async saveToCsv(query, includePhoneMissing = false) {
    if (this.results.length === 0) {
      console.log('No results to save');
      return;
    }

    const total = this.results.length;
    const safeQuery = query.replace(/[^\w\s-]/g, '').trim().replace(/\s+/g, '_');

    // Save all results
    const allFilename = path.join(this.data_dir, `hitta_${safeQuery}_alla_${total}.csv`);
    await this.writeCsv(allFilename, this.results);
    console.log(`Saved all results to: ${allFilename}`);

    // Save results with phone numbers (not missing)
    if (includePhoneMissing) {
      const withPhone = this.results.filter(r => 
        r.telefon && r.telefon.length > 0 && !r.telefon.includes('Lägg till telefonnummer')
      );
      
      if (withPhone.length > 0) {
        const withPhoneTotal = withPhone.length;
        const withPhoneFilename = path.join(this.data_dir, `hitta_${safeQuery}_visa_${withPhoneTotal}.csv`);
        await this.writeCsv(withPhoneFilename, withPhone);
        console.log(`Saved ${withPhoneTotal} results with phone numbers to: ${withPhoneFilename}`);
      }
    }
  }

  async saveToDatabase(records = this.results) {
    /**
     * Save Hitta search results to hitta_data table
     */
    if (!records || records.length === 0) {
      console.log('No results to save to database');
      return 0;
    }

    let savedCount = 0;

    for (const record of records) {
      try {
        // Save to hitta_data table
        const success = await this.saveHittaToDatabase(record);
        if (success) {
          savedCount++;
        }
      } catch (error) {
        console.log(`  ⚠ Error saving ${record.personnamn}:`, error.message);
        continue;
      }
    }

    console.log(`\n✓ Saved ${savedCount}/${records.length} records to hitta_data table`);
    return savedCount;
  }

  async writeCsv(filename, data) {
    const fieldnames = ['personnamn', 'alder', 'kon', 'gatuadress', 'postnummer', 
                       'postort', 'telefon', 'karta', 'link', 'bostadstyp', 'bostadspris'];
    
    let csv = '';
    
    for (const row of data) {
      const values = fieldnames.map(field => {
        let value = row[field];
        
        // Convert telefon arrays to a single string for CSV output
        if (field === 'telefon' && Array.isArray(value)) {
          value = value.join(' | ');
        }
        
        // Handle null/undefined values
        if (value === null || value === undefined) {
          return '';
        }
        
        // Escape quotes and wrap in quotes if contains comma, newline, or quotes
        const strValue = String(value);
        if (strValue.includes(',') || strValue.includes('"') || strValue.includes('\n')) {
          return `"${strValue.replace(/"/g, '""')}"`;
        }
        return strValue;
      });
      
      csv += values.join(',') + '\n';
    }
    
    await fs.writeFile(filename, csv, 'utf-8');
  }
}

// Main function
async function main() {
  program
    .description('Scrape person data from hitta.se')
    .argument('query', 'Search query')
    .option('--no-missing', 'Do not create separate CSV for missing phone numbers')
    .option('--no-db', 'Do not save to database')
    .option('--api-url <url>', 'Laravel API URL (default: http://localhost:8000)')
    .option('--api-token <token>', 'API authentication token')
    .option('--startPage <page>', 'Start from this page (for resume)', '0')
    .option('--startIndex <index>', 'Start from this item index (for resume)', '0')
    .option('--onlyTotals', 'Only fetch total results count and exit')
    .parse();

  const options = program.opts();
  const query = program.args[0];
  const startPage = parseInt(options.startPage) || 0;
  const startIndex = parseInt(options.startIndex) || 0;

  const scraper = new HittaScraper(options.apiUrl, options.apiToken);

  try {
    if (options.onlyTotals) {
      // Lightweight mode: load first page, output total results, exit
      const encodedQuery = encodeURIComponent(query);
      const searchUrl = `${scraper.base_url}/s%C3%B6k?vad=${encodedQuery}&typ=prv`;
      console.log(`Checking totals for: ${query}`);
      const browser = await chromium.launch({
        headless: true,
        executablePath: '/usr/bin/google-chrome',
        args: [
          '--no-sandbox',
          '--disable-dev-shm-usage',
          '--disable-gpu',
          '--window-size=1920,1080',
          '--user-agent=Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
        ]
      });
      const context = await browser.newContext();
      const page = await context.newPage();
      await page.goto(searchUrl);
      // Detect special case: "Ingen träff på" => no results at all
      try {
        await page.waitForLoadState('domcontentloaded', { timeout: 5000 });
        const noDirectResults = await page.evaluate(() => {
          try {
            const t = (document.body?.innerText || '').toLowerCase();
            return t.includes('ingen träff på');
          } catch { return false; }
        });
        if (noDirectResults) {
          console.log('NO_DIRECT_RESULTS=1');
          console.log('Total results: 0');
          await browser.close();
          return; // Exit early so caller can handle this condition
        }
      } catch {}
      try {
        await page.waitForSelector('span[data-test="search-results-count"]', { timeout: 10000 });
        const countElement = await page.$('span[data-test="search-results-count"]');
        let totalResults = null;
        if (countElement) {
          const countText = await countElement.textContent();
          const totalMatch = countText.match(/\d+/);
          if (totalMatch) {
            totalResults = parseInt(totalMatch[0]);
          }
        }
        if (totalResults !== null) {
          console.log(`Total results: ${totalResults}`);
        } else {
          console.log('Total results: 0');
        }
      } catch (e) {
        console.log('Total results: 0');
      }
      await browser.close();
      return; // Exit early
    }
    // Scrape results (limit to 1000 for testing)
    const results = await scraper.scrapeSearchResults(query, 1000, startPage, startIndex);

    if (results.length > 0) {
      console.log(`\nTotal results found: ${results.length}`);

      // Aggregate phone & house counts
      let phoneCount = 0;
      let houseCount = 0;
      let bolagCount = 0;
      for (const r of results) {
        // Phone: count entries having at least one phone number array (ps_telefon or telefon)
        const phones = Array.isArray(r.ps_telefon) ? r.ps_telefon : (Array.isArray(r.telefon) ? r.telefon : []);
        if (phones && phones.length > 0) {
          phoneCount += 1;
        }
        // House: check bostadstyp for house types (hus, villa, radhus, friliggande, kedjehus)
        const type = (r.bo_bostadstyp || r.bostadstyp || '').toLowerCase();
        if (type.match(/\bhus\b|villa|radhus|friliggande|kedjehus/)) {
          houseCount += 1;
        }
        // Bolag: count entries that will be saved to hitta_bolag (companies without kon)
        if (!r.kon) {
          bolagCount += 1;
        }
      }
      console.log(`Phones: ${phoneCount}`);
      console.log(`Houses: ${houseCount}`);
      console.log(`Bolag: ${bolagCount}`);

      // Save to CSV (include missing phone CSV by default)
      await scraper.saveToCsv(query, !options.noMissing);

      // Save to database unless --no-db flag is set
      if (!options.noDb) {
        console.log('\nSaving to database...');
        await scraper.saveToDatabase();
      }
    } else {
      console.log('No results found');
      console.log('Phones: 0');
      console.log('Houses: 0');
      console.log('Bolag: 0');
    }
  } finally {
    // Always close database connection
    scraper.closeDbConnection();
  }
}

// Run main function
main().catch(error => {
  console.error('Error:', error);
  process.exit(1);
});