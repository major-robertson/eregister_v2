# eRegister SEO plan (2026-09-05 sweep)

Source of truth for status: Linear project **SEO sweep** (team eRegister). This file is the plan as decided on 2026-09-24 and handed off on 2026-10-01; update it when a phase ships. Companion Linear documents: "SEO research: what the sweep found (evidence and data)", "SEO Phase A handoff (2026-10-01)". All 83 findings with evidence are attached to EREG-7 as `seo-sweep-findings-full.md`.

Issues: EREG-7 (decisions D1 to D7), EREG-8 (Phase A), EREG-9 (lien rule research), EREG-10 (Search Console), EREG-11 (Phase B), EREG-12 (Phase C), EREG-13 (Phase D), EREG-14 (Phase E), EREG-6 (/llc price), EREG-77 (taxresalecertificate.com redirect), EREG-19 (legacy domain retirement).

## Three conclusions

1. **The SEO build is structurally sound but the state-page content is not.** Canonicals, robots, JSON-LD and the sitemap work: zero duplicate titles, every JSON-LD block parses. The content on the state pages does not hold up: sixteen lien pages print spreadsheet-era authoring notes, Texas states an enforcement deadline that contradicts the statute, twenty-three resale pages name a "Department of Revenue" that does not exist, fourteen resale pages are word-identical apart from the state name, and three lien pages sell a $99 filing in states where checkout is blocked.
2. **Indexing, not markup, is the binding constraint. Search Console is step zero.** The waiver hub and its 50 state pages have been live, in the sitemap and internally linked since July 2026. None appear in a `site:` search. Googlebot fetches them fine, so this is a crawl-budget or quality-threshold problem, and the 97 new state pages will land in the same bucket. A `google-site-verification` DNS TXT record already exists; confirming the property and inspecting those URLs is the only way to tell "discovered, not crawled" from "crawled, not indexed".
3. **Content effort belongs in four clusters, and not in business formation.** Lien-waiver generators and templates, mechanics-lien deadline pages and calculators, per-state sales-tax registration, and per-state resale certificates are SERPs small commercial sites already win. Formation, registered-agent and operating-agreement head terms belong to LegalZoom, Northwest and Wolters Kluwer; keep those pages as conversion pages. The DOT cluster is winnable only after the portal launches.

## Decisions (Major, 2026-09-24)

- **D1 DOT compliance page.** Hold. It stays unindexed and out of the sitemap until the portal launches (it 404s live today).
- **D2 taxresalecertificate.org / .com.** Nothing on the old storefront for now. One task: 301 the `.com` (apex and www) to `https://eregister.com/resale-certificates` (EREG-77). The `.org` waits for the customer migration (EREG-18).
- **D3 Brand suffix.** Product, state and government titles carry no brand suffix. Only home, the three legal pages and `/contact` carry " | eRegister".
- **D4 Deadline calculator runtime.** Alpine over a per-state JSON blob; marketing pages drop Flux and Livewire. Livewire stays on `/contact` and the portal.
- **D5 Lien rule data.** No legal-review gate. The implementer researches each flagged row against the statute while shipping. A fact that cannot be confirmed for a state is left off that state's page. Every lien page carries "confirm with counsel" wording.
- **D6 Trust claims and contact details.** Remove every claim that cannot be confirmed, on every public page. "In business since 2013" is the approved claim (`config/company.php`). The 5.0 Google rating is real and stays, attributed to Google; the review count stays hidden while small. A "helped N businesses" figure is allowed when N comes from real counts (13,216 lifetime sales tax registrations on 2026-09-15 plus eRegister's paid orders), stored in config with its date and rendered rounded down. No email address as text or `mailto:` on any public page; link to `/contact` instead. The street address may appear. No `email` in the Organization markup.
- **D7 Resale state pages.** Research and write state-specific content for all 47, not only the top 15.

## Phase A: fix what is wrong on the live state pages (EREG-8)

Effort: S under an hour, M half a day, L multi-day. A1 to A7 are live defects since PR #26 shipped the state pages on 2026-09-22. A11 is done on main.

| # | Task | Sev | Eff | Where |
| -- | -- | -- | -- | -- |
| A1 | Stop rendering raw lien rule notes. Add `public_notes`, rewrite the notes in plain English keeping statute cites, test that no page matches "this sheet", "modeled", "proxy" or a snake_case field name. | High | M | `LienStatePage.php`, `lien-state.blade.php`, `lien_state_rules.json`, `StatePagesTest` |
| A2 | Remove placeholders and boilerplate fallbacks: "see the notes below", "See statute" on DE/NH/RI, "plan on paper recording". Display text for the 18 zero-offset deadline rows; neutral e-recording wording. | Med | M | `LienStatePage::describe()`, `keyFacts()`, `lien_deadline_rules` |
| A3 | Lien legal accuracy (D5). Texas enforcement runs from the last day to file (Tex. Prop. Code § 53.158, HB 2237). Add a `last_day_to_file` trigger; per-state filing-office label; real bordering states. | High | M | `enforcementSentence()`, `filingLocationLabel()`, rule data |
| A4 | Attorney-referral states DE/HI/MD: swap CTAs, no Offer. | Low | S | `config/lien.php`, `lien-state.blade.php` |
| A5 | Real agency, form number and official link per resale state (D7): all 47 states. | High | M | `config/resale_cert.php`, `ResaleStatePage.php`, `resale-state.blade.php` |
| A6 | Snippet formulas: resale descriptions composed within 165 chars (no hard cut); waiver titles under 60 chars. | Med | S | `ResaleStatePage::metaDescription()`, `WaiverLandingController` |
| A7 | Indefinite-article helper ("an Alabama lien"; Utah stays "a"). | Low | S | `Text::article()`, three templates, two view models |
| A8 | Residual `&amp;amp;` on government headings and Service names. | High | S | `government/*`, `seo/service.blade.php` |
| A9 | Noindex the auth, minimal and portal layouts. | Low | S | `layouts/auth/*`, `layouts/minimal`, `components/layouts/portal` |
| A10 | robots.txt: drop the Disallow lines for `/landing2` and the demos, which carry meta noindex. | Low | S | `public/robots.txt` |
| A11 | Legacy 301s: `/sales-tax`, `/sales-tax/pricing`, `/formation/pricing`, `/pricing`. **Done** (`routes/web.php`). `/patent*` stays 404. | Med | S | `routes/web.php` |
| A12 | `/index.php` duplicates: nginx redirect (Forge); canonical, og:url, sitemap and Organization `@id` from the app URL. | Low | S | Forge nginx, `partials/seo.blade.php`, `SitemapController` |
| A13 | Home page accuracy: sales tax permit vs resale certificate FAQ; duplicated LLC question; service cards for services with no page; cards link to `/register`. | Med | S | `landing.blade.php` |
| A14 | Trust claims and testimonials (D6), every public page. | High | S | `landing`, `liens`, `llc`, `sales-tax-registration`, `government/*` |
| A15 | NAP: street address in both footers and `/contact`; Organization gets address, foundingDate, description. No email. | Med | S | layout footers, `pages/contact`, `partials/seo.blade.php` |
| A16 | Pricing consistency: `/llc` $297 vs $299 (EREG-6); 8 formation/compliance pages show no price and no Offer. | Med | S/M | `llc.blade.php`, `FormationFeeSeeder`, product views |
| A17 | `/liens` hub: cost FAQ from config; remove services that have no page; "construction lien" 40 times. | Med | M | `liens.blade.php` |
| A18 | DOT marketing page (D1): nothing until launch. | Med | S | `pages/dot`, `SitemapController::PAGES` |
| A19 | Sitemap hardening: lien loop from the table, loud on missing views, root entry with trailing slash, no session cookies. | Low | S | `SitemapController`, `routes/web.php` |
| A20 | State-page cache: versioned keys, `cache:clear` on deploy, serialize round-trip test. | Med | S | state landing controllers, `LienStatePage`, `ResaleStatePage` |
| A21 | Guardrail tests: every sitemap URL (200, canonical == loc, index, one title/description/canonical/H1, JSON-LD parses, no `href="#"`, no `&amp;amp;`, no email, title and description budgets); robots.txt; noindex for auth and demo pages. | Med | M | `tests/Feature/Seo/*` |
| A22 | Two `href="#"` footer social links. | Low | S | `layouts/landing.blade.php` |

### Research the decisions call for

- **5a Lien rule verification, 50 states (D5, EREG-9):** per state, against the statute at `statute_url` and one secondary source: enforcement deadline (amount, unit, measured from), filing office, wrongful-lien statute, `public_notes` (120 to 300 words, plain English, cites kept, "confirm with counsel" where uncertain), list of unconfirmed facts. Known problems: Texas (§ 53.158, county clerk, Civ. Prac. & Rem. Code ch. 12), South Dakota (72-month placeholder), Rhode Island (court process), Maryland (petition), Washington (10-day proxy), the 18 zero-offset notice rows (AR, ID, KS, LA, MN, MO, NH, TX), DE/NH/RI enforcement nulls, `efile_allowed=false` on 43 rows. The deadline engine reads only `lien_deadline_rules` and a few `lien_state_rules` fields (`noi_lead_time_days`, `noc_*`, `lien_after_noc_days`, `*_has_lien_rights`, `lien_anchor_logic`); corrections to the enforcement, office and penalty fields are display-only, while deadline-row changes alter customer deadlines and need a dry run and Major's OK.
- **5b Resale certificate content, 47 states (D7):** agency name and URL, form number/title/PDF, issuer model, registration name and format, verification tool, MTC/SST/out-of-state/blanket acceptance with cites, good-faith rule, misuse penalty, 3 to 5 further facts with sources, whether the generator's PDF template is current, a 150 to 250 word `state_notes`. Alaska: ARSSTC, no state department. Tests: "Department of Revenue" never appears for a state whose agency is something else; no two rendered resale pages more than 80% identical.

## Phase B: deploy in slices and switch measurement on (EREG-11, EREG-10)

| # | Task | Eff |
| -- | -- | -- |
| B1 | Commit in dependency order (partly done via PR #26). | S |
| B2 | Forge deploy script: pull, composer, npm build, migrate, optimize, cache:clear, queue:restart; smoke curls on `/`, `/llc`, `/liens`, `/liens/texas`, `/resale-certificates/florida`, `/liens/lien-waivers/tx`, robots, sitemap; assert ≥180 locs, `/liens/tx` 301, no money page noindexed. | S |
| B3 | nginx on Forge: `text/javascript` in `gzip_types`; `Cache-Control: immutable` for `/build/assets`, 30 d for `/img`; trailing-slash 301; `/index.php` redirect; HSTS; `.webmanifest` mime; `http://www` in one hop. | S |
| B4 | Search Console and Bing (EREG-10): confirm the property, submit the sitemap, URL-inspect `/liens/lien-waivers`, `/liens/lien-waivers/tx`, `/llc`, `/liens/texas`; import into Bing. | S |
| B5 | Organic KPI: GA4 `purchase` on payment-success views and `sign_up` in onboarding; admin acquisition query from stored first-touch landing path, referrer and UTM; record the baseline. (Re-check: funnel work since the sweep added GA4 events on the waiver and sales tax paths.) | S |
| B6 | Six-week watch: GSC Pages report weekly; Sentry traces sample rate for `/liens/{state}` p95; Lighthouse re-run the day after deploy. | S |

## Phase C: snippets, internal links, page speed (EREG-12)

| # | Task | Eff |
| -- | -- | -- |
| C1 | Title/description rewrites (D3): 26 static descriptions run 161 to 243 chars; 15 titles overflow, including all nine government pages. Re-check every price before shipping. | M |
| C2 | Query-first H1s: H1 = query phrase, slogan demoted to a paragraph; on the six lien sub-pages move the slogan span out of the H1. | S |
| C3 | Heading order and contrast: hero cards h3 under h1 on 12 pages; footer h4 sitewide; seven colour pairs fail; "Why eRegister" misuses `dl`. | S |
| C4 | Contextual links on the money pages: 11 product pages have zero in-body internal links. | M |
| C5 | State page link graph: bordering-states map for all three sets (done for lien and resale in Phase A); "More {State} lien tools" on waiver pages; payment demand letter in the lien box (done); all-states list on lien sub-pages; waiver anchors "{State} lien waiver forms". | M |
| C6 | Visible breadcrumbs on the 65 pages that emit BreadcrumbList with the trail hidden. | S |
| C7 | Ad pixels off the critical path: load gtag/Reddit/OpenAI on window load + idle, never on first interaction; one combined Google tag; Reddit/OpenAI only on campaign, register and checkout routes. | S |
| C8 | Fonts: `&display=swap` and crossorigin preconnect now; self-host Inter later; hero asks for weight 800 which is not loaded. | S→M |
| C9 | Marketing JS bundle (D4): Alpine + collapse entry, drop `@fluxScripts` from the two marketing layouts, convert the two Flux accordions, remove `wire:navigate` there. | M |
| C10 | Real logo assets: both "SVG" logos are 80 KB base64 PNG wrappers with no dimensions. | S |
| C11 | Field data: send LCP/INP/CLS from web-vitals to GA4. | S |
| C12 | Government entry points: header link to `/government`; show the back link on mobile. | S |

Simulated expectation after C7 to C10: mobile LCP from about 10 s to 3 to 4 s, blocking time under 300 ms.

## Phase D: programmatic depth (EREG-13)

| # | Task | Eff |
| -- | -- | -- |
| D1 | Lien deadline calculator (decision D4): reuse the engine's date math, per-state JSON, Alpine; embed on each lien state page above the deadline table; standalone `/liens/deadline-calculator`. | M |
| D2 | Lien state depth, top 10 states: "How to file" H2, blank lien-claim PDF via DOMPDF, county recording table, recent-changes field (TX HB 2237), 4 to 6 more FAQs, "Popular states" row on the hub; 1,400 to 1,800 words. | L |
| D3 | Resale state notes: all 47 states (D7) with form number and link, issuer model, misuse penalty, 150 to 250 word note, "resale certificate vs {State} sales tax permit"; target 900 words, half state-specific. | M |
| D4 | No-sales-tax state pages (OR, MT, NH, DE) via a config-driven template variant; rewrite Alaska around the ARSSTC certificate. | M |
| D5 | Waiver pages and hub: render the 40 states' `ui_notes`, five-item FAQ, Service with free tier, blank statutory PDFs per waiver type on the hub, "generator vs a Word template" section. Statutory text untouched. | M |
| D6 | Sales-tax registration by state, 46 pages: seeded table first (agency/portal, form, fee, nexus threshold, processing time, filing frequency, bond note); omit AK, DE, MT, NH, OR; cross-link each resale page. | L |
| D7 | Notice-of-intent and lien-release state variants for the nine NOI states plus the top ten construction states; ungated fillable PDF, statutory basis, lead time, then the paid CTA; free demand-letter template. | L |
| D8 | Deferred: LLC-by-state waits for the trust layer; no registered-agent or DOT-by-state pages. | — |

## Phase E: content and authority (EREG-14)

| # | Task | Eff |
| -- | -- | -- |
| E1 | `/guides` hub: Article component, named author with bio on `/about`, real lastmod, 10 to 15 funnel-mapped pieces. | L |
| E2 | `/about` (404 live) and `/government/capabilities` (entity, UEI, NAICS, insurance, past performance, labelled concept builds). | M |
| E3 | Inherit the old domain (D2, EREG-19, EREG-77): 301 the `.com` now; the `.org` after the customer migration. | S→L |
| E4 | Profiles for Organization `sameAs` (LinkedIn, X, BBB/Crunchbase); the brand query ranks eregister.com fifth. | S |
| E5 | 5 to 10 links to the waiver generator hub from free-tool directories, contractor associations and the roundup posts that already rank. | M |
| E6 | Government content: rewrite `/government/accessibility` as an ADA Title II deadline guide (verify dates), state pages for FL and NC, each service page to 700 to 900 words. | L |
| E7 | DOT at launch (DOT project): per-obligation pages with official-fee vs price tables and a free biennial-update due-date lookup. | M |

## Not recommending

- FAQ markup as a lever: keep the output, never add an FAQ for the schema alone (Google removed FAQ rich results on 2026-05-07).
- Formation head terms; no registered-agent state pages unless priced under $40/yr.
- Redirecting `/patent` to the home page (soft 404).
- Product markup everywhere: pilot on the five fixed-price lien pages only.
- HTML sitemap, IndexNow, changefreq/priority tuning, the meta keywords tag on `/liens`.
- Splitting the 404 KB CSS bundle first (gzips to 54 KB; causal link unproven).
- Migrating waiver URLs to full slugs: add a full-slug alias that 301s instead.
- Any edit to the lien-waiver statutory text.

## Success criteria

- Indexed pages: from 7 to at least 150 of the 180 sitemap URLs within eight weeks, per the Search Console Pages report.
- The 51 waiver URLs leave "not indexed" within four weeks of verification plus D5.
- Organic signups by landing path reported monthly against the pre-deploy baseline (B5).
- Impressions and clicks by section weekly in Search Console.
- Lighthouse mobile performance ≥80 after Phase C; field LCP/INP visible in GA4.
- eregister.com first for "eregister" after E2 and E4.
- Sitemap contract and robots tests green on every push.

## Method and limits of the sweep

204 pages rendered in-process from the 2026-09-05 working tree; live curl probes; Lighthouse 12 mobile on the live home; nine dimension auditors, one adversarial verifier per dimension, one completeness critic. No field performance data. Legal claims were flagged, not verified, except the Texas enforcement trigger. SERP observations are point-in-time (2026-09-05, US). Baseline: 7 URLs indexed by Google (3 of them 404s); 51 live lien-waiver pages not indexed after 8 weeks; Lighthouse mobile performance 47 on the home page (LCP 10.1 s simulated).
