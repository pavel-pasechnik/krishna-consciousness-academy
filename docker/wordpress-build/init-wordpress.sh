#!/bin/bash
set -e

# Ensure WP-CLI always points to the WordPress install
wp() { command /usr/local/bin/wp --path=/var/www/html/wordpress "$@"; }

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

fi

# Check if wp-config.php exists; if not, skip steps 5 and 7
if [[ -f /var/www/html/wordpress/wp-config.php ]]; then
  SKIP_WP_SETUP=false
else
  echo "⚠️ wp-config.php not found — skipping config constants and core install steps."
  SKIP_WP_SETUP=true
fi

# Only set config constants if wp-config.php exists
if [[ "$SKIP_WP_SETUP" == false ]]; then
  # Set Redis host constant in wp-config.php
  wp config set WP_REDIS_HOST redis --type=constant --allow-root

  # Set ElasticPress host constant in wp-config.php
  wp config set EP_HOST http://elasticsearch:9200 --type=constant --allow-root

  # Turn on the language switch on the login screen
  wp config set WP_LOCALE_SWITCHER true --type=constant --allow-root
fi

# ? To reapply the configuration: docker exec -it wordpress-wordpress-1 init-wordpress.sh --reinit

# Install wp-cli RESTful package (needed for `wp rest route list`)
if ! wp package list --allow-root 2>/dev/null | grep -q wp-cli/restful; then
  echo "📦 Installing wp-cli/restful..."
  wp package install wp-cli/restful --allow-root

  REST_CLI_PATH=$(find /root/.wp-cli/packages -type f -name wp-rest-cli.php | head -n 1)
  if [[ -n "$REST_CLI_PATH" ]]; then
    WPCLI_YML="/var/www/html/wordpress/wp-cli.yml"
    # Create file if it does not exist
    if [[ ! -f "$WPCLI_YML" ]]; then
      {
        echo "require:"
        echo "  - $REST_CLI_PATH"
      } > "$WPCLI_YML"
      echo "✅ Created wp-cli.yml with REST CLI path: $REST_CLI_PATH"
    else
      # Ensure 'require:' key exists
      if ! grep -qE '^require:' "$WPCLI_YML"; then
        echo "require:" >> "$WPCLI_YML"
      fi
      # Append path only if it is not already present
      if ! grep -qF "  - $REST_CLI_PATH" "$WPCLI_YML"; then
        echo "  - $REST_CLI_PATH" >> "$WPCLI_YML"
        echo "✅ Appended REST CLI path to existing wp-cli.yml"
      else
        echo "ℹ️ REST CLI path already present in wp-cli.yml"
      fi
    fi
  else
    echo "⚠️ wp-rest-cli.php not found — wp rest route list may not work."
  fi
fi

# Check if WP is installed
if [[ "$SKIP_WP_SETUP" == false ]] && { ! wp core is-installed --allow-root || [[ "$FORCE_REINIT" == true ]]; }; then
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

  # Ensure SSH agent socket is available only if SSH is required and variable is set
  echo "🔐 SSH_AUTH_SOCK=${SSH_AUTH_SOCK:-<not set>}"
  if [[ -n "$SSH_AUTH_SOCK" ]]; then
    until [ -S "$SSH_AUTH_SOCK" ]; do
      echo "⏳ Waiting for SSH_AUTH_SOCK to be available..."
      sleep 1
    done
  else
    echo "🔐 No SSH agent configured — skipping SSH wait."
  fi

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