# Portfolio CMS (Laravel)

Your freelancer site, rebuilt as a Laravel app with an admin panel for editing
your projects, testimonials, skills, and profile info — no more hand-editing HTML.

This folder contains only the **app-specific files** (models, migrations,
controllers, views, routes). You drop them into a fresh Laravel install.

## 1. Create a fresh Laravel app

```bash
composer create-project laravel/laravel portfolio-cms
cd portfolio-cms
```

## 2. Copy these files in

From this package, copy each folder into the matching folder in `portfolio-cms/`,
overwriting where prompted:

```
app/Models/            → portfolio-cms/app/Models/
app/Http/Controllers/  → portfolio-cms/app/Http/Controllers/
database/migrations/   → portfolio-cms/database/migrations/
database/seeders/       → portfolio-cms/database/seeders/  (overwrite DatabaseSeeder.php)
resources/views/        → portfolio-cms/resources/views/    (overwrite welcome.blade.php is fine to delete)
routes/web.php          → portfolio-cms/routes/web.php      (overwrite)
```

(`app/Http/Controllers/Controller.php` and `app/Models/User.php` already exist
in a fresh Laravel install — leave those as-is.)

## 3. Set up the database

The simplest option is SQLite (no server to install):

```bash
touch database/database.sqlite
```

In `.env`, set:
```
DB_CONNECTION=sqlite
```
(remove or comment out the other `DB_*` lines)

## 4. Install, migrate, seed

```bash
composer install
php artisan key:generate
php artisan migrate --seed
```

This creates all tables and seeds:
- Your admin login: **admin@example.com / password**
- Placeholder profile, 3 example projects, your skill list, and 1 testimonial

## 5. Run it

```bash
php artisan serve
```

- Public site: **http://localhost:8000**
- Admin panel: **http://localhost:8000/admin/login**

Log in, then edit your profile, projects, testimonials, and skills from the
sidebar — changes appear on the live site immediately.

## Notes

- **Change the admin password** after your first login (there's no self-service
  password change screen yet — easiest is `php artisan tinker` and
  `User::first()->update(['password' => bcrypt('new-password')]);`).
- The admin panel uses Tailwind via CDN, so no frontend build step is required.
- The public site's design is unchanged from the original static version —
  same layout, fonts, and grid-background hero — just now pulling from the database.
- To deploy, point `DB_CONNECTION` at MySQL/Postgres instead of SQLite and run
  `php artisan migrate --seed --force` on the server.
