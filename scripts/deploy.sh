#!/usr/bin/env bash
# =============================================================================
# deploy.sh — deploy latest code to the production server
#
# Run ON the server (as the deploy user):
#   cd /var/www/cms && bash scripts/deploy.sh
#
# Run FROM your local machine:
#   bash scripts/deploy.sh --remote deploy@your-server
#   (Requires SSH access and the server already provisioned.)
#
# What it does:
#   1. Puts the site into maintenance mode
#   2. Pulls latest code from main
#   3. Installs/updates Composer dependencies (no-dev)
#   4. Runs migrations
#   5. Clears and rebuilds caches
#   6. Creates storage symlink if missing
#   7. Sets correct permissions
#   8. Restarts PHP-FPM and queue worker
#   9. Takes the site out of maintenance mode
#  10. Runs a smoke test (HTTP 200 on /)
# =============================================================================

set -euo pipefail

APP_DIR="${APP_DIR:-/home/deploy/cms}"
PHP="${PHP:-php}"

# ── Remote mode ───────────────────────────────────────────────────────────────
# If --remote <user@host> is passed, re-execute this script over SSH.
if [[ "${1:-}" == "--remote" ]]; then
  if [[ -z "${2:-}" ]]; then
    echo "Usage: bash scripts/deploy.sh --remote deploy@your-server" >&2
    exit 1
  fi
  HOST="$2"
  echo "▶ Deploying to $HOST"
  ssh -T "$HOST" "cd $APP_DIR && bash scripts/deploy.sh"
  exit $?
fi

# ── Guards ────────────────────────────────────────────────────────────────────
if [[ ! -f "$APP_DIR/artisan" ]]; then
  echo "ERROR: $APP_DIR/artisan not found. Run from the repo root or set APP_DIR." >&2
  exit 1
fi

cd "$APP_DIR"

if [[ ! -f ".env" ]]; then
  echo "ERROR: .env not found in $APP_DIR. Copy .env.production.example and fill it in." >&2
  exit 1
fi

# ── Helpers ───────────────────────────────────────────────────────────────────
step() { echo; echo "▶ $*"; }
ok()   { echo "  ✓ $*"; }

START=$(date +%s)
echo "════════════════════════════════════════════"
echo "  Deploying cms  —  $(date '+%Y-%m-%d %H:%M:%S')"
echo "════════════════════════════════════════════"

# ── 1. Maintenance mode ───────────────────────────────────────────────────────
step "Enabling maintenance mode"
$PHP artisan down --retry=10
ok "Site is down"

# ── 2. Pull latest code ───────────────────────────────────────────────────────
step "Pulling from origin/main"
git fetch origin
git reset --hard origin/main
ok "$(git log -1 --format='%h %s')"

# ── 3. Composer ───────────────────────────────────────────────────────────────
step "Installing Composer dependencies"
composer install \
  --no-dev \
  --no-interaction \
  --prefer-dist \
  --optimize-autoloader \
  --quiet
ok "Composer up to date"

# ── 4. Storage symlink ────────────────────────────────────────────────────────
if [[ ! -L "public/storage" ]]; then
  step "Creating storage symlink"
  $PHP artisan storage:link
  ok "Symlink created"
fi

# ── 5. Database migrations ────────────────────────────────────────────────────
step "Running migrations"
$PHP artisan migrate --force
ok "Migrations complete"

# ── 6. Caches ─────────────────────────────────────────────────────────────────
step "Clearing and rebuilding caches"
$PHP artisan cache:clear
$PHP artisan config:cache
$PHP artisan route:cache
$PHP artisan view:cache
$PHP artisan event:cache
ok "Caches rebuilt"

# ── 7. Permissions ────────────────────────────────────────────────────────────
step "Fixing permissions"
chmod -R 775 storage bootstrap/cache 2>/dev/null || true
chmod 664 database/database.sqlite 2>/dev/null || true
ok "Permissions set"

# ── 8. Restart services ───────────────────────────────────────────────────────
step "Restarting PHP-FPM"
PHP_VERSION=$(php -r "echo PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;")
sudo systemctl reload php${PHP_VERSION}-fpm
ok "PHP-FPM reloaded"

step "Restarting queue worker"
sudo supervisorctl restart cms-queue:* > /dev/null
ok "Queue worker restarted"

# ── 9. Back online ────────────────────────────────────────────────────────────
step "Disabling maintenance mode"
$PHP artisan up
ok "Site is live"

# ── 10. Smoke test ────────────────────────────────────────────────────────────
step "Smoke test"
APP_URL=$($PHP artisan tinker --execute="echo config('app.url');" 2>/dev/null | tail -1)
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" --max-time 10 "$APP_URL/" || echo "000")

if [[ "$HTTP_CODE" == "200" ]]; then
  ok "GET $APP_URL/ → $HTTP_CODE"
else
  echo "  ✗ GET $APP_URL/ → $HTTP_CODE" >&2
  echo "  Check /var/log/nginx/cms.error.log and storage/logs/laravel.log" >&2
  exit 1
fi

END=$(date +%s)
echo
echo "════════════════════════════════════════════"
echo "  Deploy complete in $((END - START))s"
echo "════════════════════════════════════════════"
