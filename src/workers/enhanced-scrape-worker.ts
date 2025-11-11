import { DatabaseService } from "../db/database";
import { syncWorker } from "./sync-worker";

interface ScrapeOptions {
  batchSize?: number;
  updateMode?: boolean;
  delayMs?: number;
}

interface PostnummerData {
  postNummer: string;
  postOrt: string;
  postLan: string;
  hittaForetag?: number;
  hittaPersoner?: number;
  hittaPlatser?: number;
}

// Enhanced Scrape Worker - integrates with PostgreSQL and Convex
export class EnhancedScrapeWorker {
  private isRunning = false;
  private currentJobId: string | null = null;

  async startScrapeRange(
    startPostnummer: string,
    endPostnummer: string,
    options: ScrapeOptions = {},
  ) {
    if (this.isRunning) {
      throw new Error("Scrape worker is already running");
    }

    this.isRunning = true;

    try {
      // Create job in PostgreSQL
      const jobId = crypto.randomUUID();
      const job = await DatabaseService.createScrapeJob({
        jobId,
        jobType: "range",
        startPostnummer,
        endPostnummer,
        batchSize: options.batchSize || 100,
        updateMode: options.updateMode || false,
      });

      this.currentJobId = jobId;
      console.log(`🚀 Starting scrape job: ${jobId}`);

      // Get postnummer range from PostgreSQL
      const postnummerRange = await DatabaseService.getPostnummerByRange(
        startPostnummer,
        endPostnummer,
      );

      console.log(`📊 Found ${postnummerRange.length} postnummer to process`);

      let processedCount = 0;
      let errorCount = 0;

      // Process in batches
      for (
        let i = 0;
        i < postnummerRange.length;
        i += options.batchSize || 100
      ) {
        const batch = postnummerRange.slice(i, i + (options.batchSize || 100));

        for (const postnummer of batch) {
          try {
            // Scrape data (this would call your actual scraping logic)
            const scrapedData = await this.scrapePostnummer(
              postnummer.postNummer,
            );

            // Update PostgreSQL with scraped data
            await DatabaseService.updatePostnummerCounts(
              postnummer.postNummer,
              scrapedData,
            );

            // Create sync event to notify Convex
            await DatabaseService.createSyncEvent({
              eventId: crypto.randomUUID(),
              eventType: "postnummer_updated",
              entityType: "postnummer",
              entityId: postnummer.postNummer,
              data: scrapedData,
            });

            processedCount++;

            // Update job progress
            if (processedCount % 10 === 0) {
              await DatabaseService.updateScrapeJob(jobId, {
                status: "running",
                totalProcessed: processedCount,
                totalErrors: errorCount,
                startedAt: new Date(),
              });
            }

            // Add delay to avoid rate limiting
            if (options.delayMs) {
              await new Promise((resolve) =>
                setTimeout(resolve, options.delayMs),
              );
            }
          } catch (error) {
            console.error(
              `❌ Failed to scrape ${postnummer.postNummer}:`,
              error,
            );
            errorCount++;
          }
        }
      }

      // Mark job as completed
      await DatabaseService.updateScrapeJob(jobId, {
        status: "completed",
        totalProcessed: processedCount,
        totalErrors: errorCount,
        completedAt: new Date(),
      });

      console.log(
        `✅ Scrape job completed: ${processedCount} processed, ${errorCount} errors`,
      );
    } catch (error) {
      console.error("❌ Scrape job failed:", error);

      if (this.currentJobId) {
        await DatabaseService.updateScrapeJob(this.currentJobId, {
          status: "failed",
          errorMessage: error instanceof Error ? error.message : String(error),
          completedAt: new Date(),
        });
      }

      throw error;
    } finally {
      this.isRunning = false;
      this.currentJobId = null;
    }
  }

  private async scrapePostnummer(postNummer: string): Promise<PostnummerData> {
    // This is where you'd integrate with your existing scraping logic
    // For now, I'll create a mock implementation

    try {
      // Simulate API call to Hitta or other service
      // In reality, this would call your existing fetchHittaCounts or similar functions

      // Mock data - replace with actual scraping logic
      const mockData: PostnummerData = {
        postNummer,
        postOrt: "Unknown", // This would come from your data
        postLan: "Unknown", // This would come from your data
        hittaForetag: Math.floor(Math.random() * 100),
        hittaPersoner: Math.floor(Math.random() * 500),
        hittaPlatser: Math.floor(Math.random() * 50),
      };

      console.log(`🔍 Scraped ${postNummer}:`, mockData);
      return mockData;
    } catch (error) {
      console.error(`Failed to scrape ${postNummer}:`, error);
      throw error;
    }
  }

  // Method to integrate with existing fetchHittaCounts
  // Note: This would need to be implemented as a Convex action or called via HTTP API
  async scrapeWithExistingFunction(postNummer: string) {
    try {
      // For now, we'll use the mock implementation
      // TODO: Implement proper integration with existing Convex functions
      return this.scrapePostnummer(postNummer);
    } catch (error) {
      console.error(
        `Failed to scrape ${postNummer} with existing function:`,
        error,
      );
      throw error;
    }
  }

  getStatus() {
    return {
      isRunning: this.isRunning,
      currentJobId: this.currentJobId,
    };
  }
}

// Export singleton instance
export const enhancedScrapeWorker = new EnhancedScrapeWorker();

// Start sync worker when this module is imported
syncWorker.start();
