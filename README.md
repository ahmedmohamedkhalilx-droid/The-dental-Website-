# The Dental Hauz — Website

Static marketing site for **The Dental Hauz** (implants, oral surgery, aligners and cosmetic dentistry), built from the brand sheet.

- `index.html` — the full single-page site (HTML + CSS + a little JS, no build step)
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
