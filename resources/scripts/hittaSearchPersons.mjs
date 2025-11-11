#!/usr/bin/env node

import { Command } from "commander";
import axios from "axios";
import { JSDOM } from "jsdom";
import fs from "fs";
import path from "path";

// Random User-Agent rotation
const userAgents = [
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
  "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Firefox/121.0",
  "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.2 Safari/605.1.15",
];

function getRandomUserAgent() {
  return userAgents[Math.floor(Math.random() * userAgents.length)];
}

function getRandomDelay(min = 1500, max = 3000) {
  return Math.floor(Math.random() * (max - min + 1)) + min;
}

async function extractPersonDataFromPage(searchQuery, page) {
  const encodedQuery = encodeURIComponent(searchQuery);
  const url = `https://www.hitta.se/s%C3%B6k?vad=${encodedQuery}&typ=prv&sida=${page}`;

  try {
    const response = await axios.get(url, {
      headers: {
        "User-Agent": getRandomUserAgent(),
        Accept:
          "text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7",
        "Accept-Language": "sv-SE,sv;q=0.9,en;q=0.8",
        "Accept-Encoding": "gzip, deflate, br",
        DNT: "1",
        Connection: "keep-alive",
        "Upgrade-Insecure-Requests": "1",
        "Sec-Fetch-Dest": "document",
        "Sec-Fetch-Mode": "navigate",
        "Sec-Fetch-Site": "none",
        "Sec-Fetch-User": "?1",
        "Cache-Control": "max-age=0",
      },
      timeout: 30000,
    });

    // Debug: Check if we're getting blocked
    if (
      response.data.includes("Anrop nekat") ||
      response.data.includes("Access denied") ||
      response.data.includes("Robot check")
    ) {
      console.log(
        `Page ${page}: Blocked - Response contains:`,
        response.data.substring(0, 200),
      );
    }

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
            const addressParts = lines[2]?.trim().split(" ") || [];
            if (addressParts.length >= 2) {
              postnummer = addressParts.slice(0, 2).join(" ");
              postort = addressParts.slice(2).join(" ");
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

        // Skip detail page fetching for speed - use partial phone numbers for now
        let completeTelefon = telefon;

        // Only construct revealNumber URL for reference (no detail page fetch)
        if (
          telefon &&
          telefon.trim() &&
          telefon !== "Lägg till telefonnummer"
        ) {
          const partialMatch = telefon.match(/(\d[\d\s-]*)/);
          if (partialMatch) {
            const partialNumber = partialMatch[1]
              .replace(/\s/g, "")
              .replace(/-/g, "");
            visa = `${link}?revealNumber=46${partialNumber}`;
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

    // Only continue if we have results AND not on last page
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

  // First, fetch just page 1 to get total count
  try {
    const firstPageResult = await extractPersonDataFromPage(searchQuery, 1);

    if (firstPageResult.hittaPersoner !== undefined) {
      hittaPersoner = firstPageResult.hittaPersoner;
      const totalPages = Math.ceil(hittaPersoner / 25);
      console.log(`Total persons count: ${hittaPersoner}`);
      console.log(`Total pages count: ${totalPages}`);

      // Add first page results
      allPersons = allPersons.concat(firstPageResult.persons);
      hasNextPage = firstPageResult.hasNextPage;
      currentPage = 2; // Start from page 2 since we already have page 1
    }
  } catch (error) {
    console.error(`Error getting initial page:`, error);
    return { persons: [], hittaPersoner: 0 };
  }

  // Now fetch remaining pages
  while (hasNextPage) {
    try {
      console.log(`Fetching page ${currentPage}...`);

      const { persons, hasNextPage: hasMore } = await extractPersonDataFromPage(
        searchQuery,
        currentPage,
      );

      allPersons = allPersons.concat(persons);
      hasNextPage = hasMore;
      currentPage++;

      // Random delay to avoid rate limiting
      await new Promise((resolve) => setTimeout(resolve, getRandomDelay()));
    } catch (error) {
      console.error(`Error on page ${currentPage}:`, error);
      break;
    }
  }

  console.log(`Total persons found: ${allPersons.length}`);
  return { persons: allPersons, hittaPersoner };
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
  .argument("<search-query>", "Search query to look up")
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

      // Return all values as JSON
      console.log(JSON.stringify(personData, null, 2));
    } catch (error) {
      console.error("Error:", error);
      process.exit(1);
    }
  });

program.parse();
