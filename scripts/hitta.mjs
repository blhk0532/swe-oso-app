#!/usr/bin/env node

/**
 * Hitta.se scraper script (JavaScript ES Module version)
 * Scrapes person data from hitta.se and saves to CSV and database
 */

import { program } from 'commander';
import { promises as fs } from 'fs';
import path from 'path';
import { URL } from 'url';
import { chromium } from 'playwright';

class HittaSeScraper {
  constructor(api_url, api_token) {
    this.api_url = api_url || process.env.LARAVEL_API_URL || 'http://localhost:8000';
    this.api_token = api_token || process.env.LARAVEL_API_TOKEN;
    
    this.data_dir = path.join(process.cwd(), 'scripts', 'data');
    this.results = [];
    this.base_url = 'https://www.hitta.se';
    
    // Ensure data directory exists
    fs.mkdir(this.data_dir, { recursive: true }).catch(() => {});
  }

  async scrapeSearchResults(query) {
    this.results = [];
    const encodedQuery = encodeURIComponent(query);
    const searchUrl = `${this.base_url}/s%C3%B6k?vad=${encodedQuery}&typ=prv`;
    
    console.log(`Searching for: ${query}`);
    console.log(`URL: ${searchUrl}`);

    let browser = null;
    
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
      
      await page.goto(searchUrl);
      
      // Wait for search results to load
      try {
        await page.waitForSelector('li[data-test="person-item"]', { timeout: 10000 });
      } catch (error) {
        console.log('No results found or timeout waiting for results');
        return [];
      }

      // Try to dismiss cookie/consent overlays
      try {
        await this.dismissConsentOverlay(page);
      } catch (error) {
        // Ignore consent overlay errors
      }

      // Extract all person items
      const personItems = await page.$$('li[data-test="person-item"]');
      const total = personItems.length;
      console.log(`Found ${total} results`);

      for (let i = 0; i < total; i++) {
        try {
          // Re-query items each iteration to avoid stale references
          const currentItems = await page.$$('li[data-test="person-item"]');
          if (i >= currentItems.length) break;
          
          const item = currentItems[i];
          const idx = i + 1;
          const personData = await this.extractPersonData(item, page);
          
          if (personData) {
            this.results.push(personData);
            console.log(`Extracted ${idx}/${total}: ${personData.personnamn || 'Unknown'}`);
          }
        } catch (error) {
          console.log(`Error extracting person ${i + 1}:`, error);
          continue;
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
            const addressLines = addressText.split('\n');
            for (let i = 0; i < addressLines.length; i++) {
              const line = addressLines[i].trim();
              if (i === 0) {
                // First line is gender
                try {
                  const genderSpan = await addressP.$('span.style_gender__hKSL0');
                  if (genderSpan) {
                    data.kon = (await genderSpan.textContent())?.trim() || line;
                  } else {
                    data.kon = line;
                  }
                } catch {
                  data.kon = line;
                }
              } else if (i === 1) {
                // Second line is street address
                data.gatuadress = line;
              } else if (i === 2) {
                // Third line is postal code and city
                const parts = line.split(' ', 3);
                if (parts.length >= 2) {
                  data.postnummer = `${parts[0]} ${parts[1]}`.trim();
                  if (parts.length >= 3) {
                    data.postort = parts[2].trim();
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

  async saveToCsv(query, includePhoneMissing = false) {
    if (this.results.length === 0) {
      console.log('No results to save');
      return;
    }

    const total = this.results.length;
    const safeQuery = query.replace(/[^\w\s-]/g, '').trim().replace(/\s+/g, '_');

    // Save all results
    const allFilename = path.join(this.data_dir, `hitta_se_${safeQuery}_alla_${total}.csv`);
    await this.writeCsv(allFilename, this.results);
    console.log(`Saved all results to: ${allFilename}`);

    // Save results with phone numbers (not missing)
    if (includePhoneMissing) {
      const withPhone = this.results.filter(r => 
        r.telefon && r.telefon.length > 0 && !r.telefon.includes('Lägg till telefonnummer')
      );
      
      if (withPhone.length > 0) {
        const withPhoneTotal = withPhone.length;
        const withPhoneFilename = path.join(this.data_dir, `hitta_se_${safeQuery}_visa_${withPhoneTotal}.csv`);
        await this.writeCsv(withPhoneFilename, withPhone);
        console.log(`Saved ${withPhoneTotal} results with phone numbers to: ${withPhoneFilename}`);
      }
    }
  }

  async saveToDatabase() {
    if (this.results.length === 0) {
      console.log('No results to save to database');
      return 0;
    }

    let savedCount = 0;

    for (const record of this.results) {
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

    console.log(`\n✓ Saved ${savedCount}/${this.results.length} records to database`);
    return savedCount;
  }

  async writeCsv(filename, data) {
    const fieldnames = ['personnamn', 'alder', 'kon', 'gatuadress', 'postnummer', 
                       'postort', 'telefon', 'karta', 'link'];
    
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
    .parse();

  const options = program.opts();
  const query = program.args[0];

  const scraper = new HittaSeScraper(options.apiUrl, options.apiToken);

  // Scrape results
  const results = await scraper.scrapeSearchResults(query);

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
}

// Run main function
main().catch(error => {
  console.error('Error:', error);
  process.exit(1);
});