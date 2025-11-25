#!/usr/bin/env npx tsx

import fs from 'fs';
import { Browser, BrowserContext, chromium, Page } from 'playwright';

async function getRatsitCounts(searchQuery: string): Promise<void> {
    if (!searchQuery) {
        console.error('Error: Search query is required');
        console.log('Usage: npx tsx ratsit_check_counts.tsx "[search_query]"');
        process.exit(1);
    }

    const url = `https://www.ratsit.se/sok/person?vem=${encodeURIComponent(searchQuery)}`;
    console.log(`Searching: ${url}`);

    let browser: Browser | null = null;

    try {
        // Launch browser with realistic settings
        browser = await chromium.launch({
            headless: true,
            executablePath: '/usr/bin/google-chrome', // Try system Chrome
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-accelerated-2d-canvas',
                '--no-first-run',
                '--no-zygote',
                '--disable-gpu',
            ],
        });

        const context: BrowserContext = await browser.newContext({
            userAgent:
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            viewport: { width: 1920, height: 1080 },
            locale: 'sv-SE',
        });

        const page: Page = await context.newPage();

        // Set additional headers to look more like a real browser
        await page.setExtraHTTPHeaders({
            'Accept-Language': 'sv-SE,sv;q=0.9,en;q=0.8,en-US;q=0.7',
            Accept: 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
        });

        console.log('Loading page...');

        // Navigate to page and wait for it to load
        await page.goto(url, {
            waitUntil: 'networkidle',
            timeout: 30000,
        });

        // Wait a bit for any dynamic content to load
        await page.waitForTimeout(2000);

        // Try to wait for search segment to appear
        try {
            await page.waitForSelector('.search-segment', { timeout: 10000 });
        } catch (e) {
            console.log('Search segment not found, proceeding anyway...');
        }

        // Get page content
        const content = await page.content();

        // Try multiple patterns to find Personer and Företag values
        let personerMatch: RegExpMatchArray | null = null;
        let foretagMatch: RegExpMatchArray | null = null;

        // Pattern 1: Look for specific search-segment div
        const searchSegmentMatch = content.match(
            /<div class="search-segment"[^>]*>([^<]+)<\/div>/,
        );
        if (searchSegmentMatch) {
            const segmentText = searchSegmentMatch[1];
            personerMatch = segmentText.match(/Personer:\s*([\d\s]+)st/);
            foretagMatch = segmentText.match(/Företag:\s*([\d\s]+)st/);
        }

        // Pattern 2: Look for any button with Personer/Företag text
        if (!personerMatch || !foretagMatch) {
            personerMatch = content.match(/Personer:\s*([\d\s]+)st/);
            foretagMatch = content.match(/Företag:\s*([\d\s]+)st/);
        }

        // Pattern 3: Try to extract from button elements specifically
        if (!personerMatch || !foretagMatch) {
            const buttonTexts = await page.$$eval(
                'button',
                (buttons: HTMLElement[]) =>
                    buttons.map((btn) => btn.textContent || '').join(' '),
            );

            personerMatch = buttonTexts.match(/Personer:\s*([\d\s]+)st/);
            foretagMatch = buttonTexts.match(/Företag:\s*([\d\s]+)st/);
        }

        // Pattern 4: Look for data attributes or other patterns
        if (!personerMatch || !foretagMatch) {
            personerMatch = content.match(/"personer":\s*(\d+)/);
            foretagMatch = content.match(/"foretag":\s*(\d+)/);
        }

        // Pattern 5: Try to get from window object
        if (!personerMatch || !foretagMatch) {
            try {
                const windowData = await page.evaluate(() => {
                    // Try to get data from various window objects
                    const data: any = {};

                    if ((window as any).__INITIAL_STATE__) {
                        data.initialState = (window as any).__INITIAL_STATE__;
                    }

                    if ((window as any).__NUXT__) {
                        data.nuxt = (window as any).__NUXT__;
                    }

                    // Look for any global search data
                    Object.keys(window).forEach((key) => {
                        if (
                            key.toLowerCase().includes('search') ||
                            key.toLowerCase().includes('result')
                        ) {
                            data[key] = (window as any)[key];
                        }
                    });

                    return data;
                });

                // Search through window data
                const dataString = JSON.stringify(windowData);
                personerMatch = dataString.match(/personer["\s:]+(\d+)/i);
                foretagMatch = dataString.match(/foretag["\s:]+(\d+)/i);
            } catch (e) {
                // Continue with other patterns
            }
        }

        if (personerMatch && foretagMatch) {
            const personer = personerMatch[1].replace(/\s/g, '');
            const foretag = foretagMatch[1].replace(/\s/g, '');

            console.log(`"personer: ${personer}",`);
            console.log(`"foretag: ${foretag}"`);
        } else {
            console.error('Could not find Personer and Företag values');
            console.log('The page structure might have changed.');

            // Save screenshot and HTML for debugging
            await page.screenshot({
                path: 'debug_screenshot.png',
                fullPage: true,
            });
            fs.writeFileSync('debug_output.html', content);
            console.log('Screenshot saved to debug_screenshot.png');
            console.log(
                'HTML content saved to debug_output.html for inspection',
            );
        }
    } catch (error) {
        console.error('Error:', error);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
}

// Get search query from command line arguments
const searchQuery = process.argv[2];

// Run the function
getRatsitCounts(searchQuery).catch(console.error);
