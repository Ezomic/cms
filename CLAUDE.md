# CMS — Project context for Claude

## What this is

A freelancer portfolio CMS for Robbin Thijssen (Dutch developer/designer). The public site has a
portfolio home page (`/`), a work archive (`/work`), project case-study pages (`/work/{slug}`), a
client-facing docs page (`/docs`), and a CV download (`/cv.pdf`). Everything behind `/admin` is a
Blade-rendered CMS.

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
php artisan queue:work       # needed for contact-form emails (database driver)
php artisan backup:database  # manual DB backup; scheduled daily via console.php
php artisan test             # Pest suite (~25 tests)
php artisan storage:link     # once after fresh install
```

## Routes

### Public

| Route | Name | Controller method | Notes |
|-------|------|-------------------|-------|
| `GET /` | `home` | `HomeController@index` | Data cached forever under `home.page.data` |
| `GET /work` | `work.index` | `HomeController@work` | Archive with client-side JS tag filter |
| `GET /work/tag/{tag}` | `work.tag` | `HomeController@workTag` | Server-rendered tag filter page for SEO; 404 if tag doesn't exist |
| `GET /work/{project:slug}` | `project.show` | `HomeController@project` | 404 if not published |
| `GET /docs` | `docs` | `HomeController@docs` | Client-facing "Working with me" page |
| `GET /cv.pdf` | `cv` | `HomeController@cv` | Streams PDF via dompdf |
| `GET /og/home.png` | `og.home` | `OgImageController@home` | PHP GD 1200×630 OG image |
| `GET /og/work/{project:slug}.png` | `og.project` | `OgImageController@project` | Per-project OG image |
| `GET /og/blog/{post:slug}.png` | `og.post` | `OgImageController@post` | Per-post OG image (title, published date, excerpt) |
| `POST /contact` | `contact.store` | `ContactController@store` | Throttled 5/min by IP |
| `POST /locale/{locale}` | `locale.switch` | closure | Stores `en`/`nl` in session |

### Admin (all behind `auth` middleware, prefix `/admin`, name prefix `admin.`)

- `GET /admin` → `admin.dashboard`
- Projects: full CRUD + trash/restore/force-delete + `POST /admin/projects/reorder`
- Testimonials: full CRUD + trash/restore/force-delete
- Skills: full CRUD + trash/restore/force-delete + `POST /admin/skills/reorder`
- `GET|PUT /admin/profile` — profile edit
- `GET|PUT /admin/settings` — site settings
- Users: full CRUD
- 2FA: `GET /admin/two-factor`, `POST /two-factor/enable`, `POST /two-factor/confirm`, `DELETE /two-factor`

### Auth (not behind `auth`)

- `GET|POST /admin/login` — rate-limited `throttle:login` (5/min by IP)
- `GET|POST /admin/two-factor-challenge` — 2FA verify step; rate-limited

## Architecture

### Models

| Model | Traits | Notes |
|-------|--------|-------|
| `Profile` | `BustsHomeCache`, `LogsActivity` | Always access via `Profile::current()` — never `find(1)` |
| `Project` | `BustsHomeCache`, `LogsActivity`, `SoftDeletes` | Slug auto-generated on save; `published()` and `ordered()` scopes; `tagList(): array` and `imageUrl(): ?string` helpers |
| `Skill` | `BustsHomeCache`, `LogsActivity`, `SoftDeletes` | `ordered()` scope; grouped by `category` in views |
| `Testimonial` | `BustsHomeCache`, `LogsActivity`, `SoftDeletes` | `featured=true` + latest shown on home |
| `User` | — | 2FA: `two_factor_secret` (encrypted), `two_factor_confirmed_at` |
| `ActivityLog` | — | Append-only; written by `LogsActivity` |
| `PageView` | — | Append-only path+timestamp; recorded in `index()`, `work()`, `project()` |
| `ContactSubmission` | — | Saved on every valid contact form submit |

### Traits (`app/Concerns/`)

- **`BustsHomeCache`** — `saved`/`deleted` hooks call `Cache::forget('home.page.data')`
- **`LogsActivity`** — `created`/`updated`/`deleted` hooks write to `activity_logs`

### Controllers

**Public** (`app/Http/Controllers/`)

| Controller | Methods |
|------------|---------|
| `HomeController` | `index`, `docs`, `work`, `cv`, `project` |
| `OgImageController` | `home`, `project`, `post` — private `generate()` and `wrapText()` helpers |
| `ContactController` | `store` — validates, saves `ContactSubmission`, dispatches `ContactFormSubmitted` |

**Admin** (`app/Http/Controllers/Admin/`)

`DashboardController`, `ProfileController`, `ProjectController`, `SettingsController`,
`SkillController`, `TestimonialController`, `TwoFactorController`, `UserController`

**Auth** (`app/Http/Controllers/Auth/`)

`AdminLoginController`, `TwoFactorChallengeController`

### Middleware

**`SetLocale`** (`app/Http/Middleware/SetLocale.php`) — reads `session('locale')`, validates
against `['en', 'nl']`, calls `app()->setLocale()`. Appended to the `web` group in
`bootstrap/app.php` via `$middleware->web(append: [SetLocale::class])`.

### Views

**Public** (`resources/views/`)

| View | Route | Data received |
|------|-------|--------------|
| `home.blade.php` | `/` | `$profile` (stdClass), `$skills` (Collection of Collections of stdClass), `$projects` (Collection of stdClass), `$testimonials` (Collection of stdClass) |
| `work.blade.php` | `/work` | `$profile`, `$projects` (Collection of stdClass with `tag_list`, `image_url`), `$tags` |
| `project.blade.php` | `/work/{slug}` | `$profile`, `$project` (live Eloquent model) |
| `docs.blade.php` | `/docs` | `$profile`, `$skills` (grouped Collection — live Eloquent), `$projects` (live Eloquent Collection) — skills and projects power the tech-defaults and selected-work sections |
| `cv.blade.php` | `/cv.pdf` | `$profile`, `$skills` (grouped Collection of stdClass), `$projects` (Collection of stdClass with `tag_list`) |

**Admin** — `resources/views/admin/` with layout `resources/views/layouts/admin.blade.php`

**Auth** — `resources/views/auth/login.blade.php`, `auth/two-factor-challenge.blade.php`

### Caching

The home page is cached forever under `home.page.data`.

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

Session-based locale (`en` / `nl`). Language files:

| File | Used by |
|------|---------|
| `lang/en/site.php` + `lang/nl/site.php` | `home.blade.php`, `work.blade.php`, `project.blade.php` |
| `lang/en/docs.php` + `lang/nl/docs.php` | `docs.blade.php` |

The `locale.switch` route stores the choice in `session('locale')`; `SetLocale` middleware
applies it on every request. All four public views (`home`, `work`, `project`, `docs`) use
`__('*.key')` for every user-facing string and carry a `<html lang="{{ app()->getLocale() }}">`
attribute. The `cv.blade.php` view is English-only (PDF output has no language toggle).

### Auth & 2FA

Login at `/admin/login`. With 2FA enabled, successful login stores a pending user ID in
session and redirects to `/two-factor-challenge`. On verify the session is cleared and
the user is fully logged in. Recovery codes are single-use and stripped from the encrypted
array on use.

QR code on the 2FA setup page is rendered client-side via **QRious.js** CDN v4.0.2 on a
`<canvas id="qr-canvas">`. The OTP auth URI is passed via `Js::from($otpAuthUri)`.

Guest redirect: `bootstrap/app.php` sets it to `/admin/login` (not the default `login`).

### Queue

Driver: `database`. `ContactFormSubmitted` implements `ShouldQueue` but `ContactController`
currently calls `Mail::send()` (synchronous), so the queue worker is not required for emails.
If you switch back to `Mail::queue()`, run `php artisan queue:work` in dev and configure
Supervisor in production.

### Images

`intervention/image ^3.0` (GD driver): scaled to max 1600px width at quality 82, stored
in `storage/app/public/projects/`. Run `php artisan storage:link` once after install.

### CV export

`HomeController::cv()` loads `resources/views/cv.blade.php` via dompdf, sets A4 paper,
and streams with filename `str($profile->name)->slug()->append('-cv.pdf')`.

**Important**: `cv.blade.php` must use only inline styles and `<table>` layouts — dompdf
does not support CSS Grid or Flexbox.

### OG images

`OgImageController` uses PHP GD (`imagecreatetruecolor`, `imagestring`, etc.) to produce
1200×630 PNG files with the site design tokens (cream `#F7F7F4`, ink `#17181A`, orange
`#E8590C`). Served with `Cache-Control: public, max-age=604800`.

### Dashboard analytics

`DashboardController::index()` provides:
- Counts: published projects, testimonials, skills, contact submissions
- 30-day sparkline: daily `PageView` counts filled with zeros for days without data
- Top-5 paths by view count

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

3. **Soft deletes** — `Project`, `Skill`, `Testimonial` use `SoftDeletes`. Admin trash/restore/
   force-delete routes exist for all three. Always use `withTrashed()` / `onlyTrashed()` where
   appropriate and `assertSoftDeleted()` in tests.

4. **Sort order** — `Project` and `Skill` have `sort_order` columns managed by SortableJS
   drag-and-drop via `/admin/projects/reorder` and `/admin/skills/reorder` POST endpoints.

5. **OG cache orphaning** — stale OG image cache keys accumulate in the SQLite cache table.
   Pruning them requires direct SQL on the `cache` table, not `Cache::flush()` (which would
   also clear `home.page.data` and break the home page until the next request warms it).

6. **i18n files** — `__('site.*')` covers `home`, `work`, and `project` views; `__('docs.*')`
   covers the docs page. Adding a new string requires adding the key to both the `en` and `nl`
   files, otherwise Blade silently returns the raw `site.key` string as the rendered value.

7. **dompdf layout** — use `<table>` and inline styles only inside `cv.blade.php`. CSS Grid
   and Flexbox are not supported by dompdf.

## Linear

Team: **THI** (Thijssen Software) — `3b1bf7b2-5ff4-4e70-9ca5-a1efb1280839`

Branch format: `feature/thi-{number}-{description}` or `fix/thi-{number}-{description}`

Follow the full workflow in `~/.claude/CLAUDE.md`. See parent context in `~/Projects/cms/CLAUDE.md`.
