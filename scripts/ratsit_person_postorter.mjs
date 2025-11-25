#!/usr/bin/env node
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const projectRoot = join(__dirname, '..');

async function savePersonPostortData(
    postOrt,
    postNummer,
    personCount,
    ratsitLink,
) {
    try {
        const { exec } = await import('child_process');
        const { promisify } = await import('util');
        const execAsync = promisify(exec);

        // Use artisan tinker to save data
        const { stdout, stderr } = await execAsync(
            `cd ${projectRoot} && php artisan tinker --execute="\\App\\Models\\RatsitPersonPostorter::create(['post_ort' => '${postOrt}', 'post_nummer' => '${postNummer}', 'person_count' => ${personCount}, 'ratsit_link' => '${ratsitLink}']); echo 'Saved person postort: ${postOrt} ${postNummer}, Count: ${personCount}';"`,
        );

        if (stderr) {
            console.error('Save stderr:', stderr);
        }

        console.log('Save result:', stdout.trim());
        return true;
    } catch (error) {
        console.error('Error saving person postort data:', error.message);
        return false;
    }
}

async function scrapeRatsitPersonPostorter(url) {
    console.log(`Starting Ratsit person postort scraping for: ${url}`);

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

        // Try to find links that match postnummer pattern
        const allLinks = await page.$$('a');
        let postortElements = [];

        for (const element of allLinks) {
            try {
                const href = await element.getAttribute('href');
                const text = await element.textContent();

                // Check if this looks like a postort link (using robust pattern from ratsit_postorter_playwright.mjs)
                if (href && href.includes('/personer/')) {
                    const text = await element.textContent();

                    // Use the same robust pattern as the working ratsit_postorter_playwright.mjs
                    const postortMatch = text.match(
                        /^(.+?)\s*[-]\s*(\d{3})\s*\d{2})\s*[-]\s*(\d+)/,
                    );

                    if (postortMatch) {
                        const postOrt = postortMatch[1].trim();
                        const postNummer = postortMatch[2].replace(/\s/g, '');

                        // Extract person count
                        let personCount = 0;
                        const countMatch = text.match(/\(([\d\s]+)\)/);
                        if (countMatch) {
                            // Remove all spaces and convert to integer
                            personCount = parseInt(
                                countMatch[1].replace(/\s/g, ''),
                            );
                        }

                        postortElements.push(element);
                        console.log(
                            `Added postort element: "${text}" -> ${href}`,
                        );
                    } else {
                        console.log(`Skipped element: "${text}" -> ${href}`);
                    }
                } else {
                    console.log(`Skipped element: "${text}" -> ${href}`);
                }
            } catch (e) {
                // Skip problematic elements
            }
        }

        console.log(`Found ${postortElements.length} postort elements`);

        if (postortElements.length === 0) {
            console.log('No postort links found');
            await browser.close();
            return;
        }

        let totalPostorter = 0;

        for (const element of postortElements) {
            try {
                const postortText = await element.textContent();
                const postortLink = await element.getAttribute('href');

                // Extract post_ort and post_nummer from text/link
                // Example: "Surte 445 55 (528 st)" or "Ydre - 573 74"
                const postortMatch = postortText.match(
                    /^(.+?)\s*[-]\s*(\d{3})\s*\d{2})\s*[-]\s*(\d+)/,
                );

                if (!postortMatch) continue;

                const postOrt = postortMatch[1];
                const postNummer = postortMatch[2];

                // Extract person count
                let personCount = 0;
                const countMatch = postortText.match(/\(([\d\s]+)\)/);
                if (countMatch) {
                    // Remove all spaces and convert to integer
                    personCount = parseInt(countMatch[1].replace(/\s/g, ''));
                }

                // Construct full URL if it's relative
                const fullLink = postortLink.startsWith('http')
                    ? postortLink
                    : `https://www.ratsit.se${postortLink}`;

                console.log(
                    `Found postort: ${postOrt} ${postNummer}, Count: ${personCount}, Link: ${fullLink}`,
                );
                console.log(
                    `Debug - postortText: "${postortText}", countMatch: ${JSON.stringify(countMatch)}`,
                );

                // Save to database
                const saved = await savePersonPostortData(
                    postOrt,
                    postNummer,
                    personCount,
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
        'Example: node ratsit_person_postorter.mjs "https://www.ratsit.se/personer/Ale-kommun"',
    );
    process.exit(1);
}

// Run scraper
scrapeRatsitPersonPostorter(url).catch(console.error);
