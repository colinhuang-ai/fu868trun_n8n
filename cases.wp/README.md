# CASES website (WordPress)

Local WordPress (latest `wordpress:latest`, currently 7.1.4) + MariaDB, with a custom theme converted from the HTML prototype in `.website 13-5/`.

## Run

```bash
docker compose up -d
bash scripts/setup.sh      # installs WP, activates theme, creates pages/menu/products (idempotent)
```

- Site: http://localhost:8088/
- Admin: http://localhost:8088/wp-admin/ — user `admin`, password = `ADMIN_PASSWORD` in `.env` (local dev only; change before any real deployment)

## Theme: `wp-content/themes/cases`

| What | Where |
|---|---|
| Page templates | `front-page.php`, `page-about.php`, `page-products.php`, `page-infrastructure.php`, `page-certifications.php` (matched by page slug) |
| Products (34 SKUs, 6 categories) | Admin → **Products** (custom post type; featured image = product photo; specs meta box) |
| Certificates (9) | Admin → **Certificates** (custom post type, grouped by *Certificate Groups*). Title = name, editor = English description; meta box holds Vietnamese text, issuing body, scope, icon/colours, optional *Valid until* (auto-switches status to *Expired*). Every published certificate appears both as a card and in the overview table |
| Certifications page banner (US FDA block) | Admin → Pages → **Certifications** → *Highlight banner* box: label, heading, text (EN + VI), market tags (one per line), badge icon/title/subtitle, and a show/hide switch. Defaults apply until saved |
| News (10 articles, 4 categories) | Admin → **Posts**. English = normal title/excerpt/editor; the **Vietnamese version & cover** box under the editor holds the VI title, excerpt and content (HTML) plus a cover style used when there is no featured image. Listing at `/news/` (the posts page, `home.php`), categories reuse it, article layout in `single.php`. Seed content: `scripts/news-data.php` |
| Contact form inquiries | Admin → **Inquiries** (also e-mailed to the sales address) |
| Phone / e-mail / address / website | Customizer → **Company Info** |
| Navigation | Appearance → Menus → *Primary* |
| EN/VI | Each text carries `data-en` / `data-vi`; toggle in the header, saved in `localStorage` |

## Rebuilding CSS (only if you change classes in templates/JS)

```bash
cd wp-content/themes/cases && npm install && npm run build
```

Compiled `assets/css/app.css` is committed, so Node is not needed to run the site.
