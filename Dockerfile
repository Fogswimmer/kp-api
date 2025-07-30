FROM php:8.4-apache

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
    libicu-dev \
    curl \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo \
        pdo_pgsql \
        zip \
        intl \
        opcache \
        bcmath \
        gd

RUN pecl install amqp redis \
    && docker-php-ext-enable amqp redis

RUN a2enmod rewrite
RUN sed -i 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/000-default.conf

RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.enable_cli=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=256" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=16" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=20000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.validate_timestamps=0" >> /usr/local/etc/php/conf.d/opcache.ini

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --prefer-dist \
    --no-scripts \
    --no-progress \
    --no-interaction \
    --optimize-autoloader \
    --classmap-authoritative

COPY . .

RUN composer run-script --no-dev post-install-cmd || true

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

ENV APP_ENV=prod \
    COMPOSER_ALLOW_SUPERUSER=1

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]