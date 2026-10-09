FROM node:20-alpine AS assets
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.* ./
RUN composer install --no-dev --no-scripts --ignore-platform-reqs --prefer-dist --no-interaction
COPY . .
RUN composer dump-autoload --optimize --no-scripts

FROM php:8.4-cli-alpine
RUN apk add --no-cache icu-dev libzip-dev oniguruma-dev sqlite-dev postgresql-dev \
    && docker-php-ext-install intl zip pdo_sqlite pdo_pgsql mbstring bcmath opcache
WORKDIR /var/www
COPY --from=vendor /app .
COPY --from=assets /app/public/build public/build
RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs database \
    && touch database/database.sqlite
COPY docker-entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint
ENV APP_ENV=production APP_DEBUG=false DB_CONNECTION=sqlite DB_DATABASE=/var/www/database/database.sqlite
EXPOSE 8080
ENTRYPOINT ["entrypoint"]
