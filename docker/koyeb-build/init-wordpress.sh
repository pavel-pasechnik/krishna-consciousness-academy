#!/usr/bin/env bash
set -e

DOCROOT="/var/www/html"
WP="wp --allow-root --path=${DOCROOT}"

# --- 1) Wait for DB (if provided) ---
if [ -n "${WORDPRESS_DB_HOST:-}" ]; then
  echo "Waiting for MySQL at ${WORDPRESS_DB_HOST}..."
  for i in {1..60}; do
    (echo > "/dev/tcp/${WORDPRESS_DB_HOST%%:*}/${WORDPRESS_DB_HOST##*:}" 2>/dev/null) && break || sleep 1
  done
fi

# --- 2) Create wp-config.php from ENV if missing ---
if [ ! -f "${DOCROOT}/wp-config.php" ]; then
  echo "Creating wp-config.php from environment variables..."
  ${WP} config create \
    --dbname="${WORDPRESS_DB_NAME}" \
    --dbuser="${WORDPRESS_DB_USER}" \
    --dbpass="${WORDPRESS_DB_PASSWORD}" \
    --dbhost="${WORDPRESS_DB_HOST}" \
    --force \
    --skip-check

  # Write custom constants to a separate include to avoid quoting issues
  cat > "${DOCROOT}/wp-config.custom.php" <<'PHP'
<?php
$__home = getenv('WP_HOME') ?: ('https://' . getenv('KOYEB_APP_ID') . '.koyeb.app');
$__siteurl = getenv('WP_SITEURL') ?: $__home;
if (!defined('WP_HOME')) define('WP_HOME', $__home);
if (!defined('WP_SITEURL')) define('WP_SITEURL', $__siteurl);
$__debug = getenv('WORDPRESS_DEBUG');
if (!defined('WP_DEBUG')) define('WP_DEBUG', ($__debug === '1' || strtolower((string)$__debug) === 'true'));
PHP

  # Ensure the include is present just before the ABSPATH block
  if ! grep -q "wp-config.custom.php" "${DOCROOT}/wp-config.php"; then
    sed -i "/^\/\* That's all, stop editing!.*\*\//i require_once __DIR__ . '\/wp-config.custom.php';" "${DOCROOT}/wp-config.php"
  fi

  # Keys/salts
  ${WP} config shuffle-salts || true

  # Validate wp-config.php; if broken — recreate cleanly
  if ! php -l "${DOCROOT}/wp-config.php" >/dev/null 2>&1; then
    echo "wp-config.php has syntax errors. Recreating..."
    mv "${DOCROOT}/wp-config.php" "${DOCROOT}/wp-config.php.bad" 2>/dev/null || true
    ${WP} config create \
      --dbname="${WORDPRESS_DB_NAME}" \
      --dbuser="${WORDPRESS_DB_USER}" \
      --dbpass="${WORDPRESS_DB_PASSWORD}" \
      --dbhost="${WORDPRESS_DB_HOST}" \
      --force \
      --skip-check
    cat > "${DOCROOT}/wp-config.custom.php" <<'PHP'
<?php
$__home = getenv('WP_HOME') ?: ('https://' . getenv('KOYEB_APP_ID') . '.koyeb.app');
$__siteurl = getenv('WP_SITEURL') ?: $__home;
if (!defined('WP_HOME')) define('WP_HOME', $__home);
if (!defined('WP_SITEURL')) define('WP_SITEURL', $__siteurl);
$__debug = getenv('WORDPRESS_DEBUG');
if (!defined('WP_DEBUG')) define('WP_DEBUG', ($__debug === '1' || strtolower((string)$__debug) === 'true'));
PHP
    if ! grep -q "wp-config.custom.php" "${DOCROOT}/wp-config.php"; then
      sed -i "/^\/\* That's all, stop editing!.*\*\//i require_once __DIR__ . '\/wp-config.custom.php';" "${DOCROOT}/wp-config.php"
    fi
    ${WP} config shuffle-salts || true
  fi
fi

# --- 3) Optional auto-install (default admin/admin) ---
if ! ${WP} core is-installed >/dev/null 2>&1; then
  echo "Running WordPress installation..."
  SITE_URL="${WP_HOME:-https://$KOYEB_APP_ID.koyeb.app}"
  ${WP} core install \
    --url="${SITE_URL}" \
    --title="${WP_SITE_TITLE:-My Site}" \
    --admin_user="${WP_ADMIN_USER:-admin}" \
    --admin_password="${WP_ADMIN_PASS:-admin}" \
    --admin_email="${WP_ADMIN_EMAIL:-admin@example.com}" \
    --skip-email
  # Ensure pretty permalinks work on first run
  ${WP} rewrite structure '/%postname%/' --hard || true
  ${WP} rewrite flush --hard || true
fi

# --- 4) Final validation ---
if ! php -l "${DOCROOT}/wp-config.php" >/dev/null 2>&1; then
  echo "FATAL: wp-config.php still has syntax errors after repair. Dumping tail for diagnostics:" >&2
  nl -ba "${DOCROOT}/wp-config.php" | tail -n 60 >&2 || true
  exit 1
fi

# --- 5) Run Apache ---
exec apache2-foreground