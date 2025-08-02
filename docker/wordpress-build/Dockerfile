# syntax=docker/dockerfile:1.4
FROM php:8.3-apache AS composer-wordpress

WORKDIR /var/www/html

# Composer installation
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer.json and composer.lock
COPY ./composer.json composer.json
COPY ./composer.lock composer.lock

# Let's set up the necessary dependencies
RUN apt-get update && apt-get install -y \
  git \
  unzip \
  libzip-dev \
  libjpeg-dev \
  libpng-dev \
  libwebp-dev \
  libxpm-dev \
  libfreetype6-dev \
  libmagickwand-dev \
  libicu-dev \
  default-mysql-client \
  netcat-openbsd \
  && docker-php-ext-configure gd \
       --with-jpeg \
       --with-webp \
       --with-xpm \
       --with-freetype \
  && docker-php-ext-install \
       zip \
       mysqli \
       gd \
       intl \
       exif \
  && pecl install imagick \
  && docker-php-ext-enable imagick

RUN composer install --prefer-dist --no-dev --no-scripts --no-interaction

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/wordpress|g' /etc/apache2/sites-available/000-default.conf
COPY ./docker/wordpress-build/000-default.conf /etc/apache2/sites-available/000-default.conf

COPY ./wordpress-config/custom.ini /usr/local/etc/php/conf.d/custom.ini
COPY ./wordpress-config/demo /var/www/html/wordpress/

RUN sed -i 's|RewriteBase /|RewriteBase /wordpress/|g; s|RewriteRule . /index.php \[L\]|RewriteRule . /wordpress/index.php [L]|g' /var/www/html/wordpress/.htaccess || true
COPY ./wordpress-config/assign-translations.php /var/www/html/wordpress/assign-translations.php

RUN chown -R www-data:www-data /var/www/html/wordpress && \
    find /var/www/html/wordpress -type d -exec chmod 755 {} \; && \
    find /var/www/html/wordpress -type f -exec chmod 644 {} \;

# Add your entrypoint script.
COPY ./docker/wordpress-build/init-wordpress.sh /usr/local/bin/init-wordpress.sh

RUN chmod +x /usr/local/bin/init-wordpress.sh


# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
  && chmod +x wp-cli.phar \
  && mv wp-cli.phar /usr/local/bin/wp

# Add known_hosts for GitHub
RUN mkdir -p /root/.ssh && ssh-keyscan github.com >> /root/.ssh/known_hosts

# On startup, our script + the original entrypoint will be executed
CMD ["/usr/local/bin/init-wordpress.sh"]