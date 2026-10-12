// @ts-check
const { defineConfig } = require('@playwright/test');
const executablePath = process.env.PW_CHROMIUM_PATH || undefined;

// Serves the repository root under /The-dental-Website-/ exactly as GitHub Pages does.
module.exports = defineConfig({
  testDir: './tests/preview',
  reporter: [['list']],
  use: { baseURL: 'http://127.0.0.1:4175', launchOptions: { executablePath } },
  projects: [
    { name: 'desktop', use: { viewport: { width: 1366, height: 900 } } },
    { name: 'mobile', use: { viewport: { width: 390, height: 844 }, isMobile: true, hasTouch: true } },
  ],
  webServer: {
    command: 'rm -rf tests/preview/mount && mkdir -p tests/preview/mount && ln -s "$PWD" tests/preview/mount/The-dental-Website- && python3 -m http.server 4175 --bind 127.0.0.1 --directory tests/preview/mount',
    url: 'http://127.0.0.1:4175/The-dental-Website-/index.html',
    reuseExistingServer: !process.env.CI,
  },
});
