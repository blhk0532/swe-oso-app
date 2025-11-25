#!/usr/bin/env node
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const projectRoot = join(__dirname, '..');

async function runPersonPostorterScriptForKommun(kommun) {
    try {
        console.log(
            `Running person postorter script for kommun: ${kommun.kommun} with URL: ${kommun.ratsit_link}`,
        );

        // Run the ratsit_person_postorter.mjs script with the search URL
        const { exec } = await import('child_process');
        const { promisify } = await import('util');
        const execAsync = promisify(exec);

        const { stdout, stderr } = await execAsync(
            `node ${join(projectRoot, 'scripts/ratsit_person_postorter.mjs')} "${kommun.ratsit_link}"`,
            { timeout: 300000 }, // 5 minutes timeout
        );

        if (stderr) {
            console.error(`Script stderr for ${kommun.kommun}:`, stderr);
        }

        console.log(`Script output for ${kommun.kommun}:`, stdout);

        // Check if script ran successfully by looking for success indicators in output
        const successIndicators = [
            'Postorter saved successfully',
            'Saved',
            'completed',
        ];
        const wasSuccessful = successIndicators.some((indicator) =>
            stdout.toLowerCase().includes(indicator.toLowerCase()),
        );

        return wasSuccessful ? 1 : 0;
    } catch (error) {
        console.error(
            `Error running person postorter script for ${kommun.kommun}:`,
            error.message,
        );
        return 0;
    }
}

async function updateKommunStatus(kommunId, status) {
    try {
        const { exec } = await import('child_process');
        const { promisify } = await import('util');
        const execAsync = promisify(exec);

        // Use artisan tinker to update the database
        const { stdout, stderr } = await execAsync(
            `cd ${projectRoot} && php artisan tinker --execute="\\App\\Models\\RatsitPersonKommuner::where('id', ${kommunId})->update(['person_postort_saved' => ${status}]); echo 'Updated kommun ID ${kommunId} with person_postort_saved = ${status}';"`,
        );

        if (stderr) {
            console.error(`Update stderr for kommun ID ${kommunId}:`, stderr);
        }

        console.log(`Update result for kommun ID ${kommunId}:`, stdout);
    } catch (error) {
        console.error(`Error updating kommun ID ${kommunId}:`, error.message);
    }
}

async function getPersonKommunerWithLinks() {
    try {
        const { exec } = await import('child_process');
        const { promisify } = await import('util');
        const execAsync = promisify(exec);

        // Use artisan tinker to get the data
        const { stdout, stderr } = await execAsync(
            `cd ${projectRoot} && php artisan tinker --execute="
\\$kommuner = \\App\\Models\\RatsitPersonKommuner::whereNotNull('ratsit_link')
    ->where('ratsit_link', '!=', '')
    ->get(['id', 'kommun', 'ratsit_link', 'person_postort_saved']);

foreach (\\$kommuner as \\$kommun) {
    echo json_encode([
        'id' => \\$kommun->id,
        'kommun' => \\$kommun->kommun,
        'ratsit_link' => \\$kommun->ratsit_link,
        'person_postort_saved' => \\$kommun->person_postort_saved
    ]) . PHP_EOL;
}"`,
        );

        if (stderr) {
            console.error('Get kommuner stderr:', stderr);
        }

        const lines = stdout.trim().split('\n');
        const kommuner = lines
            .filter((line) => line.trim())
            .map((line) => {
                try {
                    return JSON.parse(line);
                } catch (e) {
                    console.error('Failed to parse line:', line);
                    return null;
                }
            })
            .filter((kommun) => kommun !== null);

        return kommuner;
    } catch (error) {
        console.error('Error getting kommuner:', error.message);
        return [];
    }
}

async function main() {
    console.log('Starting Ratsit person postorter batch processing...');

    try {
        // Get all kommuner with ratsit_link values from the database
        const kommuner = await getPersonKommunerWithLinks();

        console.log(
            `Found ${kommuner.length} kommuner with ratsit_link values`,
        );

        // Process each kommun
        for (const kommun of kommuner) {
            console.log(
                `\nProcessing kommun: ${kommun.kommun} (ID: ${kommun.id})`,
            );

            // Skip if already processed successfully
            if (kommun.person_postort_saved === 1) {
                console.log(
                    `Skipping ${kommun.kommun} - already processed successfully`,
                );
                continue;
            }

            // Run the person postorter script
            const status = await runPersonPostorterScriptForKommun(kommun);

            // Update the status in the database
            await updateKommunStatus(kommun.id, status);

            // Add a small delay between requests to be respectful to the server
            await new Promise((resolve) => setTimeout(resolve, 2000));
        }

        console.log('\nBatch processing completed!');
    } catch (error) {
        console.error('Error in main process:', error);
        process.exit(1);
    }
}

// Run the main function
main().catch(console.error);
