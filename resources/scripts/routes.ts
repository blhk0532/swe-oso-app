import express from "express";
import { db } from "./db";
import { scrapedData } from "./schemas";
import axios from "axios";
import { JSDOM } from "jsdom";
import { chromium } from "playwright";

export const router = express.Router();

// Save scraped content
router.post("/scrape", async (req, res) => {
  const { source, content, url } = req.body;
  try {
    await db.insert(scrapedData).values({ source, content, url });
    res.status(201).json({ message: "Saved successfully" });
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Database error" });
  }
});

// Fetch scraped data
router.get("/scrape", async (_, res) => {
  try {
    const data = await db.select().from(scrapedData);
    res.json(data);
  } catch (err) {
    console.error(err);
    res.status(500).json({ error: "Database error" });
  }
});

async function extractHittaCounts(searchQuery: string) {
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

      const extractCountAfterTitle = (titleText: string) => {
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
        console.log(
          `Attempt ${attempt} failed, retrying in ${attempt * 1000}ms...`,
        );
        await new Promise((resolve) => setTimeout(resolve, attempt * 1000));
        continue;
      }
      throw new Error(
        `Failed to fetch or parse Hitta.se after 3 attempts: ${error}`,
      );
    }
  }

  throw new Error(
    `Failed to fetch or parse Hitta.se after 3 attempts: ${lastError}`,
  );
}

// Hitta count endpoint
router.put("/hitta-count", async (req, res) => {
  const { query } = req.body;

  if (!query || typeof query !== "string") {
    return res.status(400).json({ error: "Query parameter is required in request body" });
  }

  try {
    const counts = await extractHittaCounts(query);
    
    // Extract postnummer from query (assuming it's in format like "112 52")
    const postnummerMatch = query.match(/^\d{3}\s?\d{2}/);
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
          `http://localhost:${process.env.PORT || 6969}/postnummer/${encodeURIComponent(postnummer)}`,
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
        // Don't fail the request, just log the error
      }
    }
    
    res.json(counts);
  } catch (error) {
    console.error("Hitta count error:", error);
    res.status(500).json({ error: "Failed to fetch hitta counts" });
  }
});

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

async function extractPersonDataFromPage(searchQuery: string, page: number) {
  const encodedQuery = encodeURIComponent(searchQuery);
  const url = `https://www.hitta.se/s%C3%B6k?vad=${encodedQuery}&typ=prv&sida=${page}`;

  let lastError;
  for (let attempt = 1; attempt <= 3; attempt++) {
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

      const dom = new JSDOM(response.data);
      const document = dom.window.document;

      let hittaPersoner;
      if (page === 1) {
        const contentContainers = document.querySelectorAll(
          "span.style_content__nx640",
        );
        for (const container of contentContainers) {
          const titleSpan = container.querySelector(
            "span.style_tabTitle__EC5RP",
          );
          if (titleSpan?.textContent === "Personer") {
            const countSpan = container.querySelector(
              "span.style_tabNumbers__VbAE7",
            );
            const countText = countSpan?.textContent?.trim();
            hittaPersoner = countText ? parseInt(countText.replace(/,/g, ''), 10) : undefined;
            break;
          }
        }
      }

      const personItems = document.querySelectorAll(
        'li[itemprop="itemListElement"][data-test="person-item"]',
      );
      const results = [];

      for (const item of personItems) {
        try {
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

          const infoParagraph = item.querySelector(
            "p.text-body-long-sm-regular",
          );
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

          const phoneButton = item.querySelector(
            'button[data-test="phone-link"]',
          );
          let telefon = "";
          if (phoneButton) {
            const phoneText = phoneButton.textContent?.trim() || "";
            telefon = phoneText.replace("Visa", "").trim();
          }

          const mapButton = item.querySelector(
            'a[data-test="show-on-map-button"]',
          );
          let karta = "";
          if (mapButton) {
            const href = mapButton.getAttribute("href") || "";
            karta = `hitta.se${href}`;
          }

          const linkElement = item.querySelector(
            'a[data-test="search-list-link"]',
          );
          let link = "";
          let visa = "";

          if (linkElement) {
            const href = linkElement.getAttribute("href") || "";
            link = `https://www.hitta.se${href}`;
          }

          results.push({
            personnamn,
            alder,
            kon,
            gatuadress,
            postnummer,
            postort,
            telefon,
            karta,
            link,
            visa,
          });
        } catch (error) {
          console.error("Error processing person item:", error);
        }
      }

      const nextButton = document.querySelector(
        'a[data-ga4-action="next_page"]',
      );
      const hasNextPage =
        !!nextButton && !nextButton.classList.contains("disabled");
      const hasResults = personItems.length > 0;
      const isLastPageByCount = personItems.length < 25;
      const shouldContinue = hasResults && hasNextPage && !isLastPageByCount;

      return { persons: results, hasNextPage: shouldContinue, hittaPersoner };
    } catch (error) {
      lastError = error;
      if (attempt < 3) {
        console.log(
          `Page ${page} attempt ${attempt} failed, retrying in ${attempt * 1000}ms...`,
        );
        await new Promise((resolve) => setTimeout(resolve, attempt * 1000));
        continue;
      }
      throw new Error(
        `Failed to fetch or parse Hitta.se page ${page} after 3 attempts: ${error}`,
      );
    }
  }

  throw new Error(
    `Failed to fetch or parse Hitta.se page ${page} after 3 attempts: ${lastError}`,
  );
}

async function extractAllPersonData(searchQuery: string, maxPages?: number) {
  let allPersons: any[] = [];
  let currentPage = 1;
  let hasNextPage = true;
  let hittaPersoner = 0;

  try {
    const firstPageResult = await extractPersonDataFromPage(searchQuery, 1);

    if (firstPageResult && firstPageResult.hittaPersoner !== undefined) {
      hittaPersoner = firstPageResult.hittaPersoner;
      allPersons = allPersons.concat(firstPageResult.persons);
      hasNextPage = firstPageResult.hasNextPage;
      currentPage = 2;
    }
  } catch (error) {
    console.error(`Error getting initial page:`, error);
    return { persons: [], hittaPersoner: 0 };
  }

  while (hasNextPage && (!maxPages || currentPage <= maxPages)) {
    try {
      const { persons, hasNextPage: hasMore } = await extractPersonDataFromPage(
        searchQuery,
        currentPage,
      );

      allPersons = allPersons.concat(persons);
      hasNextPage = hasMore;
      currentPage++;

      await new Promise((resolve) => setTimeout(resolve, getRandomDelay()));
    } catch (error) {
      console.error(`Error on page ${currentPage}:`, error);
      break;
    }
  }

  return { persons: allPersons, hittaPersoner };
}

// Hitta search persons endpoint
router.get("/hitta-search-persons", async (req, res) => {
  const { query, maxPages } = req.query;

  if (!query || typeof query !== "string") {
    return res.status(400).json({ error: "Query parameter is required" });
  }

  const maxPagesNum = maxPages ? parseInt(maxPages as string, 10) : undefined;

  try {
    const result = await extractAllPersonData(query, maxPagesNum);
    res.json({
      query,
      totalFound: result.hittaPersoner,
      actualResults: result.persons.length,
      persons: result.persons,
    });
  } catch (error) {
    console.error("Hitta search persons error:", error);
    res.status(500).json({ error: "Failed to fetch person search results" });
  }
});

// Ratsit helper functions
function cleanForMemberText(text: string | null): string | null {
  if (!text) return text;
  return text.replace(/Visas för medlemmar/gi, "").trim();
}

function cleanNeighborText(text: string | null): string | null {
  if (!text) return text;
  return text
    .replace(/Kolla lön direkt/gi, "")
    .replace(/Adressändringsdatum/gi, "")
    .replace(/\s{2,}/g, " ")
    .trim();
}

async function extractTextAfterLabel(
  page: any,
  labelText: string,
): Promise<string | null> {
  try {
    const labelSelector = `span.color--gray5:has-text("${labelText}")`;
    const labelElement = await page.$(labelSelector);

    if (!labelElement) {
      return null;
    }

    const parentText = await labelElement.evaluate((el: any) => {
      const p = el.closest("p");
      return p ? p.innerText : null;
    });

    if (!parentText) {
      return null;
    }

    let text = parentText.replace(labelText, "").trim();
    text = text.replace(/\s*Visas för medlemmar.*/gi, "");

    return text || null;
  } catch (e) {
    console.log(`Error extracting ${labelText}:`, e);
    return null;
  }
}

async function extractPersonnummer(page: any): Promise<string | null> {
  try {
    const labelSelector = 'span.color--gray5:has-text("Personnummer:")';
    const labelElement = await page.$(labelSelector);

    if (!labelElement) {
      return null;
    }

    const html = await labelElement.evaluate((el: any) => {
      const p = el.closest("p");
      return p ? p.innerHTML : null;
    });

    if (!html) {
      return null;
    }

    const match = html.match(
      /Personnummer:\s*([0-9-]+)\s*.*?<strong>XXXX<\/strong>/i,
    );
    if (match) {
      return match[1].trim() + "XXXX";
    }

    const text = await extractTextAfterLabel(page, "Personnummer:");
    if (text) {
      let cleanText = text.replace(/<[^>]+>/g, "").trim();
      const cleaned = cleanForMemberText(cleanText);
      if (cleaned) {
        cleanText = cleaned;
      }

      if (html.toUpperCase().includes("XXXX")) {
        if (!cleanText.endsWith("XXXX") && !cleanText.endsWith("xxxx")) {
          cleanText = cleanText.replace(/-$/, "") + "XXXX";
        }
      }
      return cleanText;
    }

    return null;
  } catch (e) {
    console.log("Error extracting personnummer:", e);
    return null;
  }
}

async function extractTelefon(page: any): Promise<string | null> {
  try {
    const labelSelector = 'span.color--gray5:has-text("Telefon:")';
    const labelElement = await page.$(labelSelector);

    if (!labelElement) {
      return null;
    }

    const telHref = await labelElement.evaluate((el: any) => {
      const p = el.closest("p");
      if (!p) return null;
      const telLink = p.querySelector('a[href^="tel:"]');
      return telLink ? telLink.getAttribute("href") : null;
    });

    if (telHref && telHref.startsWith("tel:")) {
      return telHref.replace("tel:", "");
    }

    return null;
  } catch (e) {
    console.log("Error extracting telefon:", e);
    return null;
  }
}

function mapKonValue(value: string | null): string | null {
  if (!value) {
    return null;
  }

  const valueLower = value.toLowerCase().trim();
  const mapping: { [key: string]: string } = {
    man: "Man",
    kvinna: "Kvinna",
    kvinno: "Kvinna",
    m: "Man",
    f: "Kvinna",
    o: "Annat",
    other: "Annat",
    annat: "Annat",
  };

  return mapping[valueLower] || value;
}

async function extractCivilstand(page: any): Promise<string | null> {
  try {
    const civilstandText = await extractTextAfterLabel(page, "Civilstand:");
    if (civilstandText) {
      const cleanedText = cleanForMemberText(civilstandText);
      // Make sure it's not a personnummer
      if (cleanedText && !cleanedText.match(/^\d{8}-\d{4}/)) {
        return cleanedText;
      }
    }

    const allTexts = await page.$$eval("div.col-12 p", (elements: any[]) =>
      elements.map((el) => el.textContent?.trim()).filter((text) => text),
    );

    for (const text of allTexts) {
      if (
        text &&
        (text.includes("är gift") ||
          text.includes("är inte gift") ||
          text.includes("är sambo") ||
          text.includes("är skild") ||
          text.includes("är änka") ||
          text.includes("är änkling") ||
          text.includes("Gift") ||
          text.includes("Ogift") ||
          text.includes("Sambo") ||
          text.includes("Skild") ||
          text.includes("Änka") ||
          text.includes("Änkling"))
      ) {
        if (!text.match(/^\d{8}-\d{4}/)) {
          const cleanedText = cleanForMemberText(text);
          // Extract just the civilstand part (e.g., "Thomas är inte gift")
          const civilstandMatch = cleanedText?.match(
            /(\w+)\s+(är\s+(gift|inte\s+gift|sambo|skild|änka|änkling))/,
          );
          if (civilstandMatch) {
            return `${civilstandMatch[1]} ${civilstandMatch[2]}`;
          }
          return cleanedText;
        }
      }
    }

    return null;
  } catch (e) {
    console.log("Error extracting civilstand:", e);
    return null;
  }
}

async function extractTelefonnummer(page: any): Promise<string[] | null> {
  try {
    const telefonHeader = await page.$('h3:has-text("Telefonnummer")');

    if (telefonHeader) {
      const telefonContainer = await telefonHeader.evaluateHandle(
        (header: any) => header.closest("div.col-12"),
      );

      if (telefonContainer) {
        const telefonLinks = await telefonContainer.$$(
          "div.mt-1 a[href^='tel:']",
        );

        const telefonNumbers = [];
        for (const link of telefonLinks) {
          const text = await link.textContent();
          if (text && text.trim()) {
            telefonNumbers.push(text.trim());
          }
        }
        return telefonNumbers.length > 0 ? telefonNumbers : null;
      }
    }
    return null;
  } catch (e) {
    console.log("Error extracting telefonnummer:", e);
    return null;
  }
}

async function extractPersoner(page: any): Promise<string[] | null> {
  try {
    const personerHeader = await page.$('h3:has-text("Personer")');
    if (personerHeader) {
      const personerContainer = await personerHeader.evaluateHandle(
        (header: any) => header.closest("div.col-12"),
      );
      if (personerContainer) {
        const personerLinks = await personerContainer.$$(
          "div.col-12.col-lg-8 a strong",
        );
        const personer = [];
        for (const link of personerLinks) {
          const text = await link.textContent();
          if (text && text.trim()) {
            personer.push(text.trim());
          }
        }
        return personer.length > 0 ? personer : null;
      }
    }
    return null;
  } catch (e) {
    console.log("Error extracting personer:", e);
    return null;
  }
}

async function extractForetag(page: any): Promise<string[] | null> {
  try {
    const foretagHeader = await page.$('h3:has-text("Företag på adressen")');
    if (foretagHeader) {
      const foretagContainer = await foretagHeader.evaluateHandle(
        (header: any) => header.closest("div.col-12"),
      );
      if (foretagContainer) {
        const foretagData = [];
        const rows = await foretagContainer.$$("tbody tr");
        for (const row of rows) {
          const cells = await row.$$("td");
          if (cells.length >= 3) {
            const foretagsnamn = await cells[0].textContent();
            const status = await cells[1].textContent();
            const ansvarig = await cells[2].textContent();
            foretagData.push(
              `${foretagsnamn.trim()}, ${status.trim()}, ${ansvarig.trim()}`,
            );
          }
        }
        return foretagData.length > 0 ? foretagData : null;
      }
    }
    return null;
  } catch (e) {
    console.log("Error extracting foretag:", e);
    return null;
  }
}

async function extractGrannar(page: any): Promise<string[] | null> {
  try {
    const grannarButtons = await page.$$(
      "button.accordion-neighbours__title strong",
    );
    const grannarData = [];

    for (const button of grannarButtons) {
      const neighbourContainer = await button.evaluateHandle((btn: any) =>
        btn.closest("div"),
      );
      if (neighbourContainer) {
        const table = await neighbourContainer.$("table");
        if (table) {
          const rows = await table.$$("tbody tr");
          for (const row of rows) {
            const cells = await row.$$("td");
            if (cells.length >= 2) {
              const namn = await cells[0].textContent();
              const datum = await cells[1].textContent();

              let cleanNamn = (namn || "").trim();
              let cleanDatum = (datum || "").trim();

              cleanNamn = cleanNamn
                .replace(/Kolla lön direkt/gi, "")
                .replace(/Adressändringsdatum/gi, "")
                .trim();
              cleanDatum = cleanDatum
                .replace(/Adressändringsdatum/gi, "")
                .trim();

              const dateMatch = cleanDatum.match(/(\d{4}-\d{2}-\d{2})/);
              const extractedDate = dateMatch ? dateMatch[1] : "";

              const ageMatch = cleanNamn.match(/(\d+)\s*år/);
              const age = ageMatch ? ageMatch[1] + " år" : "";

              cleanNamn = cleanNamn
                .replace(/\d+\s*år/, "")
                .replace(/\d{4}-\d{2}-\d{2}/, "")
                .trim();

              const formattedEntry = `${cleanNamn}, ${age} , ${extractedDate}`
                .replace(/\s{2,}/g, " ")
                .replace(/,\s*,/, ",");
              grannarData.push(formattedEntry);
            }
          }
        }
      }
    }

    return grannarData.length > 0 ? grannarData : null;
  } catch (e) {
    console.log("Error extracting grannar:", e);
    return null;
  }
}

async function extractFordon(page: any): Promise<string[] | null> {
  try {
    const fordonHeader = await page.$('h3:has-text("Fordon på adressen")');
    if (fordonHeader) {
      const fordonContainer = await fordonHeader.evaluateHandle((header: any) =>
        header.closest("div.col-12"),
      );
      if (fordonContainer) {
        const fordonData = [];
        const rows = await fordonContainer.$$("tbody tr");
        for (const row of rows) {
          const cells = await row.$$("td");
          if (cells.length >= 5) {
            const marke = await cells[0].textContent();
            const modell = await cells[1].textContent();
            const modellar = await cells[2].textContent();
            const farg = await cells[3].textContent();
            const agare = await cells[4].textContent();
            fordonData.push(
              `${marke.trim()},${modell.trim()}, ${modellar.trim()}, ${farg.trim()}, ${agare.trim()}`,
            );
          }
        }
        return fordonData.length > 0 ? fordonData : null;
      }
    }
    return null;
  } catch (e) {
    console.log("Error extracting fordon:", e);
    return null;
  }
}

async function extractHundar(page: any): Promise<string[] | null> {
  try {
    const hundarHeader = await page.$('h3:has-text("Hundar på adressen")');
    if (hundarHeader) {
      const hundarContainer = await hundarHeader.evaluateHandle((header: any) =>
        header.closest("div.col-12"),
      );
      if (hundarContainer) {
        const hundarData = [];
        const rows = await hundarContainer.$$("tbody tr");
        for (const row of rows) {
          const cells = await row.$$("td");
          if (cells.length >= 5) {
            const ras = await cells[0].textContent();
            const fodelsedatum = await cells[3].textContent();
            const agare = await cells[4].textContent();
            hundarData.push(
              `${ras.trim()}, ${fodelsedatum.trim()}, ${agare.trim()}`,
            );
          }
        }
        return hundarData.length > 0 ? hundarData : null;
      }
    }
    return null;
  } catch (e) {
    console.log("Error extracting hundar:", e);
    return null;
  }
}

async function extractBolagsengagemang(page: any): Promise<string[] | null> {
  try {
    const bolagsHeader = await page.$('h2:has-text("Bolagsengagemang")');
    if (bolagsHeader) {
      const bolagsContainer = await bolagsHeader.evaluateHandle((header: any) =>
        header.closest("div.col-12"),
      );
      if (bolagsContainer) {
        const bolagsData = [];
        const rows = await bolagsContainer.$$("tbody tr");
        for (const row of rows) {
          const cells = await row.$$("td");
          if (cells.length >= 8) {
            const foretag = await cells[0].textContent();
            const typ = await cells[1].textContent();
            const status = await cells[2].textContent();
            const befattnning = await cells[3].textContent();
            const kontrollerar = await cells[4].textContent();
            const bokslut = await cells[5].textContent();
            const omsattning = await cells[6].textContent();
            const vinst = await cells[7].textContent();
            bolagsData.push(
              `${foretag.trim()}, ${typ.trim()}, ${status.trim()}, ${befattnning.trim()}, ${kontrollerar.trim()}, ${bokslut.trim()}, ${omsattning.trim()}, ${vinst.trim()}`,
            );
          }
        }
        return bolagsData.length > 0 ? bolagsData : null;
      }
    }
    return null;
  } catch (e) {
    console.log("Error extracting bolagsengagemang:", e);
    return null;
  }
}

async function extractCoordinates(
  page: any,
): Promise<{ latitude: string | null; longitude: string | null }> {
  try {
    const coordDiv = await page.$(
      "div.col-12.col-lg-5.d-lg-flex.align-items-lg-center",
    );
    if (coordDiv) {
      const coordText = await coordDiv.textContent();
      const latMatch = coordText?.match(/Latitud:\s*([\d.]+)/);
      const lonMatch = coordText?.match(/Longitud:\s*([\d.]+)/);
      return {
        latitude: latMatch ? latMatch[1] : null,
        longitude: lonMatch ? lonMatch[1] : null,
      };
    }
    return { latitude: null, longitude: null };
  } catch (e) {
    console.log("Error extracting coordinates:", e);
    return { latitude: null, longitude: null };
  }
}

async function extractGoogleMaps(page: any): Promise<string | null> {
  try {
    const mapsLink = await page.$('a[href*="maps.google.com"]');
    if (mapsLink) {
      return await mapsLink.getAttribute("href");
    }
    return null;
  } catch (e) {
    console.log("Error extracting google maps:", e);
    return null;
  }
}

async function extractGoogleStreetview(page: any): Promise<string | null> {
  try {
    const streetviewLink = await page.$('a[href*="google.com/maps/@"]');
    if (streetviewLink) {
      return await streetviewLink.getAttribute("href");
    }
    return null;
  } catch (e) {
    console.log("Error extracting google streetview:", e);
    return null;
  }
}

async function extractRatsitDetails(
  personnamn: string,
  gatuadress: string,
  postort: string,
) {
  const encodedName = encodeURIComponent(personnamn);
  const encodedAddress = encodeURIComponent(gatuadress);
  const encodedCity = encodeURIComponent(postort);
  const searchUrl = `https://www.ratsit.se/sok/person?vem=${encodedName},%20${encodedAddress},%20${encodedCity}`;

  let browser = null;

  try {
    browser = await chromium.launch({
      channel: "chrome",
      headless: true,
    });

    const context = await browser.newContext({
      userAgent:
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36",
    });
    const page = await context.newPage();

    console.log(`Searching: ${searchUrl}`);
    await page.goto(searchUrl, { waitUntil: "networkidle", timeout: 30000 });
    await page.waitForTimeout(2000);

    const resultList = await page.$("ul.search-result-list");
    if (!resultList) {
      throw new Error("No search results found");
    }

    const firstLink = await resultList.$(
      'li a[href^="https://www.ratsit.se/"]',
    );
    if (!firstLink) {
      throw new Error("No link found in search results");
    }

    const ratsitLink = await firstLink.getAttribute("href");
    if (!ratsitLink) {
      throw new Error("No href found on search result link");
    }
    console.log(`Navigating to: ${ratsitLink}`);

    await page.goto(ratsitLink, { waitUntil: "networkidle", timeout: 30000 });
    await page.waitForTimeout(2000);

    const coordinates = await extractCoordinates(page);

    const result = {
      ratsit_link: ratsitLink,
      ratsit_data_personnummer: await extractPersonnummer(page),
      ratsit_data_alder: await extractTextAfterLabel(page, "Ålder:"),
      ratsit_data_fodelsedag: await extractTextAfterLabel(page, "Födelsedag:"),
      ratsit_data_kon: mapKonValue(
        await extractTextAfterLabel(page, "Juridiskt kön:"),
      ),
      ratsit_data_telefon: await extractTelefon(page),
      ratsit_data_personnamn: await extractTextAfterLabel(page, "Personnamn:"),
      ratsit_data_fornamn: await extractTextAfterLabel(page, "Förnamn:"),
      ratsit_data_efternamn: await extractTextAfterLabel(page, "Efternamn:"),
      ratsit_data_gatuadress: await extractTextAfterLabel(page, "Gatuadress:"),
      ratsit_data_postnummer: await extractTextAfterLabel(page, "Postnummer:"),
      ratsit_data_postort: await extractTextAfterLabel(page, "Postort:"),
      ratsit_data_civilstand: await extractCivilstand(page),
      ratsit_data_forsamling: await extractTextAfterLabel(page, "Församling:"),
      ratsit_data_kommun: await extractTextAfterLabel(page, "Kommun:"),
      ratsit_data_lan: await extractTextAfterLabel(page, "Län:"),
      ratsit_data_adressandring: await extractTextAfterLabel(
        page,
        "Adressändring:",
      ),
      ratsit_data_telefonnummer: await extractTelefonnummer(page),
      ratsit_data_stjarntecken: await extractTextAfterLabel(
        page,
        "Stjärntecken:",
      ),
      ratsit_data_agandeform: await extractTextAfterLabel(page, "Ägandeform:"),
      ratsit_data_bostadstyp: await extractTextAfterLabel(page, "Bostadstyp:"),
      ratsit_data_boarea: await extractTextAfterLabel(page, "Boarea:"),
      ratsit_data_byggar: await extractTextAfterLabel(page, "Byggår:"),
      ratsit_data_personer: await extractPersoner(page),
      ratsit_data_foretag: await extractForetag(page),
      ratsit_data_grannar: await extractGrannar(page),
      ratsit_data_fordon: await extractFordon(page),
      ratsit_data_hundar: await extractHundar(page),
      ratsit_data_bolagsengagemang: await extractBolagsengagemang(page),
      ratsit_data_latitude: coordinates.latitude,
      ratsit_data_longitude: coordinates.longitude,
      ratsit_data_google_maps: await extractGoogleMaps(page),
      ratsit_data_google_streetview: await extractGoogleStreetview(page),
    };

    if (result.ratsit_data_telefon) {
      (result as any).ratsit_data_telefon = [result.ratsit_data_telefon];
    } else {
      (result as any).ratsit_data_telefon = [];
    }

    const cleanedResult: any = {};
    for (const [key, value] of Object.entries(result)) {
      if (
        value !== null &&
        value !== "" &&
        !(Array.isArray(value) && value.length === 0)
      ) {
        if (typeof value === "string") {
          cleanedResult[key] = cleanForMemberText(value);
        } else if (Array.isArray(value)) {
          cleanedResult[key] = value.map((item) =>
            typeof item === "string" ? cleanForMemberText(item) : item,
          );
        } else {
          cleanedResult[key] = value;
        }
      }
    }

    await browser.close();
    return cleanedResult;
  } catch (error) {
    if (browser) {
      await browser.close();
    }
    throw new Error(`Failed to fetch or parse Ratsit.se: ${error}`);
  }
}

// Ratsit search person details endpoint
router.get("/ratsit-search-person-details", async (req, res) => {
  const { personnamn, gatuadress, postort } = req.query;

  if (
    !personnamn ||
    typeof personnamn !== "string" ||
    !gatuadress ||
    typeof gatuadress !== "string" ||
    !postort ||
    typeof postort !== "string"
  ) {
    return res.status(400).json({
      error: "All parameters are required: personnamn, gatuadress, postort",
    });
  }

  try {
    const result = await extractRatsitDetails(personnamn, gatuadress, postort);
    res.json({
      search: {
        personnamn,
        gatuadress,
        postort,
      },
      data: result,
    });
  } catch (error) {
    console.error("Ratsit search person details error:", error);
    res
      .status(500)
      .json({ error: "Failed to fetch person details from Ratsit" });
  }
});
