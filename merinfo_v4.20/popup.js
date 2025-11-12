document.addEventListener("DOMContentLoaded", function() {
  let isRunning = false;
  let pageCount = 0;
  let totalDataCount = 0;
  let filteredDataCount = 0;
  let allFilteredData = [];
  let allTotalData = [];

  const startButton = document.getElementById("startButton");
  const stopButton = document.getElementById("stopButton");
  const csvButton = document.getElementById("csvButton");
  const statusElement = document.getElementById("status");
  const pageCounterElement = document.getElementById("pageCounter");
  const dataCounterElement = document.getElementById("dataCounter");
  const filteredCounterElement = document.getElementById("filteredCounter");

  if (startButton && stopButton && csvButton && statusElement && pageCounterElement && dataCounterElement && filteredCounterElement) {

    startButton.addEventListener("click", () => {
      if (!isRunning) {
        isRunning = true;
        chrome.runtime.sendMessage({ action: "start" });
        statusElement.innerText = "Status: Running";
        stopButton.disabled = false;
        csvButton.disabled = false;
        startButton.disabled = true;
      }
    });

    stopButton.addEventListener("click", () => {
      isRunning = false;
      chrome.runtime.sendMessage({ action: "stop" });
      statusElement.innerText = "Status: Stopped";
      stopButton.disabled = true;
      startButton.disabled = false;
    });

    csvButton.addEventListener("click", () => {
      downloadCSV(allTotalData, "total");
      downloadCSV(allFilteredData, "filtered");
    });

    chrome.runtime.onMessage.addListener((message) => {
      if (message.action === "updatePageCount") {
        pageCount = message.page;
        pageCounterElement.innerText = `Page: ${pageCount}`;
      } else if (message.action === "updateDataCount") {
        totalDataCount = message.totalDataCount;
        dataCounterElement.innerText = `Total: ${totalDataCount}`;
      } else if (message.action === "updateFilteredCount") {
        filteredDataCount = message.filteredDataCount;
        filteredCounterElement.innerText = `Filter: ${filteredDataCount}`;
      } else if (message.action === "updateTotalData") {
        allTotalData = message.data;
      } else if (message.action === "updateFilteredData") {
        allFilteredData = message.data;
      }
    });

    // Function to generate CSV files
    function downloadCSV(data, type) {
      if (data.length === 0) {
        console.warn(`No data available for ${type} download.`);
        return;
      }

      const orderedData = data.map(entry => ({
        Address: entry.address,
        "Zip Code": entry.zipCode,
        City: entry.city,
        DOB: entry.dob,
        Name: entry.name,
        "Phone Number": entry.phoneNumber
      }));

      const csvContent = orderedData.map(row => Object.values(row).join(",")).join("\n");
      const blob = new Blob([csvContent], { type: "text/csv" });
      const timestamp = new Date().toISOString().replace(/[:.]/g, "-");
      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");
      link.href = url;
      link.download = `merinfo_${type}_data_${timestamp}.csv`;
      link.click();
      URL.revokeObjectURL(url); // Clean up URL
    }
  } else {
    console.error("Some popup elements are missing, check the HTML structure.");
  }
});
