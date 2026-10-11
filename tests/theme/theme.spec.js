// @ts-check
const { test, expect } = require('@playwright/test');

test.describe('WordPress theme (rendered)', () => {
  test('home renders with brand tokens, no console errors, no missing files', async ({ page }) => {
    const errors = [];
    const bad = [];
    page.on('pageerror', (e) => errors.push(String(e)));
    page.on('response', (r) => { if (r.status() >= 400) bad.push(r.url()); });
    await page.goto('/index.html', { waitUntil: 'networkidle' });
    expect(errors).toEqual([]);
    expect(bad).toEqual([]);
    const vars = await page.evaluate(() => ['--umber', '--sage-deep', '--sage', '--blush', '--cream']
      .map((v) => getComputedStyle(document.documentElement).getPropertyValue(v).trim().toUpperCase()));
    expect(vars).toEqual(['#514A43', '#657568', '#A8B6A5', '#D8B8B2', '#F4F0E8']);
  });

  test('all images load and keep their proportions', async ({ page }) => {
    await page.goto('/index.html', { waitUntil: 'networkidle' });
    const imgs = await page.$$eval('img', (list) => list.map((i) => {
      const r = i.getBoundingClientRect();
      return { src: i.getAttribute('src'), ok: i.complete && i.naturalWidth > 0, fit: getComputedStyle(i).objectFit, ratio: r.height / r.width, natural: i.naturalHeight / i.naturalWidth };
    }));
    expect(imgs.length).toBe(8);
    for (const i of imgs) {
      expect(i.ok, `broken: ${i.src}`).toBe(true);
      if (i.fit === 'cover') expect(i.ratio, i.src).toBeLessThanOrEqual(1.6);
      else expect(Math.abs(i.ratio / i.natural - 1), `distorted: ${i.src}`).toBeLessThan(0.02);
    }
  });

  test('no horizontal scroll; every #anchor has a target', async ({ page }) => {
    await page.goto('/index.html', { waitUntil: 'networkidle' });
    const w = await page.evaluate(() => [document.documentElement.scrollWidth, document.documentElement.clientWidth]);
    expect(w[0]).toBeLessThanOrEqual(w[1]);
    const hrefs = await page.$$eval('a[href*="#"]', (as) => [...new Set(as.map((a) => a.getAttribute('href')))]);
    for (const h of hrefs) {
      const id = h.slice(h.indexOf('#') + 1);
      expect(await page.locator(`[id="${id}"]`).count(), `missing target for ${h}`).toBe(1);
    }
  });

  test('menu fallback is present and mobile toggle works', async ({ page }, info) => {
    await page.goto('/index.html', { waitUntil: 'networkidle' });
    const nav = page.locator('#site-nav');
    await expect(nav.locator('ul > li > a')).toHaveCount(5);
    if (info.project.name !== 'mobile') { await expect(page.locator('#menu-toggle')).toBeHidden(); return; }
    await expect(nav).toBeHidden();
    await page.click('#menu-toggle');
    await expect(nav).toBeVisible();
    await nav.getByRole('link', { name: 'FAQ' }).click();
    await expect(nav).toBeHidden();
  });

  test('booking form posts to admin-post.php with the fields the handler reads', async ({ page }) => {
    await page.goto('/index.html');
    const form = page.locator('#booking-form');
    await expect(form).toHaveAttribute('method', 'post');
    await expect(form).toHaveAttribute('action', /\/wp-admin\/admin-post\.php$/);
    await expect(form.locator('input[name="action"]')).toHaveValue('dh_booking');
    for (const n of ['dh_name', 'dh_phone', 'dh_email', 'dh_treatment', 'dh_message', 'dh_website']) {
      await expect(form.locator(`[name="${n}"]`), n).toHaveCount(1);
    }
    await expect(form.locator('[name="dh_name"]')).toHaveAttribute('required', '');
    await expect(form.locator('[name="dh_phone"]')).toHaveAttribute('required', '');
    // honeypot is out of sight for people
    const box = await form.locator('.hp-field').boundingBox();
    expect(box === null || box.x < 0 || box.height === 0).toBe(true);
  });

  test('every form control has a label', async ({ page }) => {
    await page.goto('/index.html');
    const bad = await page.$$eval('#booking-form input:not([type=hidden]), #booking-form select, #booking-form textarea',
      (els) => els.filter((e) => !document.querySelector(`label[for="${e.id}"]`)).map((e) => e.outerHTML));
    expect(bad).toEqual([]);
  });

  test('booking status messages show after redirect', async ({ page }) => {
    await page.goto('/sent.html');
    await expect(page.locator('#form-note')).toContainText('Your request has been sent');
    await page.goto('/invalid.html');
    await expect(page.locator('#form-note')).toContainText('name and phone');
    await expect(page.locator('#form-note')).toHaveClass(/is-error/);
    await page.goto('/index.html');
    await expect(page.locator('#form-note')).toHaveCount(0);
  });

  test('clinic details come from the Customizer and hide when empty', async ({ page }) => {
    await page.goto('/index.html');
    await expect(page.locator('.contact-lines')).toHaveCount(0);
    await expect(page.locator('.hours')).toHaveCount(0);
    await expect(page.locator('body')).not.toContainText('to be added');

    await page.goto('/details.html');
    await expect(page.locator('.contact-lines')).toContainText('+20 100 000 0000');
    await expect(page.locator('.contact-lines a[href^="tel:"]')).toHaveAttribute('href', 'tel:+201000000000');
    await expect(page.locator('.contact-lines')).toContainText('hello@example.test');
    await expect(page.locator('.contact-lines')).toContainText('Cairo');
    await expect(page.locator('.hours li')).toHaveCount(2);
    await expect(page.locator('.topbar')).toContainText('Sat – Thu · 10:00 – 21:00');

    await page.goto('/admin.html');
    await expect(page.locator('main')).toContainText('Appearance → Customize → Clinic details');
  });

  test('404 template renders inside the site chrome', async ({ page }) => {
    await page.goto('/404.html');
    await expect(page.getByRole('heading', { name: 'Page not found' })).toBeVisible();
    await expect(page.locator('.site-header')).toBeVisible();
    await expect(page.locator('.footer')).toBeVisible();
  });
});
