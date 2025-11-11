import {
  pgTable,
  text,
  integer,
  timestamp,
  boolean,
  jsonb,
  serial,
} from "drizzle-orm/pg-core";

// Postnummer table - main data storage
export const postnummer = pgTable("postnummer", {
  id: serial("id").primaryKey(),
  postNummer: text("post_nummer").notNull().unique(),
  postOrt: text("post_ort").notNull(),
  postLan: text("post_lan").notNull(),

  // Hitta API counts
  hittaForetag: integer("hitta_foretag"),
  hittaPersoner: integer("hitta_personer"),
  hittaPlatser: integer("hitta_platser"),

  // Metadata
  lastUpdated: timestamp("last_updated"),
  scrapeStatus: text("scrape_status"), // 'pending', 'processing', 'completed', 'error'
  errorMessage: text("error_message"),

  // Additional counts
  checkForetag: integer("check_foretag"),
  checkPersoner: integer("check_personer"),
  checkPlatser: integer("check_platser"),
  updateTime: timestamp("update_time"),
  checkTime: timestamp("check_time"),
});

// Scrape jobs table - track batch operations
export const scrapeJobs = pgTable("scrape_jobs", {
  id: serial("id").primaryKey(),
  jobId: text("job_id").notNull().unique(),
  jobType: text("job_type").notNull(), // 'range', 'batch', 'single'
  status: text("status").notNull(), // 'pending', 'running', 'completed', 'failed'

  // Job parameters
  startPostnummer: text("start_postnummer"),
  endPostnummer: text("end_postnummer"),
  batchSize: integer("batch_size"),
  updateMode: boolean("update_mode"),

  // Results
  totalProcessed: integer("total_processed").default(0),
  totalErrors: integer("total_errors").default(0),
  errorMessage: text("error_message"),

  // Timestamps
  createdAt: timestamp("created_at").defaultNow(),
  startedAt: timestamp("started_at"),
  completedAt: timestamp("completed_at"),
});

// Real-time sync table - bridge between Convex and PostgreSQL
export const syncEvents = pgTable("sync_events", {
  id: serial("id").primaryKey(),
  eventId: text("event_id").notNull().unique(),
  eventType: text("event_type").notNull(), // 'postnummer_updated', 'job_created'
  entityType: text("entity_type").notNull(), // 'postnummer', 'scrape_job'
  entityId: text("entity_id").notNull(),

  // Event data
  data: jsonb("data"),

  // Sync status
  syncedToConvex: boolean("synced_to_convex").default(false),
  syncedAt: timestamp("synced_at"),

  // Timestamps
  createdAt: timestamp("created_at").defaultNow(),
});

// Types
export type Postnummer = typeof postnummer.$inferSelect;
export type NewPostnummer = typeof postnummer.$inferInsert;
export type ScrapeJob = typeof scrapeJobs.$inferSelect;
export type NewScrapeJob = typeof scrapeJobs.$inferInsert;
export type SyncEvent = typeof syncEvents.$inferSelect;
export type NewSyncEvent = typeof syncEvents.$inferInsert;
