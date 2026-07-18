# Demo Notes — Mapping the Concept Demo to RFP 78-26

How each RFP requirement is demonstrated (or deliberately deferred) in the front-end concept at `/clay-demo`.

## Requirement mapping

| RFP theme | Requirement (§) | In the demo |
| --- | --- | --- |
| **Virtual Welcome Center** | 1.3.2 — 24/7 hub for info, trip planning, reservations, alerts | Homepage answers "what's open today?" first: conditions panel, alert banner, quick actions, Build-Your-Day planner, Reserve always one action away |
| **Unified content** | 2.2 — merge Clay County + VisitClayMO content | Explore directory mixes parks, lake amenities, beaches, marinas, and historic sites in one taxonomy; county facts + tourism photography on the same pages |
| **Mobile-first** | 2.7.2 | Re-prioritized mobile layouts (conditions above evergreen content, sticky Reserve bar, bottom-sheet filters, horizontal card rails, full-height nav sheet) — not shrunk desktop |
| **ADA accessibility** | 2.8.3 | WCAG 2.2 AA-quality front end: skip link, landmarks, one h1/page, visible focus rings (yellow on dark), 44px targets, focus-trapped modals that restore focus, icon+text status (never color alone), reduced-motion support, labeled form errors |
| **Trails & mapping** | 2.4.1–2.4.3 | Trails Explorer with the six named systems and verified distances, filters (activity/difficulty/distance/surface/accessibility/status), concept map with legend + selectable markers, links to the official trail map PDF and AllTrails |
| **Alerts & notifications** | 2.5.1–2.5.4 | Alert banner → severity-ranked drawer; opt-in signup modal with topic chips (email/SMS field, demo success state). Production: staff-editable Alert post type + delivery integration |
| **Events** | 2.6.1–2.6.2 | Calendar/list toggle, month navigation, category filters, event-detail modal with Register/Add-to-calendar, community submission modal with validation error state and reviewed-before-publish messaging |
| **Reservation integration** | 2.3.1 — WebTrac | Explained external handoff: interstitial states the destination system and context, opens WebTrac in a new tab with external-link iconography everywhere |
| **Marina software (future)** | 2.3.2 | Marina cards route slip-rental actions through the same reservation handoff pattern — swap the target when the marina platform lands |
| **SEO** | 2.9 | Unique titles + meta descriptions, Open Graph, clean semantic URLs, descriptive internal links, heading hierarchy, JSON-LD (GovernmentOrganization, Park, Museum). Demo pages are deliberately `noindex` |
| **WordPress CMS** | 2.1.1 | See content-model mapping below |
| **Staff-updatable conditions** | 2.5.1 | Simulated statuses labeled "Prototype data" everywhere; the panel/card components are the staff-editable surface in production |

## WordPress-ready content structure

The JSON files in `resources/demo/clay-county/` are shaped as the proposed custom post types, so demo content translates 1:1:

- **Destination** (`destinations.json`) → CPT with taxonomy `destination_type` (park / lake / beach / nature / historic), fields: summary, image+alt, address, phone, area, `activities[]`, `amenities[]`, `accessibility[]`, map coords. Cards, directory filters, and detail templates all read from this shape.
- **Trail** (`trails.json`) → CPT with activity taxonomy, distance, surface, difficulty (+ `sampleFields` marking unverified data), status + note, trailhead, `segments[]`.
- **Event** (`events.json`) → The Events Calendar plugin; category taxonomy, date/time, venue (Destination ref), registration flag, featured flag.
- **Alert** (`alerts.json`) → severity (info/advisory/closure), scope, body, posted timestamp — drives banner + drawer.
- **FAQ** (`faqs.json`) → question/answer/topic — drives lake FAQ and Plan Your Visit.

Blade partials map to theme parts: header/footer/overlays → theme chrome; destination-card, trail card, event card, status card → blocks/template parts.

## Known prototype limitations

- Conditions, statuses, availability, and events are static sample data (labeled as such).
- Maps are illustrative SVG concept maps, not geographic (production: Leaflet/Google + real coordinates).
- No forms transmit anything; success states are explicit demo confirmations.
- Hours/admission/fees link to official sources rather than being restated.
- Some photography is placeholder-pending (see ASSET_SOURCES.md).

## Suggested 3–5 minute walkthrough

1. **Homepage (30s)** — "One front door for the lake, trails, parks, and history." Point at the conditions panel and alert banner: *staff post it once, visitors see it everywhere.* Note the Prototype-data labels — we never fake live data.
2. **Build Your Day (45s)** — pick "Full day", interests "On the water + History", generate. *Trip planning from structured content — the same Destination records powering the rest of the site.*
3. **Smithville Lake (60s)** — Know Before You Go up top; verified numbers (752 sites, USACE-cited stats); campground cards with real site counts; click Reserve → the explained WebTrac handoff (new tab, county's existing system, no rebuild).
4. **Trails Explorer (45s)** — filter to Mountain bike; show verified distances; toggle the accessible-surface filter and the honest "(sample)" difficulty labels; open the map view.
5. **Historic Sites → Jesse James Birthplace (60s)** — the register shift (serif, rust, museum tone, no theme-park western); story, timeline, artifact cards; hours deliberately link out. *Credible history, same design system.*
6. **Events (30s)** — calendar/list toggle, category chips, open a detail modal, then "Submit a community event" → validation + reviewed-before-publish messaging (RFP 2.6.2).
7. **Close (15s)** — resize to phone width: sticky Reserve bar, bottom-sheet filters, conditions-first ordering. *Mobile-first, ADA-minded, WordPress-ready.*
