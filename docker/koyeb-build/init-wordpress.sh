#!/usr/bin/env bash
set -e

DOCROOT="/var/www/html"
WP="wp --allow-root --path=${DOCROOT}"

# Waiting for database availability (optional, but useful)
if [ -n "${WORDPRESS_DB_HOST}" ]; then
  echo "Waiting for MySQL at ${WORDPRESS_DB_HOST}..."
  for i in {1..30}; do
    (echo > /dev/tcp/${WORDPRESS_DB_HOST%%:*}/${WORDPRESS_DB_HOST##*:} 2>/dev/null) && break || sleep 2
  done
fi

# Create wp-config.php from ENV if it does not exist
if [ ! -f "${DOCROOT}/wp-config.php" ]; then
  echo "Creating wp-config.php from environment variables..."
  ${WP} config create \
    --dbname="${WORDPRESS_DB_NAME}" \
    --dbuser="${WORDPRESS_DB_USER}" \
    --dbpass="${WORDPRESS_DB_PASSWORD}" \
    --dbhost="${WORDPRESS_DB_HOST}" \
    --force \
    --skip-check

  # Additional constants (domains, WP_DEBUG, etc.)
  ${WP} config set WP_HOME "${WP_HOME:-https://$KOYEB_APP_ID.koyeb.app}" --type=constant --raw=false
  ${WP} config set WP_SITEURL "${WP_SITEURL:-${WP_HOME:-https://$KOYEB_APP_ID.koyeb.app}}" --type=constant --raw=false
  ${WP} config set WP_DEBUG "${WORDPRESS_DEBUG:-false}" --type=constant --raw=true

  # Keys/salts, if not specified
  ${WP} config shuffle-salts || true

  # Validate wp-config.php syntax; if broken (e.g., due to bad escaping), recreate cleanly
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
    ${WP} config set WP_HOME "${WP_HOME:-https://$KOYEB_APP_ID.koyeb.app}" --type=constant --raw=false
    ${WP} config set WP_SITEURL "${WP_SITEURL:-${WP_HOME:-https://$KOYEB_APP_ID.koyeb.app}}" --type=constant --raw=false
    ${WP} config set WP_DEBUG "${WORDPRESS_DEBUG:-false}" --type=constant --raw=true
    ${WP} config shuffle-salts || true
  fi
fi

# Do not import or activate anything automatically (policy-safe)

# Run Apache in the foreground
apache2-foreground