#!/usr/bin/env node
/**
 * Merinfo.se HTML Parser and API Updater
 *
 * This script extracts personer and foretag counts from merinfo.se HTML
 * and updates the Laravel API with the extracted data.
 *
 * Usage:
 *   node merinfo_html_parser.js "15332" "<html_content>"
 *   node merinfo_html_parser.js "153 32" "$(cat html_file.html)"
 */

const https = require('https');
const http = require('http');
const { JSDOM } = require('jsdom');

// Configuration
const API_BASE_URL = process.env.LARAVEL_API_URL || 'http://localhost:8000';
const API_TOKEN = process.env.LARAVEL_API_TOKEN; // Optional Sanctum token

class MerinfoHtmlParser {
    constructor(apiUrl = API_BASE_URL, apiToken = API_TOKEN) {
        this.apiUrl = apiUrl;
        this.apiToken = apiToken;
    }

    /**
     * Extract personer and foretag counts from HTML
     */
    extractCounts(html) {
        try {
            const dom = new JSDOM(html);
            const document = dom.window.document;

            // Extract personer count (from link with href containing "d=p")
            const personerLink = document.querySelector('a[href*="d=p"] span:last-child');
            const personer = personerLink ? parseInt(personerLink.textContent.trim()) || 0 : 0;

            // Extract foretag count (from link with href containing "d=c")
            const foretagLink = document.querySelector('a[href*="d=c"] span:last-child');
            const foretag = foretagLink ? parseInt(foretagLink.textContent.trim()) || 0 : 0;

            return { personer, foretag };
        } catch (error) {
            console.error('Error parsing HTML:', error.message);
            return { personer: 0, foretag: 0 };
        }
    }

    /**
     * Update merinfo counts via API
     */
    async updateApi(postNummer, personer, foretag) {
        const url = `${this.apiUrl}/api/post-nummer/by-code/${encodeURIComponent(postNummer)}`;

        const data = {
            merinfo_personer: personer,
            merinfo_foretag: foretag
        };

        const options = {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                ...(this.apiToken && { 'Authorization': `Bearer ${this.apiToken}` })
            }
        };

        return new Promise((resolve, reject) => {
            const req = https.request(url, options, (res) => {
                let body = '';

                res.on('data', (chunk) => {
                    body += chunk;
                });

                res.on('end', () => {
                    try {
                        const response = JSON.parse(body);
                        if (res.statusCode >= 200 && res.statusCode < 300) {
                            resolve(response);
                        } else {
                            reject(new Error(`API Error ${res.statusCode}: ${response.message || body}`));
                        }
                    } catch (error) {
                        reject(new Error(`Parse Error: ${error.message}`));
                    }
                });
            });

            req.on('error', (error) => {
                reject(error);
            });

            req.write(JSON.stringify(data));
            req.end();
        });
    }

    /**
     * Process HTML and update API
     */
    async process(postNummer, html) {
        console.log(`Processing postal code: ${postNummer}`);

        // Extract counts from HTML
        const { personer, foretag } = this.extractCounts(html);
        console.log(`Extracted: personer=${personer}, foretag=${foretag}`);

        if (personer === 0 && foretag === 0) {
            console.warn('No counts found in HTML. Check if the HTML structure has changed.');
            return null;
        }

        // Update API
        try {
            const result = await this.updateApi(postNummer, personer, foretag);
            console.log('API Update successful:', result.message);
            return result;
        } catch (error) {
            console.error('API Update failed:', error.message);
            throw error;
        }
    }
}

// CLI interface
async function main() {
    const args = process.argv.slice(2);

    if (args.length < 2) {
        console.error('Usage: node merinfo_html_parser.js <post_nummer> <html_content>');
        console.error('Example: node merinfo_html_parser.js "15332" "<html>...</html>"');
        process.exit(1);
    }

    const [postNummer, html] = args;

    const parser = new MerinfoHtmlParser();
    try {
        await parser.process(postNummer, html);
        console.log('Processing completed successfully');
    } catch (error) {
        console.error('Processing failed:', error.message);
        process.exit(1);
    }
}

// Export for use as module
module.exports = { MerinfoHtmlParser };

// Run CLI if called directly
if (require.main === module) {
    main();
}