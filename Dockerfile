FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git \
    zip unzip \
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
    && pecl install amqp redis \
    && docker-php-ext-enable amqp redis

RUN a2enmod rewrite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader

COPY . .

RUN chown -R www-data:www-data /var/www/html

RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

COPY entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh

ENV APP_ENV=dev XDEBUG_MODE=off

ENTRYPOINT ["/entrypoint.sh"]

EXPOSE 80
