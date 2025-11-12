#!/usr/bin/env node

import { Command } from "commander";
import axios from "axios";
import { JSDOM } from "jsdom";
import fs from "fs";
import path from "path";
// Note: We attempt to load better-sqlite3 dynamically at runtime to allow
// falling back to an HTTP API when native bindings are unavailable.

async function extractPersonDataFromPage(searchQuery, page) {
  const encodedQuery = encodeURIComponent(searchQuery);
  const url = `https://www.hitta.se/s%C3%B6k?vad=${encodedQuery}&typ=prv&sida=${page}`;

  try {
    const response = await axios.get(url, {
      headers: {
        "User-Agent":
          "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
      },
    });

    const dom = new JSDOM(response.data);
    const document = dom.window.document;

    // Extract hittaPersoner count (only on first page)
    let hittaPersoner;
    if (page === 1) {
      const contentContainers = document.querySelectorAll(
        "span.style_content__nx640",
      );
      for (const container of contentContainers) {
        const titleSpan = container.querySelector("span.style_tabTitle__EC5RP");
        if (titleSpan?.textContent === "Personer") {
          const countSpan = container.querySelector(
            "span.style_tabNumbers__VbAE7",
          );
          const countText = countSpan?.textContent?.trim();
          hittaPersoner = countText ? parseInt(countText, 10) : undefined;
          break;
        }
      }
    }

    // Find all person result rows
    const personItems = document.querySelectorAll(
      'li[itemprop="itemListElement"][data-test="person-item"]',
    );
    const results = [];

    for (const item of personItems) {
      try {
        // Extract name and age
        const titleElement = item.querySelector(
          'h2[data-test="search-result-title"]',
        );
        let personnamn = "";
        let alder = "";

        if (titleElement) {
          const ageSpan = titleElement.querySelector("span.style_age__ZgTHo");
          alder = ageSpan?.textContent?.trim() || "";
          personnamn =
            titleElement.textContent
              ?.replace(ageSpan?.textContent || "", "")
              .trim() || "";
        }

        // Extract gender, address, postal code, city
        const infoParagraph = item.querySelector("p.text-body-long-sm-regular");
        let kon = "";
        let gatuadress = "";
        let postnummer = "";
        let postort = "";

        if (infoParagraph) {
          const lines = infoParagraph.innerHTML.split("<br>");
          const genderSpan = infoParagraph.querySelector(
            "span.style_gender__hKSL0",
          );
          kon = genderSpan?.textContent?.trim() || "";

          if (lines.length >= 2) {
            gatuadress = lines[1]?.trim() || "";
          }
          if (lines.length >= 3) {
            const addressLine = lines[2]?.trim() || "";
            // Postal code format is "XXX XX", so look for pattern with space
            const postalMatch = addressLine.match(/^(\d{3}\s\d{2})\s+(.+)$/);
            if (postalMatch) {
              postnummer = postalMatch[1];
              postort = postalMatch[2];
            } else {
              // Fallback: try splitting by space and assume first 2 parts are postal code
              const addressParts = addressLine.split(" ") || [];
              if (addressParts.length >= 3) {
                postnummer = `${addressParts[0]} ${addressParts[1]}`;
                postort = addressParts.slice(2).join(" ");
              }
            }
          }
        }

        // Extract phone number
        const phoneButton = item.querySelector(
          'button[data-test="phone-link"]',
        );
        let telefon = "";
        if (phoneButton) {
          const phoneText = phoneButton.textContent?.trim() || "";
          // Extract phone number (remove "Visa" text and clean up)
          telefon = phoneText.replace("Visa", "").trim();
        }

        // Extract map link
        const mapButton = item.querySelector(
          'a[data-test="show-on-map-button"]',
        );
        let karta = "";
        if (mapButton) {
          const href = mapButton.getAttribute("href") || "";
          karta = `hitta.se${href}`;
        }

        // Extract person link
        const linkElement = item.querySelector(
          'a[data-test="search-list-link"]',
        );
        let link = "";
        let visa = "";
        let personId = "";

        if (linkElement) {
          const href = linkElement.getAttribute("href") || "";
          link = `https://www.hitta.se${href}`;

          // Extract person ID from link for revealNumber functionality
          // Link format: /joris+frederik+dekkers/j%C3%A4rna/person/dRPZZ___w5
          const linkMatch = href.match(/\/person\/([^\/\?]+)/);
          if (linkMatch) {
            personId = linkMatch[1];
          }
        }

        // Try to get complete phone number using revealNumber if we have personId and phone
        let completeTelefon = telefon;
        if (personId && telefon && telefon.trim()) {
          try {
            // Extract partial number from display text (e.g., "070-977 19" from "070-977 19 Visa")
            const partialMatch = telefon.match(/(\d[\d\s-]*)/);
            if (partialMatch && personId) {
              const partialNumber = partialMatch[1]
                .replace(/\s/g, "")
                .replace(/-/g, "");

              // Construct revealNumber URL using person's link
              const revealUrl = `${link}?revealNumber=46${partialNumber}`;
              visa = revealUrl;

              // Only reveal phone numbers for first 5 people to avoid timeout
              // Change this number to reveal more/less phone numbers
              if (results.length < 5) {
              try {
                  console.log(
                    `Attempting to reveal number for ${personnamn}: ${revealUrl}`,
                  );
                  const revealResponse = await axios.get(revealUrl, {
                    headers: {
                      "User-Agent":
                        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
                    },
                    timeout: 5000,
                  });

                  const revealDom = new JSDOM(revealResponse.data);
                  const revealDocument = revealDom.window.document;

                  // Look for complete phone number in revealed page
                  // First try JSON-LD structured data
                  const jsonLdScript = revealDocument.querySelector('script[type="application/ld+json"]');
                  if (jsonLdScript) {
                    try {
                      const jsonLdData = JSON.parse(jsonLdScript.textContent);
                      if (jsonLdData.telephone) {
                        completeTelefon = jsonLdData.telephone;
                        console.log(`Revealed complete phone from JSON-LD for ${personnamn}: ${completeTelefon}`);
                      }
                    } catch (e) {
                      console.log(`Failed to parse JSON-LD for ${personnamn}: ${e.message}`);
                    }
                  }
                  
                  // If JSON-LD didn't work, try regular HTML elements
                  if (completeTelefon === telefon) {
                    const phoneSelectors = [
                      'span[data-test="phone-numbers"]',
                      'a[href^="tel:"]',
                      ".phone-number",
                      '[data-phone]',
                      '[data-telephone]'
                    ];
                    
                    for (const selector of phoneSelectors) {
                      const phoneElement = revealDocument.querySelector(selector);
                      if (phoneElement) {
                        const phoneText = phoneElement.textContent || phoneElement.getAttribute('data-phone') || phoneElement.getAttribute('data-telephone') || "";
                        const cleanPhone = phoneText.replace(/[^\d+]/g, "");
                        if (cleanPhone.length >= 10 && !cleanPhone.includes("XX")) {
                          completeTelefon = cleanPhone.startsWith("+")
                            ? cleanPhone
                            : `+46${cleanPhone.replace(/^0/, "")}`;
                          console.log(
                            `Revealed complete phone from HTML for ${personnamn}: ${completeTelefon}`,
                          );
                          break;
                        }
                      }
                    }
                  }
                } catch (revealError) {
                  console.log(
                    `Could not reveal complete number for ${personnamn}: ${revealError.message}`,
                  );
                }
              }
              } // End of phone reveal limit
            } catch (error) {
            console.log(
              `Error processing phone number for ${personnamn}: ${error.message}`,
            );
          }
        }
        results.push({
          personnamn,
          alder,
          kon,
          gatuadress,
          postnummer,
          postort,
          telefon: completeTelefon,
          karta,
          link,
          visa,
        });
      } catch (error) {
        console.error("Error processing person item:", error);
      }
    }

    // Check if there's a next page - look for disabled state or absence of next button
    const nextButton = document.querySelector('a[data-ga4-action="next_page"]');
    const hasNextPage =
      !!nextButton && !nextButton.classList.contains("disabled");

    // Also check if we have results on this page
    const hasResults = personItems.length > 0;

    // Additional check: if we have fewer than 25 results, we're likely on the last page
    const isLastPageByCount = personItems.length < 25;

    console.log(
      `Page ${page}: Found ${personItems.length} persons, hasNextPage: ${hasNextPage}, hasResults: ${hasResults}, isLastPageByCount: ${isLastPageByCount}`,
    );

    // Continue if we have results AND there's a next page
    // Stop when we hit a page with no results (even if HTTP 200)
    const shouldContinue = hasResults && hasNextPage && !isLastPageByCount;

    return { persons: results, hasNextPage: shouldContinue, hittaPersoner };
  } catch (error) {
    throw new Error(`Failed to fetch or parse Hitta.se page ${page}: ${error}`);
  }
}

async function extractAllPersonData(searchQuery) {
  let allPersons = [];
  let currentPage = 1;
  let hasNextPage = true;
  let hittaPersoner = 0;

  console.log(`Starting search for: ${searchQuery}`);

  while (hasNextPage) {
    try {
      const {
        persons,
        hasNextPage: hasMore,
        hittaPersoner: count,
      } = await extractPersonDataFromPage(searchQuery, currentPage);

      allPersons = allPersons.concat(persons);
      hasNextPage = hasMore;

      // Get hittaPersoner count from first page and display counts before first page fetch log
      if (currentPage === 1 && count !== undefined) {
        hittaPersoner = count;
        console.log(`Total persons count: ${hittaPersoner}`);
        const totalPages = Math.floor(hittaPersoner / 25);
        console.log(`Total pages count: ${totalPages}`);
      }

      console.log(`Fetching page ${currentPage}...`);

      currentPage++;

      // Add a small delay to avoid overwhelming the server
      await new Promise((resolve) => setTimeout(resolve, 500));
    } catch (error) {
      console.error(`Error on page ${currentPage}:`, error);
      break;
    }
  }

  console.log(`Total persons found: ${allPersons.length}`);
  return { persons: allPersons, hittaPersoner };
}

// --- Database helpers ------------------------------------------------------

/**
 * Attempt to resolve the path to the primary Laravel sqlite database.
 * Falls back through several relative locations so the script can be run
 * from repository root, scripts/, or resources/scripts/.
 */
function resolveSqlitePath() {
  const candidates = [
    path.join(process.cwd(), "database", "database.sqlite"),
    path.join(process.cwd(), "..", "database", "database.sqlite"),
    path.join(process.cwd(), "..", "..", "database", "database.sqlite"),
    path.join(path.dirname(new URL(import.meta.url).pathname), "..", "database", "database.sqlite"),
  ];
  for (const p of candidates) {
    if (fs.existsSync(p)) {
      return p;
    }
  }
  throw new Error("Could not locate database/database.sqlite. Run from project root or adjust path.");
}

/**
 * Initialize a better-sqlite3 database connection.
 */
async function initDb() {
  // Dynamically import so the script can gracefully fall back if bindings are missing
  const { default: Database } = await import("better-sqlite3");
  const dbPath = resolveSqlitePath();
  const db = new Database(dbPath);
  db.pragma("journal_mode = WAL");
  return db;
}

/**
 * Prepare insert & update statements (upsert by link uniqueness heuristic).
 */
function prepareStatements(db) {
  // Fetch existing link (no unique constraint but we treat link as natural key when present)
  const findByLink = db.prepare("SELECT id FROM hitta_se WHERE link = ? LIMIT 1");

  const insert = db.prepare(`
    INSERT INTO hitta_se (
      personnamn, alder, kon, gatuadress, postnummer, postort,
      telefon, karta, link, bostadstyp, bostadspris, is_active, is_telefon, is_ratsit,
      created_at, updated_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
  `);

  const update = db.prepare(`
    UPDATE hitta_se SET
      personnamn = ?,
      alder = ?,
      kon = ?,
      gatuadress = ?,
      postnummer = ?,
      postort = ?,
      telefon = ?,
      karta = ?,
      bostadstyp = ?,
      bostadspris = ?,
      is_active = ?,
      is_telefon = ?,
      is_ratsit = ?,
      updated_at = ?
    WHERE id = ?
  `);

  return { findByLink, insert, update };
}

/**
 * Convert a person record from scraping output to DB-ready values.
 */
function mapPersonToDb(person) {
  // Telefon stored as JSON -> we convert to array unless placeholder/empty
  let telefonArray = [];
  if (person.telefon && person.telefon !== "Lägg till telefonnummer") {
    // Clean & normalize single number -> array
    const cleaned = person.telefon.trim();
    if (cleaned) {
      telefonArray = [cleaned];
    }
  }
  const telefonJson = telefonArray.length ? JSON.stringify(telefonArray) : null;

  return {
    personnamn: person.personnamn || null,
    alder: person.alder || null,
    kon: person.kon || null,
    gatuadress: person.gatuadress || null,
    postnummer: person.postnummer || null,
    postort: person.postort || null,
    telefon: telefonJson,
    karta: person.karta || null,
    link: person.link || null,
    bostadstyp: null, // Not scraped yet
    bostadspris: null, // Not scraped yet
    is_active: 1, // default true
    is_telefon: telefonArray.length ? 1 : 0,
    is_ratsit: 0,
  };
}

/**
 * Persist persons to hitta_se with upsert behavior based on "link" if present.
 */
function savePersonsToDatabase(db, persons, { batchSize = 100 } = {}) {
  const { findByLink, insert, update } = prepareStatements(db);
  const now = () => new Date().toISOString().slice(0, 19).replace("T", " ");

  let inserted = 0;
  let updated = 0;

  const batch = [];

  for (const person of persons) {
    const mapped = mapPersonToDb(person);
    const existing = mapped.link ? findByLink.get(mapped.link) : null;

    if (existing) {
      // Update
      update.run(
        mapped.personnamn,
        mapped.alder,
        mapped.kon,
        mapped.gatuadress,
        mapped.postnummer,
        mapped.postort,
        mapped.telefon,
        mapped.karta,
        mapped.bostadstyp,
        mapped.bostadspris,
        mapped.is_active,
        mapped.is_telefon,
        mapped.is_ratsit,
        now(),
        existing.id,
      );
      updated++;
    } else {
      // Queue for insert (bulk transaction) for speed
      batch.push(mapped);
      if (batch.length >= batchSize) {
        const tx = db.transaction((rows) => {
          for (const r of rows) {
            insert.run(
              r.personnamn,
              r.alder,
              r.kon,
              r.gatuadress,
              r.postnummer,
              r.postort,
              r.telefon,
              r.karta,
              r.link,
              r.bostadstyp,
              r.bostadspris,
              r.is_active,
              r.is_telefon,
              r.is_ratsit,
              now(),
              now(),
            );
            inserted++;
          }
        });
        tx(batch);
        batch.length = 0; // reset
      }
    }
  }

  // Flush remaining inserts
  if (batch.length) {
    const tx = db.transaction((rows) => {
      for (const r of rows) {
        insert.run(
          r.personnamn,
          r.alder,
          r.kon,
          r.gatuadress,
          r.postnummer,
          r.postort,
          r.telefon,
          r.karta,
          r.link,
          r.bostadstyp,
          r.bostadspris,
          r.is_active,
          r.is_telefon,
          r.is_ratsit,
          now(),
          now(),
        );
        inserted++;
      }
    });
    tx(batch);
  }

  return { inserted, updated };
}

// --- HTTP API fallback ------------------------------------------------------

function mapPersonToApiPayload(person) {
  const payload = {
    personnamn: person.personnamn || null,
    alder: person.alder || null,
    kon: person.kon || null,
    gatuadress: person.gatuadress || null,
    postnummer: person.postnummer || null,
    postort: person.postort || null,
    karta: person.karta || null,
    link: person.link || null,
    bostadstyp: null,
    bostadspris: null,
    is_active: true,
    is_telefon: false,
    is_ratsit: false,
  };

  if (person.telefon && person.telefon !== "Lägg till telefonnummer") {
    const clean = String(person.telefon).trim();
    if (clean) {
      payload.telefon = [clean];
      payload.is_telefon = true;
    } else {
      payload.telefon = [];
    }
  } else {
    payload.telefon = [];
  }

  return payload;
}

async function savePersonsViaApi(persons, searchQuery, baseUrl) {
  const apiBase = (baseUrl || process.env.API_BASE || process.env.APP_URL || "http://127.0.0.1:8000").replace(/\/$/, "");
  const endpoint = `${apiBase}/api/hitta-se`;

  let created = 0;
  let updated = 0;
  let failed = 0;

  for (let i = 0; i < persons.length; i++) {
    const p = persons[i];
    const payload = mapPersonToApiPayload(p);
    try {
      const res = await axios.post(endpoint, payload, {
        headers: { "Content-Type": "application/json" },
        timeout: 10000,
      });
      if (res.status === 201) {
        created++;
      } else if (res.status === 200) {
        updated++;
      } else {
        failed++;
        console.log(`Unexpected status ${res.status} for index ${i}`);
      }
    } catch (err) {
      failed++;
      console.log(`API save failed for index ${i}: ${err.message}`);
    }
    // Gentle pacing to avoid overwhelming the server
    await new Promise((r) => setTimeout(r, 100));
  }

  return { created, updated, failed };
}

function saveToCSV(data, filename) {
  const headers = [
    "personnamn",
    "alder",
    "kon",
    "gatuadress",
    "postnummer",
    "postort",
    "telefon",
    "karta",
    "link",
    "visa",
  ];

  const csvLines = [headers.join(",")];

  for (const person of data) {
    const row = [
      `"${(person.personnamn || "").replace(/"/g, '""')}"`,
      `"${(person.alder || "").replace(/"/g, '""')}"`,
      `"${(person.kon || "").replace(/"/g, '""')}"`,
      `"${(person.gatuadress || "").replace(/"/g, '""')}"`,
      `"${(person.postnummer || "").replace(/"/g, '""')}"`,
      `"${(person.postort || "").replace(/"/g, '""')}"`,
      `"${(person.telefon || "").replace(/"/g, '""')}"`,
      `"${(person.karta || "").replace(/"/g, '""')}"`,
      `"${(person.link || "").replace(/"/g, '""')}"`,
      `"${(person.visa || "").replace(/"/g, '""')}"`,
    ];
    csvLines.push(row.join(","));
  }

  const csvContent = csvLines.join("\n");

  // Ensure data directory exists
  const dataDir = path.join(process.cwd(), "data", "csv");
  if (!fs.existsSync(dataDir)) {
    fs.mkdirSync(dataDir, { recursive: true });
  }

  const filepath = path.join(dataDir, filename);
  fs.writeFileSync(filepath, csvContent, "utf8");

  return filepath;
}

function saveDetailsToCSV(data, searchQuery, filename) {
  // Filter results where telefon is NOT "Lägg till telefonnummer"
  const filteredData = data.filter(
    (person) => person.telefon !== "Lägg till telefonnummer",
  );

  const csvLines = filteredData.map((person) => {
    return [
      person.personnamn,
      person.gatuadress,
      searchQuery,
      person.postort,
      person.link,
    ]
      .map((field) => `"${field.replace(/"/g, '""')}"`)
      .join(",");
  });

  const csvContent = csvLines.join("\n");

  // Ensure data directory exists
  const dataDir = path.join(process.cwd(), "data", "csv");
  if (!fs.existsSync(dataDir)) {
    fs.mkdirSync(dataDir, { recursive: true });
  }

  const filepath = path.join(dataDir, filename);
  fs.writeFileSync(filepath, csvContent, "utf8");

  return filepath;
}

const program = new Command();
program
  .name("hittaSearchPersons")
  .description(
    "Extract all person data from Hitta.se search results with pagination and save to CSV",
  )
  .arguments("<search-query>", "Search query to look up")
  .action(async (searchQuery) => {
    try {
      const result = await extractAllPersonData(searchQuery);
      const { persons: personData, hittaPersoner } = result;

      if (personData.length === 0) {
        console.log("No results found");
        return;
      }

      // Create filename with query and total count
      const sanitizedQuery = searchQuery.replace(/[^a-zA-Z0-9åäöÅÄÖ]/g, "_");
      const filename = `hitta_search_persons_${sanitizedQuery}_total_${personData.length}.csv`;

      const filepath = saveToCSV(personData, filename);
      console.log(`Saved ${personData.length} results to: ${filepath}`);

      // Save details CSV with phone number filter
      const detailsFilename = `hitta_search_persons_details_${sanitizedQuery}_total_${personData.length}.csv`;
      const detailsFilepath = saveDetailsToCSV(
        personData,
        searchQuery,
        detailsFilename,
      );
      console.log(`Saved details to: ${detailsFilepath}`);

      // Persist directly to SQLite hitta_se table (upsert by link) or fall back to API
      let persisted = false;
      try {
        console.log(`Persisting ${personData.length} persons to hitta_se table (SQLite)...`);
        const db = await initDb();
        const { inserted, updated } = savePersonsToDatabase(db, personData, { batchSize: 100 });
        console.log(`✅ SQLite persistence complete. Inserted: ${inserted}, Updated: ${updated}. Total processed: ${personData.length}.`);
        db.close();
        persisted = true;
      } catch (dbErr) {
        console.error(`❌ SQLite persistence failed: ${dbErr.message}`);
        console.error("Falling back to HTTP API at /api/hitta-se. Ensure your Laravel server is running (e.g., php artisan serve).\nYou can override the base URL with API_BASE or APP_URL env vars.");
      }

      if (!persisted) {
        try {
          const { created, updated, failed } = await savePersonsViaApi(personData, searchQuery);
          console.log(`✅ API persistence complete. Created: ${created}, Updated: ${updated}, Failed: ${failed}. Total: ${personData.length}.`);
        } catch (apiErr) {
          console.error(`❌ API persistence failed: ${apiErr.message}`);
          console.error("Please ensure the Laravel server is running at APP_URL and that /api/hitta-se is reachable.");
        }
      }

      // Output JSON (post-persistence) for optional downstream processing
      console.log(JSON.stringify({
        query: searchQuery,
        totalScraped: personData.length,
        data: personData,
      }, null, 2));
    } catch (error) {
      console.error("Error:", error);
      process.exit(1);
    }
  });

program.parse(process.argv);
