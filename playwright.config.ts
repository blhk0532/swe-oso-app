import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
  testDir: './tests',
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: 0,
  workers: 1,
  reporter: 'html',
  timeout: 120000, // 2 minutes per test
  
  use: {
    baseURL: 'https://www.merinfo.se',
    trace: 'on-first-retry',
    screenshot: 'only-on-failure',
    video: 'off', // Disabled due to ffmpeg compatibility
    locale: 'sv-SE',
    timezoneId: 'Europe/Stockholm',
    geolocation: { latitude: 59.3293, longitude: 18.0686 },
    permissions: ['geolocation'],
    actionTimeout: 30000,
  },

  projects: [
    {
      name: 'Google Chrome',
      use: { 
        ...devices['Desktop Chrome'], 
        channel: 'chrome',
        launchOptions: {
          args: [
            '--disable-blink-features=AutomationControlled',
            '--disable-dev-shm-usage',
            '--no-sandbox',
          ],
          slowMo: 100, // Slow down by 100ms to appear more human
        },
        // Uncomment to use saved authentication state after manual Cloudflare solve:
        // storageState: 'playwright/.auth/merinfo-session.json',
      },
    },
    {
      name: 'Microsoft Edge',
      use: { 
        ...devices['Desktop Edge'], 
        channel: 'msedge',
        launchOptions: {
          args: [
            '--disable-blink-features=AutomationControlled',
            '--disable-dev-shm-usage',
            '--no-sandbox',
          ],
          slowMo: 100,
        },
      },
    },
  ],
});