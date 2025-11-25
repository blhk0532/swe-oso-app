#!/usr/bin/env node

import mysql from 'mysql2/promise';
import { chromium } from 'playwright';

async function scrapeRatsitPostorter(url) {
    if (!url) {
        console.error('Error: URL is required');
        console.log(
            'Usage: node ratsit_postorter_playwright.mjs "https://www.ratsit.se/personer/Ydre-kommun/Bruzaholm-57599"',
        );
        process.exit(1);
    }

    console.log(`Starting Ratsit postal area scraping for: ${url}`);

    let browser = null;
    let connection = null;

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

        // Go to the specific URL
        await page.goto(url, {
            waitUntil: 'networkidle',
            timeout: 30000,
        });

        // Wait for cookie dialog to potentially appear and handle it
        try {
            await page.waitForSelector('#CybotCookiebotDialog', {
                timeout: 5000,
            });
            console.log('Cookie dialog found, trying to accept...');

            // Try to find and click accept button
            const acceptButton = await page.$(
                '#CybotCookiebotDialog button[type="submit"], #CybotCookiebotDialog button, .accept-cookies',
            );
            if (acceptButton) {
                await acceptButton.click();
                console.log('Clicked accept cookies button');
                await page.waitForTimeout(2000);
            }
        } catch (e) {
            console.log('No cookie dialog found or already handled');
        }

        // Wait for content to load
        await page.waitForTimeout(5000);

        // Wait for any dynamic content to load
        await page.waitForTimeout(3000);

        // Wait for any dynamic content to load
        await page.waitForTimeout(3000);

        // Check if content has loaded
        await page.waitForFunction(
            () => {
                return document.body && document.body.innerHTML.length > 1000;
            },
            { timeout: 10000 },
        );

        console.log('Page content loaded, checking for postal areas...');

        // Extract postal area information
        const postorter = await page.evaluate(() => {
            try {
                const result = [];

                // Debug: Get page title and content
                const pageTitle = document.title;
                const hasContent =
                    document.body && document.body.innerHTML.length > 1000;

                console.log(`Page title: "${pageTitle}"`);
                console.log(`Has body content: ${hasContent}`);

                // Look for postal area links - try broader approach
                const allLinks = document.querySelectorAll(
                    'a[href*="/personer/"]',
                );
                const postalAreaLinks = Array.from(allLinks).filter((link) => {
                    const text = link.textContent.trim();
                    return /\d{3}\s*\d{2}/.test(text);
                });

                console.log(
                    `Found ${postalAreaLinks.length} potential postal area links`,
                );

                // Debug: Show what we found
                for (let i = 0; i < Math.min(3, postalAreaLinks.length); i++) {
                    const link = postalAreaLinks[i];
                    console.log(
                        `Link ${i}: href="${link.href}", text="${link.textContent.trim()}"`,
                    );
                }

                // Also check for any other elements that might contain postal areas
                const allElements = document.querySelectorAll('li, div, span');
                console.log(`Total elements found: ${allElements.length}`);

                // Look for any elements containing postal codes
                const postalCodeElements = Array.from(allElements).filter(
                    (el) => {
                        const text = el.textContent || '';
                        return /\d{3}\s*\d{2}/.test(text);
                    },
                );
                console.log(
                    `Elements with postal codes: ${postalCodeElements.length}`,
                );

                // Show first few elements with postal codes
                for (
                    let i = 0;
                    i < Math.min(3, postalCodeElements.length);
                    i++
                ) {
                    const el = postalCodeElements[i];
                    console.log(
                        `Postal element ${i}: "${el.textContent.trim()}"`,
                    );
                }

                for (const link of postalAreaLinks) {
                    const href = link.href;
                    const text = link.textContent.trim();
                    const countSpan = link.parentElement?.querySelector(
                        '.tree-structure__count',
                    );

                    // Extract postal area name and postal code from text
                    // Pattern: "Ydre - 573 74" where "573 74" is the postal code
                    const match = text.match(
                        /^(.+?)\s*[-(]\s*(\d{3}\s*\d{2})\s*[)-]\s*$/,
                    );

                    if (match) {
                        const postOrt = match[1].trim();
                        const postNummer = match[2].replace(/\s/g, '');
                        const countText =
                            countSpan?.textContent.replace(/[^\d]/g, '') || '0';
                        const count = parseInt(countText) || 0;

                        result.push({
                            post_ort: postOrt,
                            post_nummer: postNummer,
                            post_nummer_count: count,
                            post_nummer_link: href,
                        });
                    } else {
                        // If no match, try to extract postal code differently
                        const parts = text.split('-');
                        if (parts.length >= 2) {
                            const postOrt = parts[0].trim();
                            const postNummer = parts[1]?.trim() || '';
                            const countText =
                                countSpan?.textContent.replace(/[^\d]/g, '') ||
                                '0';
                            const count = parseInt(countText) || 0;

                            result.push({
                                post_ort: postOrt,
                                post_nummer: postNummer,
                                post_nummer_count: count,
                                post_nummer_link: href,
                            });
                        }
                    }
                }

                return result;
            } catch (error) {
                console.error('Error in page evaluation:', error);
                return [];
            }
        });

        console.log(`Found ${postorter.length} postal areas`);

        // Show first few results for debugging
        console.log('Sample results:');
        postorter.slice(0, 5).forEach((p) => {
            console.log(
                `  ${p.post_ort}: ${p.post_nummer} (${p.post_nummer_count} addresses)`,
            );
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
        for (const postort of postorter) {
            try {
                // Check if postal area already exists
                const [rows] = await connection.execute(
                    'SELECT id FROM ratsit_postorter_sverige WHERE post_ort = ? AND post_nummer = ?',
                    [postort.post_ort, postort.post_nummer],
                );

                let existingId = null;
                if (rows.length > 0) {
                    existingId = rows[0].id;
                }

                // Insert or update postal area
                if (existingId) {
                    await connection.execute(
                        'UPDATE ratsit_postorter_sverige SET post_nummer_count = ?, post_nummer_link = ? WHERE id = ?',
                        [
                            postort.post_nummer_count,
                            postort.post_nummer_link,
                            existingId,
                        ],
                    );
                    console.log(
                        `Updated ${postort.post_ort} ${postort.post_nummer} (${postort.post_nummer_count} addresses)`,
                    );
                } else {
                    await connection.execute(
                        'INSERT INTO ratsit_postorter_sverige (post_ort, post_nummer, post_nummer_count, post_nummer_link) VALUES (?, ?, ?, ?)',
                        [
                            postort.post_ort,
                            postort.post_nummer,
                            postort.post_nummer_count,
                            postort.post_nummer_link,
                        ],
                    );
                    console.log(
                        `Added ${postort.post_ort} ${postort.post_nummer} (${postort.post_nummer_count} addresses)`,
                    );
                }
            } catch (error) {
                console.error(`Error processing ${postort.post_ort}:`, error);
            }
        }

        console.log('Scraping completed successfully');
    } catch (error) {
        console.error('Scraping error:', error);
        process.exit(1);
    } finally {
        if (browser) {
            await browser.close();
        }
        if (connection) {
            await connection.end();
        }
    }
}

// Get URL from command line arguments
const url = process.argv[2];

// Run the scraper
scrapeRatsitPostorter(url);
