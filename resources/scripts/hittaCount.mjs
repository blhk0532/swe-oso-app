#!/usr/bin/env node

import { Command } from "commander";
import axios from "axios";
import { JSDOM } from "jsdom";

async function extractHittaCounts(searchQuery) {
  const encodedQuery = encodeURIComponent(searchQuery);
  const url = `https://www.hitta.se/s%C3%B6k?vad=${encodedQuery}`;

  try {
    const response = await axios.get(url, {
      headers: {
        "User-Agent":
          "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
      },
    });

    const dom = new JSDOM(response.data);
    const document = dom.window.document;

    // Find the nav with data-trackcat="search-result-tabs"
    const nav = document.querySelector(
      'nav[data-trackcat="search-result-tabs"]',
    );
    if (!nav) {
      throw new Error("Search result tabs not found");
    }

    // Helper function to extract count after a specific tab title
    const extractCountAfterTitle = (titleText) => {
      // Look for the content container that has both title and count
      const contentContainers = nav.querySelectorAll(
        "span.style_content__nx640",
      );

      for (const container of contentContainers) {
        const titleSpan = container.querySelector("span.style_tabTitle__EC5RP");
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
    throw new Error(`Failed to fetch or parse Hitta.se: ${error}`);
  }
}

const program = new Command();
program
  .name("hittaCount")
  .description("Extract count values from Hitta.se search results")
  .argument("<search-query>", "Search query to look up")
  .action(async (searchQuery) => {
    try {
      const counts = await extractHittaCounts(searchQuery);
      
      // Extract postnummer from query (assuming it's in format like "100 05")
      const postnummerMatch = searchQuery.match(/^\d{3}\s?\d{2}/);
      if (postnummerMatch) {
        const postnummer = postnummerMatch[0];
        
        try {
          // Make PUT request to update postnummer data
          const putData = {
            checkForetag: counts.hittaForetag,
            checkPersoner: counts.hittaPersoner,
            checkPlatser: counts.hittaPlatser
          };
          
          await axios.put(
            `http://localhost:5000/postnummer/${encodeURIComponent(postnummer)}`,
            putData,
            {
              headers: {
                "Content-Type": "application/json"
              }
            }
          );
          
          console.log(`Updated postnummer ${postnummer} with counts:`, putData);
        } catch (putError) {
          console.error("Failed to update postnummer data:", putError);
          // Don't fail the script, just log the error
        }
      }
      
      console.log(JSON.stringify(counts));
    } catch (error) {
      console.error("Error:", error);
      process.exit(1);
    }
  });

program.parse();
