import https from 'https'

async function scrapeRatsitKommuner() {
    try {
        console.log('Starting Ratsit municipality scraping...');

        // Get the page with all municipalities
        const response = await fetch('https://www.ratsit.se/personer');
        const html = await response.text();

        // Parse the HTML to extract municipality data
        const municipalities = [];

        // Find all municipality links
        const linkRegex = /<a href="\/personer\/([^"]+)"/g;
        const matches = html.match(linkRegex);

        if (matches) {
            for (const match of matches) {
                const kommune = match[1];
                const countMatch = match[0].match(
                    /<span class="tree-structure__count">([^<]+)<\/span>/,
                );
                const count = countMatch
                    ? parseInt(countMatch[1].replace(/&nbsp;/g, ''))
                    : 0;

                municipalities.push({
                    kommun: kommune,
                    count: count,
                });
            }
        }

        console.log(`Found ${municipalities.length} municipalities`);

        // Save to database
        const mysql = require('mysql2');
        const connection = mysql.createConnection({
            host: process.env.DB_HOST || 'localhost',
            user: process.env.DB_USERNAME || 'root',
            password: process.env.DB_PASSWORD || '',
            database: process.env.DB_DATABASE || 'fireflow',
        });

        for (const municipality of municipalities) {
            try {
                // Check if municipality already exists
                const [rows] = await new Promise((resolve, reject) => {
                    connection.query(
                        'SELECT id FROM ratsit_kommuner_sverige WHERE kommun = ?',
                        [municipality.kommun],
                        (err, results) => {
                            if (err) {
                                reject(err);
                            } else {
                                resolve(results);
                            }
                        },
                    );
                });

                let existingId = null;
                if (rows.length > 0) {
                    existingId = rows[0].id;
                }

                // Insert or update municipality
                if (existingId) {
                    await new Promise((resolve, reject) => {
                        connection.query(
                            'UPDATE ratsit_kommuner_sverige SET personer_total = ?, post_nummer = ? WHERE id = ?',
                            [
                                municipality.count,
                                municipality.kommun,
                                existingId,
                            ],
                            (err) => {
                                if (err) reject(err);
                                else resolve();
                            },
                        );
                    });
                } else {
                    await new Promise((resolve, reject) => {
                        connection.query(
                            'INSERT INTO ratsit_kommuner_sverige (kommun, post_nummer, personer_total) VALUES (?, ?, ?)',
                            [
                                municipality.kommun,
                                municipality.kommun,
                                municipality.count,
                            ],
                            (err) => {
                                if (err) reject(err);
                                else resolve();
                            },
                        );
                    });
                }

                console.log(
                    `Processed: ${municipality.kommun} (${municipality.count} persons)`,
                );
            } catch (error) {
                console.error(
                    `Error processing ${municipality.kommun}:`,
                    error,
                );
            }
        }

        console.log('Scraping completed successfully');
        connection.end();
    } catch (error) {
        console.error('Scraping error:', error);
        process.exit(1);
    }
}

// Run the scraper
scrapeRatsitKommuner();
