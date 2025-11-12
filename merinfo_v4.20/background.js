let isRunning = false;
let currentPage = 1;
let totalDataCount = 0;
let filteredDataCount = 0;
let allFilteredData = [];
let allTotalData = [];

const exclusionPattern = /lgh|1 tr|2 tr|3 tr|4 tr|5 tr|6 tr| nb| bv|\b([1-9][0-9]?|100) [A-Z]\b/i;

chrome.runtime.onMessage.addListener((message, sender, sendResponse) => {
  if (message.action === "start") {
    isRunning = true;
    allTotalData = [];
    allFilteredData = [];
    refreshAndStartNavigation();
  } else if (message.action === "stop") {
    isRunning = false;
  }
});

function refreshAndStartNavigation() {
  chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
    console.log("Refreshing the page once before starting navigation.");
    chrome.tabs.reload(tabs[0].id, { bypassCache: true });
  });

  chrome.tabs.onUpdated.addListener(function initialListener(tabId, changeInfo) {
    if (changeInfo.status === "complete" && isRunning) {
      chrome.tabs.onUpdated.removeListener(initialListener);
      setTimeout(scrapeDataAndNavigate, 5000); // Wait 5 seconds after reload, then start scraping and navigating
    }
  });
}

function scrapeDataAndNavigate() {
  if (!isRunning) return;

  chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
    chrome.scripting.executeScript({
      target: { tabId: tabs[0].id },
      func: scrapeData
    }, (results) => {
      const rawData = results[0].result.filter(entry => entry.phoneNumber);  // Filter out entries with empty phone numbers
      const filteredData = rawData.filter(entry => !exclusionPattern.test(entry.address)); // Filter based on exclusion pattern
      allTotalData = allTotalData.concat(rawData);
      allFilteredData = allFilteredData.concat(filteredData);

      const pageFilteredCount = filteredData.length;

      totalDataCount += rawData.length;
      filteredDataCount += pageFilteredCount;

      console.log(`Page ${currentPage}: ${pageFilteredCount} entries passed filter, ${rawData.length} total entries.`);

      // Send counts and data arrays to popup
      chrome.runtime.sendMessage({
        action: "updateDataCount",
        totalDataCount: totalDataCount
      });
      chrome.runtime.sendMessage({
        action: "updateFilteredCount",
        filteredDataCount: filteredDataCount
      });
      chrome.runtime.sendMessage({
        action: "updateTotalData",
        data: allTotalData
      });
      chrome.runtime.sendMessage({
        action: "updateFilteredData",
        data: allFilteredData
      });

      if (rawData.length < 10) {
        console.log("No more results. STOPPED.");
        isRunning = false;
        chrome.runtime.sendMessage({ action: "stop" });
      } else {
        navigateNextPage(); // Proceed to the next page
      }
    });
  });
}

function scrapeData() {
  const data = [];
  const uniqueEntries = new Set(); // Track unique entries
  const resultDivs = document.querySelectorAll("div.mi-flex.mi-flex-wrap");
  
  resultDivs.forEach(div => {
    const entry = {};

    // Scrape phone number
    const phoneAnchor = div.querySelector("a[href^='tel:']");
    entry.phoneNumber = phoneAnchor ? phoneAnchor.getAttribute("href").replace("tel:", "") : "";

    // Only save entries with a phone number
    if (entry.phoneNumber) {
      // Scrape name
      const nameElement = div.querySelector("h2");
      entry.name = nameElement ? nameElement.textContent.trim() : "";

      // Scrape date of birth
      const dobElement = div.querySelector("p.mi-my-1");
      entry.dob = dobElement ? dobElement.textContent.trim() : "";

      // Scrape address, zip code, and city
      const addressElement = div.querySelector("address");
      if (addressElement) {
        const spanElements = addressElement.querySelectorAll("span");
        entry.address = spanElements[0] ? spanElements[0].textContent.trim() : "";
        const zipCity = spanElements[1] ? spanElements[1].textContent.trim() : "";
        entry.zipCode = zipCity ? zipCity.substring(0, 6).trim() : "";
        entry.city = zipCity ? zipCity.substring(6).trim() : "";
      } else {
        entry.address = "";
        entry.zipCode = "";
        entry.city = "";
      }

      // Create a unique key for the entry based on critical fields
      const uniqueKey = `${entry.phoneNumber}-${entry.name}-${entry.address}`;

      // Add entry only if it hasn't been added before
      if (!uniqueEntries.has(uniqueKey)) {
        uniqueEntries.add(uniqueKey); // Mark this entry as added
        data.push(entry); // Add entry to the final data array
      }
    }
  });

  return data;
}

function navigateNextPage() {
  if (!isRunning) return;

  chrome.tabs.query({ active: true, currentWindow: true }, (tabs) => {
    const url = new URL(tabs[0].url);
    const currentQueryPage = url.searchParams.get("page");
    
    if (url.searchParams.has("page")) {
      currentPage = parseInt(currentQueryPage) + 1;
    } else {
      currentPage = 2;
    }
    url.searchParams.set("page", currentPage);
    chrome.tabs.update(tabs[0].id, { url: url.toString() });
    
    console.log(`Navigating to page: ${currentPage}`);
    chrome.runtime.sendMessage({ action: "updatePageCount", page: currentPage });
  });

  chrome.tabs.onUpdated.addListener(function listener(tabId, changeInfo) {
    if (changeInfo.status === "complete") {
      chrome.tabs.onUpdated.removeListener(listener);
      
      // Generate a random delay between 4000ms and 6000ms (4-6 seconds)
      const randomDelay = Math.floor(Math.random() * (4000 - 2000 + 1)) + 2000;
      
      setTimeout(scrapeDataAndNavigate, randomDelay); // Wait random time before scraping next page
    }
  });
}