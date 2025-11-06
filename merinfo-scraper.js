const playwright = require('playwright-extra');
const stealthPlugin = require('puppeteer-extra-plugin-stealth')();

playwright.use(stealthPlugin);

class MerInfoScraper {
    constructor() {
        this.browser = null;
        this.page = null;
    }

    async init() {
        this.browser = await playwright.chromium.launch({
            headless: false,
            slowMo: 50,
        });

        this.page = await this.browser.newPage();
        
        // Set realistic viewport
        await this.page.setViewportSize({ width: 1280, height: 720 });
    }

    async navigateToMerInfo() {
        console.log('🌐 Navigating to merinfo.se...');
        await this.page.goto('https://www.merinfo.se', {
            waitUntil: 'networkidle',
            timeout: 30000
        });
        return true;
    }

    async performSearch(searchQuery) {
        console.log(`🔍 Performing search for: "${searchQuery}"`);

        // Multiple strategies to find search field
        const searchSelectors = [
            'input.search-field-input',
            'input[type="search"]',
            'input[name="q"]',
            'input[placeholder*="sök"]',
            'input[placeholder*="search"]',
            '.search-field input'
        ];

        for (const selector of searchSelectors) {
            const element = await this.page.$(selector);
            if (element) {
                console.log(`✅ Using selector: ${selector}`);
                
                await element.click({ delay: 100 });
                await element.fill(searchQuery, { delay: 30 });
                await element.press('Enter');
                
                return true;
            }
        }

        throw new Error('Could not find search field with any selector');
    }

    async waitForResults(timeout = 15000) {
        console.log('⏳ Waiting for search results...');
        
        try {
            // Wait for URL change or network idle
            await this.page.waitForLoadState('networkidle', { timeout });
            
            // Additional wait for content to render
            await this.page.waitForTimeout(3000);
            
            return true;
        } catch (error) {
            console.warn('Timeout waiting for results, continuing anyway...');
            return false;
        }
    }

    async extractData() {
        console.log('📊 Extracting data from results...');
        
        const data = {
            url: this.page.url(),
            title: await this.page.title(),
            results: []
        };

        // Try to extract search result items
        try {
            data.results = await this.page.$$eval(
                '.result, .search-result, .item, li',
                elements => elements.map(el => ({
                    text: el.textContent?.trim().substring(0, 200),
                    html: el.innerHTML?.substring(0, 300)
                }))
            );
        } catch (error) {
            console.log('Could not extract structured results');
        }

        return data;
    }

    async close() {
        if (this.browser) {
            await this.browser.close();
            console.log('🔚 Browser closed');
        }
    }

    async scrape(searchQuery) {
        try {
            await this.init();
            await this.navigateToMerInfo();
            await this.performSearch(searchQuery);
            await this.waitForResults();
            
            const data = await this.extractData();
            
            // Save screenshot
            await this.page.screenshot({ path: 'final-results.png' });
            
            return data;
            
        } catch (error) {
            console.error('❌ Scraping failed:', error);
            
            // Save error screenshot
            if (this.page) {
                await this.page.screenshot({ path: 'error-state.png' });
            }
            
            throw error;
        } finally {
            await this.close();
        }
    }
}

// Usage
async function main() {
    const scraper = new MerInfoScraper();
    const searchTerm = process.argv[2] || 'Karin Johansson';
    
    try {
        const results = await scraper.scrape(searchTerm);
        console.log('🎉 Scraping completed!');
        console.log('Page Title:', results.title);
        console.log('URL:', results.url);
        console.log(`Found ${results.results.length} potential result elements`);
        
    } catch (error) {
        console.error('💥 Script failed:', error.message);
    }
}

main();