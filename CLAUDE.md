# CMS — Project context for Claude

## What this is

A freelancer portfolio CMS for Robbin Thijssen (Dutch developer/designer). The public site has a
portfolio home page (`/`), a work archive (`/work`), project case-study pages (`/work/{slug}`), a
blog (`/blog`), a client-facing docs page (`/docs`), and a CV download (`/cv.pdf`). Everything
behind `/admin` is a Blade-rendered CMS.

## Stack

- **PHP 8.4, Laravel 13** — Blade only, no Inertia, no Vue, no Livewire
- **SQLite** — single file at `database/database.sqlite`
- **No Tailwind** — all styling is inline `<style>` blocks with CSS custom properties
- `barryvdh/laravel-dompdf ^3.1` — CV PDF generation
- `intervention/image ^3.0` (GD driver) — project image processing
- No npm build step beyond a bare `vite.config.js`

## Running locally

Site runs under **Herd** at `cms.test`. No `php artisan serve` needed.

```bash
php artisan migrate          # run pending migrations
php artisan db:seed          # seed initial user + profile
php artisan queue:work       # optional; contact emails send synchronously, no worker needed
php artisan backup:database  # manual DB backup; scheduled daily via console.php
php artisan test             # Pest/PHPUnit suite
php artisan storage:link     # once after fresh install
```

## Routes

### Public

| Route | Name | Controller method | Notes |
|-------|------|-------------------|-------|
| `GET /` | `home` | `HomeController@index` | Data cached forever, per locale, under `home.page.data.{en,nl}` |
| `GET /work` | `work.index` | `HomeController@work` | Archive with client-side JS tag filter |
| `GET /work/tag/{tag}` | `work.tag` | `HomeController@workTag` | Server-rendered tag filter page for SEO; 404 if tag doesn't exist |
| `GET /work/{project:slug}` | `project.show` | `HomeController@project` | 404 if not published |
| `GET /docs` | `docs` | `HomeController@docs` | Client-facing "Working with me" page |
| `GET /cv.pdf` | `cv` | `HomeController@cv` | Streams PDF via dompdf |
| `GET /og/home.png` | `og.home` | `OgImageController@home` | PHP GD 1200×630 OG image |
| `GET /og/work/{project:slug}.png` | `og.project` | `OgImageController@project` | Per-project OG image |
| `GET /og/blog/{post:slug}.png` | `og.post` | `OgImageController@post` | Per-post OG image (title, published date, excerpt) |
| `GET /blog` | `blog.index` | `HomeController@blog` | Blog index (published posts) |
| `GET /blog/{post:slug}` | `blog.show` | `HomeController@post` | 404 if not published |
| `GET /sitemap.xml` | `sitemap` | `SitemapController` | Includes projects, tags, and posts |
| `GET /robots.txt` | `robots` | closure | Disallows `/admin` |
| `POST /contact` | `contact.store` | `ContactController@store` | Throttled via `throttle:contact` |

Every public route is registered **twice**: unprefixed (English default) and under a `/nl` prefix
(`nl.` name prefix) for Dutch. There is no locale-switch route — locale is chosen by the URL prefix.

### Admin (all behind `auth` middleware, prefix `/admin`, name prefix `admin.`)

- `GET /admin` → `admin.dashboard`
- Projects: full CRUD + trash/restore/force-delete + `POST /admin/projects/reorder`
- Testimonials: full CRUD + trash/restore/force-delete
- Skills: full CRUD + trash/restore/force-delete + `POST /admin/skills/reorder`
- Posts: full CRUD + trash/restore/force-delete
- `GET|PUT /admin/profile` — profile edit
- Users: full CRUD
- Contact submissions: `index`, mark read/unread, destroy
- `GET /admin/security` — passkey management (`security.show`)

### Auth (not behind `auth`)

- `GET /admin/login` — email entry (`admin.login`); `POST /admin/logout`
- Email login code (rate-limited `throttle:login`): `POST /admin/login/code` (`admin.login.code.send`),
  `GET /admin/login/code` (`.challenge`), `POST /admin/login/code/verify` (`.verify`)
- Passkey routes are registered by **laravel/passkeys** (`passkey.login`, `passkey.store`,
  `passkey.destroy`, and their `*-options` endpoints)

## Architecture

### Models

| Model | Traits | Notes |
|-------|--------|-------|
| `Profile` | `BustsHomeCache`, `HasLocalizedContent`, `LogsActivity` | Always access via `Profile::current()` — never `find(1)` |
| `Project` | `BustsHomeCache`, `HasLocalizedContent`, `LogsActivity`, `SoftDeletes` | Slug auto-generated on save; `published()` and `ordered()` scopes; `tagList(): array` and `imageUrl(): ?string` helpers |
| `Skill` | `BustsHomeCache`, `LogsActivity`, `SoftDeletes` | `ordered()` scope; grouped by `category` in views |
| `Testimonial` | `BustsHomeCache`, `HasLocalizedContent`, `LogsActivity`, `SoftDeletes` | `featured=true` + latest shown on home |
| `Post` | `BustsHomeCache`, `HasLocalizedContent`, `LogsActivity`, `SoftDeletes` | Blog posts; slug auto-generated; `published()` scope; `published_at` |
| `User` | — | Passkey login (`Laravel\Passkeys`) + emailed login code (`login_code_hash`, `login_code_expires_at`); uses `#[Fillable]`/`#[Hidden]` attributes, no password |
| `ActivityLog` | — | Append-only; written by `LogsActivity` |
| `PageView` | — | Append-only path+timestamp; recorded in every public page controller |
| `PageViewTotal` | — | Per-path lifetime totals rolled up from pruned `page_views` (see Dashboard) |
| `ContactSubmission` | — | Saved on every valid contact form submit; `read_at` inbox flag |

### Traits (`app/Concerns/`)

- **`BustsHomeCache`** — `saved`/`deleted` hooks forget both `home.page.data.en` and `home.page.data.nl`
- **`LogsActivity`** — `created`/`updated`/`deleted` hooks write to `activity_logs`
- **`HasLocalizedContent`** — `localized($field)` returns the `_nl` column when locale is `nl` and it's filled, else the base column

### Controllers

**Public** (`app/Http/Controllers/`)

| Controller | Methods |
|------------|---------|
| `HomeController` | `index`, `docs`, `work`, `workTag`, `cv`, `project`, `blog`, `post` |
| `OgImageController` | `home`, `project`, `post` — private `generate()`, `wrapText()`, `textWidth()` helpers |
| `SitemapController` | `__invoke` — XML sitemap |
| `ContactController` | `store` — validates, saves `ContactSubmission`, sends `ContactFormSubmitted` synchronously |

**Admin** (`app/Http/Controllers/Admin/`)

`ContactSubmissionController`, `DashboardController`, `PostController`, `ProfileController`,
`ProjectController`, `SecurityController`, `SkillController`, `TestimonialController`, `UserController`

`Project`, `Skill`, `Testimonial`, `Post` share the `HandlesSoftDeleteActions` trait
(`restore`/`forceDelete`); `Project` and `Skill` also use `HandlesReordering` (`reorder`).

**Auth** (`app/Http/Controllers/Auth/`)

`AdminLoginController` (login page + logout), `LoginCodeController` (send/challenge/verify)

### Middleware

**`SetLocale`** (`app/Http/Middleware/SetLocale.php`) — sets the locale from the **first URL
segment**: `/nl/*` → `nl`, otherwise the app default (`config('app.locale')`, `en`). Appended to
the `web` group in `bootstrap/app.php` via `$middleware->web(append: [SetLocale::class])`.

### Views

**Public** (`resources/views/`)

| View | Route | Data received |
|------|-------|--------------|
| `home.blade.php` | `/` | `$profile` (stdClass), `$skills` (Collection of Collections of stdClass), `$projects` (Collection of stdClass), `$testimonials` (Collection of stdClass) |
| `work.blade.php` | `/work` | `$profile`, `$projects` (Collection of stdClass with `tag_list`, `image_url`), `$tags` |
| `project.blade.php` | `/work/{slug}` | `$profile`, `$project` (live Eloquent model) |
| `docs.blade.php` | `/docs` | `$profile`, `$skills` (grouped Collection — live Eloquent), `$projects` (live Eloquent Collection) — skills and projects power the tech-defaults and selected-work sections |
| `cv.blade.php` | `/cv.pdf` | `$profile`, `$skills` (grouped Collection of stdClass), `$projects` (Collection of stdClass with `tag_list`) |
| `blog.blade.php` | `/blog` | `$profile`, `$posts` (live Eloquent Collection) |
| `blog-post.blade.php` | `/blog/{slug}` | `$profile`, `$post` (live Eloquent model) |

**Admin** — `resources/views/admin/` with layout `resources/views/layouts/admin.blade.php`

**Auth** — `resources/views/auth/login.blade.php`, `auth/login-code-challenge.blade.php`

### Caching

The home page data is cached forever, **keyed per locale**, under `home.page.data.en` /
`home.page.data.nl` (translatable fields are resolved to the locale before caching).

**Critical**: the cache stores **plain arrays** (`->toArray()`), not Eloquent model instances.
Storing models directly causes `__PHP_Incomplete_Class` errors on deserialization in this
environment (PHP-FPM + SQLite-backed cache). Pre-compute derived values (`tag_list`, `image_url`)
before storing. `BustsHomeCache` invalidates the key whenever content changes. Tests flush
the cache in `Tests\TestCase::setUp()`.

OG images are cached forever under `og.home.{updated_at_ts}`,
`og.project.{id}.{updated_at_ts}`, and `og.post.{id}.{updated_at_ts}`. Stale keys are orphaned
(never hit again) rather than evicted — do not flush the entire cache table to clear them in
production.

### i18n

**URL-prefix locale** (`en` default, `nl` under `/nl`). Language files:

| File | Used by |
|------|---------|
| `lang/en/site.php` + `lang/nl/site.php` | `home.blade.php`, `work.blade.php`, `project.blade.php` |
| `lang/en/docs.php` + `lang/nl/docs.php` | `docs.blade.php` |

Every public route is registered twice (unprefixed + `/nl`); `SetLocale` reads the prefix and
calls `app()->setLocale()`. Views use `__('*.key')` for user-facing strings and carry
`<html lang="{{ app()->getLocale() }}">`. The `localized_route()` and `alternate_locale_url()`
helpers (`app/Support/helpers.php`) build locale-aware URLs and the language-toggle link.
Per-model translatable fields use `_nl` columns via `HasLocalizedContent`. The `cv.blade.php`
view is English-only (PDF output has no language toggle).

### Auth (passkey + email login code)

No passwords. Login at `/admin/login` (enter email). Two paths:

- **Email login code** — `SendLoginCode` generates a 6-digit code, stores its **hash** with a
  10-minute expiry (`login_code_hash`, `login_code_expires_at`) and emails it (`LoginCodeMail`).
  `VerifyLoginCode` checks expiry + `Hash::check`, then clears the code (single-use) and logs in.
  `LoginCodeController` returns a **generic** message whether or not the email matches an account
  (no enumeration). Both send/verify are rate-limited via `throttle:login`.
- **Passkeys** — via **laravel/passkeys** (`User implements PasskeyUser`). Registered/managed from
  `/admin/security` (`SecurityController`); package routes handle registration and WebAuthn login.

There is no TOTP/2FA, recovery codes, or password column (removed in the passkey migration).
Guest redirect: `bootstrap/app.php` sets it to `/admin/login` (not the default `login`).

SSO: `thijssensoftware/id-client` provides "Sign in with Thijssensoftware"; on a successful
callback it logs the user into the `web` guard and (if `provision` is on) auto-creates the local
account, redirecting to `/admin` (`config/id-client.php`).

### Queue

Driver: `database`. The contact notification (`ContactFormSubmitted`) is a plain Mailable sent
**synchronously** in the request by `ContactController`, so the queue worker is **not** required
for contact emails. The send is wrapped in a try/catch: the submission is always saved to the
admin inbox first, so an SMTP failure is reported (logged) without breaking the visitor's success
response. If you add queued jobs later (or make this mailable `ShouldQueue`), run
`php artisan queue:work` in dev and rely on the Supervisor worker in production.

### Images

`intervention/image ^3.0` (GD driver): scaled to max 1600px width at quality 82, stored
in `storage/app/public/projects/`. Run `php artisan storage:link` once after install.

### CV export

`HomeController::cv()` loads `resources/views/cv.blade.php` via dompdf, sets A4 paper,
and streams with filename `str($profile->name)->slug()->append('-cv.pdf')`.

**Important**: `cv.blade.php` must use only inline styles and `<table>` layouts — dompdf
does not support CSS Grid or Flexbox.

### OG images

`OgImageController` uses PHP GD to produce 1200×630 PNG files with the site design tokens
(cream `#F7F7F4`, ink `#17181A`, orange `#E8590C`). Text is drawn with **`imagettftext()`** using
the shipped TrueType fonts in `resources/fonts/` (Space Grotesk / Inter) — not GD bitmap fonts —
so accented characters render correctly and match the site typography. Served with
`Cache-Control: public, max-age=604800`. Requires GD compiled with FreeType.

### Dashboard analytics

`DashboardController::index()` provides:
- Counts: published projects, testimonials, skills, contact submissions
- 30-day sparkline: daily `PageView` counts filled with zeros for days without data
- Total page views and top-5 paths — **all-time**, combining live `page_views` rows with the
  `page_view_totals` rollup

**Retention**: `page-views:prune` (scheduled daily) rolls `page_view` rows older than `--days`
(default 90, floored at 30 to protect the sparkline) into per-path `page_view_totals`, then
deletes them — inside a transaction. Indexed on `created_at` and `path`.

### Database backups

`php artisan backup:database` copies the SQLite file to `storage/app/backups/` with a
timestamp suffix, pruning to the 14 most recent. Scheduled daily in `routes/console.php`.

## Testing

```bash
php artisan test
php artisan test --filter ProjectTest
```

- `RefreshDatabase` + `Cache::flush()` in `TestCase::setUp()`
- Tests hit the real `database.sqlite` (not in-memory SQLite)
- Soft-deleted records: use `assertSoftDeleted`, not `assertDatabaseMissing`
- No database mocking — use factories or direct model creation

## Key gotchas

1. **`Profile::current()`** — always use this, never `Profile::find(1)`. It calls `->refresh()`
   after `firstOrCreate` because Eloquent only hydrates the search key in memory after insert.
   Without refresh, `->toArray()` returns `['id' => 1]` with all other columns missing.

2. **Cache + CSRF** — never cache rendered HTML. A cached page bakes one visitor's CSRF token
   into every subsequent visitor's response. Cache data arrays only.

3. **Soft deletes** — `Project`, `Skill`, `Testimonial`, `Post` use `SoftDeletes` (restore/
   force-delete via the shared `HandlesSoftDeleteActions` trait). Admin trash/restore/force-delete
   routes exist for all four. Always use `withTrashed()` / `onlyTrashed()` where appropriate and
   `assertSoftDeleted()` in tests.

4. **Sort order** — `Project` and `Skill` have `sort_order` columns managed by SortableJS
   drag-and-drop via `/admin/projects/reorder` and `/admin/skills/reorder` POST endpoints.

5. **OG cache orphaning** — stale OG image cache keys accumulate in the SQLite cache table.
   Pruning them requires the `og:prune-cache` command (scheduled weekly), not `Cache::flush()`
   (which would also clear `home.page.data.{en,nl}` and break the home page until the next
   request warms it).

6. **i18n files** — `__('site.*')` covers `home`, `work`, and `project` views; `__('docs.*')`
   covers the docs page. Adding a new string requires adding the key to both the `en` and `nl`
   files, otherwise Blade silently returns the raw `site.key` string as the rendered value.

7. **dompdf layout** — use `<table>` and inline styles only inside `cv.blade.php`. CSS Grid
   and Flexbox are not supported by dompdf.

## Tracker

File tickets under the **CMS** ("Portfolio CMS") project via the `create-linear-ticket` skill
(`--project CMS`), producing `CMS-###` identifiers — **not** the THI umbrella project.

Branch format: `feature/CMS-{number}-{description}` or `fix/CMS-{number}-{description}`.

Follow the full workflow in `~/.claude/CLAUDE.md`. See parent context in `~/Projects/cms/CLAUDE.md`.
