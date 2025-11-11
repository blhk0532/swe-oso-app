"use node";

import { v } from "convex/values";
import { mutation, query, action } from "./_generated/server";
import { internal } from "./_generated/api";

// Event Hub Functions - Create and manage events
export const createSyncEvent = mutation({
  args: {
    eventType: v.string(),
    entityType: v.string(),
    entityId: v.string(),
    data: v.optional(v.any()),
  },
  handler: async (ctx, args) => {
    const eventId = crypto.randomUUID();

    // Store event in Convex for real-time updates
    await ctx.db.insert("syncEvents", {
      eventId,
      eventType: args.eventType,
      entityType: args.entityType,
      entityId: args.entityId,
      data: args.data,
      createdAt: Date.now(),
      syncedToPostgres: false,
    });

    // Trigger action to sync to PostgreSQL
    await ctx.scheduler.runAfter(0, internal.sync.syncToPostgres, {
      eventId,
      eventType: args.eventType,
      entityType: args.entityType,
      entityId: args.entityId,
      data: args.data,
    });

    return eventId;
  },
});

// Real-time queries for frontend
export const getActiveJobs = query({
  args: {},
  handler: async (ctx) => {
    const jobs = await ctx.db
      .query("scrapeJobs")
      .filter((q) =>
        q.or(
          q.eq(q.field("status"), "pending"),
          q.eq(q.field("status"), "running"),
        ),
      )
      .collect();

    return jobs;
  },
});

export const getPostnummerStatus = query({
  args: { postNummer: v.string() },
  handler: async (ctx, args) => {
    const postnummer = await ctx.db
      .query("postNummerDB")
      .withIndex("by_post_nummer", (q) => q.eq("post_nummer", args.postNummer))
      .first();

    return postnummer;
  },
});

export const getRecentEvents = query({
  args: { limit: v.optional(v.number()) },
  handler: async (ctx, args) => {
    const events = await ctx.db
      .query("syncEvents")
      .order("desc")
      .take(args.limit || 50);

    return events;
  },
});

// Action to sync events to PostgreSQL
export const syncToPostgres = action({
  args: {
    eventId: v.string(),
    eventType: v.string(),
    entityType: v.string(),
    entityId: v.string(),
    data: v.optional(v.any()),
  },
  handler: async (ctx, args) => {
    // Import database service dynamically to avoid Convex import issues
    const { DatabaseService } = await import("../src/db/database");

    try {
      // Create sync event in PostgreSQL
      await DatabaseService.createSyncEvent({
        eventId: args.eventId,
        eventType: args.eventType,
        entityType: args.entityType,
        entityId: args.entityId,
        data: args.data,
      });

      // Update Convex event as synced
      const convexEvent = await ctx.runQuery(internal.sync.getSyncEvent, {
        eventId: args.eventId,
      });

      if (convexEvent) {
        await ctx.runMutation(internal.sync.markEventSynced, {
          eventId: args.eventId,
        });
      }

      return { success: true };
    } catch (error) {
      console.error("Failed to sync to PostgreSQL:", error);
      return { success: false, error: error.message };
    }
  },
});

// Internal helper functions
export const getSyncEvent = query({
  args: { eventId: v.string() },
  handler: async (ctx, args) => {
    return await ctx.db
      .query("syncEvents")
      .withIndex("by_eventId", (q) => q.eq("eventId", args.eventId))
      .first();
  },
});

export const markEventSynced = mutation({
  args: { eventId: v.string() },
  handler: async (ctx, args) => {
    const event = await ctx.db
      .query("syncEvents")
      .withIndex("by_eventId", (q) => q.eq("eventId", args.eventId))
      .first();

    if (event) {
      await ctx.db.patch(event._id, {
        syncedToPostgres: true,
        syncedAt: Date.now(),
      });
    }
  },
});

// Scrape job management
export const createScrapeJob = mutation({
  args: {
    jobType: v.string(),
    startPostnummer: v.optional(v.string()),
    endPostnummer: v.optional(v.string()),
    batchSize: v.optional(v.number()),
    updateMode: v.optional(v.boolean()),
  },
  handler: async (ctx, args) => {
    const jobId = crypto.randomUUID();

    // Create job in Convex for real-time tracking
    const jobIdConvex = await ctx.db.insert("scrapeJobs", {
      jobId,
      jobType: args.jobType,
      startPostnummer: args.startPostnummer,
      endPostnummer: args.endPostnummer,
      batchSize: args.batchSize,
      updateMode: args.updateMode || false,
      status: "pending",
      totalProcessed: 0,
      totalErrors: 0,
      createdAt: Date.now(),
    });

    // Create sync event to replicate in PostgreSQL
    await ctx.runMutation(internal.sync.createSyncEvent, {
      eventType: "job_created",
      entityType: "scrape_job",
      entityId: jobId,
      data: {
        jobType: args.jobType,
        startPostnummer: args.startPostnummer,
        endPostnummer: args.endPostnummer,
        batchSize: args.batchSize,
        updateMode: args.updateMode,
      },
    });

    return jobId;
  },
});

export const updateJobStatus = mutation({
  args: {
    jobId: v.string(),
    status: v.string(),
    totalProcessed: v.optional(v.number()),
    totalErrors: v.optional(v.number()),
    errorMessage: v.optional(v.string()),
  },
  handler: async (ctx, args) => {
    const job = await ctx.db
      .query("scrapeJobs")
      .withIndex("by_jobId", (q) => q.eq("jobId", args.jobId))
      .first();

    if (!job) {
      throw new Error(`Job ${args.jobId} not found`);
    }

    const updateData: any = { status: args.status };

    if (args.totalProcessed !== undefined) {
      updateData.totalProcessed = args.totalProcessed;
    }
    if (args.totalErrors !== undefined) {
      updateData.totalErrors = args.totalErrors;
    }
    if (args.errorMessage !== undefined) {
      updateData.errorMessage = args.errorMessage;
    }

    if (args.status === "running" && !job.startedAt) {
      updateData.startedAt = Date.now();
    }
    if (args.status === "completed" || args.status === "failed") {
      updateData.completedAt = Date.now();
    }

    await ctx.db.patch(job._id, updateData);

    // Create sync event
    await ctx.runMutation(internal.sync.createSyncEvent, {
      eventType: "job_updated",
      entityType: "scrape_job",
      entityId: args.jobId,
      data: updateData,
    });
  },
});
