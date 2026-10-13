// @ts-check
const { defineConfig } = require('@playwright/test');
const executablePath = process.env.PW_CHROMIUM_PATH || undefined;

// Real WordPress end-to-end test. Started by tools/wp-e2e/run.sh, which brings up WordPress and the mail server first.
module.exports = defineConfig({
  testDir: './tests/wp-e2e',
  workers: 1,
  fullyParallel: false,
  timeout: 90000,
  reporter: [['list']],
  use: { baseURL: `http://127.0.0.1:${process.env.WP_E2E_PORT || 8099}`, launchOptions: { executablePath }, viewport: { width: 1366, height: 900 } },
});
