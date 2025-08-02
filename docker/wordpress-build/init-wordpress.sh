#!/bin/bash
set -e

# Ensure mod_rewrite is enabled
if ! apache2ctl -M | grep -q rewrite_module; then
  echo "🔧 Enabling Apache mod_rewrite..."
  a2enmod rewrite
fi

export HTTP_HOST=localhost:8000

# Waiting for the database to be available
until nc -z "$(echo $WORDPRESS_DB_HOST | cut -d: -f1)" "$(echo $WORDPRESS_DB_HOST | cut -d: -f2)"; do
  echo "⏳ Waiting for TCP connection to MySQL..."
  sleep 2
done

# If wp-config.php doesn't exist yet but env variables are set — trigger creation
if [[ ! -f /var/www/html/wordpress/wp-config.php && -n "$WORDPRESS_DB_HOST" ]]; then
  echo "⚙️ Triggering wp-config.php creation..."
  cd /var/www/html/wordpress
  wp config create \
    --dbname="$WORDPRESS_DB_NAME" \
    --dbuser="$WORDPRESS_DB_USER" \
    --dbpass="$WORDPRESS_DB_PASSWORD" \
    --dbhost="$WORDPRESS_DB_HOST" \
    --path=/var/www/html/wordpress \
    --allow-root \
    --skip-check

  echo "📌 Add WP_LOCALE_SWITCHER manually to wp-config.php"
  echo "define('WP_LOCALE_SWITCHER', true);" >> wp-config.php
fi

# Wait for wp-config.php to appear
until [[ -f /var/www/html/wordpress/wp-config.php ]]; do
  echo "⏳ Waiting for wp-config.php to be generated..."
  sleep 2
done

# Set Redis host constant in wp-config.php
wp config set WP_REDIS_HOST redis --type=constant --allow-root

# Set ElasticPress host constant in wp-config.php
wp config set EP_HOST http://elasticsearch:9200 --type=constant --allow-root

# Turn on the language switch on the login screen
wp config set WP_LOCALE_SWITCHER true --type=constant --allow-root

# ? To reapply the configuration: docker exec -it wordpress-wordpress-1 init-wordpress.sh --reinit

#
# Install wp-cli RESTful package (needed for `wp rest route list`)
if ! wp package list --allow-root | grep -q wp-cli/restful; then
  echo "📦 Installing wp-cli/restful..."
  wp package install wp-cli/restful --allow-root

  REST_CLI_PATH=$(find /root/.wp-cli/packages -type f -name wp-rest-cli.php | head -n 1)
  if [[ -n "$REST_CLI_PATH" ]]; then
    echo "require:" > /var/www/html/wordpress/wp-cli.yml
    echo "  - $REST_CLI_PATH" >> /var/www/html/wordpress/wp-cli.yml
    echo "✅ Created wp-cli.yml with REST CLI path: $REST_CLI_PATH"
  else
    echo "⚠️ wp-rest-cli.php not found — wp rest route list may not work."
  fi
fi

# Check if WP is installed
if ! wp core is-installed --allow-root || [[ "$FORCE_REINIT" == true ]]; then
  echo "🚀 Configuring WordPress..."

  wp core install \
    --url="http://localhost:8000" \
    --title="Krishna Academy" \
    --admin_user=admin \
    --admin_password=admin \
    --admin_email=admin@example.com \
    --skip-email \
    --allow-root

  WP_SITE_URL="http://localhost:8000"

  # Install and activate the import plugin
  wp plugin install wordpress-importer --activate --url="$WP_SITE_URL" --allow-root

  # Install WordPress languages
  wp language core install uk --activate --url="$WP_SITE_URL" --allow-root
  wp language core install ru_RU --url="$WP_SITE_URL" --allow-root
  wp language core install en_US --url="$WP_SITE_URL" --allow-root

  # Import demo data
  wp import /var/www/html/wordpress/demo.xml --authors=skip --url="$WP_SITE_URL" --allow-root
  wp import /var/www/html/wordpress/demo-ru.xml --authors=skip --url="$WP_SITE_URL" --allow-root
  wp import /var/www/html/wordpress/demo-en.xml --authors=skip --url="$WP_SITE_URL" --allow-root
  wp import /var/www/html/wordpress/demo-home.xml --authors=skip --url="$WP_SITE_URL" --allow-root

  # Set the front page
  FRONT_PAGE_ID=$(wp post list --post_type=page --name=home --format=ids --allow-root --url="$WP_SITE_URL")
  wp option update show_on_front page --allow-root --url="$WP_SITE_URL"
  wp option update page_on_front "$FRONT_PAGE_ID" --allow-root --url="$WP_SITE_URL"

  # Add a default base category
  echo "📁 Creating default category 'Без категорії'..."
  DEFAULT_CAT_ID=$(wp term create category "Без категорії" --slug=bez-kategoriyi --porcelain --allow-root --url="$WP_SITE_URL")
  wp option update default_category "$DEFAULT_CAT_ID" --allow-root --url="$WP_SITE_URL"

  wp plugin activate polylang-pro --allow-root --url="$WP_SITE_URL"

  # Prepare SSH known_hosts for GitHub to avoid host verification prompt
  mkdir -p ~/.ssh
  ssh-keyscan github.com >> ~/.ssh/known_hosts

  # Ensure SSH agent socket is available before cloning
  echo "🔐 SSH_AUTH_SOCK=$SSH_AUTH_SOCK"
  until [ -S "$SSH_AUTH_SOCK" ]; do
    echo "⏳ Waiting for SSH_AUTH_SOCK to be available..."
    sleep 1
  done

  # Disable Polylang setup wizard before adding languages
  wp option update pll_setup_complete 1 --url="$WP_SITE_URL" --allow-root
  echo "🔧 Setting a serialized value for an option 'polylang'..."

  # Automatic update of rewrite rules
  # Setting up a permalink structure
  wp rewrite structure '/%postname%/' --hard --url="$WP_SITE_URL" --allow-root
  wp rewrite flush --hard --url="$WP_SITE_URL" --allow-root

  # Activating your own topic
  wp theme activate krishna-academy --url="$WP_SITE_URL" --allow-root
  wp plugin install redis-cache --url="$WP_SITE_URL" --allow-root
  wp plugin install wp-super-cache --url="$WP_SITE_URL" --allow-root

  # Installing plugin to safely allow SVG uploads
  wp plugin install safe-svg --activate --url="$WP_SITE_URL" --allow-root

  # Fix permissions for WP Super Cache config
  if [[ -f /var/www/html/wordpress/wp-content/wp-cache-config.php ]]; then
    chmod 666 /var/www/html/wordpress/wp-content/wp-cache-config.php
  fi

  # Installing the ElasticSearch plugin
  wp plugin install elasticpress --url="$WP_SITE_URL" --allow-root
  
  echo "✅ WordPress has been successfully set up for development"

  # Activate the set-user-lang-on-login plugin
  wp plugin activate set-user-lang-on-login --url="$WP_SITE_URL" --allow-root

else
  echo "✅ WordPress already installed"
fi

#
# 🧩 Language selector plugin must be installed separately

# Set permissions 
echo "🔧 Setting full access to WordPress directory for www-data"
chown -R www-data:www-data /var/www/html/wordpress
find /var/www/html/wordpress -type d -exec chmod 775 {} \;
find /var/www/html/wordpress -type f -exec chmod 664 {} \;

exec apache2-foreground