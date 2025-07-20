#!/bin/bash
set -e

# Waiting for the database to be available
until nc -z "$(echo $WORDPRESS_DB_HOST | cut -d: -f1)" "$(echo $WORDPRESS_DB_HOST | cut -d: -f2)"; do
  echo "⏳ Waiting for TCP connection to MySQL..."
  sleep 2
done

if [ ! -f /var/www/html/index.php ]; then
  echo "📦 WordPress not found. Click to copy в /var/www/html..."
  cp -a /usr/src/wordpress/. /var/www/html/
else
  echo "✅ WordPress is already installed. Skip copying."
fi

# Wait for WordPress core to be copied by docker-entrypoint.sh
until [[ -f /var/www/html/wp-includes/version.php ]]; do
  echo "⏳ Waiting for WordPress core to be copied to /var/www/html..."
  sleep 2
done

# If wp-config.php doesn't exist yet but env variables are set — trigger creation
if [[ ! -f /var/www/html/wp-config.php && -n "$WORDPRESS_DB_HOST" ]]; then
  echo "⚙️ Triggering wp-config.php creation..."
  wp config create \
    --dbname="$WORDPRESS_DB_NAME" \
    --dbuser="$WORDPRESS_DB_USER" \
    --dbpass="$WORDPRESS_DB_PASSWORD" \
    --dbhost="$WORDPRESS_DB_HOST" \
    --path=/var/www/html \
    --allow-root \
    --skip-check
fi

# Wait for wp-config.php to appear
until [[ -f /var/www/html/wp-config.php ]]; do
  echo "⏳ Waiting for wp-config.php to be generated..."
  sleep 2
done

# Set Redis host constant in wp-config.php
wp config set WP_REDIS_HOST redis --type=constant --allow-root

# Set ElasticPress host constant in wp-config.php
wp config set EP_HOST http://elasticsearch:9200 --type=constant --allow-root

# ? To reapply the configuration: docker exec -it wordpress-wordpress-1 init-wordpress.sh --reinit

# Check if WP is installed
if ! wp core is-installed --allow-root || [[ "$FORCE_REINIT" == true ]]; then
  echo "🚀 Configuring WordPress..."

  wp core multisite-install \
    --url="localhost:8000" \
    --title="Krishna Academy Network" \
    --admin_user=admin \
    --admin_password=admin \
    --admin_email=admin@example.com \
    --skip-email \
    --allow-root

  WP_SITE_URL="http://localhost:8000"

  wp option update WPLANG uk --url="$WP_SITE_URL" --allow-root

  # Set languages
  wp language core install uk --allow-root
  wp language core install ru_RU --allow-root
  wp language core install en_US --allow-root

  # Changing the default site language
  if wp help | grep -q 'pll'; then
    wp language core activate uk --allow-root
  fi

  # Installing the WordPress demo data
  wp plugin install wordpress-importer --activate --url="$WP_SITE_URL" --allow-root
  wp import /var/www/html/demo.xml --authors=create --url="$WP_SITE_URL" --allow-root
  wp import /var/www/html/demo-ru.xml --authors=create --url="$WP_SITE_URL/ru" --allow-root
  wp import /var/www/html/demo-en.xml --authors=create --url="$WP_SITE_URL/en" --allow-root

  # Adding Polylang languages
  if wp help | grep -q 'pll'; then
    wp pll language add uk --locale=uk --slug=uk --name="Українська" --allow-root
  fi
  if wp help | grep -q 'pll'; then
    wp pll language add ru --locale=ru_RU --slug=ru --name="Русский" --allow-root
  fi
  if wp help | grep -q 'pll'; then
    wp pll language add en --locale=en_US --slug=en --name="English" --allow-root
  fi

  # Linking translations to pages
  if wp help | grep -q 'pll'; then
    if wp pll post list --allow-root | grep -q "Про академію"; then
      wp pll post update 2 --lang=uk --allow-root
      wp pll post update 3 --lang=uk --allow-root
      wp pll post update 4 --lang=uk --allow-root

      wp pll post update 102 --lang=ru --allow-root
      wp pll post update 103 --lang=ru --allow-root
      wp pll post update 104 --lang=ru --allow-root

      wp pll post update 202 --lang=en --allow-root
      wp pll post update 203 --lang=en --allow-root
      wp pll post update 204 --lang=en --allow-root

      # Translation Association
      wp pll post associate 2 ru=102 en=202 --allow-root
      wp pll post associate 3 ru=103 en=203 --allow-root
      wp pll post associate 4 ru=104 en=204 --allow-root
    fi
  fi

  # Enable user-selectable admin language (if user-language-switcher plugin is installed)
  wp plugin install user-language-switcher --activate --url="$WP_SITE_URL" --allow-root || echo "⚠️ Plugin user-language-switcher not found, skipping"

  # Automatic update of rewrite rules
  # Set the administrator language manually
  wp user meta update admin locale en_US --url="$WP_SITE_URL" --allow-root

  # Setting up a permalink structure
  wp rewrite structure '/%postname%/' --hard --url="$WP_SITE_URL" --allow-root
  wp rewrite flush --hard --url="$WP_SITE_URL" --allow-root

  # Activating your own topic
  wp theme activate krishna-academy --url="$WP_SITE_URL" --allow-root

  wp plugin install redis-cache --activate --url="$WP_SITE_URL" --allow-root
  wp plugin install wp-super-cache --activate --url="$WP_SITE_URL" --allow-root
  wp redis enable --url="$WP_SITE_URL" --allow-root

  # Installing and activating Polylang to support multilingualism
  wp plugin install polylang-cli --activate
  wp plugin install polylang --activate --url="$WP_SITE_URL" --allow-root
  # wp plugin install admin-language-per-user --activate --url="$WP_SITE_URL" --allow-root

  # Customizing languages (if not added manually)
  # Removed adding languages via pll language add commands as requested

  # Installing the ElasticSearch plugin
  wp plugin install elasticpress --activate --url="$WP_SITE_URL" --allow-root
  # wp elasticpress put-settings '{"index":{"analysis":{"analyzer":{"default":{"type":"standard"}}}}}' --url="$WP_SITE_URL" --allow-root
  wp elasticpress sync --setup --url="$WP_SITE_URL" --allow-root
  # wp elasticpress sync --url="$WP_SITE_URL" --allow-root \
  # --host=http://elasticsearch:9200
  wp elasticpress sync --url="$WP_SITE_URL" --allow-root
  echo "✅ WordPress has been successfully set up for development"
  # wp elasticpress activate-feature autosuggest --url="$WP_SITE_URL" --allow-root
  wp elasticpress activate-feature related_posts --url="$WP_SITE_URL" --allow-root
  wp elasticpress activate-feature rest-api --url="$WP_SITE_URL" --allow-root

  # Activating multilingual support in ElasticPress
  # Removed wp elasticpress activate-feature polylang as requested

  # Re-indexing for multilingualism
  wp elasticpress sync --url="$WP_SITE_URL" --allow-root

  # Install and activate all inactive plugins from wp-content/plugins, if any
  for plugin in $(wp plugin list --field=name --status=inactive --url="$WP_SITE_URL" --allow-root); do
    wp plugin activate "$plugin" --url="$WP_SITE_URL" --allow-root
  done
else
  echo "✅ WordPress already installed"
fi

# Run the standard entrypoint
exec docker-entrypoint.sh apache2-foreground