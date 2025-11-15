/**
 * Automa JavaScript code to extract personer/foretag counts from merinfo.se HTML
 * and update the Laravel API
 *
 * This script should be used in an Automa JavaScript code block after
 * scraping the merinfo.se search results page.
 */

// Configuration - adjust these as needed
const API_BASE_URL = 'http://localhost:8000';
const POST_NUMMER_VARIABLE = 'post_nummer'; // Automa variable containing the postal code

(async () => {
  try {
    // Get the postal code from Automa variables
    const postNummer = automaRefData('variables', POST_NUMMER_VARIABLE);
    if (!postNummer) {
      console.error('No postal code found in variables');
      return;
    }

    console.log('Processing postal code:', postNummer);

    // Get the scraped HTML - adjust variable name based on your scraping setup
    const scrapedHtml = automaRefData('variables', 'scraped_html') ||
                       automaRefData('variables', 'page_html') ||
                       document.body.innerHTML;

    if (!scrapedHtml) {
      console.error('No HTML content found');
      return;
    }

    // Extract counts from HTML using DOM parsing
    const dom = new DOMParser();
    const doc = dom.parseFromString(scrapedHtml, 'text/html');

    // Extract personer count (from link with href containing "d=p")
    const personerLink = doc.querySelector('a[href*="d=p"] span:last-child');
    const personer = personerLink ? parseInt(personerLink.textContent.trim()) || 0 : 0;

    // Extract foretag count (from link with href containing "d=c")
    const foretagLink = doc.querySelector('a[href*="d=c"] span:last-child');
    const foretag = foretagLink ? parseInt(foretagLink.textContent.trim()) || 0 : 0;

    console.log(`Extracted counts: personer=${personer}, foretag=${foretag}`);

    if (personer === 0 && foretag === 0) {
      console.warn('No counts found in HTML. The page structure may have changed.');
      return;
    }

    // Prepare API payload
    const payload = {
      merinfo_personer: personer,
      merinfo_foretag: foretag
    };

    // Update via API
    const encodedPostNummer = encodeURIComponent(String(postNummer));
    const response = await fetch(`${API_BASE_URL}/api/post-nummer/by-code/${encodedPostNummer}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(payload)
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.error('API update failed:', response.status, response.statusText, errorText);
      return;
    }

    const result = await response.json();
    console.log('API update successful:', result.message);

    // Store results in Automa variables for debugging
    automaSetVar('extraction_result', { personer, foretag, api_response: result });

    // Proceed to next block
    automaNextBlock();

  } catch (error) {
    console.error('Script error:', error);
    automaSetVar('extraction_error', error.message);
  }
})();