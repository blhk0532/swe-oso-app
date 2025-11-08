#!/usr/bin/env node

/**
 * Hitta.se + Ratsit.se combined scraper script
 * Scrapes person data from hitta.se and runs ratsit.mjs for house owners
 */

import { program } from 'commander';
import { promises as fs } from 'fs';
import path from 'path';
import { URL } from 'url';
import { chromium } from 'playwright';
import { spawn } from 'child_process';
import mysql from 'mysql2/promise';

class HittaRatsitScraper {
  constructor(api_url, api_token) {
    this.api_url = api_url || process.env.LARAVEL_API_URL || 'http://localhost:8000';
    this.api_token = api_token || process.env.LARAVEL_API_TOKEN;
    
    this.data_dir = path.join(process.cwd(), 'scripts', 'data');
    this.results = [];
    this.base_url = 'https://www.hitta.se';
    
    // Database connection pool (will be created when needed)
    this.dbPool = null;
    
    // Ensure data directory exists
    fs.mkdir(this.data_dir, { recursive: true }).catch(() => {});
  }

  async getDbConnection() {
    /** Get or create database connection pool */
    if (this.dbPool) {
      return this.dbPool;
    }

    // Read database credentials from environment or use defaults
    this.dbPool = await mysql.createPool({
      host: process.env.DB_HOST || '127.0.0.1',
      port: process.env.DB_PORT || 3306,
      user: process.env.DB_USERNAME || 'root',
      password: process.env.DB_PASSWORD || 'bkkbkk',
      database: process.env.DB_DATABASE || 'laravel',
      waitForConnections: true,
      connectionLimit: 10,
      queueLimit: 0
    });

    return this.dbPool;
  }

  async saveRatsitToDatabase(ratsitData) {
    /**
     * Save Ratsit data directly to database without API call
     * This function handles the upsert logic for ratsit_data table
     */
    try {
      const pool = await this.getDbConnection();
      
      // Prepare data for database insertion
      const dbData = {
        ps_personnummer: ratsitData.ps_personnummer || null,
        ps_alder: ratsitData.ps_alder || null,
        ps_fodelsedag: ratsitData.ps_fodelsedag || null,
        ps_kon: ratsitData.ps_kon || null,
        ps_civilstand: ratsitData.ps_civilstand || null,
        ps_fornamn: ratsitData.ps_fornamn || null,
        ps_efternamn: ratsitData.ps_efternamn || null,
        ps_personnamn: ratsitData.ps_personnamn || null,
        ps_telefon: Array.isArray(ratsitData.ps_telefon) ? JSON.stringify(ratsitData.ps_telefon) : null,
        ps_epost_adress: ratsitData.ps_epost_adress ? JSON.stringify(ratsitData.ps_epost_adress) : null,
        ps_bolagsengagemang: ratsitData.ps_bolagsengagemang ? JSON.stringify(ratsitData.ps_bolagsengagemang) : null,
        bo_gatuadress: ratsitData.bo_gatuadress || null,
        bo_postnummer: ratsitData.bo_postnummer || null,
        bo_postort: ratsitData.bo_postort || null,
        bo_forsamling: ratsitData.bo_forsamling || null,
        bo_kommun: ratsitData.bo_kommun || null,
        bo_lan: ratsitData.bo_lan || null,
        bo_agandeform: ratsitData.bo_agandeform || null,
        bo_bostadstyp: ratsitData.bo_bostadstyp || null,
        bo_boarea: ratsitData.bo_boarea || null,
        bo_byggar: ratsitData.bo_byggar || null,
        bo_fastighet: ratsitData.bo_fastighet || null,
        bo_personer: ratsitData.bo_personer ? JSON.stringify(ratsitData.bo_personer) : null,
        bo_foretag: ratsitData.bo_foretag ? JSON.stringify(ratsitData.bo_foretag) : null,
        bo_grannar: ratsitData.bo_grannar ? JSON.stringify(ratsitData.bo_grannar) : null,
        bo_fordon: ratsitData.bo_fordon ? JSON.stringify(ratsitData.bo_fordon) : null,
        bo_hundar: ratsitData.bo_hundar ? JSON.stringify(ratsitData.bo_hundar) : null,
        bo_longitude: ratsitData.bo_longitude || null,
        bo_latitud: ratsitData.bo_latitud || null,
        is_active: true,
      };

      // Build the INSERT ... ON DUPLICATE KEY UPDATE query
      const fields = Object.keys(dbData);
      const placeholders = fields.map(() => '?').join(', ');
      const updates = fields
        .filter(f => f !== 'created_at') // Don't update created_at
        .map(f => `${f} = VALUES(${f})`)
        .join(', ');

      const query = `
        INSERT INTO ratsit_data (${fields.join(', ')}, created_at, updated_at)
        VALUES (${placeholders}, NOW(), NOW())
        ON DUPLICATE KEY UPDATE ${updates}, updated_at = NOW()
      `;

      const values = fields.map(f => dbData[f]);
      
      const [result] = await pool.execute(query, values);
      
      if (result.affectedRows > 0) {
        const action = result.insertId ? 'created' : 'updated';
        console.log(`  ✓ Ratsit data saved to database (${action})`);
        return true;
      } else {
        console.log('  ⚠ No changes made to database');
        return false;
      }
      
    } catch (error) {
      console.log('  ✗ Error saving Ratsit data to database:', error.message);
      return false;
    }
  }

  async closeDbConnection() {
    /** Close database connection pool */
    if (this.dbPool) {
      await this.dbPool.end();
      this.dbPool = null;
    }
  }

  async scrapeRatsitData(query) {
    /**
     * Scrape Ratsit data directly (inline scraping without subprocess)
     * Returns array of person data objects
     */
    console.log(`  → Starting inline Ratsit scrape for: "${query}"`);
    
    const encodedQuery = encodeURIComponent(query);
    const searchUrl = `https://www.ratsit.se/sok/person?vem=${encodedQuery}`;
    
    let browser = null;
    const results = [];
    
    try {
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
      
      // Get search results
      await page.goto(searchUrl, { waitUntil: 'networkidle', timeout: 30000 });
      await page.waitForTimeout(2000);
      
      // Find all person links
      const links = [];
      const resultList = await page.$('ul.search-result-list');
      
      if (resultList) {
        const linkElements = await resultList.$$('li a[href^="https://www.ratsit.se/"]');
        for (const linkElement of linkElements) {
          const href = await linkElement.getAttribute('href');
          if (href && href.startsWith('https://www.ratsit.se/')) {
            links.push(href);
          }
        }
      }
      
      console.log(`  → Found ${links.length} Ratsit result(s)`);
      
      // Scrape each person page
      for (let i = 0; i < links.length; i++) {
        const link = links[i];
        console.log(`  → [${i + 1}/${links.length}] Scraping: ${link}`);
        
        try {
          await page.goto(link, { waitUntil: 'networkidle', timeout: 30000 });
          await page.waitForTimeout(2000);
          
          // Extract person data
          const personData = {
            ps_personnummer: await this.extractRatsitTextAfterLabel(page, 'Personnummer:'),
            ps_alder: await this.extractRatsitTextAfterLabel(page, 'Ålder:'),
            ps_fodelsedag: await this.extractRatsitTextAfterLabel(page, 'Födelsedag:'),
            ps_kon: await this.extractRatsitTextAfterLabel(page, 'Juridiskt kön:'),
            ps_telefon: await this.extractRatsitTelefon(page),
            ps_personnamn: await this.extractRatsitTextAfterLabel(page, 'Personnamn:'),
            ps_fornamn: await this.extractRatsitTextAfterLabel(page, 'Förnamn:'),
            ps_efternamn: await this.extractRatsitTextAfterLabel(page, 'Efternamn:'),
            bo_gatuadress: await this.extractRatsitTextAfterLabel(page, 'Gatuadress:'),
            bo_postnummer: await this.extractRatsitTextAfterLabel(page, 'Postnummer:'),
            bo_postort: await this.extractRatsitTextAfterLabel(page, 'Postort:'),
          };
          
          // Map gender value
          if (personData.ps_kon) {
            const konMap = { 'man': 'M', 'kvinna': 'F', 'kvinno': 'F' };
            personData.ps_kon = konMap[personData.ps_kon.toLowerCase()] || personData.ps_kon;
          }
          
          // Clean up empty values
          const cleanData = {};
          for (const [key, value] of Object.entries(personData)) {
            if (value !== null && value !== '') {
              cleanData[key] = value;
            }
          }
          
          if (Object.keys(cleanData).length > 0) {
            results.push(cleanData);
            console.log(`  → ✓ Extracted data for ${cleanData.ps_personnamn || 'Unknown'}`);
          }
          
          await page.waitForTimeout(1000);
          
        } catch (error) {
          console.log(`  → ✗ Error scraping ${link}:`, error.message);
        }
      }
      
      await browser.close();
      
    } catch (error) {
      console.log(`  → ✗ Error during Ratsit scraping:`, error.message);
      if (browser) {
        await browser.close();
      }
    }
    
    return results;
  }

  async extractRatsitTextAfterLabel(page, labelText) {
    /** Extract text value after a label span on Ratsit pages */
    try {
      const labelSelector = `span.color--gray5:has-text("${labelText}")`;
      const labelElement = await page.$(labelSelector);
      
      if (!labelElement) {
        return null;
      }
      
      const parentText = await labelElement.evaluate((el) => {
        const p = el.closest('p');
        return p ? p.innerText : null;
      });
      
      if (!parentText) {
        return null;
      }
      
      let text = parentText.replace(labelText, '').trim();
      text = text.replace(/\s*Visas för medlemmar.*/gi, '');
      
      return text || null;
    } catch (e) {
      return null;
    }
  }

  async extractRatsitTelefon(page) {
    /** Extract telefon number from href tel: link on Ratsit pages */
    try {
      const labelSelector = 'span.color--gray5:has-text("Telefon:")';
      const labelElement = await page.$(labelSelector);
      
      if (!labelElement) {
        return [];
      }
      
      const telHref = await labelElement.evaluate((el) => {
        const p = el.closest('p');
        if (!p) return null;
        const telLink = p.querySelector('a[href^="tel:"]');
        return telLink ? telLink.getAttribute('href') : null;
      });
      
      if (telHref && telHref.startsWith('tel:')) {
        return [telHref.replace('tel:', '')];
      }
      
      return [];
    } catch (e) {
      return [];
    }
  }

  async scrapeSearchResults(query, maxResults = 50) {
    this.results = [];
    const encodedQuery = encodeURIComponent(query);
    const searchUrl = `${this.base_url}/s%C3%B6k?vad=${encodedQuery}&typ=prv`;
    
    console.log(`Searching for: ${query} (max ${maxResults} results)`);
    console.log(`URL: ${searchUrl}`);

    let browser = null;
    let currentPage = 1;
    let totalPages = null;
    let hasMorePages = true;
    
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
        for (let i = 0; i < pageTotal; i++) {
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
              console.log(`[Page ${currentPage}] Extracted ${idx}/${pageTotal}: ${personData.personnamn || 'Unknown'}`);
              // If we have a phone number, flush pending results to DB, then try running ratsit for this person
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

                // Run ratsit immediately for this person if required args are present
                const hasFullAddress = !!(personData.personnamn && personData.gatuadress && personData.postort);
                if (hasFullAddress) {
                  console.log(`  → Running ratsit now for ${personData.personnamn}`);
                  await this.runRatsitForPerson(personData);
                } else {
                  console.log('  → Skipping ratsit (missing required fields for immediate run)');
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

    return data;
  }

  async runRatsitForPerson(personData) {
    /** Run inline Ratsit scraping for a specific person and save to database */
    try {
      // Validate that ALL required arguments are present
      if (!personData.personnamn || !personData.gatuadress || !personData.postort) {
        const missing = [];
        if (!personData.personnamn) missing.push('personnamn');
        if (!personData.gatuadress) missing.push('gatuadress');
        if (!personData.postort) missing.push('postort');
        console.log(`  → ⚠ Skipping ratsit - missing required fields: ${missing.join(', ')}`);
        return;
      }
      
      // Build search query for ratsit: "personnamn gatuadress postort"
      const ratsitQuery = `${personData.personnamn} ${personData.gatuadress} ${personData.postort}`;
      console.log(`  → Running inline Ratsit scrape: "${ratsitQuery}"`);
      
      // Scrape Ratsit data inline
      const ratsitResults = await this.scrapeRatsitData(ratsitQuery);
      
      // Save each result to database
      if (ratsitResults && ratsitResults.length > 0) {
        console.log(`  → Saving ${ratsitResults.length} Ratsit record(s) to database...`);
        
        for (const ratsitData of ratsitResults) {
          await this.saveRatsitToDatabase(ratsitData);
        }
        
        console.log(`  → ✓ Completed Ratsit processing for ${personData.personnamn}`);
      } else {
        console.log(`  → No Ratsit data found for ${personData.personnamn}`);
      }
      
    } catch (error) {
      console.log(`  → Error running ratsit for ${personData.personnamn}:`, error.message);
    }
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
    const allFilename = path.join(this.data_dir, `hitta_ratsit_${safeQuery}_alla_${total}.csv`);
    await this.writeCsv(allFilename, this.results);
    console.log(`Saved all results to: ${allFilename}`);

    // Save results with phone numbers (not missing)
    if (includePhoneMissing) {
      const withPhone = this.results.filter(r => 
        r.telefon && r.telefon.length > 0 && !r.telefon.includes('Lägg till telefonnummer')
      );
      
      if (withPhone.length > 0) {
        const withPhoneTotal = withPhone.length;
        const withPhoneFilename = path.join(this.data_dir, `hitta_ratsit_${safeQuery}_visa_${withPhoneTotal}.csv`);
        await this.writeCsv(withPhoneFilename, withPhone);
        console.log(`Saved ${withPhoneTotal} results with phone numbers to: ${withPhoneFilename}`);
      }
    }
  }

  async saveToDatabase(records = this.results) {
    if (!records || records.length === 0) {
      console.log('No results to save to database');
      return 0;
    }

    let savedCount = 0;

    for (const record of records) {
      try {
        // Handle phone numbers - convert to array or null
        let telefon = record.telefon;
        if (Array.isArray(telefon)) {
          // If list is empty or contains "Lägg till telefonnummer", set to null
          if (telefon.length === 0 || (telefon.length === 1 && telefon[0] === 'Lägg till telefonnummer')) {
            telefon = null;
          }
        } else if (telefon === 'Lägg till telefonnummer' || !telefon) {
          telefon = null;
        }

        // Prepare data for database
        const dbData = {
          personnamn: record.personnamn,
          alder: record.alder,
          kon: record.kon,
          gatuadress: record.gatuadress,
          postnummer: record.postnummer,
          postort: record.postort,
          telefon: telefon,
          karta: record.karta,
          link: record.link,
          bostadstyp: record.bostadstyp,
          bostadspris: record.bostadspris,
          is_active: true,
          is_telefon: telefon !== null && telefon.length > 0,
          is_ratsit: false,
        };

        // Send to API
        const headers = { 'Content-Type': 'application/json' };
        if (this.api_token) {
          headers['Authorization'] = `Bearer ${this.api_token}`;
        }

        const response = await fetch(`${this.api_url}/api/hitta-se`, {
          method: 'POST',
          headers,
          body: JSON.stringify(dbData),
        });

        if (response.status === 200 || response.status === 201) {
          savedCount++;
        } else {
          console.log(`  ⚠ Failed to save ${record.personnamn}: ${response.status}`);
        }

      } catch (error) {
        console.log(`  ⚠ Error saving ${record.personnamn}:`, error);
        continue;
      }
    }

    console.log(`\n✓ Saved ${savedCount}/${records.length} records to database`);
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
    .description('Scrape person data from hitta.se and run ratsit for house owners')
    .argument('query', 'Search query')
    .option('--no-missing', 'Do not create separate CSV for missing phone numbers')
    .option('--no-db', 'Do not save to database')
    .option('--api-url <url>', 'Laravel API URL (default: http://localhost:8000)')
    .option('--api-token <token>', 'API authentication token')
    .parse();

  const options = program.opts();
  const query = program.args[0];

  const scraper = new HittaRatsitScraper(options.apiUrl, options.apiToken);

  try {
    // Scrape results (limit to 10 for testing)
    const results = await scraper.scrapeSearchResults(query, 1000);

    if (results.length > 0) {
      console.log(`\nTotal results found: ${results.length}`);

      // Save to CSV (include missing phone CSV by default)
      await scraper.saveToCsv(query, !options.noMissing);

      // Save to database unless --no-db flag is set
      if (!options.noDb) {
        console.log('\nSaving to database...');
        await scraper.saveToDatabase();
      }
    } else {
      console.log('No results found');
    }
  } finally {
    // Always close database connection
    await scraper.closeDbConnection();
  }
}

// Run main function
main().catch(error => {
  console.error('Error:', error);
  process.exit(1);
});