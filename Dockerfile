FROM ghcr.io/withercom/docker-laravel:php82 as npm
WORKDIR /usr/src/app
COPY . .
RUN apk add nodejs npm
RUN composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --optimize-autoloader
RUN npm install --no-package-lock && npm run build

FROM ghcr.io/withercom/docker-laravel:php82
ADD --chown=nginx:nginx ./ /srv/http
COPY --chown=nginx:nginx --from=npm /usr/src/app/public /srv/http/public
WORKDIR /srv/http
RUN composer install --no-ansi --no-dev --no-interaction --no-plugins --no-progress --optimize-autoloader
