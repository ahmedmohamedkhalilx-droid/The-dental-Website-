// @ts-check
const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

const SLUGS = Object.keys(JSON.parse(fs.readFileSync(path.join(__dirname, 'fixtures/source-strings.json'), 'utf8')));
const file = (slug) => (slug === 'home' ? '/index.html' : `/${slug}.html`);
const SERVICES = ['comprehensive-oral-examination', 'dental-cleaning', 'tooth-colored-restorations', 'crowns-bridges', 'dental-implants', 'orthodontics-clear-aligners', 'simple-wisdom-tooth-extractions', 'root-canal-therapy', 'kids-dentistry', 'teeth-whitening'];

test.describe('every page of the old site exists in the theme', () => {
  test('21 pages are rendered', () => {
    expect(SLUGS.length).toBe(21);
  });

  for (const slug of SLUGS) {
    test(`${slug}: loads cleanly, one h1, no sideways scroll, images keep their shape, anchors resolve`, async ({ page }) => {
      const errors = [];
      const bad = [];
      page.on('pageerror', (e) => errors.push(String(e)));
      page.on('response', (r) => { if (r.status() >= 400 && !r.url().endsWith('.mp4')) bad.push(`${r.status()} ${r.url()}`); });
      await page.goto(file(slug), { waitUntil: 'networkidle' });
      expect(errors).toEqual([]);
      expect(bad).toEqual([]);
      await expect(page.locator('h1')).toHaveCount(1);
      await expect(page.locator('h1')).not.toBeEmpty();

      const w = await page.evaluate(() => [document.documentElement.scrollWidth, document.documentElement.clientWidth]);
      expect(w[0], 'horizontal scroll').toBeLessThanOrEqual(w[1]);

      const imgs = await page.$$eval('img', (list) => list.map((i) => {
        const r = i.getBoundingClientRect();
        return { src: i.getAttribute('src'), ok: i.complete && i.naturalWidth > 0, fit: getComputedStyle(i).objectFit, ratio: r.height / r.width, natural: i.naturalHeight / i.naturalWidth, shown: r.width > 0 };
      }));
      for (const i of imgs) {
        expect(i.ok, `broken: ${i.src}`).toBe(true);
        if (i.shown && i.fit !== 'cover' && i.fit !== 'contain') expect(Math.abs(i.ratio / i.natural - 1), `distorted: ${i.src}`).toBeLessThan(0.02);
        if (i.fit === 'cover') expect(i.ratio, `frame too tall: ${i.src}`).toBeLessThanOrEqual(1.6);
      }

      const hrefs = await page.$$eval('a[href^="#"]', (as) => [...new Set(as.map((a) => a.getAttribute('href')))]);
      for (const h of hrefs) expect(await page.locator(`[id="${h.slice(1)}"]`).count(), `missing target ${h}`).toBeGreaterThan(0);
    });
  }
});

test.describe('header, menu and footer', () => {
  test('menu has the old site\'s items in order, and Services lists all ten treatments', async ({ page }, info) => {
    await page.goto('/index.html');
    const top = await page.$$eval('#site-nav > ul > li > a', (as) => as.map((a) => a.textContent.trim()));
    expect(top).toEqual(['Home', 'About Us', 'Services', 'Testimonial', "FAQ's", 'Forms', 'Contact Us']);
    const subs = await page.$$eval('#site-nav .sub-menu a', (as) => as.map((a) => a.textContent.trim()));
    expect(subs).toEqual(['Comprehensive Oral Examination', 'Dental Cleaning', 'Tooth-Colored Restorations', 'Crowns & Bridges', 'Dental Implants', 'Orthodontics & Clear Aligners', 'Simple & Wisdom Tooth Extractions', 'Root Canal Therapy', 'Kids Dentistry', 'Teeth whitening'].map((s) => s === 'Teeth whitening' ? 'Teeth whitening' : s));
    if (info.project.name === 'mobile') {
      await expect(page.locator('#site-nav')).toBeHidden();
      await page.click('#menu-toggle');
      await expect(page.locator('#site-nav')).toBeVisible();
      await expect(page.locator('#site-nav .sub-menu')).toBeHidden();
      await page.locator('.sub-toggle').click();
      await expect(page.locator('#site-nav .sub-menu a').first()).toBeVisible();
      await expect(page.locator('#site-nav .sub-menu a')).toHaveCount(10);
    } else {
      await expect(page.locator('#site-nav .sub-menu')).toBeHidden();
      await page.getByRole('link', { name: 'Services', exact: true }).first().hover();
      await expect(page.locator('#site-nav .sub-menu')).toBeVisible();
    }
  });

  test('top bar, header button and footer carry the real contact details', async ({ page }) => {
    await page.goto('/home-default.html');
    await expect(page.locator('.topbar')).toContainText('8321 Kennedy Rd #22, Markham, ON L3R 5N4');
    await expect(page.locator('.topbar')).toContainText('thedentalhauz@gmail.com');
    await expect(page.locator('.topbar a[href*="facebook.com"]')).toHaveCount(1);
    await expect(page.locator('.topbar a[href*="instagram.com/thedental.hauz"]')).toHaveCount(1);
    await expect(page.locator('.site-header a.btn')).toHaveAttribute('href', 'tel:9054704289');
    await expect(page.locator('.site-header a.btn')).toHaveText('(905) 470-4289');
    const foot = page.locator('.footer');
    await expect(foot).toContainText('The Dental Hauz offers trusted dental care in Kennedy Road, Markham, ON.');
    for (const t of ['Important Link', 'Our Services', 'Get in Touch', 'Working Hours', 'Monday – Friday', '10:00 AM – 6:00 PM', 'Saturday', '9:00 AM – 6:00 PM', 'Sunday', 'Closed']) await expect(foot).toContainText(t);
    await expect(foot.locator('a[href="https://clinic.test/forms/"]')).toHaveCount(1);
    await expect(foot.locator('ul').nth(1).locator('a')).toHaveCount(10);
    await expect(page.locator('body')).not.toContainText(/lorem|dentisto|to be added|king street melbourne/i);
  });

  test('details come from the Customizer when changed, and disappear when emptied', async ({ page }) => {
    await page.goto('/home-custom.html');
    await expect(page.locator('.topbar')).toContainText('1 Test St, Toronto, ON');
    await expect(page.locator('.topbar')).toContainText('front@example.test');
    await expect(page.locator('.site-header a.btn')).toHaveAttribute('href', 'tel:4165550100');
    await expect(page.locator('.footer .hours li')).toHaveCount(2);
    await page.goto('/home-empty.html');
    await expect(page.locator('.quick')).toHaveCount(0);
    await expect(page.locator('.site-header a.btn')).toHaveCount(0);
    await expect(page.locator('.topbar a')).toHaveCount(0);
    await expect(page.locator('.footer .hours')).toHaveCount(0);
  });
});

test.describe('home page', () => {
  test('carries the old home page copy and sections', async ({ page }) => {
    await page.goto('/index.html');
    await expect(page.locator('h1')).toHaveText('Healthy. Everyday.');
    await expect(page.locator('.hero')).toContainText('Trusted Dentist in Markham, Ontario');
    await expect(page.locator('.hero-actions a.btn').first()).toHaveText('Make Appointment');
    await expect(page.locator('.hero-actions a.btn').first()).toHaveAttribute('href', '#book');
    await expect(page.locator('.hero-actions a.btn').nth(1)).toHaveAttribute('href', 'tel:9054704289');
    await expect(page.locator('.quick__item')).toHaveCount(3);
    await expect(page.locator('.quick')).toContainText('(905) 470-4289');
    await expect(page.locator('.quick')).toContainText('8321 Kennedy Rd #22, Markham, ON L3R 5N4');
    await expect(page.locator('.quick')).toContainText('Monday – Friday: 10:00 AM – 6:00 PM');
    await expect(page.locator('.quick')).toContainText('Sunday: Closed');
    await expect(page.locator('.video video')).toHaveCount(1);
    await expect(page.locator('.video figcaption')).toHaveText('Watch Our Video');
    await expect(page.locator('.svc')).toHaveCount(10);
    await expect(page.locator('.svc__num').first()).toHaveText('01');
    await expect(page.locator('.svc__num').last()).toHaveText('10');
    await expect(page.locator('.cta-band a').first()).toHaveAttribute('href', 'tel:9054704289');
    await expect(page.locator('.cta-band a').nth(1)).toHaveAttribute('href', 'sms:9054704289');
    await expect(page.locator('.programs li')).toHaveCount(8);
    await expect(page.locator('.devices .box')).toHaveCount(3);
    await expect(page.locator('.faq details')).toHaveCount(4);
    await expect(page.locator('.reviews__widget')).toContainText('Google reviews widget');
    await expect(page.locator('#book .form')).toBeVisible();
  });

  test('without the video file, a photo takes its place; without the reviews plugin, that block is omitted', async ({ page }) => {
    await page.goto('/home-novideo.html');
    await expect(page.locator('.video')).toHaveCount(0);
    await expect(page.locator('.reviews')).toHaveCount(0);
  });

  test('service cards link to the treatment pages', async ({ page }) => {
    await page.goto('/index.html');
    const links = await page.$$eval('.svc h3 a', (as) => as.map((a) => a.getAttribute('href')));
    expect(links).toEqual(SERVICES.map((s) => `https://clinic.test/${s}/`));
  });

  test('FAQ accordion opens and closes', async ({ page }) => {
    await page.goto('/index.html');
    const item = page.locator('.faq details').nth(1);
    await item.locator('summary').click();
    await expect(item).toHaveAttribute('open', '');
    await item.locator('summary').click();
    await expect(item).not.toHaveAttribute('open', '');
  });
});

test.describe('inner pages', () => {
  test('every treatment page has the intro, "what to expect" and care sections and a booking form', async ({ page }) => {
    for (const s of SERVICES) {
      await page.goto(file(s));
      await expect(page.locator('.crumbs')).toContainText('Services');
      await expect(page.locator('.service-intro .box')).toHaveCount(2);
      await expect(page.locator('.expect .box')).toHaveCount(4);
      await expect(page.locator('.service-care .box')).toHaveCount(1);
      await expect(page.locator('.service-intro .split__media img')).toHaveCount(2);
      await expect(page.locator('#book .form')).toBeVisible();
    }
  });

  test('about: counters hidden by default, shown when switched on', async ({ page }) => {
    await page.goto('/about-default.html');
    await expect(page.locator('.stats')).toHaveCount(0);
    await page.goto('/about-us.html');
    await expect(page.locator('.stats dd')).toHaveText(['875+', '115+', '95+', '79+']);
    await expect(page.locator('.mission')).toContainText('Our Mission');
    await expect(page.locator('.about-intro .box')).toHaveCount(2);
  });

  test('contact: details, hours, map and form', async ({ page }) => {
    await page.goto('/contact-us.html');
    const cards = page.locator('.contact-card');
    await expect(cards).toHaveCount(4);
    await expect(cards.nth(0)).toContainText('thedentalhauz@gmail.com');
    await expect(cards.nth(1)).toContainText('8321 Kennedy Rd #22');
    await expect(cards.nth(2)).toContainText('(905) 470-4289');
    await expect(cards.nth(3)).toContainText('Monday – Friday: 10:00 AM – 6:00 PM');
    await expect(cards.nth(3)).toContainText('Sunday: Closed');
    const src = await page.locator('.map iframe').getAttribute('src');
    expect(decodeURIComponent(src)).toContain('The Dental Hauz 8321 Kennedy Rd #22, Markham, ON L3R 5N4, Canada');
    await expect(page.locator('#book .eyebrow')).toHaveText('Write To Our Dental Office');
    await expect(page.locator('#book h2')).toHaveText('Contact Our Friendly Reception Staff with any Enquiry');
  });

  test('forms index links to the four forms', async ({ page }) => {
    await page.goto('/forms.html');
    const links = await page.$$eval('.form-card h3', (hs) => hs.map((h) => h.textContent.trim()));
    expect(links).toEqual(['Medical History Form', 'Request for Records', 'Dental Treatment Payment Agreement', 'Payment Agreement Form']);
    const hrefs = await page.$$eval('.form-card a.btn', (as) => as.map((a) => a.getAttribute('href')));
    expect(hrefs).toEqual(['medical-history-form', 'request-for-records', 'dental-treatment-payment-agreement', 'payment-agreement'].map((s) => `https://clinic.test/${s}/`));
  });

  test('FAQ page: its six questions', async ({ page }) => {
    await page.goto('/faqs.html');
    await expect(page.locator('.faq details')).toHaveCount(6);
  });

  test('testimonial page: reviews widget, or an admin note when the plugin is off', async ({ page }) => {
    await page.goto('/testimonial.html');
    await expect(page.locator('.reviews__widget')).toContainText('Google reviews widget');
    await page.goto('/testimonial-noplugin-admin.html');
    await expect(page.locator('.admin-note')).toContainText('Trustindex');
  });

  test('a page that is not from the old site shows its editor content', async ({ page }) => {
    await page.goto('/generic.html');
    await expect(page.locator('.entry')).toContainText('Editor content');
  });

  test('404 page sits inside the site chrome', async ({ page }) => {
    await page.goto('/404.html');
    await expect(page.getByRole('heading', { name: 'Page not found' })).toBeVisible();
    await expect(page.locator('.site-header')).toBeVisible();
  });
});

test.describe('forms', () => {
  test('booking form: fields, options and submit target', async ({ page }) => {
    await page.goto('/index.html');
    const form = page.locator('#form-booking');
    await expect(form).toHaveAttribute('method', 'post');
    await expect(form).toHaveAttribute('action', /\/wp-admin\/admin-post\.php$/);
    await expect(form.locator('input[name="action"]')).toHaveValue('dh_form');
    await expect(form.locator('input[name="dh_form"]')).toHaveValue('booking');
    const opts = async (n) => form.locator(`select[name="${n}"] option`).allTextContents();
    expect(await opts('dh_service')).toEqual(['Select', 'New Patient Exam', 'Cleaning', 'Emergency Exam', 'Fillings', 'Extraction', 'Invisalign / Orthodontics', 'Teeth Whitening', 'Other']);
    expect(await opts('dh_day')).toEqual(['Select', 'Monday', 'Wednesday', 'Friday', 'Saturday']);
    expect(await opts('dh_time')).toEqual(['Select', 'Morning', 'Afternoon', 'Evening', 'No Preference']);
    for (const n of ['dh_first_name', 'dh_phone', 'dh_email', 'dh_service', 'dh_day', 'dh_time']) await expect(form.locator(`[name="${n}"]`)).toHaveAttribute('required', '');
    await expect(form.locator('[name="dh_last_name"]')).not.toHaveAttribute('required', '');
    await expect(form.getByRole('button', { name: 'Book Appointment' })).toBeVisible();
    const box = await form.locator('.hp-field').boundingBox();
    expect(box === null || box.x < 0 || box.height === 0).toBe(true);
  });

  test('every control on every form page has a label', async ({ page }) => {
    for (const s of ['index', 'contact-us', 'medical-history-form', 'request-for-records', 'dental-treatment-payment-agreement', 'payment-agreement']) {
      await page.goto(`/${s}.html`);
      const bad = await page.$$eval('form input:not([type=hidden]), form select, form textarea',
        (els) => els.filter((e) => !document.querySelector(`label[for="${e.id}"]`)).map((e) => e.outerHTML));
      expect(bad, s).toEqual([]);
    }
  });

  test('medical history form has all its sections and questions', async ({ page }) => {
    await page.goto('/medical-history-form.html');
    await expect(page.locator('.page-hero')).toContainText('8321 Kennedy Rd #22, Markham, ON L3R 5N4');
    await expect(page.locator('.page-hero')).toContainText('(905) 470-4289');
    await expect(page.locator('.form-heading')).toHaveText(['About you', 'In case of emergency, we should notify', 'Your doctors and insurance', 'Your health', 'Consent']);
    await expect(page.locator('fieldset.field--choices').filter({ has: page.locator('input[type=radio]') })).toHaveCount(16);
    await expect(page.locator('input[name="dh_conditions[]"]')).toHaveCount(21);
    await expect(page.locator('input[name="dh_consent"]')).toHaveAttribute('required', '');
    await expect(page.locator('.field--agree')).toContainText('Dr. Summer Al Maqdassy');
    await expect(page.getByRole('button', { name: 'Submit' })).toBeVisible();
    // "If yes" follow-ups are not required
    await expect(page.locator('input[name$="_more"][required]')).toHaveCount(0);
  });

  test('records and payment pages show their terms and fields', async ({ page }) => {
    await page.goto('/request-for-records.html');
    await expect(page.locator('input[name="dh_records[]"]')).toHaveCount(6);
    await expect(page.getByRole('button', { name: 'Send' })).toBeVisible();
    await page.goto('/dental-treatment-payment-agreement.html');
    await expect(page.locator('.form-terms')).toContainText('A 3% additional charge applies');
    await expect(page.locator('.form-address')).toContainText('8321 Kennedy Rd #22');
    await page.goto('/payment-agreement.html');
    await expect(page.locator('.form-terms')).toContainText('Your credit card will be securely kept on file');
    await expect(page.getByRole('button', { name: 'Submit' })).toBeVisible();
  });

  test('status messages show after the redirect', async ({ page }) => {
    await page.goto('/home-sent.html');
    await expect(page.locator('#form-booking .form-note')).toContainText('Your submission was successful');
    await page.goto('/home-invalid.html');
    await expect(page.locator('#form-booking .form-note')).toHaveClass(/is-error/);
    await page.goto('/index.html');
    await expect(page.locator('#form-booking .form-note')).toHaveCount(0);
  });
});

test.describe('treatment card motion', () => {
  const heights = (c) => c.evaluate((el) => ({
    inner: el.querySelector('.svc__details-inner').getBoundingClientRect().height,
    desc: getComputedStyle(el.querySelector('.svc__details')).opacity,
    title: el.querySelector('h3').getBoundingClientRect().height,
    lift: getComputedStyle(el).transform,
  }));

  test('at rest only the number and name show; hover reveals the description and Learn More', async ({ page }, info) => {
    test.skip(info.project.name === 'mobile', 'hover is a desktop gesture');
    await page.goto('/our-services.html', { waitUntil: 'networkidle' });
    const card = page.locator('.svc').first();
    await card.scrollIntoViewIfNeeded();
    await expect(card).not.toHaveClass(/will-reveal/, { timeout: 6000 });
    await page.mouse.move(2, 2);
    await page.waitForTimeout(700);
    const rest = await heights(card);
    expect(rest.inner).toBeLessThan(2);
    expect(rest.desc).toBe('0');
    expect(rest.title).toBeGreaterThan(20);
    expect(rest.lift).toBe('none');
    await card.hover();
    await page.waitForTimeout(900);
    const up = await heights(card);
    expect(up.inner).toBeGreaterThan(40);
    expect(up.desc).toBe('1');
    expect(up.lift).not.toBe('none');
    await expect(card.locator('.more')).toBeVisible();
    await expect(card.locator('p')).toContainText('Detect dental concerns early');
  });

  test('keyboard focus reveals the details too', async ({ page }, info) => {
    test.skip(info.project.name === 'mobile', 'keyboard');
    await page.goto('/our-services.html', { waitUntil: 'networkidle' });
    const card = page.locator('.svc').nth(1);
    await card.scrollIntoViewIfNeeded();
    await expect(card).not.toHaveClass(/will-reveal/, { timeout: 6000 });
    await card.locator('h3 a').focus();
    await page.waitForTimeout(900);
    expect((await heights(card)).inner).toBeGreaterThan(40);
  });

  test('the whole card is one link to its treatment page', async ({ page }, info) => {
    test.skip(info.project.name === 'mobile', 'desktop click');
    await page.goto('/our-services.html', { waitUntil: 'networkidle' });
    const card = page.locator('.svc').first();
    await card.scrollIntoViewIfNeeded();
    await expect(card).not.toHaveClass(/will-reveal/, { timeout: 6000 });
    const box = await card.boundingBox();
    const hit = await page.evaluate(([x, y]) => document.elementFromPoint(x, y).closest('a')?.getAttribute('href'), [box.x + box.width / 2, box.y + box.height / 3]);
    expect(hit).toBe('https://clinic.test/comprehensive-oral-examination/');
  });

  test('on touch: first tap shows the details, second tap follows the link', async ({ page }, info) => {
    test.skip(info.project.name !== 'mobile', 'touch only');
    await page.goto('/our-services.html', { waitUntil: 'networkidle' });
    const card = page.locator('.svc').first();
    await card.scrollIntoViewIfNeeded();
    await expect(card).not.toHaveClass(/will-reveal/, { timeout: 6000 });
    expect((await heights(card)).inner).toBeLessThan(2);
    await card.locator('h3').tap();
    await expect(card).toHaveClass(/is-open/);
    await page.waitForTimeout(900);
    expect((await heights(card)).inner).toBeGreaterThan(40);
    expect(page.url()).toContain('/our-services.html');
    const other = page.locator('.svc').nth(1);
    await other.scrollIntoViewIfNeeded();
    await expect(other).not.toHaveClass(/will-reveal/, { timeout: 6000 });
    await other.locator('h3').tap();
    await expect(other).toHaveClass(/is-open/);
    await expect(card).not.toHaveClass(/is-open/);
  });

  test('cards below the fold slide in as you scroll and all end up visible', async ({ page }) => {
    await page.goto('/our-services.html', { waitUntil: 'networkidle' });
    const last = page.locator('.svc').last();
    await expect(last).toHaveClass(/will-reveal/);
    await last.scrollIntoViewIfNeeded();
    await expect.poll(() => last.evaluate((c) => getComputedStyle(c).opacity)).toBe('1');
    // after arriving, the card goes back to normal styles so hover motion works
    await expect(last).not.toHaveClass(/will-reveal/, { timeout: 5000 });
    await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
    await expect.poll(async () => page.$$eval('.svc', (cs) => cs.filter((c) => getComputedStyle(c).opacity !== '1').length), { timeout: 6000 }).toBe(0);
  });

  test('a safety timer shows every card even if the visitor never scrolls', async ({ page }) => {
    await page.goto('/our-services.html', { waitUntil: 'networkidle' });
    await expect.poll(async () => page.$$eval('.svc', (cs) => cs.filter((c) => getComputedStyle(c).opacity !== '1').length), { timeout: 8000 }).toBe(0);
  });
});

test.describe('treatment cards without script or with reduced motion', () => {
  test.describe('script off', () => {
    test.use({ javaScriptEnabled: false });
    test('every card is visible', async ({ page }) => {
      await page.goto('/our-services.html');
      expect(await page.$$eval('.svc', (cs) => cs.filter((c) => getComputedStyle(c).opacity !== '1' || c.classList.contains('will-reveal')).length)).toBe(0);
    });
  });
  test.describe('reduced motion', () => {
    test.use({ reducedMotion: 'reduce' });
    test('no card is hidden or animated in', async ({ page }) => {
      await page.goto('/our-services.html', { waitUntil: 'networkidle' });
      expect(await page.$$eval('.svc', (cs) => cs.filter((c) => c.classList.contains('will-reveal')).length)).toBe(0);
    });
  });
});
