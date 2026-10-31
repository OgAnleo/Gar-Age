# Production image: Apache + PHP 8.5 serving public/, with the frontend
# built in a separate stage. Used by Render (see render.yaml).

# --- Build: Composer dependencies and the Vite bundle -------------------------
# The Wayfinder Vite plugin runs artisan, so this stage needs PHP and Node.
FROM php:8.5-cli AS build

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=node:22-slim /usr/local/bin/node /usr/local/bin/node
COPY --from=node:22-slim /usr/local/lib/node_modules /usr/local/lib/node_modules
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --no-interaction --prefer-dist

COPY package.json package-lock.json ./
RUN npm ci

COPY . .
RUN composer dump-autoload --optimize --no-dev \
    && npm run build \
    && rm -rf node_modules

# --- Runtime -------------------------------------------------------------------
FROM php:8.5-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
RUN a2enmod rewrite headers \
    && sed -i 's/^Listen 80$/Listen ${PORT}/' /etc/apache2/ports.conf

WORKDIR /var/www/html
COPY --from=build --chown=www-data:www-data /app .

# Render routes traffic to $PORT (10000 unless set otherwise).
ENV PORT=10000
EXPOSE 10000

CMD ["sh", "docker/start.sh"]
