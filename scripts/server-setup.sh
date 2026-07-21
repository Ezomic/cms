#!/usr/bin/env bash
# =============================================================================
# server-setup.sh — one-time provisioning for a fresh Ubuntu 24.04 LTS VPS
#
# Usage (run as root on the server):
#   curl -fsSL https://raw.githubusercontent.com/Ezomic/cms/main/scripts/server-setup.sh | bash
#   — or —
#   bash scripts/server-setup.sh
#
# What it does:
#   1. Installs PHP 8.4, Nginx, Composer, SQLite3, Certbot, Supervisor
#   2. Creates a dedicated `deploy` user with a home at /home/deploy
#   3. Clones the repository and wires up the .env
#   4. Configures Nginx + PHP-FPM for the domain
#   5. Obtains an SSL certificate via Let's Encrypt
#   6. Sets up Supervisor for the queue worker
#   7. Installs the cron for the daily backup + scheduled tasks
#
# Variables you MUST set before running:
#   DOMAIN      — the public domain, e.g. robbinthijssen.nl
#   GIT_REPO    — SSH or HTTPS clone URL, e.g. git@github.com:Ezomic/cms.git
#   ADMIN_EMAIL — used for Certbot renewal notices
# =============================================================================

set -euo pipefail

# ── Configuration ─────────────────────────────────────────────────────────────
DOMAIN="${DOMAIN:-}"
GIT_REPO="${GIT_REPO:-git@github.com:Ezomic/cms.git}"
ADMIN_EMAIL="${ADMIN_EMAIL:-}"
APP_DIR="/home/deploy/cms"
DEPLOY_USER="deploy"
PHP_VERSION="8.4"

# ── Guards ────────────────────────────────────────────────────────────────────
if [[ $EUID -ne 0 ]]; then
  echo "ERROR: run this script as root (sudo -i, then bash server-setup.sh)" >&2
  exit 1
fi

if [[ -z "$DOMAIN" ]]; then
  echo "ERROR: set DOMAIN before running, e.g.  DOMAIN=robbinthijssen.nl bash server-setup.sh" >&2
  exit 1
fi

if [[ -z "$ADMIN_EMAIL" ]]; then
  echo "ERROR: set ADMIN_EMAIL before running" >&2
  exit 1
fi

# ── Helpers ───────────────────────────────────────────────────────────────────
step() { echo; echo "▶ $*"; }
ok()   { echo "  ✓ $*"; }

# ── 1. System packages ────────────────────────────────────────────────────────
step "Updating apt"
apt-get update -q
apt-get upgrade -y -q

step "Adding PHP $PHP_VERSION PPA"
apt-get install -y -q software-properties-common
add-apt-repository -y ppa:ondrej/php
apt-get update -q

step "Installing PHP $PHP_VERSION and extensions"
apt-get install -y -q \
  php${PHP_VERSION}-cli \
  php${PHP_VERSION}-fpm \
  php${PHP_VERSION}-sqlite3 \
  php${PHP_VERSION}-mbstring \
  php${PHP_VERSION}-xml \
  php${PHP_VERSION}-curl \
  php${PHP_VERSION}-zip \
  php${PHP_VERSION}-gd \
  php${PHP_VERSION}-bcmath \
  php${PHP_VERSION}-intl
ok "PHP $(php${PHP_VERSION} --version | head -1)"

step "Installing Nginx"
apt-get install -y -q nginx
ok "Nginx $(nginx -v 2>&1)"

step "Installing SQLite3, Git, Unzip, Certbot, Supervisor"
apt-get install -y -q sqlite3 git unzip certbot python3-certbot-nginx supervisor

step "Installing Composer"
if ! command -v composer &>/dev/null; then
  curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
fi
ok "Composer $(composer --version --no-ansi)"

# ── 2. Deploy user ────────────────────────────────────────────────────────────
step "Creating $DEPLOY_USER user"
if ! id "$DEPLOY_USER" &>/dev/null; then
  useradd -m -s /bin/bash "$DEPLOY_USER"
  ok "User $DEPLOY_USER created"
else
  ok "User $DEPLOY_USER already exists"
fi

# Allow deploy user to reload PHP-FPM and Nginx without a password
echo "$DEPLOY_USER ALL=(ALL) NOPASSWD: /bin/systemctl reload php${PHP_VERSION}-fpm, /bin/systemctl reload nginx, /bin/systemctl restart supervisor" \
  > /etc/sudoers.d/deploy
chmod 440 /etc/sudoers.d/deploy

# ── 3. Clone repository ───────────────────────────────────────────────────────
step "Cloning $GIT_REPO to $APP_DIR"
if [[ ! -d "$APP_DIR/.git" ]]; then
  sudo -u "$DEPLOY_USER" git clone "$GIT_REPO" "$APP_DIR"
  ok "Cloned"
else
  ok "Already cloned — skipping"
fi

# ── 4. PHP-FPM pool ───────────────────────────────────────────────────────────
step "Configuring PHP-FPM pool for $DEPLOY_USER"
cat > /etc/php/${PHP_VERSION}/fpm/pool.d/cms.conf << POOL
[cms]
user = $DEPLOY_USER
group = $DEPLOY_USER
listen = /run/php/php${PHP_VERSION}-cms.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = dynamic
pm.max_children = 10
pm.start_servers = 2
pm.min_spare_servers = 1
pm.max_spare_servers = 5
pm.max_requests = 500
php_admin_value[error_log] = /var/log/php-cms.log
php_admin_flag[log_errors] = on
POOL

# Disable the default www pool
mv /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf \
   /etc/php/${PHP_VERSION}/fpm/pool.d/www.conf.disabled 2>/dev/null || true

systemctl enable php${PHP_VERSION}-fpm
systemctl restart php${PHP_VERSION}-fpm
ok "PHP-FPM pool cms configured"

# ── 5. Nginx virtual host ─────────────────────────────────────────────────────
step "Writing Nginx config for $DOMAIN"
cat > /etc/nginx/sites-available/cms << NGINX
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN www.$DOMAIN;

    root $APP_DIR/public;
    index index.php;

    # Security headers are set by the Laravel SecurityHeaders middleware
    # (X-Frame-Options: DENY, X-Content-Type-Options, Referrer-Policy,
    # Permissions-Policy, HSTS). Do not duplicate them here — nginx and
    # Laravel emitting the same header produced conflicting values.

    # Block access to sensitive files
    location ~ /\\.(?!well-known) { deny all; }
    location ~* \\.(env|sqlite|log|sh)$ { deny all; }
    location ~ ^/(admin|storage|database)/ {
        # admin is served via PHP; storage/database should never be web-accessible
    }

    # OG images and static assets — long cache
    location ~* \\.(png|jpg|jpeg|gif|ico|svg|woff2|woff|ttf)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
        try_files \$uri \$uri/ =404;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \\.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VERSION}-cms.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 60;
    }

    client_max_body_size 10M;
    gzip on;
    gzip_types text/plain text/css application/javascript image/svg+xml;

    access_log /var/log/nginx/cms.access.log;
    error_log  /var/log/nginx/cms.error.log;
}
NGINX

ln -sf /etc/nginx/sites-available/cms /etc/nginx/sites-enabled/cms
rm -f /etc/nginx/sites-enabled/default

nginx -t
systemctl enable nginx
systemctl reload nginx
ok "Nginx config active (HTTP only — SSL follows)"

# ── 6. SSL via Let's Encrypt ──────────────────────────────────────────────────
step "Obtaining SSL certificate for $DOMAIN"
certbot --nginx \
  --non-interactive \
  --agree-tos \
  --email "$ADMIN_EMAIL" \
  --domains "$DOMAIN,www.$DOMAIN" \
  --redirect
ok "SSL certificate obtained — auto-renewal is handled by certbot systemd timer"

# ── 7. Storage directories and permissions ────────────────────────────────────
step "Setting up storage directories"
sudo -u "$DEPLOY_USER" mkdir -p \
  "$APP_DIR/storage/app/public/projects" \
  "$APP_DIR/storage/app/backups" \
  "$APP_DIR/storage/framework/cache/data" \
  "$APP_DIR/storage/framework/sessions" \
  "$APP_DIR/storage/framework/views" \
  "$APP_DIR/storage/logs"

sudo -u "$DEPLOY_USER" touch "$APP_DIR/database/database.sqlite"
chown -R "$DEPLOY_USER:$DEPLOY_USER" "$APP_DIR/storage" "$APP_DIR/database"
chmod -R 755 "$APP_DIR/storage"
ok "Storage directories ready"

# ── 8. Supervisor — queue worker ──────────────────────────────────────────────
step "Configuring Supervisor queue worker"
cat > /etc/supervisor/conf.d/cms-queue.conf << SUPERVISOR
[program:cms-queue]
process_name=%(program_name)s_%(process_num)02d
command=php $APP_DIR/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=$DEPLOY_USER
numprocs=1
redirect_stderr=true
stdout_logfile=$APP_DIR/storage/logs/queue.log
stopwaitsecs=3600
SUPERVISOR

supervisorctl reread
supervisorctl update
ok "Queue worker configured"

# ── 9. Cron — Laravel scheduler ───────────────────────────────────────────────
step "Installing cron for Laravel scheduler"
CRON_LINE="* * * * * php $APP_DIR/artisan schedule:run >> /dev/null 2>&1"
(crontab -u "$DEPLOY_USER" -l 2>/dev/null | grep -qF "schedule:run") \
  || (crontab -u "$DEPLOY_USER" -l 2>/dev/null; echo "$CRON_LINE") \
  | crontab -u "$DEPLOY_USER" -
ok "Cron installed"

# ── 10. Firewall ───────────────────────────────────────────────────────────────
step "Configuring UFW firewall"
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow http
ufw allow https
ufw --force enable
ok "Firewall active (SSH + HTTP + HTTPS)"

# ── Done ──────────────────────────────────────────────────────────────────────
echo
echo "════════════════════════════════════════════════════════════"
echo "  Server provisioning complete."
echo
echo "  Next steps:"
echo "  1. Copy .env.production.example to $APP_DIR/.env"
echo "     and fill in APP_KEY, MAIL_*, APP_URL=$DOMAIN"
echo
echo "  2. As the deploy user, run the first deploy:"
echo "     su - $DEPLOY_USER"
echo "     cd $APP_DIR && bash scripts/deploy.sh"
echo
echo "  3. Visit https://$DOMAIN to verify."
echo "════════════════════════════════════════════════════════════"
