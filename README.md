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
npm test                          # HTML validation + 20 browser tests (desktop + mobile)
```
The same checks run on GitHub Actions on every push (`.github/workflows/test.yml`).

## WordPress theme
Upload `the-dental-hauz-theme.zip` at **Appearance → Themes → Add New → Upload Theme**, then activate it.

- **Clinic details:** Appearance → Customize → Clinic details (phone, email, address, opening hours, where booking requests are emailed). Empty fields are hidden.
- **Menu:** Appearance → Menus → assign to "Primary menu". Until you do, a default menu linking to the home-page sections is shown.
- **Booking form:** submits to WordPress and emails the clinic via `wp_mail` (site admin email by default). On hosts that don't deliver PHP mail reliably, install an SMTP plugin such as WP Mail SMTP.
- **Home page:** the theme's `front-page.php` is used for the home page whatever is chosen under Settings → Reading. Other pages and posts use `page.php` / `single.php`.
- Tests: `npm run theme:php` (PHP lint + booking handler checks), `npm run theme:render && npm run theme:e2e` (templates rendered to HTML and tested in a browser, desktop + mobile).
