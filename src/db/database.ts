import { drizzle } from "drizzle-orm/postgres-js";
import postgres from "postgres";
import { eq, sql } from "drizzle-orm";
import * as schema from "./schema";

// Database connection
const connectionString =
  process.env.DATABASE_URL || "postgresql://localhost:5432/postnummer_db";
const client = postgres(connectionString);
export const db = drizzle(client, { schema });

// Database service class
export class DatabaseService {
  // Postnummer operations
  static async getPostnummerByRange(start: string, end: string) {
    const startNum = start.replace(/ /g, "");
    const endNum = end.replace(/ /g, "");

    return await db
      .select()
      .from(schema.postnummer)
      .where(
        sql`${schema.postnummer.postNummer} >= ${startNum} AND ${schema.postnummer.postNummer} <= ${endNum}`,
      )
      .orderBy(schema.postnummer.postNummer);
  }

  static async updatePostnummerCounts(
    postNummer: string,
    counts: {
      hittaForetag?: number;
      hittaPersoner?: number;
      hittaPlatser?: number;
      checkForetag?: number;
      checkPersoner?: number;
      checkPlatser?: number;
    },
  ) {
    return await db
      .update(schema.postnummer)
      .set({
        ...counts,
        lastUpdated: new Date(),
        scrapeStatus: "completed",
        errorMessage: null,
      })
      .where(eq(schema.postnummer.postNummer, postNummer))
      .returning();
  }

  static async createScrapeJob(jobData: {
    jobId: string;
    jobType: string;
    startPostnummer?: string;
    endPostnummer?: string;
    batchSize?: number;
    updateMode?: boolean;
  }) {
    return await db
      .insert(schema.scrapeJobs)
      .values({
        ...jobData,
        status: "pending",
        totalProcessed: 0,
        totalErrors: 0,
        createdAt: new Date(),
      })
      .returning();
  }

  static async updateScrapeJob(
    jobId: string,
    updates: {
      status?: string;
      totalProcessed?: number;
      totalErrors?: number;
      errorMessage?: string;
      startedAt?: Date;
      completedAt?: Date;
    },
  ) {
    return await db
      .update(schema.scrapeJobs)
      .set(updates)
      .where(eq(schema.scrapeJobs.jobId, jobId))
      .returning();
  }

  static async createSyncEvent(eventData: {
    eventId: string;
    eventType: string;
    entityType: string;
    entityId: string;
    data?: any;
  }) {
    return await db
      .insert(schema.syncEvents)
      .values({
        ...eventData,
        createdAt: new Date(),
        syncedToConvex: false,
      })
      .returning();
  }

  static async getUnsyncedEvents() {
    return await db
      .select()
      .from(schema.syncEvents)
      .where(eq(schema.syncEvents.syncedToConvex, false))
      .orderBy(schema.syncEvents.createdAt);
  }

  static async markEventSynced(eventId: string) {
    return await db
      .update(schema.syncEvents)
      .set({
        syncedToConvex: true,
        syncedAt: new Date(),
      })
      .where(eq(schema.syncEvents.eventId, eventId));
  }
}

// Export types and utilities
export { schema };
