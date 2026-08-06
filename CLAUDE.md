# CMS: Project context for Claude

## What this is

A freelancer portfolio CMS for Robbin Thijssen (Dutch developer/designer). The public site has a
portfolio home page (`/`), a work archive (`/work`), project case-study pages (`/work/{slug}`), a
client-facing docs page (`/docs`), and a CV download (`/cv.pdf`). Everything behind `/admin` is the
CMS.

The two halves are built differently on purpose:

- **Public site: Blade, server-rendered.** SEO is the point here: schema.org markup, a sitemap,
  per-page OG images, hreflang alternates. No JavaScript framework, no build-time hydration.
- **Admin: Inertia + Vue 3 + Tailwind v4.** Every `Admin\*` controller returns `Inertia::render`.

There is no blog. It was removed in CMS-105 (the public routes had already been pulled by CMS-88).
The `Post` model, its migrations and the `posts` table survive so the section can return without a
data migration, but nothing in `app/` references the model.

## Stack

- **PHP 8.4, Laravel 13**
- **SQLite locally**, single file at `database/database.sqlite`. **Production runs MySQL**
  (database `cms` on `127.0.0.1`). Do not assume SQLite when writing anything that touches the
  database directly rather than going through Eloquent.
- **Inertia 3 + Vue 3 + TypeScript** for the admin (`inertiajs/inertia-laravel`, `@inertiajs/vue3`)
- **Tailwind CSS v4** (`@tailwindcss/vite`) for the admin; the public Blade views use inline
  `<style>` blocks with CSS custom properties instead
- **shadcn-vue style primitives** in `resources/js/components/ui/`, built on **reka-ui**, with
  **lucide-vue-next** icons. Compose these, do not hand-edit them.
- **Vite 8** with `laravel-vite-plugin`; entry point is `resources/js/app.ts`
- `barryvdh/laravel-dompdf ^3.1`: CV PDF generation
- `intervention/image ^3.0` (GD driver): project image processing
- `laravel/passkeys ^0.2`: passwordless admin login
- `thijssensoftware/id-client ^0.1`: "Sign in with Thijssensoftware" SSO

## Running locally

Site runs under **Herd** at `cms.test` (https). No `php artisan serve` needed.

```bash
php artisan migrate          # run pending migrations
php artisan db:seed          # seed initial user + profile
php artisan storage:link     # once after fresh install
php artisan test             # Pest/PHPUnit suite
php artisan backup:database  # manual DB backup; scheduled daily via console.php
npm run dev                  # Vite dev server for the admin
npm run build                # production assets
```

### Quality gates

CI (`.github/workflows/tests.yml`, pull requests only) runs all of these. Run them before pushing:

```bash
vendor/bin/pint --test        # PHP formatting
vendor/bin/phpstan analyse    # static analysis, level 10
php artisan test              # Pest suite
npm run typecheck             # vue-tsc over resources/js
npm run lint                  # ESLint 9, --max-warnings=0
npm run format:check          # Prettier
```

`typescript` is pinned to `^5.9` on purpose: `vue-tsc` resolves `typescript/lib/tsc`, an entry point
the TypeScript 7 native port no longer exports, so TS 7 breaks the typecheck (CMS-109).

Deploys are **manual** (CMS-97). The GitHub Actions deploy workflow is `workflow_dispatch` only and
never fires on merge to `main`.

## Routes

### Public

| Route | Name | Controller method | Notes |
|-------|------|-------------------|-------|
| `GET /` | `home` | `HomeController@index` | Data cached forever, per locale, under `home.page.data.{en,nl}` |
| `GET /work` | `work.index` | `HomeController@work` | Archive with client-side JS tag filter |
| `GET /work/tag/{tag}` | `work.tag` | `HomeController@workTag` | Server-rendered tag filter page for SEO; 404 if tag doesn't exist |
| `GET /work/{project:slug}` | `project.show` | `HomeController@project` | 404 if not published |
| `GET /work/{project:slug}/preview` | `project.preview` | `HomeController@projectPreview` | Signed URL only; renders unpublished drafts (CMS-106) |
| `GET /docs` | `docs` | `HomeController@docs` | Client-facing "Working with me" page |
| `GET /cv.pdf` | `cv` | `HomeController@cv` | Streams PDF via dompdf |
| `GET /og/home.png` | `og.home` | `OgImageController@home` | PHP GD 1200×630 OG image |
| `GET /og/work/{project:slug}.png` | `og.project` | `OgImageController@project` | Per-project OG image |
| `GET /sitemap.xml` | `sitemap` | `SitemapController` | Projects and tags, both locales, with hreflang alternates |
| `GET /robots.txt` | `robots` | closure | Disallows `/admin` |
| `POST /contact` | `contact.store` | `ContactController@store` | Throttled via `throttle:contact` |

Every route in the `$publicRoutes` closure is registered **twice**: unprefixed (English default) and
under a `/nl` prefix (`nl.` name prefix). `/cv.pdf`, the OG images, the sitemap, robots and the
contact endpoint sit outside that closure and exist once. There is no locale-switch route; locale
comes from the URL prefix.

### Admin (behind `auth`, prefix `/admin`, name prefix `admin.`)

The admin group also carries `HandleInertiaRequests`; it is **not** in the global `web` group
(CMS-110). Every screen below is an Inertia page component under `resources/js/pages/`.

- `GET /admin` → `admin.dashboard`
- Projects: full CRUD + trash/restore/force-delete + `POST /admin/projects/reorder`
- Testimonials: full CRUD + trash/restore/force-delete
- Skills: full CRUD + trash/restore/force-delete + `POST /admin/skills/reorder`
- `GET|PUT /admin/profile`: profile edit
- Users: full CRUD
- Contact submissions: `index`, mark read/unread, destroy
- `GET /admin/security`: passkey management (`security.show`)

### Auth (not behind `auth`)

- `GET /admin/login`: email entry (`admin.login`); `POST /admin/logout`
- Email login code (rate-limited `throttle:login`): `POST /admin/login/code` (`admin.login.code.send`),
  `GET /admin/login/code` (`.challenge`), `POST /admin/login/code/verify` (`.verify`)
- Passkey routes are registered by **laravel/passkeys** (`passkey.login`, `passkey.store`,
  `passkey.destroy`, `passkey.confirm`, and their `*-options` endpoints)
- SSO routes come from `thijssensoftware/id-client` (`sso.redirect`, `sso.callback`)

## Architecture

### Models

| Model | Traits | Notes |
|-------|--------|-------|
| `Profile` | `BustsHomeCache`, `HasLocalizedContent`, `LogsActivity` | Always access via `Profile::current()`, never `find(1)` |
| `Project` | `BustsHomeCache`, `HasLocalizedContent`, `LogsActivity`, `SoftDeletes` | Slug auto-generated on save; `published()` and `ordered()` scopes; `tagList()`, `imageUrl()`, `imageAlt()` helpers; `images()` hasMany |
| `ProjectImage` | (none) | Gallery images for a project, ordered by `sort_order` |
| `Skill` | `BustsHomeCache`, `LogsActivity`, `SoftDeletes` | `ordered()` scope; grouped by `category` in views |
| `Testimonial` | `BustsHomeCache`, `HasLocalizedContent`, `LogsActivity`, `SoftDeletes` | `featured=true` + latest shown on home |
| `Post` | `BustsHomeCache`, `HasLocalizedContent`, `LogsActivity`, `SoftDeletes` | **Orphaned.** Kept for a possible blog return; no controller, route or view uses it |
| `User` | (none) | Passkey login + emailed login code (`login_code_hash`, `login_code_expires_at`); uses `#[Fillable]`/`#[Hidden]` attributes, no password |
| `ActivityLog` | (none) | Append-only; written by `LogsActivity` |
| `PageView` | (none) | Append-only path+timestamp; written via the `RecordsPageViews` concern |
| `PageViewTotal` | (none) | Per-path lifetime totals rolled up from pruned `page_views` |
| `ContactSubmission` | (none) | Saved on every valid contact form submit; `read_at` inbox flag |

### Traits

**Model concerns** (`app/Concerns/`)

- **`BustsHomeCache`**: `saved`/`deleted` hooks forget both `home.page.data.en` and `home.page.data.nl`
- **`LogsActivity`**: `created`/`updated`/`deleted` hooks write to `activity_logs`
- **`HasLocalizedContent`**: `localized($field)` returns the `_nl` column when locale is `nl` and it's filled, else the base column

**Controller concerns** (`app/Http/Controllers/Concerns/`)

- **`RecordsPageViews`**: `recordPageView()`; skips crawler and script traffic by user agent (CMS-107)
- **`HandlesSoftDeleteActions`**: shared `restore`/`forceDelete`, with a `beforeForceDelete()` hook for file cleanup
- **`HandlesReordering`**: shared `reorder` endpoint for `sort_order` columns

### Controllers

**Public** (`app/Http/Controllers/`)

| Controller | Methods |
|------------|---------|
| `HomeController` | `index`, `docs`, `work`, `workTag`, `project`, `projectPreview`, `cv` |
| `OgImageController` | `home`, `project` (plus private `generate()`, `wrapText()`, `textWidth()` helpers) |
| `SitemapController` | `__invoke` (XML sitemap) |
| `ContactController` | `store`: validates, saves `ContactSubmission`, sends `ContactFormSubmitted` synchronously |

**Admin** (`app/Http/Controllers/Admin/`): all return `Inertia::render`

`ContactSubmissionController`, `DashboardController`, `ProfileController`, `ProjectController`,
`SecurityController`, `SkillController`, `TestimonialController`, `UserController`

`Project`, `Skill` and `Testimonial` share `HandlesSoftDeleteActions`; `Project` and `Skill` also
use `HandlesReordering`.

**Auth** (`app/Http/Controllers/Auth/`)

`AdminLoginController` (login page + logout), `LoginCodeController` (send/challenge/verify)

### Middleware

- **`SetLocale`**: sets the locale from the **first URL segment**: `/nl/*` → `nl`, otherwise
  `config('app.locale')`. Appended to the `web` group.
- **`SecurityHeaders`**: X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy, HSTS on
  secure requests. Appended to the `web` group.
- **`HandleInertiaRequests`**: applied to the **admin route group only**, not the `web` group.
  Shares `auth`, `counts.unread`, `flash.status` and the portal app switcher data.

### Frontend layout

```
resources/js/
├── app.ts                  Inertia entry point; resolves ./pages/**/*.vue eagerly
├── layouts/AdminLayout.vue Sidebar, header, portal switcher, flash messages
├── pages/                  One directory per resource: Index / Form / Trash
├── components/
│   ├── ui/                 shadcn-vue style primitives (do not hand-edit; compose)
│   ├── AppIcon.vue, PortalSwitcher.vue, TrashTable.vue
└── lib/
    ├── utils.ts            cn() class merge helper
    └── pagination.ts       decodes Laravel's paginator entity labels
```

**Public** Blade views (`resources/views/`)

| View | Route | Data received |
|------|-------|--------------|
| `home.blade.php` | `/` | `$profile` (stdClass), `$skills`, `$projects`, `$testimonials`, all from the cached array |
| `work.blade.php` | `/work`, `/work/tag/{tag}` | `$profile`, `$projects` (stdClass with `tag_list`, `image_url`), `$tags`, `$activeTag` |
| `project.blade.php` | `/work/{slug}`, `.../preview` | `$profile`, `$project` (live Eloquent model), optional `$preview` |
| `docs.blade.php` | `/docs` | `$profile`, `$skills` (grouped), `$projects` (live Eloquent) |
| `cv.blade.php` | `/cv.pdf` | `$profile`, `$skills`, `$projects` |

Shared partials live in `resources/views/partials/` (`seo`, `fonts`, `public-nav`, `public-footer`,
`schema/*`). The only layout is `layouts/public.blade.php`; the Inertia root view is
`admin-app.blade.php`.

### Caching

The home page data is cached forever, **keyed per locale**, under `home.page.data.en` /
`home.page.data.nl` (translatable fields are resolved to the locale before caching).

**Critical**: the cache stores **plain arrays** (`->toArray()`), not Eloquent model instances.
Storing models directly causes `__PHP_Incomplete_Class` errors on deserialization in this
environment (PHP-FPM + SQLite-backed cache). Pre-compute derived values (`tag_list`, `image_url`)
before storing. `BustsHomeCache` invalidates the key whenever content changes. Tests flush
the cache in `Tests\TestCase::setUp()`.

OG images are cached forever under `og.home.{updated_at_ts}` and `og.project.{id}.{updated_at_ts}`.
Stale keys are orphaned rather than evicted, do not flush the entire cache table to clear them in
production; use `og:prune-cache`.

### i18n

**URL-prefix locale** (`en` default, `nl` under `/nl`). Language files:

| File | Used by |
|------|---------|
| `lang/en/site.php` + `lang/nl/site.php` | `home`, `work`, `project` views |
| `lang/en/docs.php` + `lang/nl/docs.php` | `docs` view |

Views use `__('*.key')` and carry `<html lang="{{ app()->getLocale() }}">`. The `localized_route()`
and `alternate_locale_url()` helpers (`app/Support/helpers.php`) build locale-aware URLs and the
language-toggle link. Per-model translatable fields use `_nl` columns via `HasLocalizedContent`.
`cv.blade.php` is English-only (PDF output has no language toggle).

### Auth (passkey + email login code)

No passwords. Login at `/admin/login` (enter email). Two paths:

- **Email login code**: `SendLoginCode` generates a 6-digit code, stores its **hash** with a
  10-minute expiry and emails it (`LoginCodeMail`). `VerifyLoginCode` checks expiry + `Hash::check`,
  clears the code (single-use) and logs in. `LoginCodeController` returns a **generic** message
  whether or not the email matches an account (no enumeration). Both send/verify are rate-limited
  via `throttle:login`.
- **Passkeys**: via **laravel/passkeys** (`User implements PasskeyUser`), managed from
  `/admin/security`.

There is no TOTP/2FA, recovery codes, or password column. Guest redirect is `/admin/login`
(set in `bootstrap/app.php`).

SSO: `thijssensoftware/id-client` provides "Sign in with Thijssensoftware"; on a successful
callback it logs the user into the `web` guard and (if `provision` is on) auto-creates the local
account.

### Queue

Driver: `database`. The contact notification (`ContactFormSubmitted`) is a plain Mailable sent
**synchronously** in the request, so the queue worker is **not** required for contact emails. The
send is wrapped in try/catch: the submission is saved to the admin inbox first, so an SMTP failure
is logged without breaking the visitor's success response.

### Images

`intervention/image ^3.0` (GD driver): scaled to max 1600px width at quality 82, stored in
`storage/app/public/projects/`. A project has one hero `image` plus up to 8 gallery `images`.

**File lifecycle** (CMS-102): soft-deleting a project keeps every file on disk, so a restore is
lossless. Files are removed in `beforeForceDelete()` (hero + gallery), when an image is replaced on
update, and when a gallery image is explicitly removed.

### CV export

`HomeController::cv()` renders `resources/views/cv.blade.php` via dompdf, A4, with the TrueType
fonts registered from `resources/fonts/`, and streams it as `{name}-cv.pdf`.

**Important**: `cv.blade.php` must use only inline styles and `<table>` layouts, dompdf does not
support CSS Grid or Flexbox.

### OG images

`OgImageController` uses PHP GD to produce 1200×630 PNGs with the site design tokens. Text is drawn
with **`imagettftext()`** using the shipped TrueType fonts in `resources/fonts/` (not GD bitmap
fonts), so accented characters render correctly. Served with `Cache-Control: public, max-age=604800`.
Requires GD compiled with FreeType. The renderer used is surfaced in an `X-OG-Renderer` header so a
failure on a server you cannot SSH into is diagnosable with curl.

### Dashboard analytics

`DashboardController::index()` provides content counts, a 30-day `PageView` sparkline (zero-filled),
and all-time totals plus top-5 paths combining live `page_views` rows with the `page_view_totals`
rollup.

**Retention**: `page-views:prune` (scheduled daily) rolls `page_view` rows older than `--days`
(default 90, floored at 30 to protect the sparkline) into per-path `page_view_totals`, then deletes
them, inside a transaction. Indexed on `created_at` and `path`.

**Crawler filtering** (CMS-107): `RecordsPageViews` drops hits from known crawlers, HTTP clients,
headless browsers and link-preview bots, and from requests with no user agent at all. Only what gets
written from now on is affected; historical rows were left alone.

### Database backups

`php artisan backup:database` copies the SQLite file to `storage/app/backups/` with a timestamp
suffix, pruning to the 14 most recent. Scheduled daily in `routes/console.php`, alongside
`og:prune-cache` (weekly) and `page-views:prune` (daily). Each scheduled task appends output to
`storage/logs/schedule.log`, because the provisioning cron pipes `schedule:run` to `/dev/null`.

**Neither of those works in production today** (found during the 2026-08-06 deploy, tickets filed):

- `backup:database` refuses to run on anything but SQLite, and production is MySQL, so there is no
  automated production backup. Take one by hand with `mysqldump` before risky work.
- There is no `schedule:run` cron entry for cms on the droplet (every other app has one), so
  `backup:database`, `og:prune-cache` and `page-views:prune` have never run in production.

## Testing

```bash
php artisan test
php artisan test --filter ProjectTest
```

- `RefreshDatabase` + `Cache::flush()` in `TestCase::setUp()`
- Tests run against **in-memory SQLite** (`phpunit.xml` sets `DB_DATABASE=:memory:`), not the dev
  database file
- Soft-deleted records: use `assertSoftDeleted`, not `assertDatabaseMissing`
- No database mocking, use factories or direct model creation
- Admin screens: `Admin/ScreensRenderTest` asserts every route renders its expected Inertia page
  component. Add new screens there.
- The test HTTP client sends a default `User-Agent`, but it is not a browser agent. Tests that
  expect a page view to be recorded must send a real browser agent (see `PageViewRecordingTest`).

## Key gotchas

1. **`Profile::current()`**: always use this, never `Profile::find(1)`. It calls `->refresh()`
   after `firstOrCreate` because Eloquent only hydrates the search key in memory after insert.
   Without refresh, `->toArray()` returns `['id' => 1]` with all other columns missing.

2. **Cache + CSRF**: never cache rendered HTML. A cached page bakes one visitor's CSRF token
   into every subsequent visitor's response. Cache data arrays only.

3. **Soft deletes and unique slugs**: `projects.slug` and `posts.slug` are uniquely indexed, and
   that index does not honour the soft-delete scope. Slug generation uses `withTrashed()` for this
   reason (CMS-101). Any new uniqueness check on a soft-deleted model needs the same treatment.

4. **Sort order**: `Project` and `Skill` have `sort_order` columns managed by drag-and-drop via
   `/admin/projects/reorder` and `/admin/skills/reorder`.

5. **OG cache orphaning**: stale OG image cache keys accumulate in the SQLite cache table. Prune
   them with `og:prune-cache` (scheduled weekly), not `Cache::flush()`, which would also clear
   `home.page.data.{en,nl}` and break the home page until the next request warms it.

6. **i18n files**: adding a new string requires adding the key to both the `en` and `nl` files,
   otherwise Blade silently renders the raw `site.key` string.

7. **dompdf layout**: use `<table>` and inline styles only inside `cv.blade.php`. CSS Grid and
   Flexbox are not supported.

8. **The public site is not Inertia**: do not reach for `Inertia::render` in a public controller.
   The middleware is not even applied there, and the SEO surface depends on server-rendered HTML.

## Tracker

File tickets under the **CMS** ("Portfolio CMS") project via the `create-linear-ticket` skill
(`--project CMS`), producing `CMS-###` identifiers, **not** the THI umbrella project.

Branch format: `feature/CMS-{number}-{description}` or `fix/CMS-{number}-{description}`.

Follow the full workflow in `~/.claude/CLAUDE.md`. See parent context in `~/Projects/cms/CLAUDE.md`.
