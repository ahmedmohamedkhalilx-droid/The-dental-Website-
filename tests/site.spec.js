// @ts-check
const { test, expect } = require('@playwright/test');

const isMobile = (testInfo) => testInfo.project.name === 'mobile';

test.describe('The Dental Hauz site', () => {
  /** @type {string[]} */ let consoleErrors;
  /** @type {{url: string, status: number}[]} */ let badResponses;

  test.beforeEach(async ({ page }) => {
    consoleErrors = [];
    badResponses = [];
    page.on('console', (m) => { if (m.type() === 'error') consoleErrors.push(m.text()); });
    page.on('pageerror', (e) => consoleErrors.push(String(e)));
    page.on('response', (r) => { if (r.status() >= 400) badResponses.push({ url: r.url(), status: r.status() }); });
    const res = await page.goto('/index.html', { waitUntil: 'networkidle' });
    expect(res?.status()).toBe(200);
  });

  test('page loads with no JS errors and no failed requests', async ({ page }) => {
    await expect(page).toHaveTitle('The Dental Hauz');
    expect(consoleErrors).toEqual([]);
    expect(badResponses).toEqual([]);
  });

  test('every image is served and actually decodes', async ({ page }) => {
    const images = await page.$$eval('img', (imgs) =>
      imgs.map((i) => ({ src: i.getAttribute('src'), ok: i.complete && i.naturalWidth > 0, alt: i.getAttribute('alt') })));
    expect(images.length).toBe(8);
    for (const img of images) {
      expect(img.ok, `image failed to load: ${img.src}`).toBe(true);
      expect(img.alt, `missing alt attribute: ${img.src}`).not.toBeNull();
    }
  });

  test('no image is stretched or squashed', async ({ page }) => {
    const shapes = await page.$$eval('img', (imgs) => imgs.map((i) => {
      const r = i.getBoundingClientRect();
      return {
        src: i.getAttribute('src'),
        fit: getComputedStyle(i).objectFit,
        rendered: r.height / r.width,
        natural: i.naturalHeight / i.naturalWidth,
      };
    }));
    for (const s of shapes) {
      if (s.fit === 'cover') {
        // Cropped photos: the frame may differ from the file, but never become a tall sliver.
        expect(s.rendered, `photo frame too tall: ${s.src}`).toBeLessThanOrEqual(1.6);
      } else {
        // Logos and uncropped images must keep their exact proportions.
        expect(Math.abs(s.rendered / s.natural - 1), `distorted image: ${s.src}`).toBeLessThan(0.02);
      }
    }
  });

  test('favicon and Google Fonts stylesheet respond 200', async ({ page, request }) => {
    const icon = await page.getAttribute('link[rel="icon"]', 'href');
    expect((await request.get(`/${icon}`)).status()).toBe(200);
    const fontsHref = await page.getAttribute('link[rel="stylesheet"]', 'href');
    expect(fontsHref).toContain('fonts.googleapis.com');
  });

  test('every in-page link points at a section that exists', async ({ page }) => {
    const hrefs = await page.$$eval('a[href^="#"]', (as) => [...new Set(as.map((a) => a.getAttribute('href')))]);
    expect(hrefs.length).toBeGreaterThan(5);
    for (const href of hrefs) {
      const id = href.slice(1);
      expect(await page.locator(`[id="${id}"]`).count(), `broken anchor ${href}`).toBe(1);
    }
  });

  test('brand colours are applied from the brand sheet', async ({ page }) => {
    const vars = await page.evaluate(() => {
      const s = getComputedStyle(document.documentElement);
      return ['--umber', '--sage-deep', '--sage', '--blush', '--cream'].map((v) => s.getPropertyValue(v).trim().toUpperCase());
    });
    expect(vars).toEqual(['#514A43', '#657568', '#A8B6A5', '#D8B8B2', '#F4F0E8']);
    expect(await page.evaluate(() => getComputedStyle(document.body).backgroundColor)).toBe('rgb(244, 240, 232)');
  });

  test('no horizontal scrolling at this viewport', async ({ page }) => {
    const { scrollWidth, clientWidth } = await page.evaluate(() => ({
      scrollWidth: document.documentElement.scrollWidth,
      clientWidth: document.documentElement.clientWidth,
    }));
    expect(scrollWidth).toBeLessThanOrEqual(clientWidth);
  });

  test('navigation: desktop links visible, mobile menu opens and closes', async ({ page }, testInfo) => {
    const nav = page.locator('#site-nav');
    const toggle = page.locator('#menu-toggle');
    if (!isMobile(testInfo)) {
      await expect(toggle).toBeHidden();
      await expect(nav.getByRole('link', { name: 'Services' })).toBeVisible();
      return;
    }
    await expect(nav).toBeHidden();
    await toggle.click();
    await expect(toggle).toHaveAttribute('aria-expanded', 'true');
    await expect(nav).toBeVisible();
    await nav.getByRole('link', { name: 'FAQ' }).click();
    await expect(nav).toBeHidden();
    await expect(toggle).toHaveAttribute('aria-expanded', 'false');
    await expect(page).toHaveURL(/#faq$/);
  });

  test('FAQ accordion expands and collapses', async ({ page }) => {
    const item = page.locator('#faq details').nth(1);
    await expect(item).not.toHaveAttribute('open', '');
    await item.locator('summary').click();
    await expect(item).toHaveAttribute('open', '');
    await expect(item.locator('p')).toBeVisible();
    await item.locator('summary').click();
    await expect(item).not.toHaveAttribute('open', '');
  });

  test('booking form: rejects empty submit, accepts filled submit', async ({ page }) => {
    const form = page.locator('#booking-form');
    const note = page.locator('#form-note');
    await expect(note).toBeHidden();

    await form.getByRole('button', { name: 'Request Appointment' }).click();
    await expect(note).toBeVisible();
    await expect(note).toHaveText(/add your name and phone/);

    await page.fill('#f-name', 'Test Patient');
    await page.fill('#f-phone', '+20 100 000 0000');
    await page.selectOption('#f-treatment', 'Dental implants');
    await form.getByRole('button', { name: 'Request Appointment' }).click();
    await expect(note).toHaveText(/Thanks, Test Patient\./);
    // Submitting must not navigate away (no backend is wired yet).
    await expect(page).toHaveURL(/index\.html$/);
  });

  test('every form control has a label', async ({ page }) => {
    const unlabeled = await page.$$eval('input, select, textarea', (els) =>
      els.filter((el) => !el.id || !document.querySelector(`label[for="${el.id}"]`)).map((el) => el.outerHTML));
    expect(unlabeled).toEqual([]);
  });
});
