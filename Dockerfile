FROM registry.pandora-new.ktk.bme.hu/core/docker/laravel:8.4 as builder
WORKDIR /srv/http
COPY . .
RUN install-php-extensions soap
RUN composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --optimize-autoloader --no-scripts
RUN npm install --no-package-lock && npm run build
RUN composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --optimize-autoloader

FROM registry.pandora-new.ktk.bme.hu/core/docker/laravel:8.4
COPY --from=builder /srv/http /srv/http
