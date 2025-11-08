#!/usr/bin/env node

/**
 * Test script to verify address extraction from Hitta.se search results
 */

import { chromium } from 'playwright';

async function testAddressExtraction() {
  console.log('Testing address extraction from Hitta.se...\n');
  
  const browser = await chromium.launch({
    headless: true,
    executablePath: '/usr/bin/google-chrome',
  });

  const context = await browser.newContext();
  const page = await context.newPage();
  
  // Search for a specific postal code
  const searchUrl = 'https://www.hitta.se/s%C3%B6k?vad=153%2032&typ=prv';
  console.log(`Navigating to: ${searchUrl}`);
  await page.goto(searchUrl);
  
  await page.waitForSelector('li[data-test="person-item"]', { timeout: 10000 });
  console.log('Search results loaded\n');
  
  // Extract first 3 items
  const items = await page.$$('li[data-test="person-item"]');
  console.log(`Found ${items.length} results\n`);
  
  for (let i = 0; i < Math.min(3, items.length); i++) {
    const item = items[i];
    console.log(`=== Person ${i + 1} ===`);
    
    // Extract name
    try {
      const title = await item.$('h2[data-test="search-result-title"]');
      if (title) {
        const titleText = await title.textContent();
        console.log(`Name: ${titleText?.trim()}`);
      }
    } catch (e) {
      console.log('Name: ERROR');
    }
    
    // Extract address
    try {
      const addressP = await item.$('p.text-body-long-sm-regular');
      if (addressP) {
        const addressText = await addressP.textContent();
        console.log(`Raw address text: ${addressText}`);
        
        if (addressText) {
          // Test NEW regex-based parsing
          console.log('\n--- NEW REGEX PARSING ---');
          // Find LAST occurrence of postal code pattern (3 digits + space + 2 digits + city)
          const postalMatch = addressText.match(/(\d{3})\s+(\d{2})\s+([^\d]+)$/);
          
          if (postalMatch) {
            console.log('✓ Postal code pattern matched!');
            const postnummer = `${postalMatch[1]} ${postalMatch[2]}`.trim();
            const postort = postalMatch[3].trim();
            
            // Everything before the postal code is gender + street
            const beforePostal = addressText.substring(0, addressText.indexOf(postalMatch[0]));
            
            // Extract gender
            const genderMatch = beforePostal.match(/^(Kvinna|Man|Kvinno)/);
            const gender = genderMatch ? genderMatch[1] : null;
            
            // Street is everything after gender
            const streetStart = genderMatch ? genderMatch[0].length : 0;
            const gatuadress = beforePostal.substring(streetStart).trim();
            
            console.log(`  Gender: ${gender}`);
            console.log(`  Street: ${gatuadress}`);
            console.log(`  Postal: ${postnummer}`);
            console.log(`  City: ${postort}`);
          } else {
            console.log('✗ Postal code pattern did not match');
          }
          
          if (postalMatch) {
            console.log('✓ Postal code pattern matched!');
            const postnummer = `${postalMatch[1]} ${postalMatch[2]}`.trim();
            const postort = postalMatch[3].trim();
            
            // Everything before the postal code is gender + street
            const beforePostal = addressText.substring(0, addressText.indexOf(postalMatch[0]));
            
            // Extract gender
            const genderMatch = beforePostal.match(/^(Kvinna|Man|Kvinno)/);
            const gender = genderMatch ? genderMatch[1] : null;
            
            // Street is everything after gender
            const streetStart = genderMatch ? genderMatch[0].length : 0;
            const gatuadress = beforePostal.substring(streetStart).trim();
            
            console.log(`  Gender: ${gender}`);
            console.log(`  Street: ${gatuadress}`);
            console.log(`  Postal: ${postnummer}`);
            console.log(`  City: ${postort}`);
          } else {
            console.log('✗ Postal code pattern did not match');
          }
          
          // Test OLD line-based parsing
          console.log('\n--- OLD LINE-BASED PARSING ---');
          const addressLines = addressText.split('\n').map(l => l.trim()).filter(Boolean);
          console.log(`Parsed lines (${addressLines.length}):`);
          addressLines.forEach((line, idx) => {
            console.log(`  [${idx}]: "${line}"`);
          });
          
          if (addressLines.length >= 3) {
            const gatuadress = addressLines[1];
            const postcodeCity = addressLines[2];
            const parts = postcodeCity.split(' ', 3);
            const postnummer = parts.length >= 2 ? `${parts[0]} ${parts[1]}`.trim() : null;
            const postort = parts.length >= 3 ? parts[2].trim() : null;
            
            console.log(`\nOld method extracted:`);
            console.log(`  gatuadress: ${gatuadress}`);
            console.log(`  postnummer: ${postnummer}`);
            console.log(`  postort: ${postort}`);
          }
        }
      } else {
        console.log('Address paragraph not found');
      }
    } catch (e) {
      console.log(`Address extraction error: ${e.message}`);
    }
    
    console.log('');
  }
  
  await browser.close();
  console.log('Test complete');
}

testAddressExtraction().catch(error => {
  console.error('Test failed:', error);
  process.exit(1);
});
