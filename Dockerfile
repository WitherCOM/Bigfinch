FROM ghcr.io/withercom/docker-laravel:php8.2 as builder
WORKDIR /usr/src/app
COPY . .
RUN apk add nodejs npm wget
RUN wget -O - https://getcomposer.org/installer | php -- --filename=composer --install-dir=/usr/bin
RUN composer install --no-scripts --no-ansi --no-dev --no-interaction --no-plugins --no-progress --optimize-autoloader
RUN npm install --no-package-lock && npm run build
RUN composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --optimize-autoloader

FROM ghcr.io/withercom/docker-laravel:php8.2
COPY --chown=www-data:www-data --from=builder /usr/src/app /app
