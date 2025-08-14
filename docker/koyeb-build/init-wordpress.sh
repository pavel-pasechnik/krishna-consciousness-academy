#!/usr/bin/env bash
set -e

DOCROOT="/var/www/html"

# Ждём доступности БД (опционально, но полезно)
if [ -n "${WORDPRESS_DB_HOST}" ]; then
  echo "Waiting for MySQL at ${WORDPRESS_DB_HOST}..."
  for i in {1..30}; do
    (echo > /dev/tcp/${WORDPRESS_DB_HOST%%:*}/${WORDPRESS_DB_HOST##*:} 2>/dev/null) && break || sleep 2
  done
fi

# Создать wp-config.php из ENV, если его нет
if [ ! -f "${DOCROOT}/wp-config.php" ]; then
  echo "Creating wp-config.php from environment variables..."
  wp config create \
    --path="${DOCROOT}" \
    --dbname="${WORDPRESS_DB_NAME}" \
    --dbuser="${WORDPRESS_DB_USER}" \
    --dbpass="${WORDPRESS_DB_PASSWORD}" \
    --dbhost="${WORDPRESS_DB_HOST}" \
    --force \
    --skip-check

  # Доп. константы (домены, WP_DEBUG и пр.)
  wp config set WP_HOME "${WP_HOME:-https://$KOYEB_APP_ID.koyeb.app}" --path="${DOCROOT}" --type=constant --raw=false
  wp config set WP_SITEURL "${WP_SITEURL:-${WP_HOME:-https://$KOYEB_APP_ID.koyeb.app}}" --path="${DOCROOT}" --type=constant --raw=false
  wp config set WP_DEBUG "${WORDPRESS_DEBUG:-false}" --path="${DOCROOT}" --type=constant --raw=true

  # Ключи/соли, если не заданы
  wp config shuffle-salts --path="${DOCROOT}" || true
fi

# Никаких установок/импортов/активаций плагинов здесь не делаем.

# Запуск Apache в форграунде
apache2-foreground