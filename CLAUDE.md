# eRegister (eregister_v2)

Laravel 12 + Livewire 4 + Flux Pro. Domain modules live in `app/Domains/`. Products: lien filings and deadlines, lien waivers, sales tax registration, resale certificates, formations, an unlaunched DOT portal, and RFP sales demos.

**Full handbook (Linear, team eRegister):** start at
https://linear.app/major-holdings/document/handbook-1-start-here-eregister-onboarding-ce7855fbe463
The other pages cover local dev and tests, production and ops, product invariants, and growth (ads, SEO, funnels). Read the relevant page before working in an area. Linear is the source of truth for todos and status: https://linear.app/major-holdings/document/how-we-use-linear-941b1e8ba058

## Must-know rules

### Safety
- Local `.env` files may point at **production** S3 (bucket `eregister.com`) and the **production** Postmark server. Never write or delete s3 media locally. Use Postmark read-only.
- The runtime dev DB is a **shared remote** MySQL. Clean up every row you create.
- Put `Storage::fake('s3')` in any test that attaches media or renders or signs PDFs.
- Checkout tests set `config(['cashier.secret' => null])`.
- Never run `npm run dev`: it breaks Herd Share tunnels. Use `npm run watch` (`vite build --watch`) or `npm run build`.

### Tests
- Tests use a local MySQL DB, `eregister_test_v2`.
- Run **one** `php artisan test` process at a time. Parallel runs corrupt each other.
- `SupportCompiledWireKeys` errors mean stale compiled views. Run `php artisan view:clear`.

### Code invariants
- `form_applications` queries that sort must use `forList()`. Otherwise MySQL error 1038 takes down dashboards.
- For user-facing times, call `->eastern()` before `->format()`. Timestamps are stored in UTC.
- Generated PDFs use spatie/laravel-pdf with `->driver('dompdf')` (CSS 2.1 only). Resale certificates are the one exception: they stamp official state PDFs with FPDI. Don't migrate them.
- Never edit the lien waiver statutory text (`resources/views/documents/lien/waivers/bodies/`) or the shell's type sizes without re-checking the statute. The same goes for the lien document clauses and bodies (`database/data/lien_documents/`, `database/data/lien_counties/`, `resources/views/documents/lien/`).
- Never edit price amounts in place. Add new price variants and retire the old ones, because payments reference `price_id`.
- `lien_project_deadlines.status` is not maintained. Compute it with `StepStatusCalculator`.
- Deadline reminders only ever go forward. Simulate any change that widens who gets them on prod first.
- New admin boards must be added to the `admin.home` board list in `routes/admin.php`.

### Working style
- Start with the smallest change. Ask before adding machinery such as sweeps, automatic status moves or new states.
- Customer-facing copy uses short, plain sentences. Never invent proof such as numbers, reviews or names.
- Branch names carry the Linear key (`fix/ereg-123-slug`). PR bodies say `Fixes EREG-123`.
- Merges to `main` auto-deploy through Forge. Major merges.
- Prod data changes need a dry run and Major's OK.
