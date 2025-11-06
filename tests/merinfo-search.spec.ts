import { test, expect } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';
import { fileURLToPath } from 'url';

// ES module equivalent of __dirname
const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

// Directory to save authentication state
const authFile = path.join(__dirname, '../playwright/.auth/merinfo-session.json');

test.describe('Merinfo.se Person Search', () => {
  
  test.beforeAll(async () => {
    // Ensure auth directory exists
    const authDir = path.dirname(authFile);
    if (!fs.existsSync(authDir)) {
      fs.mkdirSync(authDir, { recursive: true });
    }
  });

  test('should navigate to homepage and handle Cloudflare challenge', async ({ page }) => {
    // Navigate to homepage
    await page.goto('/');
    
    // Wait a bit for initial load
    await page.waitForTimeout(3000);
    
    const title = await page.title();
    console.log(`Page title: ${title}`);
    
    // If Cloudflare challenge appears, give time to solve it manually
    if (title.includes('Vänta') || title.includes('Just a moment') || title.includes('Checking')) {
      console.log('⚠️  Cloudflare challenge detected!');
      console.log('👉 Please solve the challenge in the browser window.');
      console.log('   The test will wait up to 60 seconds...');
      
      // Wait for challenge to be solved (max 60 seconds)
      try {
        await page.waitForFunction(
          () => {
            const t = document.title;
            return !t.includes('Vänta') && !t.includes('Just a moment') && !t.includes('Checking');
          },
          { timeout: 60000 }
        );
        console.log('✓ Cloudflare challenge passed!');
      } catch (error) {
        console.log('✗ Cloudflare challenge timeout');
        throw new Error('Could not pass Cloudflare challenge');
      }
    }
    
    // Verify we're on the homepage
    await expect(page).toHaveURL(/merinfo\.se/);
    
    // Take a screenshot
    await page.screenshot({ path: 'tests/screenshots/homepage.png', fullPage: true });
  });

  test('should search for persons by address', async ({ page, context }) => {
    const searchQuery = '733 32 Sala';
    
    // Navigate to homepage
    await page.goto('/');
    await page.waitForTimeout(2000);
    
    // Check for Cloudflare
    const title = await page.title();
    if (title.includes('Vänta') || title.includes('Just a moment')) {
      console.log('⚠️  Cloudflare challenge detected - waiting...');
      console.log('👉 Please solve the challenge in the browser window (you have 2 minutes)');
      try {
        await page.waitForFunction(
          () => !document.title.includes('Vänta') && !document.title.includes('Just a moment'),
          { timeout: 120000 } // 2 minutes
        );
        console.log('✓ Cloudflare challenge passed!');
      } catch (error) {
        console.log('⚠️  Still waiting for challenge... Try reloading the page in browser');
        // Give one more chance with reload
        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(3000);
        const newTitle = await page.title();
        if (newTitle.includes('Vänta') || newTitle.includes('Just a moment')) {
          throw new Error('Could not pass Cloudflare challenge after 2 minutes');
        }
      }
    }
    
    // Find search input field
    const searchInput = page.locator('input.search-field-input').first();
    await expect(searchInput).toBeVisible({ timeout: 10000 });
    
    // Fill search query slowly (human-like)
    await searchInput.click();
    await page.waitForTimeout(500);
    await searchInput.fill('');
    for (const char of searchQuery) {
      await searchInput.type(char, { delay: 100 });
    }
    
    console.log(`Searching for: ${searchQuery}`);
    
    // Submit search (press Enter)
    await searchInput.press('Enter');
    
    // Wait for navigation to results page
    await page.waitForURL('**/search?q=**', { timeout: 15000 });
    console.log(`✓ Navigated to: ${page.url()}`);
    
    // Wait for results to load
    await page.waitForTimeout(3000);
    
    // Look for result cards
    const resultList = page.locator('div.result-list');
    await expect(resultList).toBeVisible({ timeout: 10000 });
    
    const cards = page.locator('div.mi-text-sm.mi-bg-white.mi-shadow-dark-blue-20.mi-p-0.mi-mb-6');
    const cardCount = await cards.count();
    
    console.log(`✓ Found ${cardCount} result cards`);
    expect(cardCount).toBeGreaterThan(0);
    
    // Take a screenshot of results
    await page.screenshot({ path: 'tests/screenshots/results.png', fullPage: true });
    
    // Extract data from first few cards
    const results = [];
    const maxCards = Math.min(cardCount, 5);
    
    for (let i = 0; i < maxCards; i++) {
      const card = cards.nth(i);
      const data: any = {};
      
      // Extract person name
      const nameLink = card.locator('a[href*="/person/"]').first();
      if (await nameLink.count() > 0) {
        data.name = await nameLink.innerText();
      }
      
      // Extract address
      const addressElement = card.locator('address span').first();
      if (await addressElement.count() > 0) {
        data.address = await addressElement.innerText();
      }
      
      // Extract phone if visible
      const phoneLink = card.locator('a[href^="tel:"]').first();
      if (await phoneLink.count() > 0) {
        data.phone = await phoneLink.innerText();
      }
      
      results.push(data);
      console.log(`Card ${i + 1}:`, data);
    }
    
    expect(results.length).toBeGreaterThan(0);
    
    // Save authentication state for future runs (to skip Cloudflare next time)
    await context.storageState({ path: authFile });
    console.log(`✓ Session saved to: ${authFile}`);
    console.log('💡 To use saved session, uncomment storageState in playwright.config.ts');
  });
  
  test('should extract all person data from results', async ({ page }) => {
    const searchQuery = '733 32 Sala';
    
    // Navigate and search
    await page.goto('/');
    await page.waitForTimeout(2000);
    
    const searchInput = page.locator('input.search-field-input').first();
    await searchInput.fill(searchQuery);
    await searchInput.press('Enter');
    
    await page.waitForURL('**/search?q=**', { timeout: 15000 });
    await page.waitForTimeout(3000);
    
    // Extract all data
    const cards = page.locator('div.mi-text-sm.mi-bg-white.mi-shadow-dark-blue-20.mi-p-0.mi-mb-6');
    const cardCount = await cards.count();
    
    const allResults = [];
    
    for (let i = 0; i < cardCount; i++) {
      const card = cards.nth(i);
      const data: any = {};
      
      // ps_personnamn
      const nameLink = card.locator('a[href*="/person/"]').first();
      if (await nameLink.count() > 0) {
        data.ps_personnamn = (await nameLink.innerText()).trim();
      }
      
      // ps_personnummer (look for pattern YYYYMMDD-XXXX)
      const cardText = await card.innerText();
      const personnummerMatch = cardText.match(/(\d{8}-\s*\w+)/);
      if (personnummerMatch) {
        data.ps_personnummer = personnummerMatch[1].replace(/\s+/g, '');
      }
      
      // Address information
      const addressSpans = card.locator('address span');
      const spanCount = await addressSpans.count();
      
      if (spanCount >= 1) {
        data.bo_gatuadress = (await addressSpans.nth(0).innerText()).trim();
      }
      
      if (spanCount >= 2) {
        const fullLocation = (await addressSpans.nth(1).innerText()).trim();
        if (fullLocation.length >= 6) {
          data.bo_postnummer = fullLocation.substring(0, 6).trim();
          data.bo_postort = fullLocation.substring(6).trim();
        }
      }
      
      // ps_telefon
      const phoneLink = card.locator('a[href^="tel:"]').first();
      if (await phoneLink.count() > 0) {
        const phoneText = (await phoneLink.innerText()).trim();
        data.ps_telefon = [phoneText];
      }
      
      allResults.push(data);
    }
    
    console.log(`\n✓ Extracted ${allResults.length} complete records:`);
    console.log(JSON.stringify(allResults, null, 2));
    
    // Save to CSV-like format
    const dataDir = path.join(__dirname, '../scripts/data');
    if (!fs.existsSync(dataDir)) {
      fs.mkdirSync(dataDir, { recursive: true });
    }
    
    const timestamp = new Date().toISOString().replace(/[:.]/g, '-').split('T')[0];
    const outputFile = path.join(dataDir, `merinfo_playwright_${timestamp}.json`);
    fs.writeFileSync(outputFile, JSON.stringify(allResults, null, 2));
    console.log(`\n✓ Data saved to: ${outputFile}`);
    
    expect(allResults.length).toBeGreaterThan(0);
  });
});
