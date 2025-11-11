import { v } from "convex/values";
import { query, mutation, action } from "./_generated/server";
import { api } from "./_generated/api";

// Updated Convex functions for hybrid architecture

// Create scrape job that will be processed by background worker
export const createScrapeJob = mutation({
  args: {
    jobType: v.string(),
    startPostnummer: v.optional(v.string()),
    endPostnummer: v.optional(v.string()),
    batchSize: v.optional(v.number()),
    updateMode: v.optional(v.boolean()),
    delay: v.optional(v.number()),
  },
  handler: async (ctx, args) => {
    const jobId = `job_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;

    // Store job in Convex for real-time tracking
    const jobRecord = await ctx.db.insert("scrapeJobs", {
      jobId,
      jobType: args.jobType,
      startPostnummer: args.startPostnummer,
      endPostnummer: args.endPostnummer,
      batchSize: args.batchSize,
      updateMode: args.updateMode,
      status: "pending",
      totalProcessed: 0,
      totalErrors: 0,
      createdAt: Date.now(),
    });

    // Trigger background worker (this would call your Node.js service)
    console.log(`Created scrape job: ${jobId}`);

    // In real implementation, this would call your background service
    // For now, we'll simulate by calling the existing action
    if (
      args.jobType === "range" &&
      args.startPostnummer &&
      args.endPostnummer
    ) {
      // Trigger the existing range processor
  // Using an action is not available on the mutation context; schedule instead.
  await ctx.scheduler.runAfter(0, api.myFunctions.processPostnummerRange, {
        startPostnummer: args.startPostnummer,
        endPostnummer: args.endPostnummer,
        delayMs: args.delay || 1000,
        onlyUnprocessed: args.updateMode ? false : true,
        updateMode: args.updateMode,
      });
    }

    return { jobId, success: true };
  },
});

// Get real-time job status
export const getScrapeJobStatus = query({
  args: {
    jobId: v.string(),
  },
  handler: async (ctx, args) => {
    const job = await ctx.db
      .query("scrapeJobs")
      .withIndex("by_jobId", (q) => q.eq("jobId", args.jobId))
      .first();

    return job;
  },
});

// Get all active jobs
export const getActiveScrapeJobs = query({
  handler: async (ctx) => {
    const jobs = await ctx.db
      .query("scrapeJobs")
      .withIndex("by_status", (q) => q.eq("status", "running"))
      .collect();

    return jobs;
  },
});

// Sync events from PostgreSQL to Convex (called by background service)
export const syncPostnummerUpdate = mutation({
  args: {
    postNummer: v.string(),
    hittaForetag: v.optional(v.number()),
    hittaPersoner: v.optional(v.number()),
    hittaPlatser: v.optional(v.number()),
    lastUpdated: v.number(),
  },
  handler: async (ctx, args) => {
    // Update or create postnummer record in Convex for real-time access
    const existing = await ctx.db
      .query("post_nummer")
      .withIndex("by_post_nummer", (q) => q.eq("post_nummer", args.postNummer))
      .first();

    if (existing) {
      await ctx.db.patch(existing._id, {
        check_foretag: args.hittaForetag,
        check_personer: args.hittaPersoner,
        check_platser: args.hittaPlatser,
        updateTime: args.lastUpdated,
      });
    } else {
      await ctx.db.insert("post_nummer", {
        post_nummer: args.postNummer,
        post_ort: "Unknown", // Would be populated from PostgreSQL
        post_lan: "Unknown",
        check_foretag: args.hittaForetag,
        check_personer: args.hittaPersoner,
        check_platser: args.hittaPlatser,
        updateTime: args.lastUpdated,
      });
    }

    return { success: true, postNummer: args.postNummer };
  },
});

// Enhanced range processor that works with update mode
export const processPostnummerRangeFixed = action({
  args: {
    startPostnummer: v.string(),
    endPostnummer: v.string(),
    delayMs: v.optional(v.number()),
    onlyUnprocessed: v.optional(v.boolean()),
    updateMode: v.optional(v.boolean()),
  },
  handler: async (ctx, args) => {
    const delayMs = args.delayMs || 1000;
    const onlyUnprocessed = args.updateMode
      ? false
      : args.onlyUnprocessed !== false;

    // Get all records (no limit) - this is the fix
    const allRecords = await ctx.runQuery(api.myFunctions.getAllPostNummer);

    // Filter for range
    const start = args.startPostnummer.replace(/ /g, "");
    const end = args.endPostnummer.replace(/ /g, "");

    const rangeRecords = allRecords.filter((record) => {
      const postNum = record.post_nummer.replace(/ /g, "");
      return postNum >= start && postNum <= end;
    });

    // Filter for unprocessed if needed
    const recordsToProcess = onlyUnprocessed
      ? rangeRecords.filter(
          (record) =>
            record.check_foretag === undefined ||
            record.check_personer === undefined ||
            record.check_platser === undefined ||
            record.checkTime === undefined,
        )
      : rangeRecords;

    console.log(
      `Found ${recordsToProcess.length} records to process in range ${args.startPostnummer} to ${args.endPostnummer}`,
    );

    const processedRecords = [] as Array<{ post_nummer: string; success: boolean; error?: string }>;
    let totalErrors = 0;

    for (const postRecord of recordsToProcess) {
      try {
        // Call the scraper API
        const response = await fetch(`http://localhost:6969/api/hitta-count`, {
          method: "PUT",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({
            query: postRecord.post_nummer,
          }),
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        const data = await response.json();
        const currentTime = Date.now();

        // Update the record
        await ctx.runMutation(api.myFunctions.updatePostNummerCounts, {
          post_nummer: postRecord.post_nummer,
          check_foretag: parseNumberWithCommas(data.hittaForetag),
          check_personer: parseNumberWithCommas(data.hittaPersoner),
          check_platser: parseNumberWithCommas(data.hittaPlatser),
          checkTime: currentTime,
        });

        console.log(
          `✓ ${postRecord.post_nummer}: F=${parseNumberWithCommas(data.hittaForetag)}, P=${parseNumberWithCommas(data.hittaPersoner)}, L=${parseNumberWithCommas(data.hittaPlatser)}`,
        );

        processedRecords.push({
          post_nummer: postRecord.post_nummer,
          success: true,
        });
      } catch (error) {
        totalErrors++;
        const errorMessage =
          error instanceof Error ? error.message : String(error);
        console.error(`✗ Error processing ${postRecord.post_nummer}:`, error);

        processedRecords.push({
          post_nummer: postRecord.post_nummer,
          success: false,
          error: errorMessage,
        });
      }

      // Add delay
      if (
        delayMs > 0 &&
        recordsToProcess.indexOf(postRecord) < recordsToProcess.length - 1
      ) {
        await new Promise((resolve) => setTimeout(resolve, delayMs));
      }
    }

    return {
      success: true,
      totalProcessed: processedRecords.filter((r) => r.success).length,
      totalErrors,
      processedRecords,
    };
  },
});

// Helper function (moved from original location)
function parseNumberWithCommas(value: any): number | undefined {
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
