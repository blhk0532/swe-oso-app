#!/usr/bin/env node

/**
 * Hitta.se count extractor
 * Fetches Företag, Personer, and Platser counts from hitta.se search results
 * 
 * Usage: node hittaCounts.mjs <search-query>
 * Example: node hittaCounts.mjs "112 52"
 * 
 * Output: JSON object with counts
 */

import { program } from "commander";
import axios from "axios";
import { JSDOM } from "jsdom";

async function extractHittaCounts(searchQuery) {
  const encodedQuery = encodeURIComponent(searchQuery);
  const url = `https://www.hitta.se/s%C3%B6k?vad=${encodedQuery}`;

  let lastError;
  for (let attempt = 1; attempt <= 3; attempt++) {
    try {
      const response = await axios.get(url, {
        headers: {
          "User-Agent":
            "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
        },
        timeout: 30000,
      });

      const dom = new JSDOM(response.data);
      const document = dom.window.document;

      const nav = document.querySelector(
        'nav[data-trackcat="search-result-tabs"]',
      );
      if (!nav) {
        throw new Error("Search result tabs not found");
      }

      const extractCountAfterTitle = (titleText) => {
        const contentContainers = nav.querySelectorAll(
          "span.style_content__nx640",
        );

        for (const container of contentContainers) {
          const titleSpan = container.querySelector(
            "span.style_tabTitle__EC5RP",
          );
          if (titleSpan?.textContent === titleText) {
            const countSpan = container.querySelector(
              "span.style_tabNumbers__VbAE7",
            );
            const countText = countSpan?.textContent?.trim();
            return countText ? parseInt(countText.replace(/,/g, ''), 10) : 0;
          }
        }

        return 0;
      };

      const hittaForetag = extractCountAfterTitle("Företag");
      const hittaPersoner = extractCountAfterTitle("Personer");
      const hittaPlatser = extractCountAfterTitle("Platser");

      return {
        hittaForetag,
        hittaPersoner,
        hittaPlatser,
      };
    } catch (error) {
      lastError = error;
      if (attempt < 3) {
        console.error(
          `Attempt ${attempt} failed, retrying in ${attempt * 1000}ms...`,
        );
        await new Promise((resolve) => setTimeout(resolve, attempt * 1000));
        continue;
      }
      throw new Error(
        `Failed to fetch or parse Hitta.se after 3 attempts: ${error.message}`,
      );
    }
  }

  throw new Error(
    `Failed to fetch or parse Hitta.se after 3 attempts: ${lastError?.message || 'Unknown error'}`,
  );
}

program
  .name("hittaCounts")
  .description("Extract count values from Hitta.se search results")
  .argument("<search-query>", "Search query to look up (e.g., postnummer)")
  .action(async (searchQuery) => {
    try {
      console.error(`Searching hitta.se for: ${searchQuery}`);
      const counts = await extractHittaCounts(searchQuery);
      
      console.error(`Found counts - Företag: ${counts.hittaForetag}, Personer: ${counts.hittaPersoner}, Platser: ${counts.hittaPlatser}`);
      
      // Output JSON to stdout for parsing by PHP
      console.log(JSON.stringify(counts));
    } catch (error) {
      console.error("Error:", error.message);
      process.exit(1);
    }
  });

program.parse();
