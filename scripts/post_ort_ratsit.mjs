#!/usr/bin/env node

/**
 * Ratsit.se scraper script (Ratsit-only)
 * - Scrapes person data from Ratsit.se based on a query string
 * - Saves results to ratsit_data via the Laravel API
 * - Optionally flags matching hitta_data rows as having Ratsit data
 */

import { program } from 'commander';
import { promises as fs } from 'fs';
import path from 'path';
import { chromium } from 'playwright';
import axios from 'axios';

class RatsitScraper {
  constructor(api_url, api_token) {
    this.api_url = api_url || process.env.LARAVEL_API_URL || 'http://localhost:8000';
    this.api_token = api_token || process.env.LARAVEL_API_TOKEN;

    this.data_dir = path.join(process.cwd(), 'scripts', 'data');

    // Ensure data directory exists
    fs.mkdir(this.data_dir, { recursive: true }).catch(() => {});
  }


  async saveRatsitToDatabase(ratsitData) {
    /**
     * Save Ratsit data via API
     */
    try {
      const apiData = {
        gatuadress: ratsitData.bo_gatuadress || null,
        postnummer: ratsitData.bo_postnummer || null,
        postort: ratsitData.bo_postort || null,
        forsamling: ratsitData.bo_forsamling || null,
        kommun: ratsitData.bo_kommun || null,
        lan: ratsitData.bo_lan || null,
        adressandring: ratsitData.adressandring || null,
        telfonnummer: Array.isArray(ratsitData.telefonnummer) ? ratsitData.telefonnummer.join(' | ') : null,
        stjarntacken: ratsitData.stjarntacken || null,
        fodelsedag: ratsitData.ps_fodelsedag || null,
        personnummer: ratsitData.ps_personnummer || null,
        alder: ratsitData.ps_alder || null,
        kon: ratsitData.ps_kon || null,
        civilstand: ratsitData.ps_civilstand || null,
        fornamn: ratsitData.ps_fornamn || null,
        efternamn: ratsitData.ps_efternamn || null,
        personnamn: ratsitData.ps_personnamn || null,
        telefon: ratsitData.ps_telefon || null,
        agandeform: ratsitData.bo_agandeform || null,
        bostadstyp: ratsitData.bo_bostadstyp || null,
        boarea: ratsitData.bo_boarea || null,
        byggar: ratsitData.bo_byggar || null,
        personer: Array.isArray(ratsitData.bo_personer) ? ratsitData.bo_personer.join(' | ') : null,
        foretag: Array.isArray(ratsitData.bo_foretag) ? ratsitData.bo_foretag.join(' | ') : null,
        grannar: Array.isArray(ratsitData.bo_grannar) ? ratsitData.bo_grannar.join(' | ') : null,
        fordon: Array.isArray(ratsitData.bo_fordon) ? ratsitData.bo_fordon.join(' | ') : null,
        hundar: Array.isArray(ratsitData.bo_hundar) ? ratsitData.bo_hundar.join(' | ') : null,
        bolagsengagemang: Array.isArray(ratsitData.ps_bolagsengagemang) ? ratsitData.ps_bolagsengagemang.join(' | ') : null,
        longitude: ratsitData.bo_longitude || null,
        latitud: ratsitData.latitud || null,
        google_maps: ratsitData.google_maps || null,
        google_streetview: ratsitData.google_streetview || null,
        ratsit_se: ratsitData.ratsit_se || null,
        is_active: true,
      };

      // Log what we're about to send to API
      console.log('\n  📤 API Payload for ratsit_data:');
      const nonNullFields = Object.entries(apiData).filter(([k, v]) => v !== null);
      console.log(`     Sending ${nonNullFields.length} non-null fields out of ${Object.keys(apiData).length} total`);
      nonNullFields.forEach(([key, value]) => {
        const display = typeof value === 'string' && value.length > 60 ? value.substring(0, 60) + '...' : value;
        console.log(`     ${key}: ${display}`);
      });
      console.log('');

      // Use API to save
      const response = await axios.post(`${this.api_url}/api/ratsit-data/bulk`, { records: [apiData] }, {
        headers: {
          'Content-Type': 'application/json',
          // Authorization may not be required for bulk but include if token supplied
          'Authorization': this.api_token ? `Bearer ${this.api_token}` : undefined,
        },
      });

      console.log(`  ✓ Saved Ratsit data for ${ratsitData.ps_personnamn} via API`);
      console.log(`  ✓ API Response:`, JSON.stringify(response.data, null, 2));
      
      // Attempt to flag matching hitta_data record as having ratsit
      try { await this.updateHittaDataRatsitFlag(ratsitData); } catch (e) { console.log('  ⚠ Failed to update hitta_data flag:', e.message); }
      return true;
    } catch (error) {
      const status = error.response?.status;
      console.log(`  ✗ Error saving Ratsit data via API${status ? ` (HTTP ${status})` : ''}:`, error.response?.data || error.message);
      return false;
    }
  }

  async updateHittaDataRatsitFlag(ratsitData) {
    /**
     * Update is_ratsit flag in hitta_data via API (no local SQLite dependency)
     */
    try {
      const personnamn = ratsitData.ps_personnamn || ratsitData.personnamn || null;
      const gatuadress = ratsitData.bo_gatuadress || ratsitData.gatuadress || null;
      if (!personnamn || !gatuadress) {
        console.log('  ⚠ Skipping hitta_data flag update (missing personnamn or gatuadress)');
        return;
      }

      // Use bulk upsert by personnamn; include gatuadress for better matching and set is_ratsit
      const payload = { records: [{ personnamn, gatuadress, is_ratsit: true }] };
      await axios.post(`${this.api_url}/api/hitta-data/bulk`, payload, {
        headers: {
          'Content-Type': 'application/json',
          'Authorization': this.api_token ? `Bearer ${this.api_token}` : undefined,
        },
      });

      console.log(`  ✓ Updated is_ratsit flag in hitta_data via API`);
    } catch (error) {
      const status = error.response?.status;
      console.log(`  ✗ Error updating hitta_data is_ratsit flag via API${status ? ` (HTTP ${status})` : ''}:`, error.response?.data || error.message);
    }
  }

  async saveToPrivateData(hittaData, ratsitData) {
    /**
     * Save combined Hitta + Ratsit data to private_data table via API
     * Only saves if BOTH hitta and ratsit data are available
     */
    if (!hittaData || !ratsitData) {
      console.log('  ⊘ Skipping private_data save (need both Hitta and Ratsit data)');
      return false;
    }

    try {
      // Combine data from both sources
      const apiData = {
        // Address fields (prefer Ratsit)
        gatuadress: ratsitData.bo_gatuadress || hittaData.gatuadress || null,
        postnummer: ratsitData.bo_postnummer || hittaData.postnummer || null,
        postort: ratsitData.bo_postort || hittaData.postort || null,
        forsamling: ratsitData.bo_forsamling || null,
        kommun: ratsitData.bo_kommun || null,
        lan: ratsitData.bo_lan || null,
        adressandring: ratsitData.adressandring || null,

        // Phone arrays (send as arrays, not JSON strings)
        telefon: Array.isArray(ratsitData.ps_telefon) ? ratsitData.ps_telefon : (Array.isArray(hittaData.telefon) ? hittaData.telefon : []),

        // Person fields (Ratsit)
        stjarntacken: ratsitData.stjarntacken || null,
        fodelsedag: ratsitData.ps_fodelsedag || null,
        personnummer: ratsitData.ps_personnummer || null,
        alder: ratsitData.ps_alder || hittaData.alder || null,
        kon: ratsitData.ps_kon || hittaData.kon || null,
        civilstand: ratsitData.ps_civilstand || null,
        fornamn: ratsitData.ps_fornamn || null,
        efternamn: ratsitData.ps_efternamn || null,
        personnamn: ratsitData.ps_personnamn || hittaData.personnamn || null,

        // Dwelling fields (Ratsit)
        agandeform: ratsitData.bo_agandeform || null,
        bostadstyp: ratsitData.bo_bostadstyp || null,
        boarea: ratsitData.bo_boarea || null,
        byggar: ratsitData.bo_byggar || null,

        // Collections (Ratsit) - send as arrays
        personer: Array.isArray(ratsitData.bo_personer) ? ratsitData.bo_personer : [],
        foretag: Array.isArray(ratsitData.bo_foretag) ? ratsitData.bo_foretag : [],
        grannar: Array.isArray(ratsitData.bo_grannar) ? ratsitData.bo_grannar : [],
        fordon: Array.isArray(ratsitData.bo_fordon) ? ratsitData.bo_fordon : [],
        hundar: Array.isArray(ratsitData.bo_hundar) ? ratsitData.bo_hundar : [],
        bolagsengagemang: Array.isArray(ratsitData.ps_bolagsengagemang) ? ratsitData.ps_bolagsengagemang : [],

        // Geo & Links (combined)
        longitude: ratsitData.bo_longitude || null,
        latitud: ratsitData.latitud || null,
        google_maps: ratsitData.google_maps || null,
        google_streetview: ratsitData.google_streetview || null,

        // Hitta specific fields
        hitta_link: hittaData.link || null,
        hitta_karta: hittaData.karta || null,
        bostad_typ: hittaData.bostadstyp || null,
        bostad_pris: hittaData.bostadspris || null,

        // Flags
        is_active: true,
      };

      // Use API to save
      const response = await axios.post(`${this.api_url}/api/data-private/bulk`, { records: [apiData] }, {
        headers: {
          'Content-Type': 'application/json',
          'Authorization': this.api_token ? `Bearer ${this.api_token}` : undefined,
        },
      });

      console.log(`  ✓ Combined data saved to private_data via API:`, response.data);
      return true;
    } catch (error) {
      console.log(`  ✗ Error saving combined data via API:`, error.response?.data || error.message);
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

  async scrapeRatsitData(query) {
    /**
     * Scrape Ratsit data with complete extraction
     * Returns array of person data objects with full details
     */
    console.log(`  → Starting Ratsit scrape for: "${query}"`);

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
          await page.waitForTimeout(1500);

          // Scroll to load lazy content
          await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
          await page.waitForTimeout(1000);

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
            // Additional labels
            bo_forsamling: await this.extractRatsitTextAfterLabel(page, 'Församling:'),
            bo_kommun: await this.extractRatsitTextAfterLabel(page, 'Kommun:'),
            bo_lan: await this.extractRatsitTextAfterLabel(page, 'Län:'),
            ps_civilstand: await this.extractRatsitCivilstand(page),
            adressandring: await this.extractRatsitTextAfterLabel(page, 'Adressändring:'),
            stjarntacken: await this.extractRatsitTextAfterLabel(page, 'Stjärntecken:'),
            // Dwelling specific labels
            bo_agandeform: await this.extractRatsitTextAfterLabel(page, 'Ägandeform:'),
            bo_bostadstyp: await this.extractRatsitTextAfterLabel(page, 'Bostadstyp:'),
            bo_boarea: await this.extractRatsitTextAfterLabel(page, 'Boarea:'),
            bo_byggar: await this.extractRatsitTextAfterLabel(page, 'Byggår:'),
          };
          // Link for saving
          personData.ratsit_se = link;

          // Sections: telefonnummer (additional), personer, foretag, grannar, fordon, hundar, bolagsengagemang
          personData.telefonnummer = await this.extractSectionTelefonnummer(page);
          const personer = await this.extractSectionListStrong(page, 'Personer');
          if (personer.length) personData.bo_personer = personer;
          const foretag = await this.extractSectionForetag(page);
          if (foretag.length) personData.bo_foretag = foretag;
          const grannar = await this.extractSectionGrannar(page);
          if (grannar.length) personData.bo_grannar = grannar;
          const fordon = await this.extractSectionFordon(page);
          if (fordon.length) personData.bo_fordon = fordon;
          const hundar = await this.extractSectionHundar(page);
          if (hundar.length) personData.bo_hundar = hundar;
          const bolag = await this.extractSectionBolagsengagemang(page);
          if (bolag.length) personData.ps_bolagsengagemang = bolag;

          // Lat/Long & Streetview
          const latLongText = await this.extractLatLongText(page);
          if (latLongText) {
            const m = latLongText.match(/Latitud:\s*([0-9.+-]+).*Longitud:\s*([0-9.+-]+)/i);
            if (m) {
              personData.latitud = m[1];
              personData.bo_longitude = m[2];
            }
          }
          personData.google_streetview = await this.extractStreetViewLink(page);

          // Google maps link from address
          if (personData.bo_gatuadress && personData.bo_postnummer && personData.bo_postort) {
            const addr = `${personData.bo_gatuadress}, ${personData.bo_postnummer} ${personData.bo_postort}`;
            personData.google_maps = `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(addr)}`;
          }

          // Map gender value
          if (personData.ps_kon) {
            const konMap = { 'man': 'M', 'kvinna': 'F', 'kvinno': 'F' };
            personData.ps_kon = konMap[personData.ps_kon.toLowerCase()] || personData.ps_kon;
          }

          // Handle telefon as string (single number) and telefonnummer as array
          if (personData.ps_telefon && personData.telefonnummer && Array.isArray(personData.telefonnummer)) {
            // If we have telefonnummer array but no telefon string, use first number from array
            if (!personData.ps_telefon && personData.telefonnummer.length > 0) {
              personData.ps_telefon = personData.telefonnummer[0];
            }
            // Remove the telefon number from telefonnummer array if it matches
            if (personData.ps_telefon) {
              personData.telefonnummer = personData.telefonnummer.filter(num => num !== personData.ps_telefon);
            }
          }

          const cleanData = {};
          for (const [key, value] of Object.entries(personData)) {
            if (value === null || value === undefined) continue;
            if (Array.isArray(value) && value.length === 0) continue;
            if (typeof value === 'string' && value.trim() === '') continue;
            
            // Filter out unwanted text like "Kolla lön direkt"
            let cleanedValue = value;
            if (typeof value === 'string') {
              cleanedValue = value.replace(/Kolla lön direkt/gi, '').trim();
              if (cleanedValue === '') continue; // Skip if only contained unwanted text
            } else if (Array.isArray(value)) {
              cleanedValue = value
                .map(item => typeof item === 'string' ? item.replace(/Kolla lön direkt/gi, '').trim() : item)
                .filter(item => item !== '' && item !== null && item !== undefined);
              if (cleanedValue.length === 0) continue;
            }
            
            cleanData[key] = cleanedValue;
          }

          if (Object.keys(cleanData).length > 0) {
            results.push(cleanData);
            console.log(`  → ✓ Extracted data for ${cleanData.ps_personnamn || 'Unknown'}`);
            
            // Log extracted data details
            console.log('\n  📋 Scraped Data Summary:');
            console.log(`     Person: ${cleanData.ps_personnummer || 'N/A'} | ${cleanData.ps_personnamn || 'N/A'} | Age: ${cleanData.ps_alder || 'N/A'}`);
            console.log(`     Address: ${cleanData.bo_gatuadress || 'N/A'}, ${cleanData.bo_postnummer || 'N/A'} ${cleanData.bo_postort || 'N/A'}`);
            console.log(`     Location: ${cleanData.bo_forsamling || 'N/A'} / ${cleanData.bo_kommun || 'N/A'} / ${cleanData.bo_lan || 'N/A'}`);
            console.log(`     Phone: ${cleanData.ps_telefon || 'N/A'}`);
            console.log(`     Additional Phones: ${Array.isArray(cleanData.telefonnummer) ? cleanData.telefonnummer.join(', ') : 'N/A'}`);
            console.log(`     Dwelling: ${cleanData.bo_bostadstyp || 'N/A'} | ${cleanData.bo_agandeform || 'N/A'} | ${cleanData.bo_boarea || 'N/A'}m² | Built: ${cleanData.bo_byggar || 'N/A'}`);
            console.log(`     Civil: ${cleanData.ps_civilstand || 'N/A'} | Sign: ${cleanData.stjarntacken || 'N/A'}`);
            console.log(`     Collections: Personer(${cleanData.bo_personer?.length || 0}) Företag(${cleanData.bo_foretag?.length || 0}) Grannar(${cleanData.bo_grannar?.length || 0})`);
            console.log(`                  Fordon(${cleanData.bo_fordon?.length || 0}) Hundar(${cleanData.bo_hundar?.length || 0}) Bolag(${cleanData.ps_bolagsengagemang?.length || 0})`);
            console.log(`     Geo: Lat ${cleanData.latitud || 'N/A'}, Long ${cleanData.bo_longitude || 'N/A'}`);
            console.log(`     Links: ${cleanData.ratsit_se ? '✓ Ratsit' : '✗'} ${cleanData.google_maps ? '✓ Maps' : '✗'} ${cleanData.google_streetview ? '✓ Street' : '✗'}\n`);
          }

          await page.waitForTimeout(500);

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
        return null;
      }

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
      return null;
    }
  }

  async extractRatsitCivilstand(page) {
    // Look for a section where heading contains Civilstånd and return the content after the heading
    try {
      const heading = await page.$('h2:has-text("Civilstånd")');
      if (heading) {
        const parent = await heading.evaluateHandle((el) => el.parentElement);
        if (parent) {
          const fullText = await parent.evaluate((el) => {
            // Get all text content including links
            return el.textContent?.trim() || null;
          });
          if (fullText && !fullText.includes('Kolla lön direkt')) {
            // Remove the heading "Civilstånd" from the beginning
            return fullText.replace(/^Civilstånd\s*/, '').trim();
          }
        }
      }
      return null;
    } catch { return null; }
  }

  async extractSectionTelefonnummer(page) {
    try {
      // Find heading by text (h3 preferred)
      let header = await page.$('h3:has-text("Telefonnummer")');
      if (!header) return [];
      // Traverse siblings until next header and collect spans that look like phone numbers
      const numbers = await header.evaluate((h3) => {
        const out = [];
        let node = h3.nextElementSibling;
        const isHeader = (el) => !el ? false : ['H1','H2','H3'].includes(el.tagName);
        const looksLikePhone = (t) => /^(?:0\d{1,3}|\+46)[\d\s-]{5,}$/.test(t) && /\d/.test(t);
        while (node && !isHeader(node)) {
          // Only scan common wrappers
          if (node.matches('p, div, section')) {
            node.querySelectorAll('span, a').forEach((el) => {
              const txt = (el.textContent || '').trim();
              if (looksLikePhone(txt)) out.push(txt);
            });
          }
          node = node.nextElementSibling;
        }
        return Array.from(new Set(out));
      });
      return numbers;
    } catch { return []; }
  }

  async extractSectionListStrong(page, headerText) {
    try {
      const header = await page.$(`h3:has-text("${headerText}")`);
      if (!header) return [];
      const container = await header.evaluateHandle((el) => el.parentElement?.parentElement);
      const items = await page.evaluate((root) => {
        if (!root) return [];
        const arr = [];
        root.querySelectorAll('strong').forEach((el) => {
          const t = el.textContent?.trim(); if (t) arr.push(t);
        });
        return arr;
      }, container);
      return items;
    } catch { return []; }
  }

  async extractSectionForetag(page) {
    try {
      const header = await page.$('h3:has-text("Företag")');
      if (!header) return [];
      const container = await header.evaluateHandle((el) => el.parentElement?.querySelector('table'));
      const rows = await page.evaluate((tbl) => {
        const out = [];
        if (!tbl) return out;
        tbl.querySelectorAll('tbody tr').forEach((tr) => {
          const cells = Array.from(tr.querySelectorAll('td')).map((td) => td.innerText.trim());
          if (cells.length) out.push(cells.join(' | '));
        });
        return out;
      }, container);
      return rows;
    } catch { return []; }
  }

  async extractSectionGrannar(page) {
    try {
      const titles = await page.$$('button.accordion-neighbours__title');
      const out = [];
      for (const btn of titles) {
        // Expand if possible (best effort)
        try { await btn.click({ timeout: 500 }); } catch {}
      }
      const rows = await page.$$('div .accordion-neighbours__title ~ div table tbody tr');
      for (const tr of rows) {
        const text = await tr.evaluate((el) => el.innerText.replace(/\s+/g, ' ').trim());
        if (text) out.push(text);
      }
      return out;
    } catch { return []; }
  }

  async extractSectionFordon(page) {
    try {
      const header = await page.$('h3:has-text("Fordon")');
      if (!header) return [];
      const table = await header.evaluateHandle((el) => el.parentElement?.querySelector('table'));
      const rows = await page.evaluate((tbl) => {
        const out = [];
        if (!tbl) return out;
        tbl.querySelectorAll('tbody tr').forEach((tr) => {
          const tds = tr.querySelectorAll('td');
          if (tds.length) {
            const brand = tds[0]?.innerText.trim();
            const model = tds[1]?.innerText.trim();
            const year = tds[2]?.innerText.trim();
            const color = tds[3]?.innerText.trim();
            const owner = tds[4]?.innerText.trim();
            out.push([brand, model, year, color, owner].filter(Boolean).join(', '));
          }
        });
        return out;
      }, table);
      return rows;
    } catch { return []; }
  }

  async extractSectionHundar(page) {
    try {
      const header = await page.$('h3:has-text("Hundar")');
      if (!header) return [];
      const container = await header.evaluateHandle((el) => el.parentElement);

      // Strategy 1: Prefer structured table parsing if present
      const table = await header.evaluateHandle((el) => el.parentElement?.querySelector('table'));
      const tableRows = await page.evaluate((tbl) => {
        const out = [];
        if (!tbl) return out;
        let rows = tbl.querySelectorAll('tbody tr');
        if (!rows.length) rows = tbl.querySelectorAll('tr');
        rows.forEach((tr) => {
          const cells = Array.from(tr.querySelectorAll('td, th')).map((c) => c.innerText.replace(/\s+/g, ' ').trim());
          if (!cells.length) return;
          // Skip header rows
          const headerLike = cells.join(' ').match(/^(Ras|Hund|Födelsedatum|Ålder|Ägare|Namn)/i);
          if (headerLike) return;
          const line = cells.filter(Boolean).join(', ');
          if (line) out.push(line);
        });
        return out;
      }, table);
      if (Array.isArray(tableRows) && tableRows.length) return tableRows;

      // Strategy 2: Fallback to heuristic grouping of lines (breed, date (age), owner)
      const lines = await page.evaluate((root) => {
        const out = [];
        if (!root) return out;
        const rawLines = (root.innerText || '')
          .split('\n')
          .map((l) => l.trim())
          .filter(Boolean)
          .filter((l) => !/^Hundar$/i.test(l) && !/Visa mer|Visa mindre/i.test(l));

        const isDateAge = (s) => /\d{4}-\d{2}-\d{2}/.test(s) || /\(\d+\s*år\)/i.test(s);
        for (let i = 0; i < rawLines.length; i++) {
          if (!isDateAge(rawLines[i])) continue;
          const dateAge = rawLines[i];
          const breed = rawLines[i - 1] && !isDateAge(rawLines[i - 1]) ? rawLines[i - 1] : null;
          const owner = rawLines[i + 1] && !isDateAge(rawLines[i + 1]) ? rawLines[i + 1] : null;
          const composed = [breed, dateAge, owner].filter(Boolean).join(', ');
          if (composed) out.push(composed);
        }
        // Deduplicate while preserving order
        return Array.from(new Set(out));
      }, container);
      return lines;
    } catch { return []; }
  }

  async extractSectionBolagsengagemang(page) {
    try {
      // Check if "Bolagsengagemang" text exists anywhere on page
      const hasText = await page.locator('text="Bolagsengagemang"').count();
      if (!hasText) {
        return [];
      }

      // Since heading might be in sidebar or other location, search page-wide for any section/div with id="engagemang"
      const sectionEl = await page.$('[id="engagemang"]');
      if (sectionEl) {
        const items = await sectionEl.evaluate((sec) => {
          const out = [];
          const tbl = sec.querySelector('table');
          if (!tbl) return out;
          let rows = tbl.querySelectorAll('tbody tr');
          if (!rows.length) rows = tbl.querySelectorAll('tr');
          rows.forEach((tr) => {
            const cells = Array.from(tr.querySelectorAll('td, th')).map(c => c.innerText.replace(/\s+/g,' ').trim());
            if (cells.length && !cells[0].match(/^(Företagsnamn|Typ|Status|Befattning)/i)) {
              out.push(cells.join(', '));
            }
          });
          return out;
        });
        if (items.length > 0) {
          return items;
        }
      }

      // Fallback: find any h2 containing "Bolagsengagemang" in main content (not sidebar)
      const mainHeader = await page.$('main h2:has-text("Bolagsengagemang"), article h2:has-text("Bolagsengagemang")');
      if (mainHeader) {
        await mainHeader.scrollIntoViewIfNeeded();
        await page.waitForTimeout(1000);
        const items = await mainHeader.evaluate((h) => {
          const out = [];
          // Search next siblings or parent container
          let node = h.nextElementSibling;
          while (node && !node.matches('h1,h2,h3')) {
            const tbl = node.matches('table') ? node : node.querySelector('table');
            if (tbl) {
              let rows = tbl.querySelectorAll('tbody tr');
              if (!rows.length) rows = tbl.querySelectorAll('tr');
              rows.forEach((tr) => {
                const cells = Array.from(tr.querySelectorAll('td, th')).map(c => c.innerText.replace(/\s+/g,' ').trim());
                if (cells.length && !cells[0].match(/^(Företagsnamn|Typ|Status|Befattning)/i)) {
                  out.push(cells.join(', '));
                }
              });
              return out;
            }
            node = node.nextElementSibling;
          }
          return out;
        });
        if (items.length > 0) {
          return items;
        }
      }

      return [];
    } catch (e) {
      return [];
    }
  }

  async extractLatLongText(page) {
    try {
      const el = await page.$('div:has-text("Latitud:")');
      if (!el) return null;
      return await el.innerText();
    } catch { return null; }
  }

  async extractStreetViewLink(page) {
    try {
      const linkEl = await page.$('a[href*="map_action=pano"][href*="viewpoint="]');
      if (!linkEl) return null;
      return await linkEl.getAttribute('href');
    } catch { return null; }
  }
}

// Main function
async function main() {
  program
    .description('Scrape person data from Ratsit.se and save to ratsit_data')
    .argument('query', 'Ratsit search query (e.g., "Namn Gatuadress Stad")')
    .option('--api-url <url>', 'Laravel API URL (default: http://localhost:8000)')
    .option('--api-token <token>', 'API authentication token')
    .parse();

  const options = program.opts();
  const query = program.args[0];

  const scraper = new RatsitScraper(options.apiUrl, options.apiToken);

  try {
    const ratsitResults = await scraper.scrapeRatsitData(query);

    if (!ratsitResults || ratsitResults.length === 0) {
      console.log('No Ratsit results found');
      return;
    }

    console.log(`\n→ Processing ${ratsitResults.length} Ratsit record(s)...`);
    let saved = 0;
    for (const r of ratsitResults) {
      const ok = await scraper.saveRatsitToDatabase(r);
      if (ok) saved += 1;
      // Note: saving to private_data requires Hitta data as well, which this script does not collect.
    }
    console.log(`\n✓ Saved ${saved}/${ratsitResults.length} record(s) to ratsit_data`);
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
