import { defineSchema, defineTable } from "convex/server";
import { v } from "convex/values";

export default defineSchema({
  numbers: defineTable({
    value: v.number(),
  }),
  postnummer: defineTable({
    postNummer: v.string(),
    postOrt: v.string(),
    postLan: v.string(),
  }).index("by_postNummer", ["postNummer"]),
  postNummerQueueCount: defineTable({
    postNummer: v.string(),
    postOrt: v.string(),
    postLan: v.string(),
    idPostNummer: v.string(),
    countForetag: v.number(),
    countPersoner: v.number(),
    countPlatser: v.number(),
  }).index("by_postNummer", ["postNummer"]),
  post_nummer: defineTable({
    post_nummer: v.string(),
    post_ort: v.string(),
    post_lan: v.string(),
    // Include old columns temporarily for cleanup
    foretag: v.optional(v.number()),
    personer: v.optional(v.number()),
    platser: v.optional(v.number()),
    check_foretag: v.optional(v.number()),
    check_personer: v.optional(v.number()),
    check_platser: v.optional(v.number()),
    updateTime: v.optional(v.number()),
    checkTime: v.optional(v.number()),
    fetch_bolag: v.optional(v.number()),
    fetch_house: v.optional(v.number()),
    fetch_phone: v.optional(v.number()),
    fetch_total: v.optional(v.number()),
  }).index("by_post_nummer", ["post_nummer"]),
  postNummerDB: defineTable({
    post_nummer: v.string(),
    post_ort: v.string(),
    post_lan: v.string(),
    countForetag: v.number(),
    countPersoner: v.number(),
    hittaForetag: v.number(),
    hittaPersoner: v.number(),
    totalForetag: v.number(),
    totalPersoner: v.number(),
  }).index("by_post_nummer", ["post_nummer"]),
  ratsit_hitta: defineTable({
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
  })
    .index("by_postnummer", ["postnummer"])
    .index("by_personnummer", ["personnummer"])
    .index("by_personnamn", ["personnamn"])
    .index("by_active", ["is_active"]),

  // New tables for event hub architecture
  syncEvents: defineTable({
    eventId: v.string(),
    eventType: v.string(),
    entityType: v.string(),
    entityId: v.string(),
    data: v.optional(v.any()),
    createdAt: v.number(),
    syncedToPostgres: v.boolean(),
    syncedAt: v.optional(v.number()),
  })
    .index("by_eventId", ["eventId"])
    .index("by_entityType", ["entityType"]),

  scrapeJobs: defineTable({
    jobId: v.string(),
    jobType: v.string(),
    startPostnummer: v.optional(v.string()),
    endPostnummer: v.optional(v.string()),
    batchSize: v.optional(v.number()),
    updateMode: v.optional(v.boolean()),
    status: v.string(),
    totalProcessed: v.number(),
    totalErrors: v.number(),
    errorMessage: v.optional(v.string()),
    createdAt: v.number(),
    startedAt: v.optional(v.number()),
    completedAt: v.optional(v.number()),
  })
    .index("by_jobId", ["jobId"])
    .index("by_status", ["status"]),
});
