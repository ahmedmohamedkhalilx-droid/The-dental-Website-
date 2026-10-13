# The Dental Hauz — Website

Static marketing site for **The Dental Hauz** (implants, oral surgery, aligners and cosmetic dentistry), built from the brand sheet.

- `index.html`, `about-us/`, `our-services/`, `dental-implants/` and the other page folders: a **static preview of the WordPress theme**, published on GitHub Pages so it can be reviewed before the theme is installed (generated, see below)
- `single-page/index.html` — the earlier single-page design (HTML + CSS + a little JS, no build step)
- `wordpress-theme/the-dental-hauz/` — the same design as a WordPress theme (source)
- `the-dental-hauz-theme.zip` — ready-to-upload WordPress theme (rebuild with `npm run theme:zip`)
- `assets/img/` — logos and photography extracted from the brand sheet

## Brand
| Token | Hex |
|---|---|
| Umber | `#514A43` |
| Sage deep | `#657568` |
| Sage | `#A8B6A5` |
| Blush | `#D8B8B2` |
| Cream | `#F4F0E8` |

Fonts: the brand faces are **The Seasons** (headings) and **Garet** (body). They aren't on Google Fonts, so the site uses Cormorant Garamond and Jost as stand-ins. Add licensed `@font-face` files named "The Seasons" and "Garet" and they will be picked up automatically.

## Still to fill in
- Phone, email and clinic address (Book section)
- Opening hours (top bar and footer use sample hours)
- Booking form: connect to a booking system or form service (see the `TODO` in the script)

## Preview (GitHub Pages)
The site at https://ahmedmohamedkhalilx-droid.github.io/The-dental-Website-/ is the WordPress theme rendered to static pages by `npm run preview:build` (run it after changing the theme, then commit). Photos load from the old site's media library; forms are inert; Google reviews are hidden. Pages carry `noindex`. `npm run preview:check` verifies every internal link.

## Preview locally
Open `index.html` in a browser, or run `python3 -m http.server` and visit http://localhost:8000.

## Publish with GitHub Pages
Settings → Pages → Deploy from a branch → choose the branch and `/ (root)`.

## Tests
```
npm install
npx playwright install chromium   # first time only
npm test
```
`npm test` runs, in order: HTML validation of the static site; its 22 browser tests; PHP lint and the form/routing checks for the theme; the theme's pages rendered to HTML with stubbed WordPress; the **completeness check** against the old site's text (`tests/theme/completeness.py`); and 84 browser tests on the rendered theme (desktop and phone sizes). The same checks run on GitHub Actions on every push.

Rebuild the theme zip with `npm run theme:zip`. To regenerate `inc/content.php` from a fresh WordPress export: `python3 tools/build-content.py <export.xml> wordpress-theme/the-dental-hauz/inc`. Never commit the export itself: it contains patient form submissions.

## Real WordPress test (forms)
`npm run wp:e2e` builds a throw-away real WordPress (core and SQLite, no MySQL) in `tools/wp-e2e/.wp`, installs `the-dental-hauz-theme.zip`, starts a real SMTP server, and drives the real pages in a browser: it submits every form and checks the emails that arrive, the dashboard "Form entries", the error cases (missing fields, spam bot, recipient change, mail server down) and that WordPress logged no theme errors. It writes `tools/wp-e2e/out/REPORT.md` and keeps each email as `tools/wp-e2e/out/mail/*.eml`. Needs network the first time (Composer for WordPress core, git for the SQLite plugin); it is not part of `npm test`.
