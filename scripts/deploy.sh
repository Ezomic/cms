#!/usr/bin/env bash
# =============================================================================
# deploy.sh — deploy latest code to the production server
#
# Run ON the server (as the deploy user):
#   cd /home/deploy/cms && bash scripts/deploy.sh
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
#   5. Builds frontend assets (Vite) and verifies the manifest
#   6. Clears and rebuilds caches
#   7. Creates storage symlink if missing
#   8. Sets correct permissions
#   9. Restarts PHP-FPM and queue worker
#  10. Takes the site out of maintenance mode
#  11. Runs a smoke test (HTTP 200 on /)
#
# Note: this script pulls its own source (step 2). If the pull changes deploy.sh,
# it re-executes the updated copy once so newly added steps are not skipped.
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

# Hash of this script before we pull, so we can detect when the pull rewrites
# deploy.sh itself and re-execute the updated version (see step 2).
SELF="$APP_DIR/scripts/deploy.sh"
SELF_HASH_BEFORE="$(sha256sum "$SELF" | awk '{print $1}')"

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

# Safety net: if any later step fails, still bring the site back up rather than
# leaving it stuck in maintenance mode. Harmless (idempotent) on the success path.
trap '$PHP artisan up > /dev/null 2>&1 || true' EXIT

# ── 2. Pull latest code ───────────────────────────────────────────────────────
step "Pulling from origin/main"
git fetch origin
git reset --hard origin/main
ok "$(git log -1 --format='%h %s')"

# If the pull changed deploy.sh itself, bash is still executing the old version
# from before the reset, so any newly added step below would be silently skipped.
# Re-exec the updated script exactly once (guarded by DEPLOY_REEXEC to avoid a loop).
if [[ -z "${DEPLOY_REEXEC:-}" && "$(sha256sum "$SELF" | awk '{print $1}')" != "$SELF_HASH_BEFORE" ]]; then
  ok "deploy.sh changed in this pull — re-executing the updated script"
  export DEPLOY_REEXEC=1
  exec bash "$SELF"
fi

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

# ── 5b. Frontend assets (Inertia/Vue via Vite) ────────────────────────────────
step "Building frontend assets"
npm ci --no-audit --no-fund
npm run build
if [[ ! -f "public/build/manifest.json" ]]; then
  echo "  ✗ Vite build produced no public/build/manifest.json — the admin (Inertia) would 500." >&2
  exit 1
fi
ok "Assets built"

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
# No `systemctl reload php8.4-fpm` here on purpose. All 21 sites on the
# droplet share one php-fpm master and therefore one opcache, so a reload
# to deploy this app would discard ~350MB of cached bytecode belonging to
# every other app and force them all to recompile. opcache.validate_timestamps
# is on, so the new code is picked up on the next request without it.
# See INFRA-28. If that setting is ever turned off, this must come back.

step "Restarting queue worker"
if sudo supervisorctl status cms-queue: > /dev/null 2>&1; then
  sudo supervisorctl restart cms-queue:* > /dev/null
  ok "Queue worker restarted"
else
  ok "No cms-queue worker configured — skipping"
fi

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
