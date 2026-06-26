FROM php:8.4-cli

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip libonig-dev default-mysql-client \
    && docker-php-ext-install mbstring pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy composer.json saja; lock di-generate fresh di dalam container supaya
# build tetap jalan tanpa composer.lock yang ikut di-commit, dan dependency
# (webonyx/graphql-php, dst.) selalu ter-resolve bersih.
COPY composer.json ./
RUN composer update --no-dev --no-interaction --prefer-dist --optimize-autoloader --no-scripts

COPY . .
RUN composer dump-autoload --optimize \
    && mkdir -p storage/app storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENV APP_ENV=production
ENV APP_DEBUG=false
ENV APP_KEY=base64:st2FXQB17Q9BmQ1YSRVAF7qh02lXXPHCoFxf5AIfgYg=
ENV APP_URL=http://localhost:3001
ENV PORT=3001
ENV DB_CONNECTION=mysql
ENV DB_HOST=mysql
ENV DB_PORT=3306
ENV DB_DATABASE=service_a
ENV DB_USERNAME=service_a
ENV DB_PASSWORD=service_a_secret
ENV IAE_INTERNAL_KEY=102022400143
ENV IAE_API_KEYS=102022400143

EXPOSE 3001

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/entrypoint.sh \
    && chmod +x /usr/local/bin/entrypoint.sh

CMD ["/usr/local/bin/entrypoint.sh"]
