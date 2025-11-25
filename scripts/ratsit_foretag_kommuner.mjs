#!/usr/bin/env node
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const projectRoot = join(__dirname, '..');

async function saveForetagKommunData(kommun, foretagCount, ratsitLink) {
    try {
        const { exec } = await import('child_process');
        const { promisify } = await import('util');
        const execAsync = promisify(exec);

        // Use artisan tinker to save data
        const { stdout, stderr } = await execAsync(
            `cd ${projectRoot} && php artisan tinker --execute="\\App\\Models\\RatsitForetagKommuner::create(['kommun' => '${kommun}', 'foretag_count' => ${foretagCount}, 'ratsit_link' => '${ratsitLink}']); echo 'Saved foretag kommun: ${kommun}, Count: ${foretagCount}';"`,
        );

        if (stderr) {
            console.error('Save stderr:', stderr);
        }

        console.log('Save result:', stdout.trim());
        return true;
    } catch (error) {
        console.error('Error saving foretag kommun data:', error.message);
        return false;
    }
}

async function scrapeRatsitForetagKommuner(url) {
    console.log(`Starting Ratsit foretag kommun scraping for: ${url}`);

    const { chromium } = await import('playwright');
    let browser = null;

    try {
        // Launch browser with realistic settings
        browser = await chromium.launch({
            headless: true,
            executablePath: '/usr/bin/google-chrome',
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

        const context = await browser.newContext({
            userAgent:
                'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
            viewport: { width: 1920, height: 1080 },
            locale: 'sv-SE',
        });

        const page = await context.newPage();

        // Navigate to URL
        await page.goto(url, { waitUntil: 'networkidle' });

        // Wait for page to load
        await page.waitForTimeout(2000);

        // Handle cookie dialog if present
        try {
            const cookieButton = await page.$(
                'button:has-text("Acceptera alla"), button:has-text("Accept all"), button[data-testid="accept-all-button"]',
            );
            if (cookieButton) {
                await cookieButton.click();
                await page.waitForTimeout(1000);
            }
        } catch (error) {
            console.log('No cookie dialog found or already handled');
        }

        // Find all kommun links
        const kommunElements = await page.$$('a[href*="/foretag/"]');

        if (kommunElements.length === 0) {
            console.log('No kommun links found');
            await browser.close();
            return;
        }

        let totalKommuner = 0;

        for (const element of kommunElements) {
            try {
                const kommunName = await element.textContent();
                const kommunLink = await element.getAttribute('href');

                // Extract foretag count if available
                let foretagCount = 0;
                const parentElement = await element.$('..');
                if (parentElement) {
                    const parentText = await parentElement.textContent();
                    // Handle counts with spaces like "2 079" -> "2079"
                    const countMatch = parentText.match(/\(([\d\s]+)\)/);
                    if (countMatch) {
                        // Remove all spaces and convert to integer
                        foretagCount = parseInt(
                            countMatch[1].replace(/\s/g, ''),
                        );
                    }
                }

                // Construct full URL if it's relative
                const fullLink = kommunLink.startsWith('http')
                    ? kommunLink
                    : `https://www.ratsit.se${kommunLink}`;

                console.log(
                    `Found kommun: ${kommunName}, Count: ${foretagCount}, Link: ${fullLink}`,
                );

                // Save to database
                const saved = await saveForetagKommunData(
                    kommunName,
                    foretagCount,
                    fullLink,
                );

                if (saved) {
                    totalKommuner++;
                }
            } catch (error) {
                console.error(
                    'Error processing kommun element:',
                    error.message,
                );
            }
        }

        console.log(
            `Scraping completed successfully. Total kommuner saved: ${totalKommuner}`,
        );
        await browser.close();
    } catch (error) {
        console.error('Error during scraping:', error);
        if (browser) {
            await browser.close();
        }
    }
}

// Get URL from command line argument
const url = process.argv[2];

if (!url) {
    console.error('Please provide a URL as argument');
    console.error(
        'Example: node ratsit_foretag_kommuner.mjs "https://www.ratsit.se/foretag"',
    );
    process.exit(1);
}

// Run scraper
scrapeRatsitForetagKommuner(url).catch(console.error);
