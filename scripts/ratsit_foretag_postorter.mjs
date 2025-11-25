#!/usr/bin/env node
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const projectRoot = join(__dirname, '..');

async function saveForetagPostortData(
    postOrt,
    postNummer,
    foretagCount,
    ratsitLink,
) {
    try {
        const { exec } = await import('child_process');
        const { promisify } = await import('util');
        const execAsync = promisify(exec);

        // Use artisan tinker to save data
        const { stdout, stderr } = await execAsync(
            `cd ${projectRoot} && php artisan tinker --execute="\\App\\Models\\RatsitForetagPostorter::create(['post_ort' => '${postOrt}', 'post_nummer' => '${postNummer}', 'foretag_count' => ${foretagCount}, 'ratsit_link' => '${ratsitLink}']); echo 'Saved foretag postort: ${postOrt} ${postNummer}, Count: ${foretagCount}';"`,
        );

        if (stderr) {
            console.error('Save stderr:', stderr);
        }

        console.log('Save result:', stdout.trim());
        return true;
    } catch (error) {
        console.error('Error saving foretag postort data:', error.message);
        return false;
    }
}

async function scrapeRatsitForetagPostorter(url) {
    console.log(`Starting Ratsit foretag postort scraping for: ${url}`);

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

        // Look for postort links with more specific patterns
        console.log('Searching for postort links...');

        // Find all list items in the post-town-list
        const postortElements = await page.$$('ul.post-town-list li');
        console.log(`Found ${postortElements.length} postort list items`);

        if (postortElements.length === 0) {
            console.log('No postort links found');
            await browser.close();
            return;
        }

        let totalPostorter = 0;

        for (const element of postortElements) {
            try {
                // Get the link element within the list item
                const linkElement = await element.$('a');
                const countElement = await element.$('.tree-structure__count');

                if (!linkElement) {
                    console.log('No link element found in list item');
                    continue;
                }

                const postortText = await linkElement.textContent();
                const postortLink = await linkElement.getAttribute('href');

                console.log(
                    `Processing element: "${postortText}" -> ${postortLink}`,
                );

                // Extract post_ort and post_nummer from text
                // Example: "Alafors - 449 50"
                const postortMatch = postortText.match(
                    /(.+?)\s+-\s+(\d{3}\s+\d{2})/,
                );

                if (!postortMatch) {
                    console.log(
                        `No match for postort pattern in: "${postortText}"`,
                    );
                    continue;
                }

                const postOrt = postortMatch[1].trim();
                const postNummer = postortMatch[2];

                // Extract foretag count from the count span
                let foretagCount = 0;
                if (countElement) {
                    const countText = await countElement.textContent();
                    const countMatch = countText.match(/\((\d+)\)/);
                    if (countMatch) {
                        foretagCount = parseInt(countMatch[1]);
                    }
                }

                // Construct full URL if it's relative
                const fullLink = postortLink.startsWith('http')
                    ? postortLink
                    : `https://www.ratsit.se${postortLink}`;

                console.log(
                    `Found postort: ${postOrt} ${postNummer}, Count: ${foretagCount}, Link: ${fullLink}`,
                );

                // Save to database
                const saved = await saveForetagPostortData(
                    postOrt,
                    postNummer,
                    foretagCount,
                    fullLink,
                );

                if (saved) {
                    totalPostorter++;
                }
            } catch (error) {
                console.error(
                    'Error processing postort element:',
                    error.message,
                );
            }
        }

        console.log(
            `Scraping completed successfully. Total postorter saved: ${totalPostorter}`,
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
        'Example: node ratsit_foretag_postorter.mjs "https://www.ratsit.se/foretag/Ale-kommun"',
    );
    process.exit(1);
}

// Run scraper
scrapeRatsitForetagPostorter(url).catch(console.error);
