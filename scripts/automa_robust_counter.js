/**
 * Improved Automa JavaScript code for robust counter management
 * Handles script interruptions and provides resume capability
 */

// Configuration
const API_BASE_URL = 'http://localhost:8000';
const POST_NUMMER_VARIABLE = 'post_nummer';

(async () => {
  try {
    // Get the postal code from Automa variables
    const postNummer = automaRefData('variables', POST_NUMMER_VARIABLE);
    if (!postNummer) {
      console.error('No postal code found in variables');
      return;
    }

    console.log('Starting robust counter update for:', postNummer);

    // First, check if we can resume from a previous state
    try {
      const resumeResponse = await fetch(`${API_BASE_URL}/api/post-nummer/resume-info/${encodeURIComponent(postNummer)}`);
      if (resumeResponse.ok) {
        const resumeData = await resumeResponse.json();
        console.log('Resume info:', resumeData.data);

        if (resumeData.data.can_resume) {
          console.log('Resuming from previous state...');
          // You can use resumeData.data.last_processed_page etc. to continue where you left off
        }
      }
    } catch (error) {
      console.log('Could not check resume info, starting fresh');
    }

    // Get current saved count from Automa (this should be maintained across script runs)
    const savedCount = parseInt(automaRefData('variables', 'savedCount')) || 0;
    const phoneCount = parseInt(automaRefData('variables', 'phoneCount')) || 0;
    const houseCount = parseInt(automaRefData('variables', 'houseCount')) || 0;

    console.log(`Current counters - saved: ${savedCount}, phone: ${phoneCount}, house: ${houseCount}`);

    // Prepare counter increments (only increment by the new amounts since last run)
    const counterIncrements = {
      personer: savedCount,  // Total people saved
      phone: phoneCount,     // People with phone numbers
      house: houseCount,     // People with house info
    };

    // Get current progress info
    const currentPage = parseInt(automaRefData('variables', 'currentPage')) || 1;
    const totalPages = parseInt(automaRefData('variables', 'totalPages')) || 1;
    const progress = Math.min(100, Math.round((currentPage / totalPages) * 100));

    // Update counters via the robust API endpoint
    const updatePayload = {
      counters: counterIncrements,
      last_processed_page: currentPage,
      progress: progress,
      status: progress >= 100 ? 'completed' : 'processing',
      is_pending: progress < 100,
      is_complete: progress >= 100,
    };

    console.log('Sending counter update:', updatePayload);

    const response = await fetch(`${API_BASE_URL}/api/post-nummer/increment-counters/${encodeURIComponent(postNummer)}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(updatePayload)
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.error('Counter update failed:', response.status, response.statusText, errorText);

      // Store error for debugging
      automaSetVar('counter_update_error', errorText);
      return;
    }

    const result = await response.json();
    console.log('Counter update successful:', result.message);

    // Store success info
    automaSetVar('last_counter_update', {
      timestamp: new Date().toISOString(),
      post_nummer: postNummer,
      counters: counterIncrements,
      progress: progress,
      api_response: result
    });

    // Reset increment counters for next run (since they're now saved to database)
    automaSetVar('savedCount', 0);
    automaSetVar('phoneCount', 0);
    automaSetVar('houseCount', 0);

    // Proceed to next block if not complete
    if (progress < 100) {
      automaNextBlock();
    } else {
      console.log('Processing completed for postal code:', postNummer);
    }

  } catch (error) {
    console.error('Script error:', error);
    automaSetVar('counter_script_error', error.message);
  }
})();