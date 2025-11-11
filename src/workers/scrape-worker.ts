import { DatabaseService } from "../db/database";

export class ScrapeWorker {
  private isRunning = false;
  private currentJob: any = null;

  async startRangeScrape(
    jobId: string,
    startPostnummer: string,
    endPostnummer: string,
    options: {
      delay?: number;
      updateMode?: boolean;
    } = {},
  ) {
    if (this.isRunning) {
      throw new Error("Worker is already running");
    }

    this.isRunning = true;
    const delay = options.delay || 1000;
    const updateMode = options.updateMode || false;

    try {
      // Update job status to running
      await DatabaseService.updateScrapeJob(jobId, {
        status: "running",
        startedAt: new Date(),
      });

      // Get postnummer records in range
      const records = await DatabaseService.getPostnummerByRange(
        startPostnummer,
        endPostnummer,
      );

      if (records.length === 0) {
        console.log(
          `No records found in range ${startPostnummer} to ${endPostnummer}`,
        );
        return;
      }

      console.log(
        `Processing ${records.length} records in range ${startPostnummer} to ${endPostnummer}`,
      );

      let processedCount = 0;
      let errorCount = 0;

      // Process each record
      for (const record of records) {
        try {
          // Call Hitta API
          const response = await fetch(
            "http://localhost:6969/api/hitta-count",
            {
              method: "PUT",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({ query: record.postNummer }),
            },
          );

          if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
          }

          const data = await response.json();

          // Update database
          await DatabaseService.updatePostnummerCounts(record.postNummer, {
            hittaForetag: this.parseNumber(data.hittaForetag),
            hittaPersoner: this.parseNumber(data.hittaPersoner),
            hittaPlatser: this.parseNumber(data.hittaPlatser),
            checkForetag: this.parseNumber(data.hittaForetag),
            checkPersoner: this.parseNumber(data.hittaPersoner),
            checkPlatser: this.parseNumber(data.hittaPlatser),
          });

          // Create sync event for Convex
          await DatabaseService.createSyncEvent({
            eventId: `${record.postNummer}-${Date.now()}`,
            eventType: "postnummer_updated",
            entityType: "postnummer",
            entityId: record.postNummer,
            data: {
              hittaForetag: this.parseNumber(data.hittaForetag),
              hittaPersoner: this.parseNumber(data.hittaPersoner),
              hittaPlatser: this.parseNumber(data.hittaPlatser),
            },
          });

          console.log(
            `✓ ${record.postNummer}: F=${this.parseNumber(data.hittaForetag)}, P=${this.parseNumber(data.hittaPersoner)}, L=${this.parseNumber(data.hittaPlatser)}`,
          );

          processedCount++;
        } catch (error) {
          console.error(`✗ Error processing ${record.postNummer}:`, error);
          errorCount++;

          // Update record with error
          await DatabaseService.updatePostnummerCounts(record.postNummer, {
            scrapeStatus: "error",
            errorMessage:
              error instanceof Error ? error.message : String(error),
          });
        }

        // Add delay between requests
        if (delay > 0 && processedCount < records.length) {
          await new Promise((resolve) => setTimeout(resolve, delay));
        }
      }

      // Update job completion
      await DatabaseService.updateScrapeJob(jobId, {
        status: "completed",
        totalProcessed: processedCount,
        totalErrors: errorCount,
        completedAt: new Date(),
      });

      console.log(
        `Job completed: ${processedCount} processed, ${errorCount} errors`,
      );
    } catch (error) {
      console.error("Job failed:", error);
      await DatabaseService.updateScrapeJob(jobId, {
        status: "failed",
        errorMessage: error instanceof Error ? error.message : String(error),
        completedAt: new Date(),
      });
    } finally {
      this.isRunning = false;
      this.currentJob = null;
    }
  }

  async startBatchScrape(
    jobId: string,
    options: {
      batchSize?: number;
      offset?: number;
      delay?: number;
      updateMode?: boolean;
    } = {},
  ) {
    // Similar implementation for batch processing
    // This would process records in batches from the database
  }

  private parseNumber(value: any): number | undefined {
    if (value === null || value === undefined || value === "") {
      return undefined;
    }
    if (typeof value === "number") {
      return value;
    }
    if (typeof value === "string") {
      const cleaned = value.replace(/,/g, "");
      const parsed = parseInt(cleaned, 10);
      return isNaN(parsed) ? undefined : parsed;
    }
    return undefined;
  }

  getStatus() {
    return {
      isRunning: this.isRunning,
      currentJob: this.currentJob,
    };
  }
}

// Export singleton instance
export const scrapeWorker = new ScrapeWorker();
