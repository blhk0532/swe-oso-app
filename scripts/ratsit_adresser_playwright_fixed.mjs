import { unlinkSync, writeFileSync } from 'fs';
import { dirname, join } from 'path';
import { chromium } from 'playwright';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const projectRoot = join(__dirname, '..');

async function saveAddressData(
    postOrt,
    postNummer,
    gatuadressNamn,
    gatuadressCount,
    gatuadressNummerLink,
) {
    try {
        // Create a temporary PHP script to save the data
        const tempScript = `<?php
require_once '${projectRoot}/vendor/autoload.php';
$app = require_once '${projectRoot}/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

\\App\\Models\\RatsitAdresserSverige::create([
    'post_ort' => '${postOrt}',
    'post_nummer' => '${postNummer}',
    'gatuadress_namn' => '${gatuadressNamn}',
    'gatuadress_count' => ${gatuadressCount},
    'gatuadress_nummer_link' => '${gatuadressNummerLink}'
]);

echo "Saved address: ${gatuadressNamn}, ${postNummer} ${postOrt}";
`;

        const tempFile = join(projectRoot, 'temp_save_address.php');
        writeFileSync(tempFile, tempScript);

        const { exec } = await import('child_process');
        const { promisify } = await import('util');
        const execAsync = promisify(exec);

        const { stdout, stderr } = await execAsync(
            `cd ${projectRoot} && php temp_save_address.php`,
        );

        // Clean up temp file
        unlinkSync(tempFile);

        if (stderr) {
            console.error('Save address stderr:', stderr);
        }

        console.log('Save result:', stdout.trim());
        return true;
    } catch (error) {
        console.error('Error saving address data:', error.message);
        return false;
    }
}

async function scrapeRatsitAddresses(url) {
    console.log(`Starting Ratsit address scraping for: ${url}`);

    try {
        const browser = await chromium.launch({
            headless: true,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
            ],
        });

        const page = await browser.newPage();

        // Set user agent to avoid detection
        await page.setUserAgent(
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        );

        // Navigate to the URL
        await page.goto(url, { waitUntil: 'networkidle' });

        // Wait for the page to load
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

        // Find the main heading to extract post_ort and post_nummer
        const headingText = await page.$eval(
            'h1.tree-structure__h1.site-h2',
            (el) => el.textContent.trim(),
        );

        console.log('Heading found:', headingText);

        // Extract post_ort and post_nummer from heading
        // Example: "Privatpersoner i Surte på postnummer 445 55 (528 st)"
        const headingMatch = headingText.match(
            /i\s+(\w+)\s+på\s+postnummer\s+(\d+\s+\d+)/,
        );

        if (!headingMatch) {
            console.error(
                'Could not extract post_ort and post_nummer from heading',
            );
            await browser.close();
            return;
        }

        const postOrt = headingMatch[1];
        const postNummer = headingMatch[2];

        console.log(
            `Extracted - Post ort: ${postOrt}, Post nummer: ${postNummer}`,
        );

        // Find all tree-structure__ul elements and extract address data
        const addressElements = await page.$$('.tree-structure__ul');

        if (addressElements.length === 0) {
            console.log('No address lists found');
            await browser.close();
            return;
        }

        let totalAddresses = 0;

        for (const ulElement of addressElements) {
            // Get all li elements within this ul
            const listItems = await ulElement.$$('li');

            for (const li of listItems) {
                try {
                    // Extract address name and link
                    const linkElement = await li.$('a');
                    if (!linkElement) continue;

                    const gatuadressNamn = await linkElement.textContent();
                    const gatuadressNummerLink =
                        await linkElement.getAttribute('href');

                    // Extract address count
                    const countElement = await li.$('.tree-structure__count');
                    let gatuadressCount = 0;

                    if (countElement) {
                        const countText = await countElement.textContent();
                        const countMatch = countText.match(/\((\d+)\)/);
                        if (countMatch) {
                            gatuadressCount = parseInt(countMatch[1]);
                        }
                    }

                    // Construct full URL if it's relative
                    const fullLink = gatuadressNummerLink.startsWith('http')
                        ? gatuadressNummerLink
                        : `https://www.ratsit.se${gatuadressNummerLink}`;

                    console.log(
                        `Found address: ${gatuadressNamn}, Count: ${gatuadressCount}, Link: ${fullLink}`,
                    );

                    // Save to database
                    const saved = await saveAddressData(
                        postOrt,
                        postNummer,
                        gatuadressNamn,
                        gatuadressCount,
                        fullLink,
                    );

                    if (saved) {
                        totalAddresses++;
                    }
                } catch (error) {
                    console.error(
                        'Error processing address item:',
                        error.message,
                    );
                }
            }
        }

        console.log(
            `Scraping completed successfully. Total addresses saved: ${totalAddresses}`,
        );
        await browser.close();
    } catch (error) {
        console.error('Error during scraping:', error);
        if (error.message.includes("Executable doesn't exist")) {
            console.error(
                '\nPlaywright browsers are not installed. Please run:',
            );
            console.error('npx playwright install chromium');
            console.error('\nOr install manually:');
            console.error(
                'PLAYWRIGHT_BROWSERS_PATH=0 npx playwright install chromium',
            );
        }
    }
}

// Get URL from command line argument
const url = process.argv[2];

if (!url) {
    console.error('Please provide a URL as argument');
    console.error(
        'Example: node ratsit_adresser_playwright.mjs "https://www.ratsit.se/personer/Ale-kommun/Surte-44555"',
    );
    process.exit(1);
}

// Run the scraper
scrapeRatsitAddresses(url).catch(console.error);
