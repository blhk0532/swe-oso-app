#!/usr/bin/env node

import { Command } from "commander";
import { chromium } from "playwright";
import fs from "fs";
import path from "path";

// Helper function to clean "Visas för medlemmar" text
function cleanForMemberText(text) {
  if (!text) return text;
  return text.replace(/Visas för medlemmar/gi, "").trim();
}

// Helper function to clean neighbor text
function cleanNeighborText(text) {
  if (!text) return text;
  return text
    .replace(/Kolla lön direkt/gi, "")
    .replace(/Adressändringsdatum/gi, "")
    .replace(/\s{2,}/g, " ")
    .trim();
}

async function extractTextAfterLabel(page, labelText) {
  try {
    const labelSelector = `span.color--gray5:has-text("${labelText}")`;
    const labelElement = await page.$(labelSelector);

    if (!labelElement) {
      return null;
    }

    const parentText = await labelElement.evaluate((el) => {
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

async function extractPersonnummer(page) {
  try {
    const labelSelector = 'span.color--gray5:has-text("Personnummer:")';
    const labelElement = await page.$(labelSelector);

    if (!labelElement) {
      return null;
    }

    const html = await labelElement.evaluate((el) => {
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
      // Remove "Visas för medlemmar" text
      cleanText = cleanForMemberText(cleanText);

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

async function extractTelefon(page) {
  try {
    const labelSelector = 'span.color--gray5:has-text("Telefon:")';
    const labelElement = await page.$(labelSelector);

    if (!labelElement) {
      return null;
    }

    const telHref = await labelElement.evaluate((el) => {
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

function mapKonValue(value) {
  if (!value) {
    return null;
  }

  const valueLower = value.toLowerCase().trim();
  const mapping = {
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

async function extractCivilstand(page) {
  try {
    // Try to extract civilstand using the same pattern as other fields
    const civilstandText = await extractTextAfterLabel(page, "Civilstand:");
    if (civilstandText) {
      const cleanedText = cleanForMemberText(civilstandText);
      // Make sure it's not a personnummer
      if (!cleanedText.match(/^\d{8}-\d{4}/)) {
        return cleanedText;
      }
    }

    // Look for civilstand in the main content area - check multiple elements
    const allTexts = await page.$$eval("div.col-12 p", (elements) =>
      elements.map((el) => el.textContent?.trim()).filter((text) => text),
    );

    // Look for civilstand patterns in all paragraph texts
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
        // Make sure it's not a personnummer
        if (!text.match(/^\d{8}-\d{4}/)) {
          const cleanedText = cleanForMemberText(text);
          // Extract just the civilstand part (e.g., "Thomas är inte gift")
          const civilstandMatch = cleanedText.match(
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

async function extractTelefonnummer(page) {
  try {
    const telefonHeader = await page.$('h3:has-text("Telefonnummer")');

    if (telefonHeader) {
      const telefonContainer = await telefonHeader.evaluateHandle((header) =>
        header.closest("div.col-12"),
      );

      if (telefonContainer) {
        // Extract phone numbers from the actual HTML structure
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

async function extractPersoner(page) {
  try {
    const personerHeader = await page.$('h3:has-text("Personer")');
    if (personerHeader) {
      const personerContainer = await personerHeader.evaluateHandle((header) =>
        header.closest("div.col-12"),
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

async function extractForetag(page) {
  try {
    const foretagHeader = await page.$('h3:has-text("Företag på adressen")');
    if (foretagHeader) {
      const foretagContainer = await foretagHeader.evaluateHandle((header) =>
        header.closest("div.col-12"),
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

async function extractGrannar(page) {
  try {
    const grannarButtons = await page.$$(
      "button.accordion-neighbours__title strong",
    );
    const grannarData = [];

    for (const button of grannarButtons) {
      const neighbourContainer = await button.evaluateHandle((btn) =>
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

              // Clean and format neighbor data
              let cleanNamn = (namn || "").trim();
              let cleanDatum = (datum || "").trim();

              // Remove unwanted phrases from name and datum
              cleanNamn = cleanNamn
                .replace(/Kolla lön direkt/gi, "")
                .replace(/Adressändringsdatum/gi, "")
                .trim();
              cleanDatum = cleanDatum
                .replace(/Adressändringsdatum/gi, "")
                .trim();

              // Extract date pattern and clean up the date field
              const dateMatch = cleanDatum.match(/(\d{4}-\d{2}-\d{2})/);
              const extractedDate = dateMatch ? dateMatch[1] : "";

              // Extract age pattern
              const ageMatch = cleanNamn.match(/(\d+)\s*år/);
              const age = ageMatch ? ageMatch[1] + " år" : "";

              // Clean name by removing age and date if present
              cleanNamn = cleanNamn
                .replace(/\d+\s*år/, "")
                .replace(/\d{4}-\d{2}-\d{2}/, "")
                .trim();

              // Format: "Name, age , date"
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

async function extractFordon(page) {
  try {
    const fordonHeader = await page.$('h3:has-text("Fordon på adressen")');
    if (fordonHeader) {
      const fordonContainer = await fordonHeader.evaluateHandle((header) =>
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

async function extractHundar(page) {
  try {
    const hundarHeader = await page.$('h3:has-text("Hundar på adressen")');
    if (hundarHeader) {
      const hundarContainer = await hundarHeader.evaluateHandle((header) =>
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

async function extractBolagsengagemang(page) {
  try {
    const bolagsHeader = await page.$('h2:has-text("Bolagsengagemang")');
    if (bolagsHeader) {
      const bolagsContainer = await bolagsHeader.evaluateHandle((header) =>
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

async function extractCoordinates(page) {
  try {
    const coordDiv = await page.$(
      "div.col-12.col-lg-5.d-lg-flex.align-items-lg-center",
    );
    if (coordDiv) {
      const coordText = await coordDiv.textContent();
      const latMatch = coordText.match(/Latitud:\s*([\d.]+)/);
      const lonMatch = coordText.match(/Longitud:\s*([\d.]+)/);
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

async function extractGoogleMaps(page) {
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

async function extractGoogleStreetview(page) {
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

async function extractRatsitDetails(personnamn, gatuadress, postort) {
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

    // Step 1: Get search results
    console.log(`Searching: ${searchUrl}`);
    await page.goto(searchUrl, { waitUntil: "networkidle", timeout: 30000 });
    await page.waitForTimeout(2000);

    // Find first search result link
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
    console.log(`Navigating to: ${ratsitLink}`);

    // Step 2: Scrape person details
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

    // Convert telefon to array format if present
    if (result.ratsit_data_telefon) {
      result.ratsit_data_telefon = [result.ratsit_data_telefon];
    } else {
      result.ratsit_data_telefon = [];
    }

    // Apply comprehensive cleaning to all data
    const cleanedResult = {};
    for (const [key, value] of Object.entries(result)) {
      if (
        value !== null &&
        value !== "" &&
        !(Array.isArray(value) && value.length === 0)
      ) {
        // Apply cleaning to all string values and arrays
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

function saveToCSV(data, filename) {
  const csvLines = [
    Object.keys(data).join(","),
    Object.values(data)
      .map((value) => {
        if (Array.isArray(value)) {
          return `"${JSON.stringify(value).replace(/"/g, '""')}"`;
        }
        return `"${String(value).replace(/"/g, '""')}"`;
      })
      .join(","),
  ];

  const csvContent = csvLines.join("\n");

  // Ensure data directory exists
  const dataDir = path.join(process.cwd(), "data");
  if (!fs.existsSync(dataDir)) {
    fs.mkdirSync(dataDir, { recursive: true });
  }

  const filePath = path.join(dataDir, filename);
  fs.writeFileSync(filePath, csvContent, "utf8");
  console.log(`Data saved to ${filePath}`);
}

// CLI setup
const program = new Command();
program
  .description("Scrape Ratsit.se for person details")
  .argument("<personnamn>", "Person name")
  .argument("<gatuadress>", "Street address")
  .argument("<postort>", "City")
  .option("-o, --output <filename>", "Output CSV filename")
  .action(async (personnamn, gatuadress, postort, options) => {
    try {
      console.log(`Searching for: ${personnamn}, ${gatuadress}, ${postort}`);

      const result = await extractRatsitDetails(
        personnamn,
        gatuadress,
        postort,
      );

      if (result && Object.keys(result).length > 0) {
        console.log("Scraping successful!");
        console.log("Result:", result);

        // Generate filename if not provided
        const filename =
          options.output ||
          `ratsit_search_persons_details_${personnamn.replace(/\s+/g, "_")}_${gatuadress.replace(/\s+/g, "_")}_${postort.replace(/\s+/g, "_")}.csv`;

        saveToCSV(result, filename);
      } else {
        console.log("No data found");
      }
    } catch (error) {
      console.error("Error:", error.message);
      process.exit(1);
    }
  });

program.parse();
