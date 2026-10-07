# The Dental Hauz — Website

Static marketing site for **The Dental Hauz** (implants, oral surgery, aligners and cosmetic dentistry), built from the brand sheet.

- `index.html` — the full single-page site (HTML + CSS + a little JS, no build step)
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
