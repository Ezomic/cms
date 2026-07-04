# Deployment

This document covers provisioning a fresh VPS, deploying for the first time, and the day-to-day deploy workflow.

## Stack assumptions

| Concern | Choice |
|---------|--------|
| OS | Ubuntu 24.04 LTS |
| Web server | Nginx |
| PHP | 8.4 via Ondřej PPA |
| Database | SQLite (single file, no server needed) |
| Queue | `database` driver + Supervisor |
| Scheduler | `* * * * * php artisan schedule:run` via cron |
| SSL | Let's Encrypt via Certbot |
| Recommended VPS | Hetzner CX22 (2 vCPU, 4 GB RAM, €5/mo, EU datacenter) |

---

## 1. Provision a new server

### Prerequisites
- A VPS running Ubuntu 24.04 LTS
- Root SSH access
- A domain pointed at the server's IP (`A` record for both `yourdomain.nl` and `www.yourdomain.nl`)

### Run the setup script

```bash
# On the server, as root:
export DOMAIN=yourdomain.nl
export ADMIN_EMAIL=you@yourdomain.nl
export GIT_REPO=git@github.com:Ezomic/cms.git

curl -fsSL https://raw.githubusercontent.com/Ezomic/cms/main/scripts/server-setup.sh | bash
```

Or clone first and run locally:

```bash
git clone git@github.com:Ezomic/cms.git /var/www/cms
DOMAIN=yourdomain.nl ADMIN_EMAIL=you@yourdomain.nl bash /var/www/cms/scripts/server-setup.sh
```

The script is idempotent — safe to re-run if it fails partway through.

**What it installs:** PHP 8.4 + extensions, Nginx, Composer, SQLite3, Certbot, Supervisor, UFW.

**What it configures:** PHP-FPM pool, Nginx vhost, SSL certificate, Supervisor queue worker, cron scheduler, firewall (SSH + HTTP + HTTPS only).

---

## 2. Configure the environment

After provisioning, create the production `.env`:

```bash
cp /var/www/cms/.env.production.example /var/www/cms/.env
nano /var/www/cms/.env          # or your editor of choice
```

Minimum required values:

```dotenv
APP_KEY=             # php artisan key:generate --show
APP_URL=https://yourdomain.nl

MAIL_HOST=           # SMTP host (Postmark, Resend, Mailgun, etc.)
MAIL_USERNAME=       # SMTP username / API token
MAIL_PASSWORD=       # SMTP password / API token
MAIL_FROM_ADDRESS=   # hello@yourdomain.nl
```

Generate the app key:

```bash
cd /var/www/cms && php artisan key:generate
```

---

## 3. First deploy

```bash
su - deploy
cd /var/www/cms && bash scripts/deploy.sh
```

This will:
1. Pull the latest `main`
2. Run `composer install --no-dev`
3. Run `php artisan migrate`
4. Create the `public/storage` symlink
5. Rebuild all caches
6. Restart PHP-FPM and the queue worker
7. Hit `GET /` and verify HTTP 200

After the first deploy, seed the initial admin user and profile:

```bash
php artisan db:seed
```

Then visit `https://yourdomain.nl/admin/login` and log in with:
- Email: `admin@example.com`
- Password: `password`

**Change the password immediately** under Admin → Admins.

---

## 4. Day-to-day deployments

### From your local machine

```bash
bash scripts/deploy.sh --remote deploy@yourdomain.nl
```

### On the server directly

```bash
ssh deploy@yourdomain.nl
cd /var/www/cms && bash scripts/deploy.sh
```

### Via GitHub Actions (automatic)

Every push to `main` triggers:
1. A test run (25 tests against SQLite)
2. If tests pass: SSH deploy to the server

Required GitHub secrets (set under **Settings → Secrets → Actions**):

| Secret | Value |
|--------|-------|
| `DEPLOY_HOST` | your server's IP or domain |
| `DEPLOY_USER` | `deploy` |
| `DEPLOY_SSH_KEY` | contents of the deploy user's private SSH key |

To generate a dedicated deploy key:

```bash
ssh-keygen -t ed25519 -C "github-actions-deploy" -f ~/.ssh/cms_deploy -N ""
# Add the public key to the server:
ssh-copy-id -i ~/.ssh/cms_deploy.pub deploy@yourdomain.nl
# Add the private key to GitHub secrets as DEPLOY_SSH_KEY
cat ~/.ssh/cms_deploy
```

---

## 5. Useful server commands

```bash
# View logs
tail -f /var/www/cms/storage/logs/laravel.log
tail -f /var/log/nginx/cms.error.log

# Queue worker status
sudo supervisorctl status cms-queue:*

# Restart queue worker
sudo supervisorctl restart cms-queue:*

# Clear all caches manually
cd /var/www/cms && php artisan cache:clear && php artisan config:cache

# Manual database backup
cd /var/www/cms && php artisan backup:database

# Run scheduler manually (for testing)
cd /var/www/cms && php artisan schedule:run --verbose
```

---

## 6. Backups

The `backup:database` command (scheduled daily at midnight) copies `database/database.sqlite`
to `storage/app/backups/` with a timestamp suffix and prunes to the 14 most recent files.

For off-site backups, set up a cron job to `rsync` the backups directory to an external location,
or use Hetzner's snapshot feature (snapshots the entire VPS).

---

## 7. Rollback

There is no built-in rollback command. Since the database is SQLite:

1. The latest backup is in `storage/app/backups/`
2. To roll back to a previous commit:

```bash
cd /var/www/cms
php artisan down
git log --oneline -10          # find the target commit
git reset --hard <commit-sha>
cp storage/app/backups/<latest>.sqlite database/database.sqlite
composer install --no-dev --optimize-autoloader
php artisan config:cache && php artisan route:cache && php artisan view:cache
sudo systemctl reload php8.4-fpm
php artisan up
```

---

## 8. SSL renewal

Certbot registers a systemd timer that renews certificates automatically.
Verify with:

```bash
systemctl status certbot.timer
certbot renew --dry-run
```
