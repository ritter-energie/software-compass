FROM php:8.4-apache

# --- System dependencies -----------------------------------------------
# libicu-dev: needed for the `intl` extension (used by Tempest\Intl).
# libzip-dev / zip: needed for the `zip` extension (composer archives).
# libxml2-dev: needed for `dom`/`xml` (required by tempest/framework).
RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        libxml2-dev \
        default-mysql-client \
    && docker-php-ext-configure intl \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_mysql \
        intl \
        zip \
        dom \
        xml \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Apache: serve the `public/` directory and enable clean URLs.
RUN a2enmod rewrite headers
COPY docker/apache/software-compass.conf /etc/apache2/sites-available/000-default.conf

# Recommended php.ini tweaks for local development.
COPY docker/php/local.ini /usr/local/etc/php/conf.d/zz-software-compass.ini

# Composer (official multi-stage copy, avoids installing curl/gnupg manually).
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy dependency manifests first so Docker can cache `composer install`.
COPY composer.json composer.lock* ./
RUN composer install --no-interaction --no-scripts --no-autoloader --prefer-dist || true

COPY . .

RUN composer install --no-interaction --prefer-dist \
    && chown -R www-data:www-data /var/www/html \
    && mkdir -p .tempest/cache && chown -R www-data:www-data .tempest

EXPOSE 80

