FROM php:8.1-apache-buster

RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    vim \
    unzip \
    libicu-dev \
    libpq-dev \
    sqlite3 \
    libsqlite3-dev

RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
RUN docker-php-ext-install -j$(nproc) pdo_pgsql pdo_sqlite

RUN a2enmod rewrite
RUN sed -i 's/\/var\/www\/html/\/var\/www\/public/g' /etc/apache2/sites-available/000-default.conf
RUN rmdir /var/www/html

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

COPY . /var/www/

WORKDIR /var/www

RUN composer install
RUN vendor/bin/phpunit

RUN chown -R www-data:www-data /var/www

RUN sed -i '3i php artisan migrate --force' /usr/local/bin/docker-php-entrypoint

ENV APP_NAME TestTask
ENV APP_ENV production
ENV APP_KEY base64:F6UbOf7osfNnZ86cxzDVL9JH57SwBdhh4+YYPj8kcn4=
ENV APP_DEBUG false
ENV LOG_LEVEL warning
ENV DB_CONNECTION pgsql
ENV DB_PORT 5432
