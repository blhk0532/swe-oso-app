#!/usr/bin/env node

import mysql from 'mysql2/promise';
import { chromium } from 'playwright';

async function getHittaCounts(searchQuery) {
    if (!searchQuery) {
        console.error('Error: Search query is required');
        console.log('Usage: node hitta_check_counts.mjs "[search_query]"');
        process.exit(1);
    }

    const url = `https://www.hitta.se/s%C3%B6k?vad=${encodeURIComponent(searchQuery)}`;
    console.log(`Searching: ${url}`);

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
        await page.waitForTimeout(3000);

        // Try to wait for search result tabs to appear
        try {
            await page.waitForSelector(
                'nav[data-trackcat="search-result-tabs"]',
                { timeout: 10000 },
            );
        } catch (e) {
            console.log('Search result tabs not found, proceeding anyway...');
        }

        // Extract counts from the page
        const counts = await page.evaluate(() => {
            const nav = document.querySelector(
                'nav[data-trackcat="search-result-tabs"]',
            );
            if (!nav) {
                return { personer: 0, foretag: 0 };
            }

            const extractCount = (titleText) => {
                const contentContainers = nav.querySelectorAll(
                    'span.style_content__nx640',
                );

                for (const container of contentContainers) {
                    const titleSpan = container.querySelector(
                        'span.style_tabTitle__EC5RP',
                    );
                    if (titleSpan?.textContent === titleText) {
                        const countSpan = container.querySelector(
                            'span.style_tabNumbers__VbAE7',
                        );
                        const countText = countSpan?.textContent?.trim();
                        return countText
                            ? parseInt(countText.replace(/,/g, ''), 10)
                            : 0;
                    }
                }
                return 0;
            };

            const foretag = extractCount('Företag');
            const personer = extractCount('Personer');

            return { personer, foretag };
        });

        if (counts.personer >= 0 || counts.foretag >= 0) {
            console.log(`"personer: ${counts.personer}",`);
            console.log(`"foretag: ${counts.foretag}"`);

            // Update database
            await updateDatabase(
                searchQuery,
                counts.personer,
                counts.foretag,
                'hitta',
            );
        } else {
            console.error('Could not find Personer and Företag values');
            console.log('The page structure might have changed.');

            // Save screenshot for debugging
            await page.screenshot({
                path: 'hitta_debug_screenshot.png',
                fullPage: true,
            });
            console.log('Screenshot saved to hitta_debug_screenshot.png');
        }
    } catch (error) {
        console.error('Error:', error);
    } finally {
        if (browser) {
            await browser.close();
        }
    }
}

async function updateDatabase(postNummer, personer, foretag, source) {
    let connection = null;

    try {
        // Database connection
        connection = await mysql.createConnection({
            host: '127.0.0.1',
            port: '3306',
            user: 'root',
            password: 'bkkbkk',
            database: 'filament',
            charset: 'utf8mb4',
        });

        // Format postnummer (add space if needed)
        const formattedPostNummer =
            postNummer.length === 5
                ? postNummer.slice(0, 3) + ' ' + postNummer.slice(3)
                : postNummer;

        // Update the database
        const updateFields =
            source === 'hitta'
                ? 'hitta_personer_total = ?, hitta_foretag_total = ?'
                : 'ratsit_personer_total = ?, ratsit_foretag_total = ?';

        const [result] = await connection.execute(
            `UPDATE post_nums SET ${updateFields}, updated_at = NOW() WHERE post_nummer = ?`,
            [personer, foretag, formattedPostNummer],
        );

        if (result.affectedRows > 0) {
            console.log(
                `Database updated: ${formattedPostNummer} -> ${source} personer: ${personer}, företag: ${foretag}`,
            );
        } else {
            console.log(
                `No record found for postnummer: ${formattedPostNummer}`,
            );
        }
    } catch (error) {
        console.error('Database error:', error.message);
    } finally {
        if (connection) {
            await connection.end();
        }
    }
}

// Get search query from command line arguments
const searchQuery = process.argv[2];

// Run the function
getHittaCounts(searchQuery).catch(console.error);
