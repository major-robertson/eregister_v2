# Research Notes — Clay County RFP 78-26 Concept Demo

Compiled 2026-07-18 from the RFP document, claycountymo.gov, visitclaymo.com, and the Claude Design handoff bundle (which includes its own research pass dated 2026-07-17).

## Sites reviewed

**claycountymo.gov (CivicPlus)** — authoritative source for names, addresses, phones, policies:
- /165/Parks-Recreation, /160/Historic-Sites, /171/Camping-Shelters-Reservations, /167/Beaches, /480/Nature-Center, /412/Dog-Park, /211/Trails, /198/Marinas, /473/Rocky-Hollow-Park, /474/Tryst-Falls-Park

**visitclaymo.com (Saffire tourism CMS)** — visitor-oriented content and photography:
- Home, /p/things-to-do/outdoors/smithville-lake, /outdoors/trails--hiking, /camping-at-smithville-lake, /historic, /historic/jesse-james-history

**Reservations** — moclaycountyweb.myvscloud.com (Vermont Systems WebTrac splash page). The demo hands off to this URL in a new tab.

## Existing content problems observed

1. **Fragmentation** (the RFP's core complaint). Facts about the same place are split across the county site, VisitClayMO, WebTrac, and jessejamesmuseum.org. Trail distances live on VisitClayMO; trail policy and 911-marker info on the county site; reservations on a third domain with no shared navigation or branding.
2. **No current-conditions surface.** Lake levels, beach water quality advisories, and ramp closures are published as news items or PDFs when they're published at all. Nothing answers "what's open today?"
3. **Template-constrained presentation.** The CivicPlus pages are text-heavy with small imagery; the county's best assets (the lake, the waterfall, the James farm) are barely visible. VisitClayMO has the photography but a playful illustrated-collage identity that doesn't read as an official parks department.
4. **Weak historic storytelling.** The five historic sites share one page with a paragraph each, despite the Jesse James Birthplace being a nationally significant collection.
5. **Reservation handoff is abrupt.** Users land in WebTrac with no explanation of what it is or that they left the county site.

## Design opportunities (what the demo demonstrates)

- One front door: a unified directory (Explore) mixing parks, lake amenities, beaches, marinas, and historic sites with shared taxonomy.
- Conditions-first UX: "Today in Clay County Parks" panel, Know Before You Go on destination pages, alert banner + drawer, opt-in notification signup.
- A trails explorer with the six named systems, verified distances, and a legible concept map.
- A respectful, museum-quality register for historic content (serif headings, rust accent) that still shares the site's header, footer, and components.
- An explained, contextual WebTrac handoff.

## Branding & asset findings

- Official county wordmark (multicolor "C", "CLAY COUNTY MISSOURI Est. 1822") — ImageRepository documentID=69; county seal — documentID=80. Both used in the demo (header/footer/favicon) alongside a text lockup; no new seal was invented.
- County site theme color is a civic blue (#023c89); the demo uses the handoff package's palette (deep lake blue #0B3A4E / #0E5A73, cream, sandstone, rust for historic) sampled to sit between civic trust and outdoor-recreation warmth.
- claycountymo.gov hosts page-specific photography in its ImageRepository (historic sites 100–104, camping 112, dog park 631, marina 641, trails map 664, Tryst Falls 773, Rocky Hollow 775, Nature Center 2948, plus named files like Beach 1.jpg). VisitClayMO's Saffire CDN hosts the scenic/tourism set (kayaks, per-trail photos, campground galleries, James farm). See ASSET_SOURCES.md.

## Verified-fact sources used in demo content

- RFP Exhibit E: campsite counts (Camp Branch 200 electric + 146 unimproved; Crows Creek 91 water/electric + 181 electric + 134 unimproved = 752), marina slip counts, 37/11.5/32 trail miles, amenity lists, park descriptions, historic-site descriptions.
- VisitClayMO trails page: per-system distances (Little Platte North 2.5 mi; Anita B. Gorman 1.9 + Cabin Fever 7.0; Backbone 2.7 + Whispering Pine 1.4 + Copperhead Ridge 0.7; Little Platte South 5.5; Westward 26; Camp Branch 6.0 + 3.0 + 1.5 + 0.5).
- County camping/beach pages: check-in/out times, quiet hours, site limits, firewood, pet, and beach rules.
- USACE figures (7,190 acres, 175 mi shoreline) are cited as USACE on the lake page and flagged for verification before production.

## Key assumptions

1. Trail **difficulty and accessibility classifications are sample data** — the county publishes distances and types but not difficulty ratings. Flagged via `sampleFields` in `trails.json` and "(sample)" labels in the UI.
2. All conditions/statuses/alerts are invented examples, labeled "Prototype data". The one real-world reference point (an E. coli advisory lifted 6/30/2026) informed the *kind* of alert shown, not its content.
3. All events are invented but plausible, labeled "Demo events"; the featured lantern tour is fictional.
4. Hours and admission are deliberately **linked out, never invented** (per the handoff's "not invented" rule).
5. Claybrook Park has no publicly downloadable photo; the demo uses a labeled "Photo pending" placeholder.
6. Production platform is WordPress per RFP 2.1.1; the demo's JSON files are shaped as the future custom post types (see DEMO_NOTES.md).
