FROM ghcr.io/withercom/docker-laravel:php8.2 as builder
WORKDIR /usr/src/app
COPY . .
RUN apk add nodejs npm php82-soap wget
RUN wget -O - https://getcomposer.org/installer | php -- --filename=composer --install-dir=/usr/bin
RUN composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --optimize-autoloader
RUN npm install --no-package-lock && npm run build

FROM ghcr.io/withercom/docker-laravel:php8.2
COPY --chown=www-data:www-data --from=npm /usr/src/app/public /app/public
COPY --chown=www-data:www-data --from=npm /usr/src/app/vendor /app
RUN apk add php82-soap
