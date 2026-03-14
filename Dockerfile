FROM registry.pandora-new.ktk.bme.hu/core/docker/laravel:8.4 as builder
WORKDIR /srv/app
COPY . .
RUN composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --optimize-autoloader
RUN npm install --no-package-lock && npm run build

FROM registry.pandora-new.ktk.bme.hu/core/docker/laravel:8.4
COPY --from=builder /srv/app /srv/app
