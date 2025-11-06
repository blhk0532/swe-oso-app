import { test, expect } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const authFile = path.join(__dirname, '../playwright/.auth/merinfo-session.json');

test.describe('Merinfo Manual Test', () => {
  test.use({ 
    viewport: { width: 1920, height: 1080 },
  });

  test('manually solve Cloudflare and search', async ({ page, context }) => {
    test.setTimeout(300000); // 5 minutes
    
    console.log('\n🚀 Starting manual test...');
    console.log('📝 Instructions:');
    console.log('   1. Browser will open and navigate to merinfo.se');
    console.log('   2. Solve any Cloudflare challenge manually');
    console.log('   3. Test will pause - press Enter in terminal when ready');
    console.log('   4. Test will then search and extract data\n');
    
    // Navigate to homepage
    await page.goto('https://www.merinfo.se/');
    await page.waitForTimeout(3000);
    
    const title = await page.title();
    console.log(`Current page title: "${title}"`);
    
    // If Cloudflare, give instructions
    if (title.includes('Vänta') || title.includes('Just a moment') || title.includes('Checking')) {
      console.log('\n⚠️  CLOUDFLARE DETECTED');
      console.log('👉 Please solve the challenge in the browser window');
      console.log('   (It might be a checkbox or captcha)');
      console.log('\n⏸️  Test is PAUSED - Press Enter here when page is loaded...');
      
      // Wait for user input in terminal
      await new Promise((resolve) => {
        process.stdin.once('data', () => {
          console.log('✓ Continuing...\n');
          resolve(true);
        });
      });
      
      // Check if solved
      const newTitle = await page.title();
      console.log(`New page title: "${newTitle}"`);
      
      if (newTitle.includes('Vänta') || newTitle.includes('Just a moment')) {
        console.log('⚠️  Challenge still active - trying reload...');
        await page.reload({ waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
      }
    } else {
      console.log('✓ No Cloudflare challenge detected!');
    }
    
    // Take screenshot of homepage
    const screenshotDir = path.join(__dirname, 'screenshots');
    if (!fs.existsSync(screenshotDir)) {
      fs.mkdirSync(screenshotDir, { recursive: true });
    }
    await page.screenshot({ path: path.join(screenshotDir, '01-homepage.png') });
    console.log('📸 Screenshot saved: tests/screenshots/01-homepage.png');
    
    // Search for persons
    console.log('\n🔍 Searching for "733 32 Sala"...');
    
    try {
      const searchInput = page.locator('input.search-field-input').first();
      await searchInput.waitFor({ state: 'visible', timeout: 10000 });
      await searchInput.click();
      await page.waitForTimeout(500);
      
      // Type slowly
      const query = '733 32 Sala';
      for (const char of query) {
        await searchInput.type(char, { delay: 80 });
      }
      
      console.log('✓ Query typed');
      await page.waitForTimeout(500);
      await searchInput.press('Enter');
      console.log('✓ Search submitted');
      
      // Wait for results
      await page.waitForURL('**/search?q=**', { timeout: 15000 });
      console.log(`✓ Results page loaded: ${page.url()}`);
      
      await page.waitForTimeout(3000);
      await page.screenshot({ path: path.join(screenshotDir, '02-results.png') });
      console.log('📸 Screenshot saved: tests/screenshots/02-results.png');
      
      // Extract data
      const cards = page.locator('div.mi-text-sm.mi-bg-white.mi-shadow-dark-blue-20.mi-p-0.mi-mb-6');
      await cards.first().waitFor({ state: 'visible', timeout: 10000 });
      
      const cardCount = await cards.count();
      console.log(`\n✓ Found ${cardCount} result cards`);
      
      // Extract first 3 cards
      const results = [];
      const maxCards = Math.min(cardCount, 3);
      
      for (let i = 0; i < maxCards; i++) {
        const card = cards.nth(i);
        const data: any = {};
        
        const nameLink = card.locator('a[href*="/person/"]').first();
        if (await nameLink.count() > 0) {
          data.name = await nameLink.innerText();
        }
        
        const addressSpans = card.locator('address span');
        if (await addressSpans.count() >= 1) {
          data.address = await addressSpans.nth(0).innerText();
        }
        
        results.push(data);
        console.log(`\nCard ${i + 1}:`);
        console.log(`  Name: ${data.name}`);
        console.log(`  Address: ${data.address}`);
      }
      
      // Save session
      await context.storageState({ path: authFile });
      console.log(`\n✅ SUCCESS! Session saved to: ${authFile}`);
      console.log('\n💡 To use saved session in future tests:');
      console.log('   Uncomment line 37 in playwright.config.ts');
      
      expect(results.length).toBeGreaterThan(0);
      
    } catch (error) {
      console.error('\n❌ Error during search:', error);
      await page.screenshot({ path: path.join(screenshotDir, 'error.png') });
      throw error;
    }
  });
});
