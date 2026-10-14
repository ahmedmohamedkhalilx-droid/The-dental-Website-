// @ts-check
const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');

const SLUGS = Object.keys(JSON.parse(fs.readFileSync(path.join(__dirname, 'fixtures/source-strings.json'), 'utf8')));
const DOCTORS = ['dr-rosemarie-sia', 'dr-sarah-assaya', 'dr-sherief-mohamed'];
const ALL = SLUGS.concat(['doctors'], DOCTORS);
const file = (slug) => (slug === 'home' ? '/index.html' : `/${slug}.html`);
const SERVICES = ['comprehensive-oral-examination', 'dental-cleaning', 'tooth-colored-restorations', 'crowns-bridges', 'dental-implants', 'orthodontics-clear-aligners', 'simple-wisdom-tooth-extractions', 'root-canal-therapy', 'kids-dentistry', 'teeth-whitening'];

test.describe('every page of the old site exists in the theme', () => {
  test('21 pages are rendered', () => {
    expect(SLUGS.length).toBe(21);
  });

  for (const slug of ALL) {
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

      // Scroll through like a visitor so lazy-loaded photos further down actually load, then check them.
      await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 400) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 30)); } window.scrollTo(0, 0); });
      await page.waitForLoadState('networkidle');
      await page.waitForFunction(() => [...document.images].every((i) => i.complete));
      const imgs = await page.$$eval('img', (list) => list.map((i) => {
        const r = i.getBoundingClientRect();
        return { src: i.getAttribute('src'), ok: i.complete && i.naturalWidth > 0, fit: getComputedStyle(i).objectFit, ratio: r.height / r.width, natural: i.naturalHeight / i.naturalWidth, shown: r.width > 0 };
      }));
      for (const i of imgs) {
        expect(i.ok, `broken: ${i.src}`).toBe(true);
        if (i.shown && i.fit !== 'cover' && i.fit !== 'contain') expect(Math.abs(i.ratio / i.natural - 1), `distorted: ${i.src}`).toBeLessThan(0.02);
        if (i.shown && i.fit === 'cover') expect(i.ratio, `frame too tall: ${i.src}`).toBeLessThanOrEqual(1.6);
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
    expect(top).toEqual(['Home', 'About Us', 'Doctors', 'Services', 'Testimonial', "FAQ's", 'Forms', 'Contact Us']);
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

  test('no top bar; header has round Facebook and Instagram icons and a Book Appointment button', async ({ page }, info) => {
    await page.goto('/home-default.html');
    await expect(page.locator('.topbar')).toHaveCount(0);
    const header = page.locator('.site-header');
    if (info.project.name === 'mobile') await page.click('#menu-toggle');
    await expect(header.locator('.social a[href*="facebook.com"]')).toHaveAttribute('aria-label', 'Facebook');
    await expect(header.locator('.social a[href*="instagram.com/thedental.hauz"]')).toHaveAttribute('aria-label', 'Instagram');
    await expect(header.locator('.social svg')).toHaveCount(2);
    const hy = await header.locator('.social__link').evaluateAll((as) => as.map((a) => Math.round(a.getBoundingClientRect().top)));
    expect(hy[0], 'header icons sit side by side').toBe(hy[1]);
    await expect(header.locator('.social a')).toHaveText(['', '']);
    const btn = header.locator('a.btn');
    await expect(btn).toHaveText('Book Appointment');
    await expect(btn).toHaveAttribute('href', '#book');
    await expect(header.locator('a.btn[href^="tel:"]')).toHaveCount(0);
  });

  test('social icons have no frame, are a usable size and sit on the bar centre line', async ({ page }, info) => {
    test.skip(info.project.name === 'mobile', 'desktop header');
    await page.goto('/home-default.html');
    const m = await page.evaluate(() => {
      const mid = (e) => { const r = e.getBoundingClientRect(); return r.top + r.height / 2; };
      const links = [...document.querySelectorAll('.site-header .social__link')];
      const btn = document.querySelector('.site-header a.btn');
      const menu = document.querySelector('#site-nav > ul > li > a');
      return {
        borders: links.map((a) => getComputedStyle(a).borderTopWidth + '/' + getComputedStyle(a).backgroundColor),
        sizes: links.map((a) => Math.round(a.getBoundingClientRect().width)),
        iconMids: links.map(mid), btnMid: mid(btn), menuMid: mid(menu),
      };
    });
    for (const b of m.borders) expect(b).toBe('0px/rgba(0, 0, 0, 0)');
    for (const w of m.sizes) expect(w).toBeGreaterThanOrEqual(28);
    for (const y of m.iconMids) { expect(Math.abs(y - m.btnMid), 'centred on the button').toBeLessThanOrEqual(1.5); expect(Math.abs(y - m.menuMid), 'centred on the menu text').toBeLessThanOrEqual(2.5); }
  });

  test('header button goes to the booking form on this page, or on the home page where a page has none', async ({ page }) => {
    await page.goto('/contact-us.html');
    await expect(page.locator('.site-header a.btn')).toHaveAttribute('href', '#book');
    await page.goto('/medical-history-form.html');
    await expect(page.locator('.site-header a.btn')).toHaveAttribute('href', 'https://clinic.test/#book');
    await page.goto('/forms.html');
    await expect(page.locator('.site-header a.btn')).toHaveAttribute('href', 'https://clinic.test/#book');
  });

  test('footer carries the real contact details and the same icons', async ({ page }) => {
    await page.goto('/home-default.html');
    const foot = page.locator('.footer');
    await expect(foot).toContainText('The Dental Hauz offers trusted dental care in Kennedy Road, Markham, ON.');
    for (const t of ['Important Link', 'Our Services', 'Get in Touch', 'Working Hours', 'Monday', '10:00 AM – 6:00 PM', 'Tuesday', '12:00 PM – 4:00 PM', 'Thursday', 'Saturday', 'Last Saturday of the month, 9:00 AM – 6:00 PM', 'Sunday', 'Closed', '(905) 470-4289', 'thedentalhauz@gmail.com', '8321 Kennedy Rd #22, Markham, ON L3R 5N4']) await expect(foot).toContainText(t);
    await expect(foot.locator('.social__link')).toHaveCount(2);
    const fy = await foot.locator('.social__link').evaluateAll((as) => as.map((a) => Math.round(a.getBoundingClientRect().top)));
    expect(fy[0], 'footer icons sit side by side').toBe(fy[1]);
    expect(await foot.locator('.social__link').first().evaluate((a) => getComputedStyle(a).borderTopWidth)).toBe('0px');
    await expect(foot.locator('a[href="https://clinic.test/forms/"]')).toHaveCount(1);
    await expect(foot.locator('ul').nth(1).locator('a')).toHaveCount(10);
    await expect(page.locator('body')).not.toContainText(/lorem|dentisto|to be added|king street melbourne/i);
  });

  test('details come from the Customizer when changed, and disappear when emptied', async ({ page }) => {
    await page.goto('/home-custom.html');
    await expect(page.locator('.quick')).toContainText('front@example.test');
    await expect(page.locator('.quick a[href="mailto:front@example.test"]')).toHaveCount(1);
    await expect(page.locator('.footer')).toContainText('front@example.test');
    await expect(page.locator('.footer')).toContainText('1 Test St, Toronto, ON');
    await expect(page.locator('.footer .hours li')).toHaveCount(2);
    await page.goto('/home-empty.html');
    await expect(page.locator('.quick')).toHaveCount(0);
    await expect(page.locator('.social')).toHaveCount(0);
    await expect(page.locator('.footer .hours')).toHaveCount(0);
    await expect(page.locator('.site-header a.btn')).toHaveText('Book Appointment');
  });
});

test.describe('home page', () => {
  test('carries the old home page copy and sections', async ({ page }) => {
    await page.goto('/index.html');
    await expect(page.locator('h1')).toHaveText('Healthy. Everyday.');
    await expect(page.locator('.hero')).toContainText('Trusted Dentist in Markham, Ontario');
    await expect(page.locator('.hero-actions a.btn').first()).toHaveText('Request Appointment');
    await expect(page.locator('.hero-actions a.btn').first()).toHaveAttribute('href', '#book');
    await expect(page.locator('.hero-actions a.btn').nth(1)).toHaveAttribute('href', 'tel:9054704289');
    await expect(page.locator('.quick__item')).toHaveCount(2);
    await expect(page.locator('.quick__label')).toHaveText(['Contact Us', 'Location']);
    await expect(page.locator('.quick')).not.toContainText('(905) 470-4289');
    await expect(page.locator('.quick a[href^="tel:"]')).toHaveCount(0);
    await expect(page.locator('.quick')).toContainText('thedentalhauz@gmail.com');
    await expect(page.locator('.quick')).toContainText('8321 Kennedy Rd #22, Markham, ON L3R 5N4');
    await expect(page.locator('.quick')).not.toContainText('Working Hours');
    await expect(page.locator('video')).toHaveCount(0);
    await expect(page.locator('.intro__media img')).toHaveCount(1);
    await expect(page.locator('.svc')).toHaveCount(10);
    await expect(page.locator('.svc__num').first()).toHaveText('01');
    await expect(page.locator('.svc__num').last()).toHaveText('10');
    await expect(page.locator('.cta-band a').first()).toHaveAttribute('href', 'tel:9054704289');
    await expect(page.locator('.cta-band a').nth(1)).toHaveAttribute('href', 'sms:9054704289');
    await expect(page.locator('.programs li')).toHaveCount(8);
    await expect(page.locator('.devices .box')).toHaveCount(3);
    await expect(page.locator('.faq details')).toHaveCount(4);
    await expect(page.locator('.reviews--compact .reviews__widget')).toContainText('Google reviews widget');
    await expect(page.locator('.reviews--compact h2')).toHaveText('Discover Why Patients Trust The Dental Hauz for Care');
    await expect(page.locator('.reviews--compact .reviews__all a')).toHaveAttribute('href', 'https://clinic.test/testimonial/');
    await expect(page.locator('#book .form')).toBeVisible();
  });

  test('the home page has no video; without the reviews plugin the built-in Google reviews are shown', async ({ page }) => {
    await page.goto('/home-novideo.html');
    await expect(page.locator('.video')).toHaveCount(0);
    await expect(page.locator('.reviews--compact .ti-widget')).toHaveCount(0);
    await expect(page.locator('.reviews--compact .rv__card')).toHaveCount(3);
    await expect(page.locator('.reviews--compact .rv__count')).toHaveText('66 Google reviews');
  });

  test('home uses its own (smaller) widget shortcode; only Trustindex shortcodes are accepted', async ({ page }) => {
    await page.goto('/home-custom.html');
    await expect(page.locator('.reviews--compact .ti-widget')).toHaveAttribute('data-shortcode', '[trustindex data-widget-id=compact123]');
    // the full-size setting held something that is not a Trustindex shortcode, so the default is used instead
    await page.goto('/testimonial-custom.html');
    await expect(page.locator('.ti-widget')).toHaveAttribute('data-shortcode', '[trustindex no-registration=google]');
  });

  test('the compact home block is smaller than the full reviews block on the testimonial page', async ({ page }) => {
    await page.goto('/index.html');
    const home = await page.locator('.reviews--compact').boundingBox();
    const headingH = await page.locator('.reviews--compact h2').evaluate((e) => parseFloat(getComputedStyle(e).fontSize));
    await page.goto('/testimonial.html');
    const full = await page.locator('.reviews').first().boundingBox();
    const fullH = await page.locator('.reviews h2').first().evaluate((e) => parseFloat(getComputedStyle(e).fontSize));
    expect(headingH).toBeLessThan(fullH);
    const homePad = await page.goto('/index.html').then(() => page.locator('.reviews--compact').evaluate((e) => parseFloat(getComputedStyle(e).paddingTop)));
    const fullPad = await page.goto('/testimonial.html').then(() => page.locator('.reviews').first().evaluate((e) => parseFloat(getComputedStyle(e).paddingTop)));
    expect(homePad).toBeLessThan(fullPad);
    expect(home && full).toBeTruthy();
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
    await expect(cards.nth(3)).toContainText('Tuesday: 12:00 PM – 4:00 PM');
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
    await expect(page.locator('.reviews--compact')).toHaveCount(0);
    await expect(page.locator('.ti-widget')).toHaveAttribute('data-shortcode', '[trustindex no-registration=google]');
    await expect(page.locator('.reviews h2')).toContainText('Patient Experiences That Inspire Confidence');
    await page.goto('/testimonial-noplugin-admin.html');
    await expect(page.locator('.admin-note')).toContainText('Trustindex');
    await expect(page.locator('.rv__card')).toHaveCount(3);
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
    expect(await opts('dh_service')).toEqual(['Select', 'New Patient Exam', 'Comprehensive Oral Examination', 'Dental Cleaning', 'Tooth-Colored Restorations', 'Crowns & Bridges', 'Dental Implants', 'Orthodontics & Clear Aligners', 'Simple & Wisdom Tooth Extractions', 'Root Canal Therapy', 'Kids Dentistry', 'Teeth whitening', 'Emergency Exam', 'Other']);
    expect(await opts('dh_day')).toEqual(['Select', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']);
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
    await expect(page.locator('.field--agree')).toContainText('The Dental Hauz dentists or their staff');
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
    await page.setViewportSize({ width: 820, height: 1100 }); // tablets keep the two-tap reveal
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

test.describe('header proportions', () => {
  for (const w of [1241, 1280, 1366, 1440, 1536, 1920]) {
    test(`at ${w}px every menu item stays on one line and nothing overflows`, async ({ page }, info) => {
      test.skip(info.project.name === 'mobile', 'desktop widths');
      await page.setViewportSize({ width: w, height: 800 });
      await page.goto('/home-default.html', { waitUntil: 'networkidle' });
      const m = await page.evaluate(() => {
        const items = [...document.querySelectorAll('#site-nav > ul > li > a')].map((a) => Math.round(a.getBoundingClientRect().height));
        const btn = document.querySelector('.site-header a.btn').getBoundingClientRect();
        const hdr = document.querySelector('.site-header').getBoundingClientRect();
        const wrap = document.querySelector('.site-header .wrap');
        return { items, btnH: Math.round(btn.height), hdrH: Math.round(hdr.height), over: wrap.scrollWidth - wrap.clientWidth, toggle: getComputedStyle(document.querySelector('#menu-toggle')).display };
      });
      expect(m.toggle).toBe('none');
      expect(new Set(m.items).size, 'all menu links have the same height').toBe(1);
      expect(m.items[0], 'one line of text').toBeLessThan(40);
      expect(m.btnH, 'button is one line').toBeLessThan(60);
      expect(m.hdrH, 'header stays slim').toBeLessThan(90);
      expect(m.over).toBeLessThanOrEqual(0);
    });
  }
});

test.describe('doctors', () => {
  test('doctors page: a card per doctor, in order, each linking to the doctor\'s own page', async ({ page }) => {
    await page.goto('/doctors.html');
    await expect(page.locator('h1')).toHaveText('Doctors');
    await expect(page.locator('.doc-card h3 a')).toHaveText(['Dr. Rosemarie Sia', 'Dr. Sarah Assaya', 'Dr. Sherief Mohamed']);
    for (const [i, slug] of DOCTORS.entries()) await expect(page.locator('.doc-card h3 a').nth(i)).toHaveAttribute('href', `https://clinic.test/${slug}/`);
    await expect(page.locator('.doctors')).not.toContainText('coming soon');
    await expect(page.locator('#book .form')).toBeVisible();
  });

  test('doctor page: hero with breadcrumb, profile section and booking form', async ({ page }) => {
    await page.goto('/dr-sherief-mohamed.html');
    await expect(page.locator('h1')).toHaveText('Dr. Sherief Mohamed');
    await expect(page.locator('.crumbs')).toContainText('Doctors');
    await expect(page.locator('.doctor')).toContainText('sees patients at The Dental Hauz in Markham, Ontario.');
    await expect(page.locator('#book .form')).toBeVisible();
  });

  test('about page uses the new cover photo', async ({ page }) => {
    await page.goto('/about-us.html');
    await expect(page.locator('.page-hero__media img')).toHaveAttribute('src', /about-hero\.jpg$/);
  });

  test('Dr. Summer Al Maqdassy appears nowhere on the site', async ({ page }) => {
    for (const slug of ALL.concat(['index'])) {
      await page.goto(`/${slug}.html`);
      await expect(page.locator('body'), slug).not.toContainText(/summer|maqdassy/i);
    }
  });

  test('doctors are in the menu and the footer', async ({ page }) => {
    await page.goto('/index.html');
    await expect(page.locator('#site-nav > ul > li > a', { hasText: 'Doctors' })).toHaveAttribute('href', 'https://clinic.test/doctors/');
    await expect(page.locator('.footer a', { hasText: 'Doctors' })).toHaveCount(1);
  });
});

test.describe('google reviews by widget ID', () => {
  test('without the plugin, a Trustindex ID embeds the widget; a bad ID is ignored', async ({ page }) => {
    await page.goto('/testimonial-custom-noplugin.html');
    await expect(page.locator('script[src="https://cdn.trustindex.io/loader.js?abc123abc123abc123"]')).toHaveCount(1);
    await expect(page.locator('.rv')).toHaveCount(0);
    await page.goto('/testimonial-badid.html');
    await expect(page.locator('script[src*="cdn.trustindex.io"]')).toHaveCount(0);
    await expect(page.locator('.rv')).toHaveCount(1);
  });
});

test.describe('built-in Google reviews', () => {
  test('summary and review cards match the clinic\'s Google listing', async ({ page }) => {
    await page.goto('/testimonial-noplugin-admin.html');
    const sum = page.locator('.rv__summary');
    await expect(sum.locator('.rv__name')).toHaveText('The Dental Hauz');
    await expect(sum.locator('.rv__count')).toHaveText('66 Google reviews');
    await expect(sum.locator('.rv__star.is-on')).toHaveCount(5);
    await expect(sum.locator('a.btn')).toHaveText('Write a review');
    await expect(sum.locator('a.btn')).toHaveAttribute('href', 'https://maps.app.goo.gl/tq5NC39rXTekr9da9');
    const cards = page.locator('.rv__card');
    await expect(cards).toHaveCount(3);
    await expect(cards.nth(0)).toContainText('Kitty Tse');
    await expect(cards.nth(0)).toContainText('Staff are always friendly and helpful.');
    await expect(cards.nth(0).locator('.rv__more')).toHaveCount(0);
    await expect(cards.nth(1)).toContainText('B M');
    await expect(cards.nth(1)).toContainText('Dr. Sherief explained the pros and cons of veneers, crowns…');
    await expect(cards.nth(1).locator('.rv__more')).toHaveAttribute('href', 'https://maps.app.goo.gl/tq5NC39rXTekr9da9');
    await expect(cards.nth(2)).toContainText('Kelly F');
    await expect(cards.nth(2)).toContainText('Dr.Sherief is outstanding!');
    await expect(cards.locator('.rv__star.is-on')).toHaveCount(15);
    await expect(cards.locator('.rv__avatar').allTextContents()).resolves.toEqual(['K', 'B', 'K']);
  });

  test('cards are in one row, fit the page, and the row scrolls with arrows', async ({ page }) => {
    await page.goto('/testimonial-noplugin-admin.html', { waitUntil: 'networkidle' });
    const tops = await page.locator('.rv__card').evaluateAll((cs) => cs.map((c) => Math.round(c.getBoundingClientRect().top)));
    expect(new Set(tops).size, 'one row').toBe(1);
    const w = await page.evaluate(() => [document.documentElement.scrollWidth, document.documentElement.clientWidth]);
    expect(w[0]).toBeLessThanOrEqual(w[1]);
    const track = page.locator('.rv__track');
    expect(await track.evaluate((t) => t.scrollWidth - t.clientWidth), 'row is wider than the window, so it scrolls').toBeGreaterThan(20);
    await expect(page.locator('.rv__nav--next')).toBeVisible();
    await expect(page.locator('.rv__nav--prev')).toBeHidden();
    const before = await track.evaluate((t) => t.scrollLeft);
    await page.locator('.rv__nav--next').click();
    await expect.poll(() => track.evaluate((t) => t.scrollLeft)).toBeGreaterThan(before + 50);
    await expect(page.locator('.rv__nav--prev')).toBeVisible();
  });

  test('the row moves along by itself, and stops while the pointer is over it', async ({ page }, info) => {
    test.skip(info.project.name === 'mobile', 'pointer');
    await page.goto('/testimonial-noplugin-admin.html', { waitUntil: 'networkidle' });
    const track = page.locator('.rv__track');
    await page.locator('.rv__summary').scrollIntoViewIfNeeded();
    await page.mouse.move(2, 2);
    await expect.poll(() => track.evaluate((t) => t.scrollLeft), { timeout: 9000 }).toBeGreaterThan(50);
    // back to the start, then hover: it must not move
    await track.evaluate((t) => t.scrollTo({ left: 0, behavior: 'instant' }));
    await track.hover();
    await page.waitForTimeout(6500);
    expect(await track.evaluate((t) => t.scrollLeft)).toBeLessThan(5);
  });

  test('the row can be dragged with the mouse', async ({ page }, info) => {
    test.skip(info.project.name === 'mobile', 'mouse');
    await page.goto('/testimonial-noplugin-admin.html', { waitUntil: 'networkidle' });
    const track = page.locator('.rv__track');
    await track.scrollIntoViewIfNeeded();
    await track.evaluate((t) => t.scrollTo({ left: 0, behavior: 'instant' }));
    const box = await track.boundingBox();
    await page.mouse.move(box.x + box.width - 60, box.y + 120);
    await page.mouse.down();
    await page.mouse.move(box.x + box.width - 260, box.y + 120, { steps: 8 });
    await page.mouse.up();
    expect(await track.evaluate((t) => t.scrollLeft)).toBeGreaterThan(80);
  });

  test('with reduced motion the row does not move by itself', async ({ browser }) => {
    const ctx = await browser.newContext({ reducedMotion: 'reduce', viewport: { width: 1366, height: 900 } });
    const page = await ctx.newPage();
    await page.goto('http://127.0.0.1:4174/testimonial-noplugin-admin.html', { waitUntil: 'networkidle' });
    await page.waitForTimeout(6500);
    expect(await page.locator('.rv__track').evaluate((t) => t.scrollLeft)).toBeLessThan(5);
    await ctx.close();
  });

  test('on a phone the row scrolls sideways and the arrow appears', async ({ page }, info) => {
    test.skip(info.project.name !== 'mobile', 'phone only');
    await page.goto('/testimonial-noplugin-admin.html', { waitUntil: 'networkidle' });
    const next = page.locator('.rv__nav--next');
    await expect(next).toBeVisible();
    const before = await page.locator('.rv__track').evaluate((t) => t.scrollLeft);
    await next.click();
    await expect.poll(() => page.locator('.rv__track').evaluate((t) => t.scrollLeft)).toBeGreaterThan(before + 100);
    await expect(page.locator('.rv__nav--prev')).toBeVisible();
  });

  test('the compact home block uses the same reviews at a smaller size', async ({ page }) => {
    await page.goto('/home-novideo.html');
    const fs = await page.locator('.reviews--compact .rv__card p').first().evaluate((p) => parseFloat(getComputedStyle(p).fontSize));
    await page.goto('/testimonial-noplugin-admin.html');
    const full = await page.locator('.rv__card p').first().evaluate((p) => parseFloat(getComputedStyle(p).fontSize));
    expect(fs).toBeLessThan(full);
  });
});

test('the stylesheet uses only the five brand colours', async () => {
  const css = require('fs').readFileSync('wordpress-theme/the-dental-hauz/style.css', 'utf8');
  const brand = new Set(['#514a43', '#657568', '#a8b6a5', '#d8b8b2', '#f4f0e8']);
  const hex = (css.match(/#[0-9a-fA-F]{3,8}\b/g) || []).map((h) => h.toLowerCase());
  expect(hex.filter((h) => !brand.has(h))).toEqual([]);
  const brandRgb = new Set(['244, 240, 232', '81, 74, 67', '168, 182, 165', '101, 117, 104', '216, 184, 178']);
  const rgb = [...css.matchAll(/rgba?\(\s*(\d+,\s*\d+,\s*\d+)/g)].map((m) => m[1].replace(/\s+/g, ' '));
  expect(rgb.filter((c) => !brandRgb.has(c))).toEqual([]);
});

test('form text stays readable even if a page builder styles labels and inputs', async ({ page }) => {
  await page.goto('/index.html');
  await page.addStyleTag({ content: 'label{color:#fff !important} input,select{background:#fff !important}' });
  const labelColor = await page.locator('#book .field label').first().evaluate((e) => getComputedStyle(e).color);
  const fieldBg = await page.locator('#book .field input').first().evaluate((e) => getComputedStyle(e).backgroundColor);
  const btn = await page.locator('#book .form .btn').evaluate((e) => [getComputedStyle(e).backgroundColor, getComputedStyle(e).color]);
  expect(labelColor).not.toBe('rgb(255, 255, 255)');
  expect(fieldBg).toBe('rgb(244, 240, 232)');
  expect(btn).toEqual(['rgb(81, 74, 67)', 'rgb(244, 240, 232)']);
});

test('phones: treatments are a two-column grid of compact tiles and one tap opens the page', async ({ page }, info) => {
  test.skip(info.project.name !== 'mobile', 'phone only');
  await page.goto('/our-services.html', { waitUntil: 'networkidle' });
  const boxes = await page.locator('.svc').evaluateAll((els) => els.slice(0, 2).map((e) => e.getBoundingClientRect()).map((r) => ({ x: r.x, y: r.y, w: r.width, h: r.height })));
  expect(Math.abs(boxes[0].y - boxes[1].y)).toBeLessThan(2);
  expect(boxes[1].x).toBeGreaterThan(boxes[0].x + boxes[0].w);
  expect(boxes[0].h).toBeLessThan(260);
  const card = page.locator('.svc').first();
  await card.scrollIntoViewIfNeeded();
  await expect(card).not.toHaveClass(/will-reveal/, { timeout: 6000 });
  const nav = page.waitForRequest((r) => r.isNavigationRequest() && r.url().includes('/comprehensive-oral-examination/'));
  await card.locator('h3').tap();
  await nav;
});

test('phones: photos are never taller than they are wide and image pairs line up', async ({ page }, info) => {
  test.skip(info.project.name !== 'mobile', 'phone only');
  for (const url of ['/index.html', '/about-us.html']) {
    await page.goto(url, { waitUntil: 'networkidle' });
    const tall = await page.$$eval('.split__media:not(.duo) > img, .intro__media > img, .page-hero__media img, .hero-media img', (imgs) => imgs.filter((i) => { const r = i.getBoundingClientRect(); return r.width && r.height > r.width * 1.01; }).length);
    expect(tall, url).toBe(0);
    const tops = await page.$$eval('.duo img', (imgs) => imgs.map((i) => Math.round(i.getBoundingClientRect().top)));
    if (tops.length === 2) expect(Math.abs(tops[0] - tops[1])).toBeLessThan(2);
  }
});

test('phone menu: Services sits in its own row between Doctors and Testimonial, even after scrolling', async ({ page }, info) => {
  test.skip(info.project.name !== 'mobile', 'phone only');
  await page.goto('/about-us.html', { waitUntil: 'networkidle' });
  await page.evaluate(() => window.scrollTo(0, 1500));
  await page.click('#menu-toggle');
  const rows = await page.$$eval('#site-nav > ul > li', (lis) => lis.map((li) => ({ text: li.firstElementChild.textContent.trim(), top: li.getBoundingClientRect().top, pos: getComputedStyle(li).position })));
  const names = rows.map((r) => r.text);
  expect(names.slice(2, 5)).toEqual(['Doctors', 'Services', 'Testimonial']);
  for (let i = 1; i < rows.length; i++) expect(rows[i].top).toBeGreaterThan(rows[i - 1].top);
  expect(rows.every((r) => r.pos === 'static')).toBe(true);
  expect(await page.$eval('.site-header', (h) => getComputedStyle(h).backdropFilter)).toBe('none');
  await page.locator('#site-nav .sub-toggle').click();
  await expect(page.locator('#site-nav .sub-menu a', { hasText: 'Dental Implants' })).toBeVisible();
});

test('phones: checklist in two columns, extra paragraphs fold behind Read more', async ({ page }, info) => {
  test.skip(info.project.name !== 'mobile', 'phone only');
  await page.goto('/index.html', { waitUntil: 'networkidle' });
  const tops = await page.$$eval('.checks li', (lis) => lis.slice(0, 2).map((li) => Math.round(li.getBoundingClientRect().top)));
  expect(tops[0]).toBe(tops[1]);
  const box = page.locator('.intro__copy');
  const extra = box.locator('p.fold-extra').first();
  await expect(extra).toBeHidden();
  await box.locator('.read-more').click();
  await expect(extra).toBeVisible();
  await expect(box.locator('.read-more')).toHaveText('Read less');
  const fs = await page.$eval('.intro__copy p:not(.eyebrow)', (p) => parseFloat(getComputedStyle(p).fontSize));
  expect(fs).toBeLessThanOrEqual(15);
});

