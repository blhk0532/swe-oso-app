import { v } from "convex/values";
import { query, mutation, action } from "./_generated/server";
import { api } from "./_generated/api";

// Write your Convex functions in any file inside this directory (`convex`).
// See https://docs.convex.dev/functions for more.

// Helper function to parse numbers with commas
function parseNumberWithCommas(value: any): number | undefined {
  if (value === null || value === undefined || value === "") {
    return undefined;
  }

  // If it's already a number, return it
  if (typeof value === "number") {
    return value;
  }

  // If it's a string, remove commas and convert to number
  if (typeof value === "string") {
    const cleaned = value.replace(/,/g, "");
    const parsed = parseInt(cleaned, 10);
    return isNaN(parsed) ? undefined : parsed;
  }

  return undefined;
}

// You can read data from the database via a query:
export const listNumbers = query({
  // Validators for arguments.
  args: {
    count: v.number(),
  },

  // Query implementation.
  handler: async (ctx, args) => {
    //// Read the database as many times as you need here.
    //// See https://docs.convex.dev/database/reading-data.
    const numbers = await ctx.db
      .query("numbers")
      // Ordered by _creationTime, return most recent
      .order("desc")
      .take(args.count);
    return {
      viewer: null,
      numbers: numbers.reverse().map((number) => number.value),
    };
  },
});

// You can write data to the database via a mutation:
export const addNumber = mutation({
  // Validators for arguments.
  args: {
    value: v.number(),
  },

  // Mutation implementation.
  handler: async (ctx, args) => {
    //// Insert or modify documents in the database here.
    //// Mutations can also read from the database like queries.
    //// See https://docs.convex.dev/database/writing-data.

    const id = await ctx.db.insert("numbers", { value: args.value });

    console.log("Added new document with id:", id);
    // Optionally, return a value from your mutation.
    // return id;
  },
});

// You can fetch data from and send data to third-party APIs via an action:
export const clearPostnummer = mutation({
  handler: async (ctx) => {
    const allRecords = await ctx.db.query("postnummer").collect();
    for (const record of allRecords) {
      await ctx.db.delete(record._id);
    }
    return { deleted: allRecords.length };
  },
});

export const importBatch = mutation({
  args: {
    records: v.array(
      v.object({
        postNummer: v.string(),
        postOrt: v.string(),
        postLan: v.string(),
      }),
    ),
  },
  handler: async (ctx, args) => {
    const insertedIds = [];
    for (const record of args.records) {
      const id = await ctx.db.insert("postnummer", record);
      insertedIds.push(id);
    }
    return insertedIds;
  },
});

export const getPostnummerCount = query({
  handler: async (ctx) => {
    const results = await ctx.db.query("postnummer").collect();
    return results.length;
  },
});

export const getPostnummerSample = query({
  args: {
    limit: v.number(),
  },
  handler: async (ctx, args) => {
    const results = await ctx.db.query("postnummer").take(args.limit);
    return results;
  },
});

export const searchPostnummer = query({
  args: {
    searchTerm: v.string(),
  },
  handler: async (ctx, args) => {
    const results = await ctx.db
      .query("postnummer")
      .withIndex("by_postNummer", (q) => q.eq("postNummer", args.searchTerm))
      .collect();

    return results;
  },
});

// Ratsit Hitta CRUD Functions
export const createRatsitHitta = mutation({
  args: {
    gatuadress: v.optional(v.string()),
    postnummer: v.optional(v.string()),
    postort: v.optional(v.string()),
    forsamling: v.optional(v.string()),
    kommun: v.optional(v.string()),
    lan: v.optional(v.string()),
    adressandring: v.optional(v.string()),
    telfonnummer: v.optional(v.array(v.string())),
    stjarntacken: v.optional(v.string()),
    fodelsedag: v.optional(v.string()),
    personnummer: v.optional(v.string()),
    alder: v.optional(v.string()),
    kon: v.optional(v.string()),
    civilstand: v.optional(v.string()),
    fornamn: v.optional(v.string()),
    efternamn: v.optional(v.string()),
    personnamn: v.optional(v.string()),
    telefon: v.optional(v.array(v.string())),
    agandeform: v.optional(v.string()),
    bostadstyp: v.optional(v.string()),
    boarea: v.optional(v.string()),
    byggar: v.optional(v.string()),
    personer: v.optional(v.array(v.any())),
    foretag: v.optional(v.array(v.any())),
    grannar: v.optional(v.array(v.any())),
    fordon: v.optional(v.array(v.any())),
    hundar: v.optional(v.array(v.any())),
    bolagsengagemang: v.optional(v.array(v.any())),
    longitude: v.optional(v.string()),
    latitud: v.optional(v.string()),
    google_maps: v.optional(v.string()),
    google_streetview: v.optional(v.string()),
    ratsit_link: v.optional(v.string()),
    hitta_link: v.optional(v.string()),
    hitta_karta: v.optional(v.string()),
    bostad_typ: v.optional(v.string()),
    bostad_pris: v.optional(v.string()),
    is_active: v.boolean(),
    is_update: v.boolean(),
  },
  handler: async (ctx, args) => {
    const id = await ctx.db.insert("ratsit_hitta", args);
    return id;
  },
});

export const getRatsitHittaByPostnummer = query({
  args: {
    postnummer: v.string(),
  },
  handler: async (ctx, args) => {
    const results = await ctx.db
      .query("ratsit_hitta")
      .withIndex("by_postnummer", (q) => q.eq("postnummer", args.postnummer))
      .collect();
    return results;
  },
});

export const getRatsitHittaByPersonnummer = query({
  args: {
    personnummer: v.string(),
  },
  handler: async (ctx, args) => {
    const results = await ctx.db
      .query("ratsit_hitta")
      .withIndex("by_personnummer", (q) =>
        q.eq("personnummer", args.personnummer),
      )
      .collect();
    return results;
  },
});

export const getRatsitHittaByName = query({
  args: {
    personnamn: v.string(),
  },
  handler: async (ctx, args) => {
    const results = await ctx.db
      .query("ratsit_hitta")
      .withIndex("by_personnamn", (q) => q.eq("personnamn", args.personnamn))
      .collect();
    return results;
  },
});

export const getActiveRatsitHitta = query({
  handler: async (ctx) => {
    const results = await ctx.db
      .query("ratsit_hitta")
      .withIndex("by_active", (q) => q.eq("is_active", true))
      .collect();
    return results;
  },
});

export const updateRatsitHitta = mutation({
  args: {
    id: v.id("ratsit_hitta"),
    updates: v.object({
      gatuadress: v.optional(v.string()),
      postnummer: v.optional(v.string()),
      postort: v.optional(v.string()),
      forsamling: v.optional(v.string()),
      kommun: v.optional(v.string()),
      lan: v.optional(v.string()),
      adressandring: v.optional(v.string()),
      telfonnummer: v.optional(v.array(v.string())),
      stjarntacken: v.optional(v.string()),
      fodelsedag: v.optional(v.string()),
      personnummer: v.optional(v.string()),
      alder: v.optional(v.string()),
      kon: v.optional(v.string()),
      civilstand: v.optional(v.string()),
      fornamn: v.optional(v.string()),
      efternamn: v.optional(v.string()),
      personnamn: v.optional(v.string()),
      telefon: v.optional(v.array(v.string())),
      agandeform: v.optional(v.string()),
      bostadstyp: v.optional(v.string()),
      boarea: v.optional(v.string()),
      byggar: v.optional(v.string()),
      personer: v.optional(v.array(v.any())),
      foretag: v.optional(v.array(v.any())),
      grannar: v.optional(v.array(v.any())),
      fordon: v.optional(v.array(v.any())),
      hundar: v.optional(v.array(v.any())),
      bolagsengagemang: v.optional(v.array(v.any())),
      longitude: v.optional(v.string()),
      latitud: v.optional(v.string()),
      google_maps: v.optional(v.string()),
      google_streetview: v.optional(v.string()),
      ratsit_link: v.optional(v.string()),
      hitta_link: v.optional(v.string()),
      hitta_karta: v.optional(v.string()),
      bostad_typ: v.optional(v.string()),
      bostad_pris: v.optional(v.string()),
      is_active: v.optional(v.boolean()),
      is_update: v.optional(v.boolean()),
    }),
  },
  handler: async (ctx, args) => {
    const { id, updates } = args;
    await ctx.db.patch(id, updates);
    return id;
  },
});

export const deleteRatsitHitta = mutation({
  args: {
    id: v.id("ratsit_hitta"),
  },
  handler: async (ctx, args) => {
    await ctx.db.delete(args.id);
  },
});

// Post_nummer CRUD Functions
export const createPostNummer = mutation({
  args: {
    post_nummer: v.string(),
    post_ort: v.string(),
    post_lan: v.string(),
  },
  handler: async (ctx, args) => {
    const id = await ctx.db.insert("post_nummer", args);
    return id;
  },
});

export const getPostNummerByPostnummer = query({
  args: {
    post_nummer: v.string(),
  },
  handler: async (ctx, args) => {
    const results = await ctx.db
      .query("post_nummer")
      .withIndex("by_post_nummer", (q) => q.eq("post_nummer", args.post_nummer))
      .collect();
    return results;
  },
});

export const getAllPostNummer = query({
  handler: async (ctx) => {
    const results = await ctx.db.query("post_nummer").collect();
    return results;
  },
});

export const getPostNummerBatch = query({
  args: {
    offset: v.number(),
    limit: v.number(),
  },
  handler: async (ctx, args) => {
    const results = await ctx.db.query("post_nummer").collect();
    return results.slice(args.offset, args.offset + args.limit);
  },
});

export const getPostNummerCount = query({
  handler: async (ctx) => {
    const results = await ctx.db.query("post_nummer").collect();
    return results.length;
  },
});

export const getPostNummerDBCount = query({
  handler: async (ctx) => {
    const results = await ctx.db.query("postNummerDB").collect();
    return results.length;
  },
});

export const getPostNummerSample = query({
  args: {
    limit: v.number(),
  },
  handler: async (ctx, args) => {
    const results = await ctx.db.query("post_nummer").take(args.limit);
    return results;
  },
});

export const getPostNummerByRangeFixed = query({
  args: {
    startPostnummer: v.string(),
    endPostnummer: v.string(),
  },
  handler: async (ctx, args) => {
    // Remove spaces for comparison and convert to numbers
    const start = parseInt(args.startPostnummer.replace(/ /g, ""), 10);
    const end = parseInt(args.endPostnummer.replace(/ /g, ""), 10);

    console.log(`DEBUG: Searching for range ${start} to ${end}`);

    // Since we don't have a range index, we need to fetch all records
    const allRecords = await ctx.db.query("post_nummer").collect();

    console.log(`DEBUG: Total records fetched: ${allRecords.length}`);

    // Filter records within the specified range
    const filteredRecords = allRecords.filter((record) => {
      const postNum = parseInt(record.post_nummer.replace(/ /g, ""), 10);
      const inRange = postNum >= start && postNum <= end;
      if (postNum >= 10000 && postNum <= 10099) {
        console.log(
          `DEBUG: Checking ${record.post_nummer} -> ${postNum}, in range: ${inRange}`,
        );
      }
      return inRange;
    });

    console.log(`DEBUG: Found ${filteredRecords.length} records in range`);

    // Sort by postnummer
    filteredRecords.sort((a, b) => {
      const aNum = parseInt(a.post_nummer.replace(/ /g, ""), 10);
      const bNum = parseInt(b.post_nummer.replace(/ /g, ""), 10);
      return aNum - bNum;
    });

    return filteredRecords;
  },
});

export const getPostNummerByRange = query({
  args: {
    startPostnummer: v.string(),
    endPostnummer: v.string(),
  },
  handler: async (ctx, args) => {
    // Remove spaces for comparison and convert to numbers
    const start = parseInt(args.startPostnummer.replace(/ /g, ""), 10);
    const end = parseInt(args.endPostnummer.replace(/ /g, ""), 10);

    // Since we don't have a range index, we need to fetch all records
    const allRecords = await ctx.db.query("post_nummer").collect();

    // Filter records within the specified range
    const filteredRecords = allRecords.filter((record) => {
      const postNum = parseInt(record.post_nummer.replace(/ /g, ""), 10);
      return postNum >= start && postNum <= end;
    });

    // Sort by postnummer
    filteredRecords.sort((a, b) => {
      const aNum = parseInt(a.post_nummer.replace(/ /g, ""), 10);
      const bNum = parseInt(b.post_nummer.replace(/ /g, ""), 10);
      return aNum - bNum;
    });

    return filteredRecords;
  },
});

export const debugPostnummerRange = query({
  args: {
    startPostnummer: v.string(),
    endPostnummer: v.string(),
  },
  handler: async (ctx, args) => {
    // Remove spaces for comparison
    const start = args.startPostnummer.replace(/ /g, "");
    const end = args.endPostnummer.replace(/ /g, "");

    console.log(`DEBUG: Looking for range ${start} to ${end}`);

    // Get all records
    const allRecords = await ctx.db.query("post_nummer").collect();
    console.log(`DEBUG: Total records: ${allRecords.length}`);

    // Find all 100xx records
    const all100xx = allRecords.filter((record) => {
      const postNum = record.post_nummer.replace(/ /g, "");
      return postNum.startsWith("100");
    });

    console.log(`DEBUG: All 100xx records: ${all100xx.length}`);
    all100xx.forEach((r) => console.log(`  - ${r.post_nummer}`));

    // Filter records within the specified range
    const filteredRecords = allRecords.filter((record) => {
      const postNum = record.post_nummer.replace(/ /g, "");
      const inRange = postNum >= start && postNum <= end;
      if (postNum.startsWith("100")) {
        console.log(
          `DEBUG: ${record.post_nummer} -> ${postNum}, in range: ${inRange}`,
        );
      }
      return inRange;
    });

    console.log(`DEBUG: Filtered records: ${filteredRecords.length}`);

    return {
      totalRecords: allRecords.length,
      all100xxCount: all100xx.length,
      all100xx: all100xx.map((r) => r.post_nummer),
      filteredCount: filteredRecords.length,
      filtered: filteredRecords.map((r) => r.post_nummer),
      start,
      end,
    };
  },
});

export const importPostNummerBatch = mutation({
  args: {
    records: v.array(
      v.object({
        post_nummer: v.string(),
        post_ort: v.string(),
        post_lan: v.string(),
      }),
    ),
  },
  handler: async (ctx, args) => {
    const insertedIds = [];
    for (const record of args.records) {
      const id = await ctx.db.insert("post_nummer", record);
      insertedIds.push(id);
    }
    return insertedIds;
  },
});

export const clearPostNummer = mutation({
  handler: async (ctx) => {
    const allRecords = await ctx.db.query("post_nummer").collect();
    for (const record of allRecords) {
      await ctx.db.delete(record._id);
    }
    return { deleted: allRecords.length };
  },
});

export const migratePostNummerToDB = mutation({
  args: {
    limit: v.optional(v.number()),
    offset: v.optional(v.number()),
  },
  handler: async (ctx, args) => {
    const limit = args.limit || 1000;
    const offset = args.offset || 0;

    const allRecords = await ctx.db.query("post_nummer").collect();
    const batch = allRecords.slice(offset, offset + limit);
    const insertedIds = [];

    for (const record of batch) {
      const newRecord = {
        post_nummer: record.post_nummer,
        post_ort: record.post_ort,
        post_lan: record.post_lan,
        countForetag: 0,
        countPersoner: 0,
        hittaForetag: 0,
        hittaPersoner: 0,
        totalForetag: 0,
        totalPersoner: 0,
      };

      const id = await ctx.db.insert("postNummerDB", newRecord);
      insertedIds.push(id);
    }

    return {
      total: allRecords.length,
      processed: batch.length,
      offset: offset,
      inserted: insertedIds.length,
      hasMore: offset + limit < allRecords.length,
    };
  },
});

// Functions to update the new columns in post_nummer table
export const updatePostNummerCounts = mutation({
  args: {
    post_nummer: v.string(),
    check_foretag: v.optional(v.number()),
    check_personer: v.optional(v.number()),
    check_platser: v.optional(v.number()),
    checkTime: v.optional(v.number()),
    fetch_bolag: v.optional(v.number()),
    fetch_house: v.optional(v.number()),
    fetch_phone: v.optional(v.number()),
    fetch_total: v.optional(v.number()),
    updateTime: v.optional(v.number()),
    // Allow setting old columns to undefined for removal
    foretag: v.optional(v.null()),
    personer: v.optional(v.null()),
    platser: v.optional(v.null()),
  },
  handler: async (ctx, args) => {
    const existing = await ctx.db
      .query("post_nummer")
      .withIndex("by_post_nummer", (q) => q.eq("post_nummer", args.post_nummer))
      .first();

    if (!existing) {
      throw new Error(`Postnummer ${args.post_nummer} not found`);
    }

    const updateData: any = {
      updateTime: Date.now(),
    };

    if (args.check_foretag !== undefined)
      updateData.check_foretag = args.check_foretag;
    if (args.check_personer !== undefined)
      updateData.check_personer = args.check_personer;
    if (args.check_platser !== undefined)
      updateData.check_platser = args.check_platser;
    if (args.checkTime !== undefined) updateData.checkTime = args.checkTime;
    if (args.fetch_bolag !== undefined)
      updateData.fetch_bolag = args.fetch_bolag;
    if (args.fetch_house !== undefined)
      updateData.fetch_house = args.fetch_house;
    if (args.fetch_phone !== undefined)
      updateData.fetch_phone = args.fetch_phone;
    if (args.fetch_total !== undefined)
      updateData.fetch_total = args.fetch_total;

    // Handle old columns - allow setting to undefined to remove
    if (args.foretag !== undefined) updateData.foretag = args.foretag;
    if (args.personer !== undefined) updateData.personer = args.personer;
    if (args.platser !== undefined) updateData.platser = args.platser;

    await ctx.db.patch(existing._id, updateData);
    return { success: true, updatedId: existing._id };
  },
});

export const batchUpdatePostNummerCounts = mutation({
  args: {
    updates: v.array(
      v.object({
        post_nummer: v.string(),
        check_foretag: v.optional(v.number()),
        check_personer: v.optional(v.number()),
        check_platser: v.optional(v.number()),
        checkTime: v.optional(v.number()),
        fetch_bolag: v.optional(v.number()),
        fetch_house: v.optional(v.number()),
        fetch_phone: v.optional(v.number()),
        fetch_total: v.optional(v.number()),
      }),
    ),
  },
  handler: async (ctx, args) => {
    const results = [];
    const currentTime = Date.now();

    for (const update of args.updates) {
      const existing = await ctx.db
        .query("post_nummer")
        .withIndex("by_post_nummer", (q) =>
          q.eq("post_nummer", update.post_nummer),
        )
        .first();

      if (existing) {
        const updateData: any = {
          updateTime: currentTime,
        };

        if (update.check_foretag !== undefined)
          updateData.check_foretag = update.check_foretag;
        if (update.check_personer !== undefined)
          updateData.check_personer = update.check_personer;
        if (update.check_platser !== undefined)
          updateData.check_platser = update.check_platser;
        if (update.checkTime !== undefined)
          updateData.checkTime = update.checkTime;
        if (update.fetch_bolag !== undefined)
          updateData.fetch_bolag = update.fetch_bolag;
        if (update.fetch_house !== undefined)
          updateData.fetch_house = update.fetch_house;
        if (update.fetch_phone !== undefined)
          updateData.fetch_phone = update.fetch_phone;
        if (update.fetch_total !== undefined)
          updateData.fetch_total = update.fetch_total;

        await ctx.db.patch(existing._id, updateData);
        results.push({ post_nummer: update.post_nummer, success: true });
      } else {
        results.push({
          post_nummer: update.post_nummer,
          success: false,
          error: "Not found",
        });
      }
    }

    return { processed: results.length, results };
  },
});

export const getPostNummerWithCounts = query({
  args: {
    post_nummer: v.string(),
  },
  handler: async (ctx, args) => {
    const result = await ctx.db
      .query("post_nummer")
      .withIndex("by_post_nummer", (q) => q.eq("post_nummer", args.post_nummer))
      .first();

    return result;
  },
});

export const removeOldColumns = mutation({
  args: {
    batchSize: v.optional(v.number()),
    offset: v.optional(v.number()),
  },
  handler: async (ctx, args) => {
    const batchSize = args.batchSize || 100;
    const offset = args.offset || 0;

    const allRecords = await ctx.db.query("post_nummer").collect();
    const batch = allRecords.slice(offset, offset + batchSize);
    const cleaned = [];

    for (const record of batch) {
      const updateData: any = {
        updateTime: Date.now(),
      };

      // Check if old columns exist and remove them
      const hasOldColumns =
        (record as any).foretag !== undefined ||
        (record as any).personer !== undefined ||
        (record as any).platser !== undefined;

      if (hasOldColumns) {
        // Migrate data to new columns if not already present
        if (
          (record as any).foretag !== undefined &&
          record.check_foretag === undefined
        ) {
          updateData.check_foretag = (record as any).foretag;
        }
        if (
          (record as any).personer !== undefined &&
          record.check_personer === undefined
        ) {
          updateData.check_personer = (record as any).personer;
        }
        if (
          (record as any).platser !== undefined &&
          record.check_platser === undefined
        ) {
          updateData.check_platser = (record as any).platser;
        }

        // Update record (old columns will be automatically removed)
        await ctx.db.patch(record._id, updateData);
        cleaned.push(record.post_nummer);
      }
    }

    return {
      total: allRecords.length,
      processed: batch.length,
      cleaned: cleaned.length,
      offset: offset,
      hasMore: offset + batchSize < allRecords.length,
    };
  },
});

export const getPostNummerStats = query({
  handler: async (ctx) => {
    const allRecords = await ctx.db.query("post_nummer").collect();

    const stats = {
      total: allRecords.length,
      withForetag: 0,
      withPersoner: 0,
      withPlatser: 0,
      withAllCounts: 0,
      lastUpdated: null as number | null,
    };

    let latestUpdate = 0;

    for (const record of allRecords) {
      if (record.check_foretag !== undefined) stats.withForetag++;
      if (record.check_personer !== undefined) stats.withPersoner++;
      if (record.check_platser !== undefined) stats.withPlatser++;
      if (
        record.check_foretag !== undefined &&
        record.check_personer !== undefined &&
        record.check_platser !== undefined
      ) {
        stats.withAllCounts++;
      }
      if (record.updateTime && record.updateTime > latestUpdate) {
        latestUpdate = record.updateTime;
      }
    }

    stats.lastUpdated = latestUpdate > 0 ? latestUpdate : null;
    return stats;
  },
});

// Action to populate counts from ratsit_hitta data in batches
export const populatePostNummerCountsFromRatsit = action({
  args: {
    offset: v.optional(v.number()),
    limit: v.optional(v.number()),
  },
  handler: async (
    ctx,
    args,
  ): Promise<{
    success: boolean;
    processed: number;
    hasMore: boolean;
    nextOffset?: number;
    totalProcessed?: number;
  }> => {
    const limit = args.limit || 500;
    const offset = args.offset || 0;

    // Get post_nummer records in batches
    const postNummerBatch = await ctx.runQuery(
      api.myFunctions.getPostNummerBatch,
      {
        offset,
        limit,
      },
    );

    if (postNummerBatch.length === 0) {
      return { success: true, processed: 0, hasMore: false };
    }

    const updates: Array<{
      post_nummer: string;
      check_foretag?: number;
      check_personer?: number;
      check_platser?: number;
    }> = [];

    console.log(
      `Processing batch of ${postNummerBatch.length} postnummer records (offset: ${offset})...`,
    );

    for (const postRecord of postNummerBatch) {
      // Get ratsit_hitta records for this postnummer
      const ratsitRecords = await ctx.runQuery(
        api.myFunctions.getRatsitHittaByPostnummer,
        {
          postnummer: postRecord.post_nummer,
        },
      );

      let foretagCount = 0;
      let personerCount = 0;
      let platserCount = 0;

      for (const ratsit of ratsitRecords) {
        // Count unique companies
        if (ratsit.foretag && Array.isArray(ratsit.foretag)) {
          foretagCount += ratsit.foretag.length;
        }

        // Count unique people
        if (ratsit.personer && Array.isArray(ratsit.personer)) {
          personerCount += ratsit.personer.length;
        }

        // Count places (each record represents a place)
        platserCount++;
      }

      updates.push({
        post_nummer: postRecord.post_nummer,
        check_foretag: foretagCount > 0 ? foretagCount : undefined,
        check_personer: personerCount > 0 ? personerCount : undefined,
        check_platser: platserCount > 0 ? platserCount : undefined,
      });
    }

    // Update this batch
    await ctx.runMutation(api.myFunctions.batchUpdatePostNummerCounts, {
      updates,
    });
    console.log(`Updated batch of ${updates.length} records`);

    const hasMore = postNummerBatch.length === limit;
    const nextOffset = hasMore ? offset + limit : undefined;

    return {
      success: true,
      processed: updates.length,
      hasMore,
      nextOffset,
    };
  },
});

// Action to run the complete population process
export const populateAllPostNummerCounts = action({
  args: {
    batchSize: v.optional(v.number()),
  },
  handler: async (
    ctx,
    args,
  ): Promise<{ success: boolean; totalProcessed: number }> => {
    const batchSize = args.batchSize || 500;
    let offset = 0;
    let totalProcessed = 0;

    console.log("Starting complete population of postnummer counts...");

    while (true) {
      const result = await ctx.runAction(
        api.myFunctions.populatePostNummerCountsFromRatsit,
        {
          offset,
          limit: batchSize,
        },
      );

      totalProcessed += result.processed;

      if (!result.hasMore) {
        break;
      }

      offset = result.nextOffset!;
      console.log(`Progress: ${totalProcessed} records processed...`);
    }

    console.log(`Complete! Total processed: ${totalProcessed}`);
    return { success: true, totalProcessed };
  },
});

export const importRatsitHittaBatch = mutation({
  args: {
    records: v.array(
      v.object({
        gatuadress: v.optional(v.string()),
        postnummer: v.optional(v.string()),
        postort: v.optional(v.string()),
        forsamling: v.optional(v.string()),
        kommun: v.optional(v.string()),
        lan: v.optional(v.string()),
        adressandring: v.optional(v.string()),
        telfonnummer: v.optional(v.array(v.string())),
        stjarntacken: v.optional(v.string()),
        fodelsedag: v.optional(v.string()),
        personnummer: v.optional(v.string()),
        alder: v.optional(v.string()),
        kon: v.optional(v.string()),
        civilstand: v.optional(v.string()),
        fornamn: v.optional(v.string()),
        efternamn: v.optional(v.string()),
        personnamn: v.optional(v.string()),
        telefon: v.optional(v.array(v.string())),
        agandeform: v.optional(v.string()),
        bostadstyp: v.optional(v.string()),
        boarea: v.optional(v.string()),
        byggar: v.optional(v.string()),
        personer: v.optional(v.array(v.any())),
        foretag: v.optional(v.array(v.any())),
        grannar: v.optional(v.array(v.any())),
        fordon: v.optional(v.array(v.any())),
        hundar: v.optional(v.array(v.any())),
        bolagsengagemang: v.optional(v.array(v.any())),
        longitude: v.optional(v.string()),
        latitud: v.optional(v.string()),
        google_maps: v.optional(v.string()),
        google_streetview: v.optional(v.string()),
        ratsit_link: v.optional(v.string()),
        hitta_link: v.optional(v.string()),
        hitta_karta: v.optional(v.string()),
        bostad_typ: v.optional(v.string()),
        bostad_pris: v.optional(v.string()),
        is_active: v.boolean(),
        is_update: v.boolean(),
      }),
    ),
  },
  handler: async (ctx, args) => {
    const insertedIds = [];
    for (const record of args.records) {
      const id = await ctx.db.insert("ratsit_hitta", record);
      insertedIds.push(id);
    }
    return insertedIds;
  },
});

export const backupPostNummer = action({
  args: {
    batchSize: v.optional(v.number()),
    offset: v.optional(v.number()),
  },
  handler: async (
    ctx,
    args,
  ): Promise<{
    filename: string;
    count: number;
    data: any[];
    timestamp: string;
    hasMore: boolean;
    nextOffset?: number;
  }> => {
    const batchSize = args.batchSize || 1000;
    const offset = args.offset || 0;

    // Get batch directly
    const batch = await ctx.runQuery(api.myFunctions.getPostNummerBatch, {
      offset,
      limit: batchSize,
    });

    // Get total count (this is small, so it's okay)
    const totalCount = await ctx.runQuery(api.myFunctions.getPostNummerCount);

    const timestamp = new Date().toISOString().replace(/[:.]/g, "-");
    const filename = `post_nummer_backup_${timestamp}_batch_${offset}-${offset + batch.length}.json`;

    const hasMore = offset + batchSize < totalCount;
    const nextOffset = hasMore ? offset + batchSize : undefined;

    return {
      filename,
      count: batch.length,
      data: batch,
      timestamp: new Date().toISOString(),
      hasMore,
      nextOffset,
    };
  },
});

// Action to fetch hitta counts for postnummer records in batches
export const fetchHittaCountsBatch = action({
  args: {
    offset: v.optional(v.number()),
    limit: v.optional(v.number()),
    delayMs: v.optional(v.number()),
    onlyUnprocessed: v.optional(v.boolean()),
    updateMode: v.optional(v.boolean()),
  },
  handler: async (
    ctx,
    args,
  ): Promise<{
    success: boolean;
    processed: number;
    errors: number;
    hasMore: boolean;
    nextOffset?: number;
    totalProcessed?: number;
  }> => {
    const limit = args.limit || 100;
    const offset = args.offset || 0;
    const delayMs = args.delayMs || 1000; // Default 1 second delay between requests
    const onlyUnprocessed = args.updateMode
      ? false
      : args.onlyUnprocessed !== false; // Default to true

    // Get post_nummer records in batches
    const postNummerBatch = await ctx.runQuery(
      api.myFunctions.getPostNummerBatch,
      {
        offset,
        limit,
      },
    );

    if (postNummerBatch.length === 0) {
      return { success: true, processed: 0, errors: 0, hasMore: false };
    }

    // Filter for unprocessed records if requested
    const recordsToProcess = onlyUnprocessed
      ? postNummerBatch.filter(
          (record) =>
            record.check_foretag === undefined ||
            record.check_personer === undefined ||
            record.check_platser === undefined ||
            record.checkTime === undefined,
        )
      : postNummerBatch;

    if (recordsToProcess.length === 0) {
      return { success: true, processed: 0, errors: 0, hasMore: true };
    }

    const updates: Array<{
      post_nummer: string;
      check_foretag?: number;
      check_personer?: number;
      check_platser?: number;
      checkTime: number;
    }> = [];
    let errorCount = 0;

    console.log(
      `Processing batch of ${recordsToProcess.length} postnummer records (offset: ${offset})...`,
    );

    for (const postRecord of recordsToProcess) {
      try {
        // Call the scraper API using fetch from the global scope
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

        updates.push({
          post_nummer: postRecord.post_nummer,
          check_foretag: parseNumberWithCommas(data.hittaForetag),
          check_personer: parseNumberWithCommas(data.hittaPersoner),
          check_platser: parseNumberWithCommas(data.hittaPlatser),
          checkTime: currentTime,
        });

        console.log(
          `✓ ${postRecord.post_nummer}: F=${parseNumberWithCommas(data.hittaForetag)}, P=${parseNumberWithCommas(data.hittaPersoner)}, L=${parseNumberWithCommas(data.hittaPlatser)}`,
        );
      } catch (error) {
        errorCount++;
        console.error(`✗ Error processing ${postRecord.post_nummer}:`, error);
      }

      // Add delay between requests to avoid overwhelming the API
      if (
        delayMs > 0 &&
        recordsToProcess.indexOf(postRecord) < recordsToProcess.length - 1
      ) {
        await new Promise((resolve) => setTimeout(resolve, delayMs));
      }
    }

    // Update this batch
    if (updates.length > 0) {
      await ctx.runMutation(api.myFunctions.batchUpdatePostNummerCounts, {
        updates,
      });
      console.log(`Updated batch of ${updates.length} records`);
    }

    const hasMore = postNummerBatch.length === limit;
    const nextOffset = hasMore ? offset + limit : undefined;

    return {
      success: true,
      processed: updates.length,
      errors: errorCount,
      hasMore,
      nextOffset,
    };
  },
});

export const updateSinglePostnummer = action({
  args: {
    post_nummer: v.string(),
    delayMs: v.optional(v.number()),
  },
  handler: async (
    ctx,
    args,
  ): Promise<{
    success: boolean;
    post_nummer: string;
    hittaForetag?: number;
    hittaPersoner?: number;
    hittaPlatser?: number;
    error?: string;
  }> => {
    const delayMs = args.delayMs || 0;

    try {
      console.log(`Updating single postnummer: ${args.post_nummer}`);

      // Call the scraper API using fetch from the global scope
      const response = await fetch(`http://localhost:6969/api/hitta-count`, {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          query: args.post_nummer,
        }),
      });

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }

      const data = await response.json();
      const currentTime = Date.now();

      const parsedForetag = parseNumberWithCommas(data.hittaForetag);
      const parsedPersoner = parseNumberWithCommas(data.hittaPersoner);
      const parsedPlatser = parseNumberWithCommas(data.hittaPlatser);

      // Update the record with the fetched counts
      await ctx.runMutation(api.myFunctions.updatePostNummerCounts, {
        post_nummer: args.post_nummer,
        check_foretag: parsedForetag,
        check_personer: parsedPersoner,
        check_platser: parsedPlatser,
        checkTime: currentTime,
      });

      console.log(
        `✓ ${args.post_nummer}: F=${parsedForetag}, P=${parsedPersoner}, L=${parsedPlatser}`,
      );

      return {
        success: true,
        post_nummer: args.post_nummer,
        hittaForetag: parsedForetag,
        hittaPersoner: parsedPersoner,
        hittaPlatser: parsedPlatser,
      };
    } catch (error) {
      const errorMessage =
        error instanceof Error ? error.message : String(error);
      console.error(`✗ Error updating ${args.post_nummer}:`, error);

      return {
        success: false,
        post_nummer: args.post_nummer,
        error: errorMessage,
      };
    }
  },
});

export const fetchAllHittaCounts = action({
  args: {
    batchSize: v.optional(v.number()),
    delayMs: v.optional(v.number()),
    onlyUnprocessed: v.optional(v.boolean()),
    maxErrors: v.optional(v.number()),
    updateMode: v.optional(v.boolean()),
  },
  handler: async (
    ctx,
    args,
  ): Promise<{
    success: boolean;
    totalProcessed: number;
    totalErrors: number;
  }> => {
    const batchSize = args.batchSize || 100;
    const delayMs = args.delayMs || 1000;
    const onlyUnprocessed = args.updateMode
      ? false
      : args.onlyUnprocessed !== false;
    const maxErrors = args.maxErrors || 50;

    let offset = 0;
    let totalProcessed = 0;
    let totalErrors = 0;

    console.log("Starting complete fetch of hitta counts...");

    while (true) {
      const result = await ctx.runAction(
        api.myFunctions.fetchHittaCountsBatch,
        {
          offset,
          limit: batchSize,
          delayMs,
          onlyUnprocessed,
        },
      );

      totalProcessed += result.processed;
      totalErrors += result.errors;

      console.log(
        `Progress: ${totalProcessed} processed, ${totalErrors} errors...`,
      );

      if (!result.hasMore || totalErrors >= maxErrors) {
        break;
      }

      offset = result.nextOffset!;
    }

    console.log(
      `Complete! Total processed: ${totalProcessed}, Total errors: ${totalErrors}`,
    );
    return { success: true, totalProcessed, totalErrors };
  },
});

export const processPostnummerRange = action({
  args: {
    startPostnummer: v.string(),
    endPostnummer: v.string(),
    delayMs: v.optional(v.number()),
    onlyUnprocessed: v.optional(v.boolean()),
    updateMode: v.optional(v.boolean()),
  },
  handler: async (
    ctx,
    args,
  ): Promise<{
    success: boolean;
    totalProcessed: number;
    totalErrors: number;
    processedRecords: Array<{
      post_nummer: string;
      success: boolean;
      error?: string;
    }>;
  }> => {
    const delayMs = args.delayMs || 1000;
    const onlyUnprocessed = args.updateMode
      ? false
      : args.onlyUnprocessed !== false;

    // Get postnummer records in the specified range
    const postNummerRecords = await ctx.runQuery(
      api.myFunctions.getPostNummerByRange,
      {
        startPostnummer: args.startPostnummer,
        endPostnummer: args.endPostnummer,
      },
    );

    if (postNummerRecords.length === 0) {
      return {
        success: true,
        totalProcessed: 0,
        totalErrors: 0,
        processedRecords: [],
      };
    }

    // Filter for unprocessed records if requested
    const recordsToProcess = onlyUnprocessed
      ? postNummerRecords.filter(
          (record) =>
            record.check_foretag === undefined ||
            record.check_personer === undefined ||
            record.check_platser === undefined ||
            record.checkTime === undefined,
        )
      : postNummerRecords;

    const processedRecords: Array<{
      post_nummer: string;
      success: boolean;
      error?: string;
    }> = [];
    let totalErrors = 0;

    console.log(
      `Processing ${recordsToProcess.length} postnummer records in range ${args.startPostnummer} to ${args.endPostnummer}...`,
    );

    for (const postRecord of recordsToProcess) {
      try {
        // Call the scraper API using fetch from the global scope
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

        // Update the record with the fetched counts
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

      // Add delay between requests to avoid overwhelming the API
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

export const myAction = action({
  // Validators for arguments.
  args: {
    first: v.number(),
    second: v.string(),
  },

  // Action implementation.
  handler: async (ctx, args) => {
    //// Use the browser-like `fetch` API to send HTTP requests.
    //// See https://docs.convex.dev/functions/actions#calling-third-party-apis-and-using-npm-packages.
    // const response = await ctx.fetch("https://api.thirdpartyservice.com");
    // const data = await response.json();

    //// Query data by running Convex queries.
    const data = await ctx.runQuery(api.myFunctions.listNumbers, {
      count: 10,
    });
    console.log(data);

    //// Write data by running Convex mutations.
    await ctx.runMutation(api.myFunctions.addNumber, {
      value: args.first,
    });
  },
});
