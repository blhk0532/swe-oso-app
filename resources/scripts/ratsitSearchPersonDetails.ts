#!/usr/bin/env node

import { Command } from "commander";
import axios from "axios";
import { JSDOM } from "jsdom";
import * as fs from "fs";
import * as path from "path";

interface RatsitData {
  ratsit_link: string;
  personnummer: string;
  alder: string;
  fodelsedag: string;
  kon: string;
  telefon: string;
  personnamn: string;
  fornamn: string;
  efternamn: string;
  gatuadress: string;
  postnummer: string;
  postort: string;
  civilstand: string;
  forsamling: string;
  kommun: string;
  lan: string;
  adressandring: string;
  telefonnummer: string[];
  stjarntecken: string;
  agandeform: string;
  bostadstyp: string;
  boarea: string;
  byggar: string;
  personer: string[];
  foretag: string[];
  grannar: string[];
  fordon: string[];
  hundar: string[];
  bolagsengagemang: string[];
  longitude: string;
  latitude: string;
  google_maps: string;
  google_steetview: string;
}

async function extractRatsitDetails(
  personnamn: string,
  gatuadress: string,
  postort: string,
): Promise<RatsitData> {
  const encodedName = encodeURIComponent(personnamn);
  const encodedAddress = encodeURIComponent(gatuadress);
  const encodedCity = encodeURIComponent(postort);
  const url = `https://www.ratsit.se/sok/person?vem=${encodedName},%20${encodedAddress},%20${encodedCity}`;

  try {
    const response = await axios.get(url, {
      headers: {
        "User-Agent":
          "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
      },
    });

    const dom = new JSDOM(response.data);
    const document = dom.window.document;

    // Find the first search result link
    const searchResultList = document.querySelector("ul.search-result-list");
    if (!searchResultList) {
      throw new Error("No search results found");
    }

    const firstLink = searchResultList.querySelector("a[href]");
    if (!firstLink) {
      throw new Error("No link found in search results");
    }

    const ratsitLink = firstLink.getAttribute("href") || "";
    console.log(`Navigating to: ${ratsitLink}`);

    // Navigate to the details page
    const detailsResponse = await axios.get(ratsitLink, {
      headers: {
        "User-Agent":
          "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36",
      },
    });

    const detailsDom = new JSDOM(detailsResponse.data);
    const detailsDocument = detailsDom.window.document;

    const result: RatsitData = {
      ratsit_link: ratsitLink,
      personnummer: "",
      alder: "",
      fodelsedag: "",
      kon: "",
      telefon: "",
      personnamn: "",
      fornamn: "",
      efternamn: "",
      gatuadress: "",
      postnummer: "",
      postort: "",
      civilstand: "",
      forsamling: "",
      kommun: "",
      lan: "",
      adressandring: "",
      telefonnummer: [],
      stjarntecken: "",
      agandeform: "",
      bostadstyp: "",
      boarea: "",
      byggar: "",
      personer: [],
      foretag: [],
      grannar: [],
      fordon: [],
      hundar: [],
      bolagsengagemang: [],
      longitude: "",
      latitude: "",
      google_maps: "",
      google_steetview: "",
    };

    // Helper function to extract text after a label
    const extractAfterLabel = (labelText: string): string => {
      const label = Array.from(
        detailsDocument.querySelectorAll("span.color--gray5"),
      ).find((span) => span.textContent?.includes(labelText));
      if (label?.nextSibling) {
        return label.nextSibling.textContent?.trim() || "";
      }
      return "";
    };

    // Extract basic information
    result.personnummer = extractAfterLabel("Personnummer:");
    result.alder = extractAfterLabel("Ålder:");
    result.fodelsedag = extractAfterLabel("Födelsedag:");
    result.kon = extractAfterLabel("Juridiskt kön:");
    result.telefon = extractAfterLabel("Telefon:");
    result.personnamn = extractAfterLabel("Personnamn:");
    result.fornamn = extractAfterLabel("Förnamn:");
    result.efternamn = extractAfterLabel("Efternamn:");
    result.gatuadress = extractAfterLabel("Gatuadress:");
    result.postnummer = extractAfterLabel("Postnummer:");
    result.postort = extractAfterLabel("Postort:");
    result.forsamling = extractAfterLabel("Församling:");
    result.kommun = extractAfterLabel("Kommun:");
    result.lan = extractAfterLabel("Län:");
    result.adressandring = extractAfterLabel("Adressändring:");
    result.stjarntecken = extractAfterLabel("Stjärntecken:");
    result.agandeform = extractAfterLabel("Ägandeform:");
    result.bostadstyp = extractAfterLabel("Bostadstyp:");
    result.boarea = extractAfterLabel("Boarea:");
    result.byggar = extractAfterLabel("Byggår:");

    // Extract civilstand
    const civilstandElement = detailsDocument.querySelector(
      "div.col-12 h2.site-p.color--gray5.mt-0 + span",
    );
    if (civilstandElement) {
      result.civilstand = civilstandElement.textContent?.trim() || "";
    }

    // Extract phone numbers
    const telefonnummerHeaders = Array.from(
      detailsDocument.querySelectorAll("h3"),
    ).filter((h3) => h3.textContent?.includes("Telefonnummer"));

    if (telefonnummerHeaders.length > 0) {
      const telefonnummerHeader = telefonnummerHeaders[0];
      const phoneContainer = telefonnummerHeader.nextElementSibling;
      if (phoneContainer) {
        const phoneSpans = phoneContainer.querySelectorAll(
          "span.col-4.col-md-2.text-nowrap",
        );
        if (phoneSpans.length > 0) {
          result.telefonnummer = Array.from(phoneSpans)
            .map((span) => span.textContent?.trim())
            .filter((text) => text && text.length > 0);
        }
      }
    }

    // Extract persons at address
    const personerSection = detailsDocument.querySelector("h3");
    if (personerSection?.textContent?.includes("Personer")) {
      const personLinks =
        personerSection.parentElement?.querySelectorAll("a strong");
      if (personLinks) {
        result.personer = Array.from(personLinks).map(
          (link) => link.textContent?.trim() || "",
        );
      }
    }

    // Extract companies at address
    const foretagSection = detailsDocument.querySelector("h2");
    if (foretagSection?.textContent?.includes("Bolagsengagemang")) {
      const tableRows =
        foretagSection.parentElement?.querySelectorAll("table tbody tr");
      if (tableRows) {
        result.foretag = Array.from(tableRows)
          .map((row) => {
            const cells = row.querySelectorAll("td");
            if (cells.length >= 3) {
              const foretagNamn =
                cells[0].querySelector("strong")?.textContent?.trim() || "";
              const status = cells[1].textContent?.trim() || "";
              const ansvarig = cells[2].textContent?.trim() || "";
              return `${foretagNamn}, ${status}, ${ansvarig}`;
            }
            return "";
          })
          .filter((item) => item);
      }
    }

    // Extract neighbors
    const grannarButtons = detailsDocument.querySelectorAll(
      "button.accordion-neighbours__title",
    );
    if (grannarButtons.length > 0) {
      result.grannar = Array.from(grannarButtons)
        .map((button) => {
          const buttonText = button.textContent?.trim() || "";
          const table = button.nextElementSibling?.querySelector("table tbody");
          if (table) {
            const rows = table.querySelectorAll("tr");
            if (rows.length > 0) {
              const firstRow = rows[0];
              const cells = firstRow.querySelectorAll("td");
              if (cells.length >= 2) {
                const neighbor =
                  cells[0].querySelector("strong")?.textContent?.trim() || "";
                const date = cells[1].textContent?.trim() || "";
                return `${neighbor}, ${date}`;
              }
            }
          }
          return buttonText;
        })
        .filter((item) => item);
    }

    // Extract vehicles
    const fordonSection = detailsDocument.querySelector(
      'h3[data-v-6ea11d52=""]',
    );
    if (fordonSection?.textContent?.includes("Fordon")) {
      const tableRows =
        fordonSection.parentElement?.querySelectorAll("table tbody tr");
      if (tableRows) {
        result.fordon = Array.from(tableRows)
          .map((row) => {
            const cells = row.querySelectorAll("td");
            if (cells.length >= 5) {
              const marke =
                cells[0].querySelector("strong")?.textContent?.trim() || "";
              const modell = cells[1].textContent?.trim() || "";
              const modellar = cells[2].textContent?.trim() || "";
              const farg = cells[3].textContent?.trim() || "";
              const agare =
                cells[4].querySelector("strong")?.textContent?.trim() || "";
              return `${marke},${modell},${modellar},${farg},${agare}`;
            }
            return "";
          })
          .filter((item) => item);
      }
    }

    // Extract dogs
    const hundarSection = detailsDocument.querySelector("h3");
    if (hundarSection?.textContent?.includes("Hundar")) {
      const tableRows =
        hundarSection.parentElement?.querySelectorAll("table tbody tr");
      if (tableRows) {
        result.hundar = Array.from(tableRows)
          .map((row) => {
            const cells = row.querySelectorAll("td");
            if (cells.length >= 5) {
              const ras =
                cells[0].querySelector("strong")?.textContent?.trim() || "";
              const chipnummer = cells[1].textContent?.trim() || "";
              const kon = cells[2]
                .querySelector("i")
                ?.className?.includes("male")
                ? "Male"
                : "Female";
              const fodelsedatum = cells[3].textContent?.trim() || "";
              const agare =
                cells[4].querySelector("strong")?.textContent?.trim() || "";
              return `${ras}, ${fodelsedatum}, ${agare}`;
            }
            return "";
          })
          .filter((item) => item);
      }
    }

    // Extract coordinates
    const coordDiv = detailsDocument.querySelector(
      "div.col-12.col-lg-5.d-lg-flex.align-items-lg-center",
    );
    if (coordDiv?.textContent?.includes("Latitud:")) {
      const coordText = coordDiv.textContent;
      const latMatch = coordText?.match(/Latitud:\s*([\d.]+)/);
      const lonMatch = coordText?.match(/Longitud:\s*([\d.]+)/);
      result.latitude = latMatch?.[1] || "";
      result.longitude = lonMatch?.[1] || "";
    }

    // Extract Google Maps links
    const mapsLink = detailsDocument.querySelector(
      'a[href*="maps.google.com"]',
    );
    if (mapsLink) {
      result.google_maps = mapsLink.getAttribute("href") || "";
    }

    const streetviewLink = detailsDocument.querySelector(
      'a[href*="google.com/maps/@"]',
    );
    if (streetviewLink) {
      result.google_steetview = streetviewLink.getAttribute("href") || "";
    }

    return result;
  } catch (error) {
    throw new Error(`Failed to fetch or parse Ratsit.se: ${error}`);
  }
}

function saveToCSV(data: RatsitData, filename: string): string {
  // Convert array fields to JSON strings for CSV
  const csvData: any = { ...data };
  Object.keys(csvData).forEach((key) => {
    if (Array.isArray(csvData[key])) {
      csvData[key] = JSON.stringify(csvData[key]);
    }
  });

  const headers = Object.keys(csvData).join(",");
  const values = Object.values(csvData)
    .map((value) => `"${String(value).replace(/"/g, '""')}"`)
    .join(",");

  const csvContent = `${headers}\n${values}`;

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
  .name("ratsitSearchPersonsDetails")
  .description("Extract detailed person data from Ratsit.se")
  .argument("<personnamn>", "Person name to search for")
  .argument("<gatuadress>", "Street address")
  .argument("<postort>", "City")
  .action(async (personnamn: string, gatuadress: string, postort: string) => {
    try {
      console.log(`Searching for: ${personnamn}, ${gatuadress}, ${postort}`);

      const ratsitData = await extractRatsitDetails(
        personnamn,
        gatuadress,
        postort,
      );

      // Create filename with search parameters
      const sanitizedName = personnamn.replace(/[^a-zA-Z0-9åäöÅÄÖ]/g, "_");
      const sanitizedAddress = gatuadress.replace(/[^a-zA-Z0-9åäöÅÄÖ\s]/g, "_");
      const sanitizedCity = postort.replace(/[^a-zA-Z0-9åäöÅÄÖ]/g, "_");
      const filename = `ratsit_search_persons_details_${sanitizedName}_${sanitizedAddress}_${sanitizedCity}.csv`;

      const filepath = saveToCSV(ratsitData, filename);
      console.log(`Saved results to: ${filepath}`);

      // Return all values as JSON
      console.log(JSON.stringify(ratsitData, null, 2));
    } catch (error) {
      console.error("Error:", error);
      process.exit(1);
    }
  });

program.parse();
