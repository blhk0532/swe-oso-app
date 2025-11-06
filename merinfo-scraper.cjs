const { chromium } = require('playwright-extra');
const stealthPlugin = require('puppeteer-extra-plugin-stealth')();

// Apply stealth plugin
chromium.use(stealthPlugin);

async function searchMerinfo(searchQuery) {
    let browser = null;
    
    try {
        console.log('🚀 Launching browser...');
        
        // Launch browser
        browser = await chromium.launch({ 
            headless: false,
            slowMo: 100,
        });

        // Create context with realistic settings
        const context = await browser.newContext({
            viewport: { width: 1280, height: 720 },
            userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            acceptDownloads: false,
            javaScriptEnabled: true,
        });

        const page = await context.newPage();

        console.log('🌐 Navigating to merinfo.se...');
        
        // Navigate to merinfo.se
        await page.goto('https://www.merinfo.se', { 
            waitUntil: 'domcontentloaded',
            timeout: 30000 
        });

        console.log('✅ Page loaded');

        // Wait a moment for page to fully load
        await page.waitForTimeout(2000);

        // Take initial screenshot
        await page.screenshot({ path: 'initial-page.png' });
        console.log('📸 Initial screenshot saved');

        // Look for search field - try multiple selectors
        console.log('🔍 Looking for search field...');
        
        const searchSelectors = [
            'input[type="search"]',
            'input.search-field-input',
            'input[name="search"]',
            'input[placeholder*="sök"]',
            'input[placeholder*="search"]',
            '.search-field input',
            'input.form-control',
            '#search'
        ];

        let searchField = null;
        let usedSelector = '';

        for (const selector of searchSelectors) {
            searchField = await page.$(selector);
            if (searchField) {
                usedSelector = selector;
                console.log(`✅ Found search field with selector: "${selector}"`);
                break;
            }
        }

        if (!searchField) {
            // Debug: show all inputs on page
            const allInputs = await page.$$eval('input', inputs => 
                inputs.map(input => ({
                    type: input.type,
                    name: input.name,
                    placeholder: input.placeholder,
                    className: input.className,
                    id: input.id
                }))
            );
            console.log('🔍 All inputs found:', allInputs);
            throw new Error('❌ Could not find search field');
        }

        // Click and type in search field
        console.log(`⌨️ Typing: "${searchQuery}"`);
        await searchField.click({ delay: 100 });
        await page.waitForTimeout(500);
        await searchField.fill(searchQuery, { delay: 50 });
        await page.waitForTimeout(1000);

        // Submit search
        console.log('🚀 Submitting search...');
        
        // Try pressing Enter first
        await searchField.press('Enter');
        
        // Wait for navigation
        await page.waitForLoadState('networkidle', { timeout: 15000 });
        await page.waitForTimeout(3000);

        // Check current state
        const currentUrl = page.url();
        const pageTitle = await page.title();
        
        console.log(`📄 Current URL: ${currentUrl}`);
        console.log(`📄 Page Title: ${pageTitle}`);

        // Take screenshot of results
        await page.screenshot({ path: 'after-search.png', fullPage: true });
        console.log('📸 Search results screenshot saved');

        // Check page content for indicators
        const pageContent = await page.content();
        
        // Look for result indicators
        const resultIndicators = ['resultat', 'sökresultat', 'träffar', 'results', 'search results'];
        const blockingIndicators = ['captcha', 'cloudflare', 'security', 'robot', 'human verification'];

        let foundResults = false;
        let foundBlocking = false;

        const contentLower = pageContent.toLowerCase();
        
        for (const indicator of resultIndicators) {
            if (contentLower.includes(indicator)) {
                foundResults = true;
                console.log(`✅ Found results indicator: "${indicator}"`);
                break;
            }
        }

        for (const blocker of blockingIndicators) {
            if (contentLower.includes(blocker)) {
                foundBlocking = true;
                console.log(`⚠️ Found blocking indicator: "${blocker}"`);
                break;
            }
        }

        if (foundResults && !foundBlocking) {
            console.log('🎉 Success! Appears to be on results page');
            
            // Extract some sample data
            const sampleData = await page.$$eval('body', (body) => {
                // Get all text content and split by lines
                const text = body[0].innerText;
                const lines = text.split('\n').map(line => line.trim()).filter(line => line.length > 0);
                return lines.slice(0, 20); // First 20 non-empty lines
            });
            
            console.log('📊 Sample page content:');
            sampleData.forEach((line, index) => {
                console.log(`  ${index + 1}: ${line.substring(0, 100)}${line.length > 100 ? '...' : ''}`);
            });
            
        } else if (foundBlocking) {
            console.log('❌ Blocked by security check');
        } else {
            console.log('❓ Uncertain about page state - check screenshots');
        }

        // Wait before closing
        console.log('⏳ Waiting 5 seconds before closing...');
        await page.waitForTimeout(5000);

    } catch (error) {
        console.error('❌ Error:', error.message);
        
        // Take error screenshot
        if (browser) {
            const pages = await browser.contexts()[0]?.pages() || [];
            if (pages[0]) {
                await pages[0].screenshot({ path: 'error.png' });
                console.log('📸 Error screenshot saved');
            }
        }
    } finally {
        if (browser) {
            await browser.close();
            console.log('🔚 Browser closed');
        }
    }
}

// Get search query from command line or use default
const searchQuery = process.argv[2] || 'Anna Andersson';
console.log(`🎯 Starting merinfo.se search for: "${searchQuery}"`);

// Run the function
searchMerinfo(searchQuery).catch(console.error);