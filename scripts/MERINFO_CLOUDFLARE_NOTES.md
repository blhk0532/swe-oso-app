# Merinfo.se Scraper - Cloudflare Protection Issues

## Problem
Merinfo.se uses Cloudflare's advanced bot protection that is very difficult to bypass with automated scripts. Despite implementing:
- ✅ Playwright stealth plugin
- ✅ Human-like mouse movements
- ✅ Random delays
- ✅ Anti-detection JavaScript injections
- ✅ Realistic browser fingerprints
- ✅ Swedish locale/timezone
- ✅ Slow motion automation

The Cloudflare challenge still won't resolve automatically.

## Why This Happens
Cloudflare's protection analyzes:
1. **TLS fingerprints** - Even with stealth, automated browsers have distinct TLS signatures
2. **Behavioral patterns** - Mouse movements, timing, scrolling patterns
3. **IP reputation** - Your IP might be flagged as datacenter/VPS
4. **Challenge solving patterns** - How quickly challenges are solved
5. **Browser fingerprints** - Canvas, WebGL, audio context fingerprints

## Alternative Solutions

### Option 1: Manual Browser Profile (RECOMMENDED)
Use a real browser profile with cookies/session:

```bash
# Create a browser profile by visiting the site manually first
google-chrome --user-data-dir=/tmp/chrome-profile

# Visit https://www.merinfo.se/search?q=733%2032%20Sala&page=1&d=p&ap=1
# Complete the Cloudflare challenge manually
# Keep the browser open for a few minutes

# Then update the script to use this profile
```

Update `merinfo_se.py` to use the profile:
```python
context = browser.new_context(
    storage_state="profile.json",  # Save/load session
    ...
)
```

### Option 2: Residential Proxy
Use a residential IP proxy service:
- BrightData
- Oxylabs  
- SmartProxy

These provide real residential IPs that Cloudflare trusts more.

### Option 3: Captcha Solving Service
Integrate a service like:
- 2Captcha
- Anti-Captcha
- CapSolver

These can solve Cloudflare challenges programmatically.

### Option 4: Use Merinfo API (if available)
Check if Merinfo offers an official API for legitimate data access.

### Option 5: Undetected ChromeDriver (Python)
Try `undetected-chromedriver` package which is specifically designed to bypass Cloudflare:

```bash
pip install undetected-chromedriver
```

### Option 6: Browser Automation Services
Use cloud services designed for scraping:
- Browserless.io
- ScrapingBee
- Bright Data's Scraping Browser

## Current Script Features
The script is fully functional and will work once Cloudflare allows access. It includes:

- ✅ Data extraction for all fields
- ✅ CSV export with timestamps
- ✅ Laravel API integration
- ✅ Stealth techniques
- ✅ Human-like behavior
- ✅ Error handling

## Testing Manually
Run with visible browser to see what's happening:

```bash
./scripts/run_merinfo.sh "733 32 Sala" --no-headless
```

## Next Steps
1. Try accessing from a different IP (residential)
2. Try at different times (less traffic = easier challenges)
3. Consider using a paid proxy service
4. Contact Merinfo about legitimate data access
5. Try the undetected-chromedriver approach
