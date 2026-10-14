// @ts-check
// Real end-to-end test: a real WordPress with the theme zip installed. The browser fills in and submits each form on the
// real pages, WordPress runs the theme's handler and sends the email through wp_mail/PHPMailer to a real SMTP server
// (tools/wp-e2e/smtp_sink.py) that keeps each message. The test then reads those messages and the dashboard.
const { test, expect } = require('@playwright/test');
const fs = require('fs');
const path = require('path');
const { execFileSync } = require('child_process');

const E2E = path.join(__dirname, '../../tools/wp-e2e');
const OUT = path.join(E2E, 'out');
const MAIL = path.join(OUT, 'mail');
const WP = path.join(E2E, '.wp');
const CLINIC = 'thedentalhauz@gmail.com';
const report = [];

function parseEml(file) {
  const raw = fs.readFileSync(file, 'utf8');
  const split = raw.indexOf('\r\n\r\n');
  const head = raw.slice(0, split).replace(/\r\n[ \t]+/g, ' ');
  const headers = {};
  for (const l of head.split('\r\n')) { const i = l.indexOf(':'); if (i > 0) headers[l.slice(0, i).toLowerCase()] = l.slice(i + 1).trim(); }
  const rawBody = raw.slice(split + 4);
  const parts = mimeParts(headers['content-type'] || '', rawBody);
  return { file: path.basename(file), headers, body: (parts.text || parts.html || '').replace(/\r\n/g, '\n').trim(), html: parts.html || '' };
}
/** Decoded text/plain and text/html parts of a message (handles multipart/alternative, quoted-printable, base64). */
function mimeParts(ctype, body) {
  const decode = (enc, s) => {
    enc = (enc || '').toLowerCase();
    if (enc === 'base64') return Buffer.from(s.replace(/\s+/g, ''), 'base64').toString('utf8');
    if (enc === 'quoted-printable') return Buffer.from(s.replace(/=\r?\n/g, '').replace(/=([0-9A-F]{2})/gi, (m, h) => String.fromCharCode(parseInt(h, 16))), 'latin1').toString('utf8');
    return s;
  };
  const out = {};
  const b = /boundary="?([^";]+)"?/i.exec(ctype);
  if (!b) { out[/html/i.test(ctype) ? 'html' : 'text'] = body; return out; }
  for (const chunk of body.split('--' + b[1])) {
    const i = chunk.indexOf('\r\n\r\n'); if (i < 0) continue;
    const h = chunk.slice(0, i).toLowerCase(); const c = chunk.slice(i + 4);
    const enc = (/content-transfer-encoding:\s*([^\r\n;]+)/.exec(h) || [])[1];
    if (h.includes('text/plain')) out.text = decode(enc, c);
    else if (h.includes('text/html')) out.html = decode(enc, c);
  }
  return out;
}
/** New mails since `before`, split into the clinic's copy and the patient's thank-you. */
const since = (before) => mails().slice(before);
const clinicMail = (before, to = CLINIC) => since(before).find((m) => m.headers.to === to);
const patientMail = (before, to) => since(before).find((m) => m.headers.to === to);
const thanks = (page) => page.locator('#dh-thanks');
const mails = () => (fs.existsSync(MAIL) ? fs.readdirSync(MAIL).filter((f) => f.endsWith('.eml')).sort().map((f) => parseEml(path.join(MAIL, f))) : []);
const envelope = () => fs.readFileSync(path.join(MAIL, 'log.jsonl'), 'utf8').trim().split('\n').map((l) => JSON.parse(l));

/** Watch the browser's own requests to the form handler. */
function watch(page) {
  const seen = { post: null, redirect: null };
  const fieldsOf = (r) => {
    const d = (r.postDataBuffer() || Buffer.from('')).toString('utf8');
    if (!/multipart\/form-data/.test(r.headers()['content-type'] || '')) return Object.fromEntries(new URLSearchParams(d));
    const f = {}; const re = /name="([^"]+)"\r?\n\r?\n([^\r\n]*)/g; let m;
    while ((m = re.exec(d))) f[m[1]] = m[2];
    return f;
  };
  const onRequest = (r) => { if (r.method() === 'POST' && r.url().includes('/wp-admin/admin-post.php')) seen.post = { url: r.url(), fields: fieldsOf(r) }; };
  const onResponse = (r) => { if (r.url().includes('/wp-admin/admin-post.php')) seen.redirect = { status: r.status(), location: r.headers()['location'] || '' }; };
  page.on('request', onRequest);
  page.on('response', onResponse);
  seen.stop = () => { page.off('request', onRequest); page.off('response', onResponse); };
  return seen;
}

/** Fill every control of a form with a sensible value: first radio of each group, first two boxes of each list, etc. */
async function fillForm(page, sel) {
  await page.evaluate((s) => {
    const form = document.querySelector(s);
    const seenRadio = new Set(); const boxCount = {};
    form.querySelectorAll('input, select, textarea').forEach((el) => {
      const n = el.getAttribute('name') || '';
      if (el.type === 'hidden' || n === 'dh_website') return;
      if (el.tagName === 'SELECT') { el.selectedIndex = 1; return; }
      if (el.tagName === 'TEXTAREA') { el.value = 'Sample message from the end-to-end test.'; return; }
      if (el.type === 'radio') { if (!seenRadio.has(n)) { el.checked = true; seenRadio.add(n); } return; }
      if (el.type === 'checkbox') { if (n.endsWith('[]')) { boxCount[n] = (boxCount[n] || 0) + 1; if (boxCount[n] <= 2) el.checked = true; } else { el.checked = true; } return; }
      if (el.type === 'email') el.value = 'patient@example.com';
      else if (el.type === 'tel') el.value = '905 555 0100';
      else if (el.type === 'date') el.value = '1990-05-17';
      else el.value = 'Sample ' + n.replace('dh_', '');
    });
  }, sel);
}

test.describe.configure({ mode: 'serial' });
test.beforeAll(() => { fs.mkdirSync(OUT, { recursive: true }); });

test('both booking forms (Contact page and Home page) email the clinic', async ({ page }) => {
  for (const [name, url] of [['Contact Us page', '/contact-us/'], ['Home page', '/']]) {
    const before = mails().length;
    const seen = watch(page);
    await page.goto(url);
    await page.fill('#f-booking-first_name', 'Test');
    await page.fill('#f-booking-last_name', 'Patient');
    await page.fill('#f-booking-phone', '905 555 0100');
    await page.fill('#f-booking-email', 'patient@example.com');
    await page.selectOption('#f-booking-service', 'Dental Implants');
    await page.selectOption('#f-booking-day', 'Monday');
    await page.selectOption('#f-booking-time', 'Morning');
    await page.getByRole('button', { name: 'Book Appointment' }).last().click();
    // The visitor stays on the same page and sees the thank-you pop-up with the clinic's contact details.
    await expect(thanks(page)).toBeVisible();
    await expect(thanks(page)).toContainText('Thank you!');
    await expect(thanks(page)).toContainText('Your registration is done');
    await expect(thanks(page)).toContainText(CLINIC);
    expect(page.url()).not.toMatch(/dh_status/);
    await expect(page.locator('#f-booking-first_name')).toHaveValue('');
    await expect.poll(() => since(before).length, { timeout: 15000 }).toBe(2);
    const m = clinicMail(before);
    expect(m, 'clinic copy').toBeTruthy();
    expect(m.headers['content-type']).toMatch(/multipart\/alternative/);
    expect(m.html).toContain('#657568');
    expect(m.html).toContain('Dental Implants');
    const p = patientMail(before, 'patient@example.com');
    expect(p, 'patient thank-you').toBeTruthy();
    expect(p.headers.subject).toBe('[The Dental Hauz] Thank you, we received your appointment request');
    expect(p.headers['reply-to']).toBe('The Dental Hauz <thedentalhauz@gmail.com>');
    expect(p.html).toContain('your registration is done');
    expect(p.html).toContain('Dental Implants');
    expect(p.html).not.toContain('Test Patient');
    await thanks(page).getByRole('button', { name: 'Done' }).click();
    await expect(thanks(page)).toBeHidden();
    expect(m.headers.from).toBe('The Dental Hauz <wordpress@127.0.0.1>');
    expect(m.headers.subject).toBe('[The Dental Hauz] Appointment request from Test Patient');
    expect(m.headers['reply-to']).toBe('Test Patient <patient@example.com>');
    for (const l of ['First Name: Test', 'Last Name: Patient', 'Phone: 905 555 0100', 'Your Email: patient@example.com', 'Service You Would Like to Book: Dental Implants', 'Preferred Day: Monday', 'Preferred Time: Morning']) expect(m.body).toContain(l);
    expect(seen.post?.url).toMatch(/\/wp-admin\/admin-post\.php$/);
    expect(seen.post?.fields.action).toBe('dh_form');
    expect(seen.post?.fields.dh_form).toBe('booking');
    expect(seen.post?.fields.dh_ajax).toBe('1');
    expect(seen.redirect?.status).toBe(200);
    seen.stop();
    report.push({ test: `Booking form on the ${name}`, seen, mail: m, patient: p, envelope: envelope()[before] });
  }
});

test('the other forms (medical history, records request, two payment agreements) email the clinic', async ({ page }) => {
  const forms = [
    ['Medical history form', '/medical-history-form/', 'medical-history-form', 'Medical history form', 'The Dental Hauz dentists or their staff'],
    ['Request for records', '/request-for-records/', 'request-for-records', 'Request for records', 'Radiographs'],
    ['Dental treatment payment agreement', '/dental-treatment-payment-agreement/', 'dental-treatment-payment-agreement', 'Dental treatment payment agreement', 'Deposit Due Prior to Appointment'],
    ['Payment agreement', '/payment-agreement/', 'payment-agreement', 'Payment agreement', 'Full Name'],
  ];
  for (const [name, url, id, subjectPart, bodyPart] of forms) {
    const before = mails().length;
    const seen = watch(page);
    await page.goto(url);
    await fillForm(page, `#form-${id}`);
    const hasEmail = await page.locator(`#form-${id} input[type=email]`).count();
    await page.locator(`#form-${id} button[type=submit]`).click();
    await expect(thanks(page)).toBeVisible();
    await expect.poll(() => since(before).length, { timeout: 15000 }).toBe(hasEmail ? 2 : 1);
    const m = clinicMail(before);
    expect(m, 'clinic copy').toBeTruthy();
    if (hasEmail) expect(patientMail(before, 'patient@example.com'), 'patient thank-you').toBeTruthy();
    await thanks(page).getByRole('button', { name: 'Done' }).click();
    expect(m.headers.subject).toContain(subjectPart);
    expect(m.body).toContain(bodyPart);
    expect(seen.post?.fields.dh_form).toBe(id);
    seen.stop();
    report.push({ test: name, seen, mail: m, envelope: envelope()[before] });
  }
});

test('a form with missing required fields is refused and sends no email', async ({ page }) => {
  const before = mails().length;
  const seen = watch(page);
  await page.goto('/contact-us/');
  await page.evaluate(() => document.querySelectorAll('#form-booking [required]').forEach((e) => e.removeAttribute('required'))); // get past the browser's own check
  await page.fill('#f-booking-last_name', 'OnlyLastName');
  await page.getByRole('button', { name: 'Book Appointment' }).last().click();
  await expect(page.locator('#form-booking .form-note')).toContainText('the form is invalid');
  await expect(thanks(page)).toBeHidden();
  await page.waitForTimeout(1500);
  expect(mails().length).toBe(before);
  report.push({ test: 'Missing required fields (browser check bypassed)', seen, note: 'The form shows an error message in place. No email was sent.' });
});

test('a spam bot that fills the hidden field gets no email', async ({ page }) => {
  const before = mails().length;
  const seen = watch(page);
  await page.goto('/contact-us/');
  await page.fill('#f-booking-first_name', 'Bot');
  await page.fill('#f-booking-phone', '1');
  await page.fill('#f-booking-email', 'bot@example.com');
  await page.selectOption('#f-booking-service', 'Other');
  await page.selectOption('#f-booking-day', 'Friday');
  await page.selectOption('#f-booking-time', 'Evening');
  await page.evaluate(() => { document.querySelector('input[name=dh_website]').value = 'http://spam.example'; });
  await page.getByRole('button', { name: 'Book Appointment' }).last().click();
  await expect(thanks(page)).toBeVisible();
  await page.waitForTimeout(1500);
  expect(mails().length).toBe(before);
  report.push({ test: 'Spam bot (hidden field filled)', seen, note: 'The bot is told it worked, but no email is sent and nothing is saved.' });
});

test('the recipient can be changed in the Customizer setting', async ({ page }) => {
  execFileSync('php', [path.join(E2E, 'set-mod.php'), WP, 'dh_form_email', 'frontdesk@example.test']);
  try {
    const before = mails().length;
    const seen = watch(page);
    await page.goto('/contact-us/');
    await page.fill('#f-booking-first_name', 'Reroute');
    await page.fill('#f-booking-phone', '905 555 0111');
    await page.fill('#f-booking-email', 'reroute@example.com');
    await page.selectOption('#f-booking-service', 'Dental Cleaning');
    await page.selectOption('#f-booking-day', 'Wednesday');
    await page.selectOption('#f-booking-time', 'Afternoon');
    await page.getByRole('button', { name: 'Book Appointment' }).last().click();
    await expect(thanks(page)).toBeVisible();
    await expect.poll(() => since(before).length, { timeout: 15000 }).toBe(2);
    const m = clinicMail(before, 'frontdesk@example.test');
    expect(m, 'clinic copy to the new address').toBeTruthy();
    report.push({ test: 'Recipient changed in the Customizer (Send form submissions to)', seen, mail: m, envelope: envelope()[before] });
  } finally {
    execFileSync('php', [path.join(E2E, 'set-mod.php'), WP, 'dh_form_email']);
  }
});

test('if the mail server is down the patient is told, and the request is still kept in the dashboard', async ({ page }) => {
  fs.writeFileSync(path.join(OUT, 'smtp-down.flag'), 'down');
  try {
    const before = mails().length;
    const seen = watch(page);
    await page.goto('/contact-us/');
    await page.fill('#f-booking-first_name', 'Offline');
    await page.fill('#f-booking-phone', '905 555 0122');
    await page.fill('#f-booking-email', 'offline@example.com');
    await page.selectOption('#f-booking-service', 'Other');
    await page.selectOption('#f-booking-day', 'Saturday');
    await page.selectOption('#f-booking-time', 'No Preference');
    await page.getByRole('button', { name: 'Book Appointment' }).last().click();
    await expect(page.locator('#form-booking .form-note')).toContainText('failed because of an error');
    await expect(thanks(page)).toBeHidden();
    await page.waitForTimeout(1000);
    expect(mails().length).toBe(before);
    report.push({ test: 'Mail server down', seen, note: 'The patient sees an error message and no email is sent, but the request is saved under Form entries in the dashboard (checked next).' });
  } finally {
    fs.rmSync(path.join(OUT, 'smtp-down.flag'), { force: true });
  }
});

test('every accepted request is also kept under "Form entries" in the WordPress dashboard', async ({ page }) => {
  await page.goto('/wp-login.php');
  await page.fill('#user_login', 'admin');
  await page.fill('#user_pass', 'e2e-password');
  await page.click('#wp-submit');
  await page.waitForURL(/\/wp-admin\//); // let the login finish before opening the list
  await page.goto('/wp-admin/edit.php?post_type=dh_submission');
  const titles = await page.locator('#the-list .row-title').allTextContents();
  // 2 bookings + 4 other forms + recipient test + mail-down test = 8 saved; the invalid request and the bot are not saved
  expect(titles.length).toBe(8);
  expect(titles.filter((t) => t.includes('Appointment request from Test Patient')).length).toBe(2);
  expect(titles.some((t) => t.includes('Appointment request from Offline'))).toBe(true);
  expect(titles.some((t) => t.includes('Medical history form'))).toBe(true);
  expect(titles.some((t) => t.includes('Bot'))).toBe(false);
  await page.locator('#the-list .row-title', { hasText: 'from Offline' }).click();
  await expect(page.locator('body')).toContainText('Phone: 905 555 0122');
  report.push({ test: 'Dashboard "Form entries"', note: `Saved entries: ${titles.length}`, entries: titles });
});

test('WordPress logged no PHP errors or warnings while all of this ran', async () => {
  const log = fs.existsSync(path.join(OUT, 'php-errors.log')) ? fs.readFileSync(path.join(OUT, 'php-errors.log'), 'utf8') : '';
  const ours = log.split('\n').filter((l) => l.includes('the-dental-hauz'));
  expect(ours, ours.join('\n')).toEqual([]);
  report.push({ test: 'PHP error log', note: log.trim() ? `Entries not from the theme: ${log.trim().split('\n').length}` : 'Empty' });
});

test.afterAll(() => {
  const md = ['# Form submissions: real WordPress end-to-end report', '',
    `Run: ${new Date().toISOString()}`, '', 'Real WordPress with the theme zip installed. The browser submits the forms; WordPress sends each email through its own wp_mail (PHPMailer) to a real SMTP server that keeps the message.', ''];
  for (const r of report) {
    md.push(`## ${r.test}`, '');
    if (r.seen?.post) md.push(`- Browser sent: \`POST ${r.seen.post.url}\``, `- Form id: \`${r.seen.post.fields.dh_form}\`, ${Object.keys(r.seen.post.fields).length} fields`);
    if (r.seen?.redirect) md.push(`- WordPress answered: \`${r.seen.redirect.status}\` redirect to \`${r.seen.redirect.location}\``);
    if (r.envelope) md.push(`- SMTP envelope: from \`${r.envelope.envelope_from}\` to \`${r.envelope.envelope_to.join(', ')}\``);
    if (r.mail) md.push(`- Email headers: To \`${r.mail.headers.to}\`, Reply-To \`${r.mail.headers['reply-to'] || '-'}\`, Subject \`${r.mail.headers.subject}\``, '', '```', r.mail.body.split('\n').slice(0, 14).join('\n') + (r.mail.body.split('\n').length > 14 ? '\n...' : ''), '```');
    if (r.note) md.push(`- ${r.note}`);
    if (r.entries) md.push('', ...r.entries.map((e) => `  - ${e}`));
    md.push('');
  }
  fs.writeFileSync(path.join(OUT, 'REPORT.md'), md.join('\n'));
});

test('without JavaScript the form still sends, by a normal page load', async ({ browser }) => {
  const ctx = await browser.newContext({ javaScriptEnabled: false, baseURL: test.info().project.use.baseURL });
  const page = await ctx.newPage();
  const before = mails().length;
  await page.goto('/contact-us/');
  await page.fill('#f-booking-first_name', 'NoScript');
  await page.fill('#f-booking-phone', '905 555 0133');
  await page.fill('#f-booking-email', 'noscript@example.com');
  await page.selectOption('#f-booking-service', 'Kids Dentistry');
  await page.selectOption('#f-booking-day', 'Tuesday');
  await page.selectOption('#f-booking-time', 'Morning');
  await page.getByRole('button', { name: 'Book Appointment' }).last().click();
  await expect(page).toHaveURL(/dh_status=sent/);
  await expect(page.locator('#form-booking .form-note')).toContainText('Your submission was successful');
  await expect.poll(() => since(before).length, { timeout: 15000 }).toBe(2);
  expect(clinicMail(before)).toBeTruthy();
  await ctx.close();
});

