# Clay County Parks, Recreation & Historic Sites — Concept Demo

Front-end proposal demo for **Clay County, Missouri RFP 78-26 — Historic Sites Website Development** (Clay County Parks, Recreation & Historic Sites). Built by eRegister as a sales/proposal artifact.

**This is a concept demo, not the production website.** There is no backend, CMS, database, authentication, payment, live data, or real notification delivery. Every interaction is front-end only; simulated conditions are labeled "Prototype data" and sample events are labeled "Demo events." The eventual production site is proposed as WordPress — see [DEMO_NOTES.md](DEMO_NOTES.md) for how the demo's content models map to WordPress custom post types.

## Running it

The demo lives inside the eRegister Laravel app as an isolated sandbox (same pattern as the MDCPS and Florida EOG demos):

```
composer install && npm install
npm run build          # never `npm run dev` in this repo (breaks Herd Share)
php artisan serve      # then open http://127.0.0.1:8000/clay-demo
```

## Routes

| Route | Page |
| --- | --- |
| `/clay-demo` | Homepage — the "virtual Welcome Center" |
| `/clay-demo/explore` | Unified destination directory (search, filters, list/map) |
| `/clay-demo/destinations/smithville-lake` | Smithville Lake flagship destination page |
| `/clay-demo/trails` | Trails Explorer (6 systems, filters, concept map) |
| `/clay-demo/historic-sites` | Historic sites editorial landing |
| `/clay-demo/historic-sites/jesse-james-birthplace` | Historic-site detail template |
| `/clay-demo/events` | Events calendar/list with detail + submission modals |
| `/clay-demo/plan-your-visit` | Hours, rules, FAQs, contact |

All routes are `noindex, nofollow` and shareable by direct URL only.

## Where things live

- **Routes** — `routes/clay_demo.php` (registered in `bootstrap/app.php`)
- **Controller** — `app/Http/Controllers/Demo/ClayCounty/ClayCountyDemoController.php`
- **Views** — `resources/views/demo/clay/` (layout, pages, partials)
- **Content models (JSON)** — `resources/demo/clay-county/` (`destinations`, `trails`, `events`, `alerts`, `faqs`)
- **Images** — `public/img/demos/clay-county/` (see [ASSET_SOURCES.md](ASSET_SOURCES.md))
- **Demo stylesheet** — `resources/css/demo/clay-county.css` (own Vite entry; only loaded by the demo layout)
- **Design source** — the Claude Design handoff bundle (`Clay County Historic Sites Website-handoff.zip`), 12 comps + design tokens

## Technology

Laravel Blade views + Tailwind CSS 4 + Alpine.js (bundled via Flux) — the repo's existing stack. Public Sans and Source Serif 4 via Bunny Fonts. No new dependencies were added. All filtering, search, calendars, modals, and the trip planner run client-side against the JSON content files serialized into each page.

## Documentation

- [RESEARCH_NOTES.md](RESEARCH_NOTES.md) — sites reviewed, content problems observed, assumptions
- [ASSET_SOURCES.md](ASSET_SOURCES.md) — provenance for every downloaded image
- [DEMO_NOTES.md](DEMO_NOTES.md) — RFP requirement mapping + suggested demo walkthrough
