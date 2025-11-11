#!/usr/bin/env node

import { Command } from "commander";
import axios from "axios";
import { JSDOM } from "jsdom";
import fs from "fs";
import path from "path";

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

      // Return all values as JSON
      console.log(JSON.stringify(personData, null, 2));
      
      // Save to API in batches
      try {
        console.log(`Saving ${personData.length} persons to API in batches...`);
        const batchSize = 50;
        let totalSaved = 0;
        
        for (let i = 0; i < personData.length; i += batchSize) {
          const batch = personData.slice(i, i + batchSize);
          console.log(`Saving batch ${Math.floor(i/batchSize) + 1}/${Math.ceil(personData.length/batchSize)} (${batch.length} persons)...`);
          
          try {
            const apiResponse = await axios.post('http://localhost:3000/api/persons', {
              persons: batch,
              query: searchQuery,
              source: 'hitta'
            }, {
              headers: {
                'Content-Type': 'application/json'
              }
            });
            
            if (apiResponse.data.success) {
              totalSaved += apiResponse.data.data.savedCount;
              console.log(`✅ Batch saved: ${apiResponse.data.data.savedCount} persons`);
            } else {
              console.log(`❌ Batch failed: ${apiResponse.data.error}`);
            }
          } catch (batchError) {
            console.log(`❌ Batch error: ${batchError.message}`);
          }
          
          // Small delay between batches
          await new Promise(resolve => setTimeout(resolve, 500));
        }
        
        console.log(`✅ Total saved to database: ${totalSaved}/${personData.length} persons`);
      } catch (apiError) {
        console.log(`❌ API Error: ${apiError.message}`);
        console.log('Make sure the development server is running on localhost:3000');
      }
    } catch (error) {
      console.error("Error:", error);
      process.exit(1);
    }
  });

program.parse(process.argv);
