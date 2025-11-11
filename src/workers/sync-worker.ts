import { DatabaseService } from "../db/database";

// Background sync worker - processes events from PostgreSQL to Convex
export class SyncWorker {
  private isRunning = false;
  private interval: NodeJS.Timeout | null = null;

  start() {
    if (this.isRunning) return;

    this.isRunning = true;
    console.log("🔄 Starting sync worker...");

    // Process unsynced events every 5 seconds
    this.interval = setInterval(async () => {
      try {
        await this.processUnsyncedEvents();
      } catch (error) {
        console.error("Sync worker error:", error);
      }
    }, 5000);
  }

  stop() {
    if (!this.isRunning) return;

    this.isRunning = false;
    if (this.interval) {
      clearInterval(this.interval);
      this.interval = null;
    }
    console.log("⏹️ Sync worker stopped");
  }

  private async processUnsyncedEvents() {
    const events = await DatabaseService.getUnsyncedEvents();

    if (events.length === 0) return;

    console.log(`📡 Processing ${events.length} sync events...`);

    for (const event of events) {
      try {
        await this.processEvent(event);
        await DatabaseService.markEventSynced(event.eventId);
        console.log(
          `✅ Processed event: ${event.eventType} for ${event.entityType}:${event.entityId}`,
        );
      } catch (error) {
        console.error(`❌ Failed to process event ${event.eventId}:`, error);
      }
    }
  }

  private async processEvent(event: any) {
    switch (event.eventType) {
      case "postnummer_updated":
        // This would trigger a Convex mutation to update real-time data
        await this.notifyConvex("postnummer_updated", {
          postNummer: event.entityId,
          data: event.data,
        });
        break;

      case "job_created":
      case "job_updated":
        // This would update job status in Convex for real-time tracking
        await this.notifyConvex("job_status_changed", {
          jobId: event.entityId,
          status: event.data?.status,
          data: event.data,
        });
        break;

      default:
        console.log(`⚠️ Unknown event type: ${event.eventType}`);
    }
  }

  private async notifyConvex(type: string, data: any) {
    // This would make an HTTP call to Convex webhook or use their API
    // For now, we'll just log it
    console.log(`🔔 Notifying Convex: ${type}`, data);

    // TODO: Implement actual Convex notification
    // Could use fetch to call a Convex action or webhook
  }
}

// Export singleton instance
export const syncWorker = new SyncWorker();

// Start worker if this file is run directly
if (require.main === module) {
  syncWorker.start();

  // Graceful shutdown
  process.on("SIGINT", () => {
    console.log("\n🛑 Shutting down sync worker...");
    syncWorker.stop();
    process.exit(0);
  });

  process.on("SIGTERM", () => {
    console.log("\n🛑 Shutting down sync worker...");
    syncWorker.stop();
    process.exit(0);
  });
}
