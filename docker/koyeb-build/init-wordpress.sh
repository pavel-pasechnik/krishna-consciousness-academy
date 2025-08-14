# Dockerfile.koyeb

FROM wordpress:php8.0-apache

# Устанавливаем зависимости для composer и wp-cli
RUN apt-get update && apt-get install -y \
    curl \
    less \
    mariadb-client \
    unzip \
    git \
    libzip-dev \
    zip \
    && docker-php-ext-install zip pdo_mysql

# Настраиваем DocumentRoot для Apache
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' /etc/apache2/sites-available/000-default.conf

# WP-CLI
RUN curl -sS -L https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar -o /usr/local/bin/wp \
  && chmod +x /usr/local/bin/wp

# Скачиваем WordPress core в /var/www/html
RUN rm -rf /var/www/html/* \
  && wp core download --path=/var/www/html --allow-root

# Копируем composer.json и composer.lock
COPY ./composer.json ./composer.lock ./

RUN composer install --no-dev --optimize-autoloader

# Копируем только контент при его наличии
# (если в репо есть wp-content с темами/плагинами)
COPY ./wp-content/ /var/www/html/wp-content/

# Копируем .htaccess, исправляющий работу ЧПУ
COPY ./.htaccess /var/www/html/.htaccess

# Копируем скрипт инициализации WP
COPY ./docker/koyeb-build/init-wordpress.sh /usr/local/bin/init-wordpress.sh
RUN chmod +x /usr/local/bin/init-wordpress.sh

# Удалена строка и комментарий:
# # Если у тебя WP core и контент лежат в ./wordpress — развернём его в корень
# COPY ./wordpress/ /var/www/html/

# Открываем порт 80
EXPOSE 80

CMD ["init-wordpress.sh"]