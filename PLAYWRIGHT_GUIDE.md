# Merinfo.se Playwright Tests - VSCode Integration

## Setup Complete ✅

I've created a full Playwright Test suite that you can run directly in VSCode with the Playwright extension.

## Files Created

1. **playwright.config.ts** - Updated with Swedish locale, anti-detection args, and slow-mo timing
2. **tests/merinfo-search.spec.ts** - Complete test suite with 3 tests:
   - Homepage navigation + Cloudflare handling
   - Person search by address
   - Complete data extraction to JSON

## Running Tests in VSCode

### Option 1: Using VSCode Playwright Extension (Recommended)

1. Open the **Testing** sidebar in VSCode (flask icon on left)
2. You'll see "Merinfo.se Person Search" tests listed
3. **First Run** - Click the ▶️ play button on any test:
   - Browser opens in **headed mode** (you can see it)
   - If Cloudflare appears, **solve it manually** in the browser window
   - Test waits up to 60 seconds for you to complete it
   - After success, session is saved to `playwright/.auth/merinfo-session.json`

4. **Subsequent Runs**:
   - Uncomment line 37 in `playwright.config.ts`:
     ```typescript
     storageState: 'playwright/.auth/merinfo-session.json',
     ```
   - Future tests will skip Cloudflare! 🎉

### Option 2: Command Line

```bash
# Run all tests (headed mode to solve Cloudflare)
npx playwright test --headed --project="Google Chrome"

# Run specific test
npx playwright test merinfo-search --headed

# Debug mode (step through with Playwright Inspector)
npx playwright test --debug
```

### Option 3: VSCode Debug Mode

1. Click on a test in the Testing sidebar
2. Right-click → "Debug Test"
3. Sets breakpoints, steps through code, inspects variables

## The Tests

### Test 1: Homepage + Cloudflare
- Navigates to merinfo.se
- Detects Cloudflare challenge
- Waits for manual solve (60s timeout)
- Takes screenshot

### Test 2: Person Search
- Searches for "733 32 Sala"
- Fills search input character-by-character (human-like)
- Submits with Enter key
- Waits for results page
- Extracts data from first 5 cards
- **Saves session** for next run

### Test 3: Complete Data Extraction
- Searches and extracts ALL person cards
- Gets: name, personnummer, address, postal code, city, phone
- Saves to `scripts/data/merinfo_playwright_YYYY-MM-DD.json`
- Logs all results to console

## Tips for Success

### First Run (Solving Cloudflare)
1. Run test in **headed mode** (not headless)
2. When browser opens, watch for the challenge
3. Solve it manually (checkbox/captcha)
4. Test continues automatically
5. Session saved for future runs

### Using Saved Session
After first successful run:
1. Edit `playwright.config.ts` line 37:
   ```typescript
   storageState: 'playwright/.auth/merinfo-session.json',
   ```
2. All future runs skip Cloudflare! ✨

### VSCode Features You Can Use

- **Show browser**: Tests run in headed mode by default
- **Trace viewer**: See timeline of all actions after test
- **Pick locator**: Use the "Pick locator" tool to find elements
- **Codegen**: Generate test code by recording your actions
- **Time travel**: Step through test execution frame-by-frame

## Troubleshooting

### Still blocked by Cloudflare?
- Try running test at different time of day
- Use different network/IP
- Try with `--project="Microsoft Edge"` instead of Chrome
- Add `--headed` flag to see and solve challenge manually

### Types error?
Already fixed - `@types/node` is installed.

### Want to try a proxy?
Add to `playwright.config.ts` use section:
```typescript
proxy: {
  server: 'http://proxy-host:port',
  username: 'user',
  password: 'pass'
}
```

## Next Steps

1. **Run your first test** in VSCode Testing sidebar
2. **Solve Cloudflare** manually in the browser window
3. **Enable storageState** in config to skip it next time
4. **Enjoy automated scraping!** 🚀

The extracted data goes to `scripts/data/merinfo_playwright_*.json` in the same format as your Python scripts.
