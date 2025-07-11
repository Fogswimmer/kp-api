FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git \
    zip \
    unzip \
    libpng-dev \
    libzip-dev \
    libpq-dev \
    libonig-dev \
    libxml2-dev \
    libssh-dev \
    librabbitmq-dev \
    curl \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_pgsql zip intl \
    && pecl install amqp \
    && docker-php-ext-enable amqp

RUN pecl install redis && docker-php-ext-enable redis

RUN a2enmod rewrite

WORKDIR /var/www/html

COPY . .

ENV APP_ENV=dev XDEBUG_MODE=off

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

RUN chown -R www-data:www-data /var/www/html

COPY entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 80

RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf