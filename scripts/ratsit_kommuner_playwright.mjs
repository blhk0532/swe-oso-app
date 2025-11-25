#!/usr/bin/env node

import mysql from 'mysql2/promise';
import { chromium } from 'playwright';

async function scrapeRatsitKommuner() {
    console.log('Starting Ratsit municipality scraping with Playwright...');

    let browser = null;
    let connection = null;

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

        // Go to Ratsit person page
        await page.goto('https://www.ratsit.se/personer', {
            waitUntil: 'networkidle',
            timeout: 30000,
        });

        // Extract all municipality links and counts
        const municipalities = await page.evaluate(() => {
            const links = Array.from(
                document.querySelectorAll('.tree-structure__ul a'),
            );
            const result = [];

            for (const link of links) {
                const kommuneMatch = link.href.match(/\/personer\/(.+)$/);
                if (kommuneMatch) {
                    const kommune = kommuneMatch[1];

                    // Get count from sibling span with class tree-structure__count
                    let count = 0;
                    const parentElement = link.parentElement;
                    if (parentElement) {
                        const countSpan = parentElement.querySelector(
                            '.tree-structure__count',
                        );
                        if (countSpan) {
                            const countText = countSpan.textContent.replace(
                                /[^\d]/g,
                                '',
                            );
                            if (countText) {
                                count = parseInt(countText);
                            }
                        }
                    }

                    result.push({
                        kommun: kommune,
                        count: count,
                    });
                }
            }

            return result;
        });

        console.log(`Found ${municipalities.length} municipalities`);

        // Show first few results with counts for debugging
        console.log('Sample results:');
        municipalities.slice(0, 5).forEach((m) => {
            console.log(`  ${m.kommun}: ${m.count} persons`);
        });

        // Database connection
        connection = await mysql.createConnection({
            host: '127.0.0.1',
            port: '3306',
            user: 'root',
            password: 'bkkbkk',
            database: 'fireflow',
            charset: 'utf8mb4',
        });

        // Save to database
        for (const municipality of municipalities) {
            try {
                // Decode URL-encoded municipality name
                const decodedKommun = decodeURIComponent(
                    municipality.kommun,
                ).replace('-kommun', '');

                // Check if municipality already exists
                const [rows] = await connection.execute(
                    'SELECT id FROM ratsit_kommuner_sverige WHERE kommun = ?',
                    [decodedKommun],
                );

                let existingId = null;
                if (rows.length > 0) {
                    existingId = rows[0].id;
                }

                // Generate Ratsit search link
                const ratsitLink = `https://www.ratsit.se/personer/${municipality.kommun}`;



                // Insert or update municipality
                if (existingId) {
                    await connection.execute(
                        'UPDATE ratsit_kommuner_sverige SET personer_total = ?, ratsit_link = ? WHERE id = ?',
                        [
                            municipality.count,
                            ratsitLink,
                            existingId,
                        ],
                    );
                } else {
                    await connection.execute(
                        'INSERT INTO ratsit_kommuner_sverige (kommun, personer_total, ratsit_link) VALUES (?, ?, ?)',
                        [
                            decodedKommun,
                            municipality.count,
                            ratsitLink,
                        ],
                    );
                }

                console.log(
                    `Processed ${decodedKommun} (${municipality.count} persons)`,
                );
            } catch (error) {
                console.error(
                    `Error processing ${municipality.kommun}:`,
                    error,
                );
            }
        }

        console.log('Scraping completed successfully');
    } catch (error) {
        console.error('Scraping error:', error);
        globalThis.process?.exit(1);
    } finally {
        if (browser) {
            await browser.close();
        }
        if (connection) {
            await connection.end();
        }
    }
}

// Run the scraper
scrapeRatsitKommuner();
