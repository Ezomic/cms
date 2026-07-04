# CMS — Project context for Claude

## What this is

A freelancer portfolio CMS for a Dutch developer/designer. The public site is a single-page
portfolio (`/`), project case-study pages (`/work/{slug}`), a documentation page (`/docs`), and
a contact form. Everything behind `/admin` is a Blade-rendered CMS.

## Stack

- PHP 8.4, Laravel 13 — **Blade only**, no Inertia, no Vue, no Livewire
- SQLite — single file at `database/database.sqlite`
- Tailwind is **not** used; all styling is inline `<style>` blocks with CSS custom properties
- No npm build step beyond a bare `vite.config.js`; the public CSS/JS lives in `resources/`

## Running locally

Site runs under **Herd** at `cms.test`. No `php artisan serve` needed.

```bash
php artisan migrate          # run pending migrations
php artisan db:seed          # seed initial user + profile
php artisan queue:work       # needed for contact-form emails (database driver)
php artisan backup:database  # manual DB backup; scheduled daily via console.php
php artisan test             # Pest suite (~25 tests)
```

## Architecture

### Models

| Model | Traits | Notes |
|-------|--------|-------|
| `Profile` | `BustsHomeCache`, `LogsActivity` | Always access via `Profile::current()` — never `find(1)` directly |
| `Project` | `BustsHomeCache`, `LogsActivity`, `SoftDeletes` | Slug auto-generated on save; `published` scope for public site |
| `Skill` | `BustsHomeCache`, `LogsActivity`, `SoftDeletes` | Grouped by `category` on home page |
| `Testimonial` | `BustsHomeCache`, `LogsActivity`, `SoftDeletes` | `featured=true` + latest shown on home |
| `User` | — | 2FA columns: `two_factor_secret` (encrypted), `two_factor_confirmed_at` |
| `ActivityLog` | — | Append-only; written by `LogsActivity` trait |
| `PageView` | — | Append-only path+timestamp rows |
| `ContactSubmission` | — | Saved on every valid contact form submit |

### Traits (`app/Concerns/`)

- **`BustsHomeCache`** — `saved`/`deleted` hooks call `Cache::forget('home.page.data')`
- **`LogsActivity`** — `created`/`updated`/`deleted` hooks write to `activity_logs`

### Actions & Services

- Business logic goes in `app/Actions/` (one `handle()` method)
- Infrastructure concerns (external APIs, etc.) go in `app/Services/`
- Controllers are thin — validate, call action, redirect

### Caching

The home page is cached forever under the key `home.page.data`.

**Critical**: the cache stores **plain arrays** (`->toArray()`), not Eloquent model instances.
Storing models directly causes `__PHP_Incomplete_Class` errors on deserialization in this
environment (PHP-FPM + SQLite-backed cache). Pre-compute derived values (`tag_list`, `image_url`)
before storing so the view receives everything it needs.

`BustsHomeCache` invalidates the key whenever content changes. Tests flush the cache in
`Tests\TestCase::setUp()` to prevent cross-test pollution.

### Auth & 2FA

Login is at `/admin/login`. Successful login with 2FA enabled redirects to `/two-factor-challenge`
which stores the pending user ID in the session. On verify, the session is cleared and the user
is fully logged in. Recovery codes are single-use and stripped from the encrypted array on use.

Rate limiters: `login` (5/min by IP), `contact` (5/min by IP) — defined in `AppServiceProvider`.

Guest redirect is set to `/admin/login` in `bootstrap/app.php` (not the default `login` route).

### Queue

Driver: `database`. The `ContactFormSubmitted` mailable implements `ShouldQueue` with 3 tries.
Run `php artisan queue:work` in development; set up a supervisor process in production.

### Images

Uploaded project images are processed via `intervention/image ^3.0` (GD driver):
scaled down to max 1600px width at quality 82, stored in `storage/app/public/projects/`.
Run `php artisan storage:link` once after a fresh install.

### Database backups

`php artisan backup:database` copies the SQLite file to `storage/app/backups/` with a
timestamp suffix and prunes to the 14 most recent. Scheduled daily in `routes/console.php`.

## Testing

```bash
php artisan test
php artisan test --filter ProjectTest
```

- Uses `RefreshDatabase` + `Cache::flush()` in `TestCase::setUp()`
- SQLite in-memory not used — tests hit the real `database.sqlite` via `RefreshDatabase`
- Soft-deleted records: assert with `assertSoftDeleted`, not `assertDatabaseMissing`
- No database mocking — use factories or direct model creation

## Key gotchas

1. **Profile::current()** refreshes after `firstOrCreate` if the row was just inserted, because
   Eloquent only populates the search key (`id`) in memory after insert. Without `->refresh()`,
   `->toArray()` returns `['id' => 1]` with all other columns missing.

2. **Cache + CSRF**: never cache rendered HTML — it bakes one visitor's CSRF token into every
   subsequent visitor's page. Cache data arrays only.

3. **Soft deletes**: Project/Skill/Testimonial use `SoftDeletes`. The admin trash/restore/force-
   delete routes exist for all three. `withTrashed()` / `onlyTrashed()` scopes apply.

4. **Sort order**: Projects and Skills have `sort_order` columns managed by SortableJS drag-and-drop
   via `/admin/projects/reorder` and `/admin/skills/reorder` POST endpoints.
