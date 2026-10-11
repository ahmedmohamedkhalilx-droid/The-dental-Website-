// @ts-check
const { defineConfig } = require('@playwright/test');
const executablePath = process.env.PW_CHROMIUM_PATH || undefined;

// Runs against the WordPress theme rendered to static HTML by tests/theme/render.php.
module.exports = defineConfig({
  testDir: './tests/theme',
  testMatch: '*.spec.js',
  reporter: [['list']],
  use: { baseURL: 'http://127.0.0.1:4174', launchOptions: { executablePath } },
  projects: [
    { name: 'desktop', use: { viewport: { width: 1366, height: 900 } } },
    { name: 'mobile', use: { viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true } },
  ],
  webServer: {
    command: 'python3 -m http.server 4174 --bind 127.0.0.1 --directory tests/theme/out',
    url: 'http://127.0.0.1:4174/index.html',
    reuseExistingServer: !process.env.CI,
  },
});
