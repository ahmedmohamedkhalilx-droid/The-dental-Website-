// @ts-check
const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

const BASE = '/The-dental-Website-';
const PNG = fs.readFileSync(path.join(__dirname, '../../wordpress-theme/the-dental-hauz/assets/img/aligners.jpg'));

// The photos live on thedentalhauz.com; serve a stand-in so the layout can be checked offline.
test.beforeEach(async ({ page }) => {
  await page.route('https://thedentalhauz.com/**', (route) => route.fulfill({ status: 200, contentType: 'image/jpeg', body: PNG }));
  await page.route('https://fonts.googleapis.com/**', (route) => route.fulfill({ status: 200, contentType: 'text/css', body: '' }));
  await page.route('https://www.google.com/**', (route) => route.fulfill({ status: 200, contentType: 'text/html', body: '<p>map</p>' }));
});

test('home loads under the GitHub Pages path with styles and scripts, and no banner above the site', async ({ page }) => {
  const bad = [];
  const errors = [];
  page.on('response', (r) => { if (r.status() >= 400) bad.push(`${r.status()} ${r.url()}`); });
  page.on('pageerror', (e) => errors.push(String(e)));
  await page.goto(`${BASE}/`, { waitUntil: 'networkidle' });
  expect(bad).toEqual([]);
  expect(errors).toEqual([]);
  await expect(page.locator('.preview-bar')).toHaveCount(0);
  await expect(page.locator('body > :first-child')).toHaveClass(/skip-link/);
  await expect(page.locator('h1')).toHaveText('Healthy. Everyday.');
  expect(await page.evaluate(() => getComputedStyle(document.body).backgroundColor)).toBe('rgb(244, 240, 232)');
  await expect(page.locator('meta[name="robots"]')).toHaveAttribute('content', 'noindex, nofollow');
  const w = await page.evaluate(() => [document.documentElement.scrollWidth, document.documentElement.clientWidth]);
  expect(w[0]).toBeLessThanOrEqual(w[1]);
});

test('navigate Home > Services > a treatment page > back, by clicking', async ({ page }, info) => {
  await page.goto(`${BASE}/`);
  if (info.project.name === 'mobile') await page.click('#menu-toggle');
  await page.locator('#site-nav > ul > li > a', { hasText: 'Services' }).click();
  await expect(page).toHaveURL(`${BASE}/our-services/`);
  await expect(page.locator('h1')).toHaveText('Services');
  await expect(page.locator('.svc')).toHaveCount(10);
  const link = page.locator('.svc h3 a', { hasText: 'Dental Implants' });
  await link.click();
  // on phones a single tap on a treatment tile opens its page
  await expect(page).toHaveURL(`${BASE}/dental-implants/`);
  await expect(page.locator('h1')).toHaveText('Dental Implants');
  await expect(page.locator('.crumbs')).toContainText('Services');
  await page.locator('.crumbs a', { hasText: 'Services' }).click();
  await expect(page).toHaveURL(`${BASE}/our-services/`);
});

test('every page opens and has a heading, all images are requested from the old site or the theme', async ({ page }) => {
  const slugs = JSON.parse(fs.readFileSync(path.join(__dirname, '../../tools/preview/pages.json'), 'utf8'));
  for (const s of slugs) {
    const r = await page.goto(`${BASE}/${s}/`, { waitUntil: 'networkidle' });
    expect(r?.status(), s).toBe(200);
    await expect(page.locator('h1'), s).toHaveCount(1);
    const srcs = await page.$$eval('img', (is) => is.map((i) => i.getAttribute('src')));
    for (const src of srcs) expect(src, s).toMatch(/^(https:\/\/thedentalhauz\.com\/wp-content\/uploads\/|\/The-dental-Website-\/assets\/img\/)/);
  }
});

test('treatment pages are separate pages, one per treatment', async ({ page }) => {
  await page.goto(`${BASE}/our-services/`);
  const hrefs = await page.$$eval('.svc h3 a', (as) => as.map((a) => a.getAttribute('href')));
  expect(new Set(hrefs).size).toBe(10);
  for (const h of hrefs) {
    const r = await page.goto(h);
    expect(r?.status(), h).toBe(200);
  }
});

test('forms are inert in the preview and say so', async ({ page }) => {
  await page.goto(`${BASE}/contact-us/`);
  await page.fill('#f-booking-first_name', 'Test');
  await page.fill('#f-booking-phone', '905 555 0100');
  await page.fill('#f-booking-email', 'a@b.test');
  await page.selectOption('#f-booking-service', 'Dental Cleaning');
  await page.selectOption('#f-booking-day', 'Monday');
  await page.selectOption('#f-booking-time', 'Morning');
  await page.getByRole('button', { name: 'Book Appointment' }).click();
  await expect(page.locator('#form-booking .form-note')).toContainText('This is a preview');
  await expect(page).toHaveURL(`${BASE}/contact-us/`);
});

test('the earlier single-page design is still available', async ({ page }) => {
  const r = await page.goto(`${BASE}/single-page/index.html`);
  expect(r?.status()).toBe(200);
  await expect(page).toHaveTitle('The Dental Hauz');
});

test('"Make Appointment" jumps straight to the booking form on the same page', async ({ page }) => {
  await page.goto(`${BASE}/`);
  await page.locator('.hero-actions a.btn', { hasText: 'Make Appointment' }).click();
  await expect(page).toHaveURL(`${BASE}/#book`);
  await expect(page.locator('#book')).toBeInViewport();
  await expect(page.locator('#form-booking')).toBeInViewport();
});
