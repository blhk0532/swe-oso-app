#!/usr/bin/env node

/**
 * Ratsit.se scraper script (JavaScript ES Module version)
 * Scrapes person data from ratsit.se and saves to database and CSV
 */

import { program } from 'commander';
import { promises as fs } from 'fs';
import path from 'path';
import { chromium } from 'playwright';

class RatsitScraper {
  constructor(api_url, api_token) {
    this.api_url = api_url || process.env.LARAVEL_API_URL || 'http://localhost:8000';
    this.api_token = api_token || process.env.LARAVEL_API_TOKEN;
    
    this.data_dir = path.join(process.cwd(), 'scripts', 'data');
    this.results = [];
    
    // Ensure data directory exists
    fs.mkdir(this.data_dir, { recursive: true }).catch(() => {});
  }

  async extractTextAfterLabel(page, labelText) {
    /** Extract text value after a label span */
    try {
      // Find label span
      const labelSelector = `span.color--gray5:has-text("${labelText}")`;
      const labelElement = await page.$(labelSelector);
      
      if (!labelElement) {
        return null;
      }
      
      // Get the parent paragraph text
      const parentText = await labelElement.evaluate((el) => {
        const p = el.closest('p');
        return p ? p.innerText : null;
      });
      
      if (!parentText) {
        return null;
      }
      
      // Remove label text and clean up
      let text = parentText.replace(labelText, '').trim();
      // Remove any tooltip text that might be present
      text = text.replace(/\s*Visas för medlemmar.*/gi, '');
      
      return text || null;
    } catch (e) {
      console.log(`Error extracting ${labelText}:`, e);
      return null;
    }
  }

  async extractPersonnummer(page) {
    /** Extract personnummer (special handling for XXXX placeholder) */
    try {
      const labelSelector = 'span.color--gray5:has-text("Personnummer:")';
      const labelElement = await page.$(labelSelector);
      
      if (!labelElement) {
        return null;
      }
      
      // Get the HTML content to find XXXX pattern
      const html = await labelElement.evaluate((el) => {
        const p = el.closest('p');
        return p ? p.innerHTML : null;
      });
      
      if (!html) {
        return null;
      }
      
      // Extract text before the link and XXXX part
      // Pattern: "19601110- " + <a>...<strong>XXXX</strong>...
      const match = html.match(/Personnummer:\s*([0-9-]+)\s*.*?<strong>XXXX<\/strong>/i);
      if (match) {
        return match[1].trim() +'XXXX';
      }
      
      // Fallback: try to get just the text value
      const text = await this.extractTextAfterLabel(page, 'Personnummer:');
      if (text) {
        // Clean up any remaining HTML entities
        let cleanText = text.replace(/<[^>]+>/g, '').trim();
        // Check if XXXX is in the HTML
        if (html.toUpperCase().includes('XXXX')) {
          if (!cleanText.endsWith('XXXX') && !cleanText.endsWith('xxxx')) {
            cleanText = cleanText.replace(/-$/, '') +'XXXX';
          }
        }
        return cleanText;
      }
      
      return null;
    } catch (e) {
      console.log('Error extracting personnummer:', e);
      return null;
    }
  }

  async extractTelefon(page) {
    /** Extract telefon number from href tel: link */
    try {
      const labelSelector = 'span.color--gray5:has-text("Telefon:")';
      const labelElement = await page.$(labelSelector);
      
      if (!labelElement) {
        return null;
      }
      
      // Find tel: link
      const telHref = await labelElement.evaluate((el) => {
        const p = el.closest('p');
        if (!p) return null;
        const telLink = p.querySelector('a[href^="tel:"]');
        return telLink ? telLink.getAttribute('href') : null;
      });
      
      if (telHref && telHref.startsWith('tel:')) {
        return telHref.replace('tel:', '');
      }
      
      return null;
    } catch (e) {
      console.log('Error extracting telefon:', e);
      return null;
    }
  }

  mapKonValue(value) {
    /** Map Swedish gender values to API format (M, F, O) */
    if (!value) {
      return null;
    }
    
    const valueLower = value.toLowerCase().trim();
    const mapping = {
      'man': 'M',
      'kvinna': 'F',
      'kvinno': 'F',
      'm': 'M',
      'f': 'F',
      'o': 'O',
      'other': 'O',
      'annat': 'O',
    };
    
    return mapping[valueLower] || value;
  }

  async scrapePersonPage(page, url) {
    /** Scrape a single person's detail page */
    console.log(`  Scraping: ${url}`);
    
    try {
      await page.goto(url, { waitUntil: 'networkidle', timeout: 30000 });
      await page.waitForTimeout(2000); // Wait for dynamic content
      
      // Extract raw values
      const rawKon = await this.extractTextAfterLabel(page, 'Juridiskt kön:');
      
      const data = {
        ps_personnummer: await this.extractPersonnummer(page),
        ps_alder: await this.extractTextAfterLabel(page, 'Ålder:'),
        ps_fodelsedag: await this.extractTextAfterLabel(page, 'Födelsedag:'),
        ps_kon: this.mapKonValue(rawKon),
        ps_telefon: await this.extractTelefon(page),
        ps_personnamn: await this.extractTextAfterLabel(page, 'Personnamn:'),
        ps_fornamn: await this.extractTextAfterLabel(page, 'Förnamn:'),
        ps_efternamn: await this.extractTextAfterLabel(page, 'Efternamn:'),
        bo_gatuadress: await this.extractTextAfterLabel(page, 'Gatuadress:'),
        bo_postnummer: await this.extractTextAfterLabel(page, 'Postnummer:'),
        bo_postort: await this.extractTextAfterLabel(page, 'Postort:'),
      };
      
      // Convert telefon to array format if present
      if (data.ps_telefon) {
        data.ps_telefon = [data.ps_telefon];
      } else {
        data.ps_telefon = [];
      }
      
      // Clean up null/empty values
      const cleanedData = {};
      for (const [key, value] of Object.entries(data)) {
        if (value !== null && value !== '') {
          cleanedData[key] = value;
        }
      }
      
      return cleanedData;
      
    } catch (e) {
      if (e.name === 'TimeoutError') {
        console.log(`  Timeout loading: ${url}`);
      } else {
        console.log(`  Error scraping ${url}:`, e);
      }
      return {};
    }
  }

  async getSearchResultLinks(page, searchUrl) {
    /** Get all person detail page links from search results */
    console.log(`Searching: ${searchUrl}`);
    
    try {
      await page.goto(searchUrl, { waitUntil: 'networkidle', timeout: 30000 });
      await page.waitForTimeout(2000); // Wait for dynamic content
      
      // Find all links in search results
      const links = [];
      const resultList = await page.$('ul.search-result-list');
      
      if (resultList) {
        // Find all li elements with links to ratsit.se
        const linkElements = await resultList.$$('li a[href^="https://www.ratsit.se/"]');
        
        for (const linkElement of linkElements) {
          const href = await linkElement.getAttribute('href');
          if (href && href.startsWith('https://www.ratsit.se/')) {
            links.push(href);
          }
        }
      }
      
      console.log(`Found ${links.length} results`);
      return links;
      
    } catch (e) {
      console.log('Error getting search results:', e);
      return [];
    }
  }

  async saveToDatabase(data) {
    /** Save data to Laravel API */
    if (!this.api_token) {
      console.log('  No API token provided, skipping database save');
      return false;
    }
    
    try {
      const url = `${this.api_url}/api/ratsit-data`;
      const headers = {
        'Authorization': `Bearer ${this.api_token}`,
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      };
      
      // Prepare data for API (only include fields that are present)
      const apiData = {};
      for (const [key, value] of Object.entries(data)) {
        if (value !== null && value !== '') {
          apiData[key] = value;
        }
      }
      
      const response = await fetch(url, {
        method: 'POST',
        headers,
        body: JSON.stringify(apiData),
      });

      // 201 Created for new, 200 OK for updated (upsert behavior)
      if (response.status === 200 || response.status === 201) {
        const action = response.status === 201 ? 'created' : 'updated';
        console.log(`  ✓ Saved to database (${action})`);
        return true;
      } else {
        const responseText = await response.text();
        console.log(`  ✗ Database save failed: ${response.status} - ${responseText.substring(0, 200)}`);
        return false;
      }
        
    } catch (e) {
      console.log(`  ✗ Error saving to database:`, e);
      return false;
    }
  }

  async saveToCsv(results) {
    /** Save results to CSV file with timestamp */
    if (!results || results.length === 0) {
      console.log('No results to save');
      return;
    }
    
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').slice(0, 19).replace('T', '_');
    const csvPath = path.join(this.data_dir, `ratsit_export_${timestamp}.csv`);
    
    // Get all unique keys from all results
    const allKeys = new Set();
    for (const result of results) {
      Object.keys(result).forEach(key => allKeys.add(key));
    }
    
    // Sort keys for consistent column order
    const fieldnames = Array.from(allKeys).sort();
    
    let csv = '';
    
    // Write data without header (as per requirements)
    for (const result of results) {
      const row = {};
      for (const [key, value] of Object.entries(result)) {
        // Convert arrays to JSON strings for CSV
        if (Array.isArray(value)) {
          row[key] = JSON.stringify(value);
        } else {
          row[key] = value;
        }
      }
      
      // Convert row to CSV
      const values = fieldnames.map(field => {
        let value = row[field] || '';
        
        // Handle null/undefined values
        if (value === null || value === undefined) {
          value = '';
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
    
    await fs.writeFile(csvPath, csv, 'utf-8');
    console.log(`\n✓ Saved ${results.length} records to ${csvPath}`);
  }

  async scrape(query) {
    /** Main scraping method */
    // URL encode query parameter
    const encodedQuery = encodeURIComponent(query);
    const searchUrl = `https://www.ratsit.se/sok/person?vem=${encodedQuery}`;
    
    let browser = null;
    
    try {
      // Try to launch browser with multiple fallback options
      try {
        // 1) Try Google Chrome if installed
        browser = await chromium.launch({ 
          channel: 'chrome', 
          headless: true 
        });
      } catch (e) {
        try {
          // 2) Try Playwright-managed Chromium
          browser = await chromium.launch({ 
            channel: 'chromium', 
            headless: true 
          });
        } catch (e2) {
          try {
            // 3) Fallback to system Chrome executable
            browser = await chromium.launch({
              executablePath: '/usr/bin/google-chrome',
              headless: true
            });
          } catch (e3) {
            console.log('Error launching browser:', e3);
            console.log('Tips:\n - Ensure Google Chrome or Chromium is installed.\n - If using Playwright-managed browsers, run: playwright install chromium\n - On Linux, you may also need system deps: playwright install-deps (requires sudo)');
            throw e3;
          }
        }
      }
      
      const context = await browser.newContext({
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36'
      });
      const page = await context.newPage();
      
      try {
        // Step 1: Get search result links
        const links = await this.getSearchResultLinks(page, searchUrl);
        
        if (!links || links.length === 0) {
          console.log('No search results found');
          return;
        }
        
        // Step 2: Scrape each person's detail page
        console.log(`\nScraping ${links.length} person pages...`);
        for (let i = 0; i < links.length; i++) {
          const link = links[i];
          process.stdout.write(`[${i + 1}/${links.length}] `);
          
          const data = await this.scrapePersonPage(page, link);
          
          if (data && Object.keys(data).length > 0) {
            this.results.push(data);
            
            // Step 3: Save to database (if token provided)
            if (this.api_token) {
              await this.saveToDatabase(data);
            }
          }
          
          // Small delay between requests
          await page.waitForTimeout(1000);
        }
        
        // Step 4: Save all results to CSV
        if (this.results.length > 0) {
          await this.saveToCsv(this.results);
          console.log(`\n✓ Scraping complete: ${this.results.length} records collected`);
        } else {
          console.log('\n✗ No data collected');
        }
          
      } finally {
        await browser.close();
      }
      
    } catch (e) {
      console.log('Error during scraping:', e);
      if (browser) {
        await browser.close();
      }
    }
  }
}

// Main function
async function main() {
  program
    .description('Scrape person data from ratsit.se')
    .argument('query', 'Search query (person name, etc.)')
    .option('--api-url <url>', 'Laravel API base URL')
    .option('--api-token <token>', 'Sanctum authentication token')
    .parse();

  const options = program.opts();
  const query = program.args[0];

  const scraper = new RatsitScraper(options.apiUrl, options.apiToken);
  await scraper.scrape(query);
}

// Run main function
main().catch(error => {
  console.error('Error:', error);
  process.exit(1);
});