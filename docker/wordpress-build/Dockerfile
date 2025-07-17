FROM wordpress:latest
RUN rm -rf /var/www/html && cp -a /usr/src/wordpress /var/www/html

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copying the theme
COPY ./themes/krishna-academy /var/www/html/wp-content/themes/krishnaacademy

# Add your entrypoint script.
COPY ./docker/wordpress-build/init-wordpress.sh /usr/local/bin/init-wordpress.sh
RUN chmod +x /usr/local/bin/init-wordpress.sh

# Install WP-CLI
RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar \
  && chmod +x wp-cli.phar \
  && mv wp-cli.phar /usr/local/bin/wp

# Install MySQL client to work with WP-CLI database
RUN apt-get update && apt-get install -y default-mysql-client netcat-openbsd

# On startup, our script + the original entrypoint will be executed
ENTRYPOINT ["/usr/local/bin/init-wordpress.sh"]