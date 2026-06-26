# IAE-T2 Route & Schedule Service - standalone Dockerfile
# Tidak bergantung pada Laravel Sail. Berbasis image PHP resmi.
FROM php:8.4-cli

# Install dependency sistem + ekstensi PHP yang dibutuhkan Laravel + MySQL
RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring zip bcmath dom xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Ambil Composer dari image resmi
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for layer caching
COPY composer.json composer.lock ./

# Install dependencies quickly using composer install
RUN composer install --no-interaction --prefer-dist --no-scripts

# Copy seluruh source code (vendor & lock dikecualikan via .dockerignore)
COPY . .

# Sekarang kode sumber (termasuk 'artisan') sudah ada, jalankan dump-autoload
RUN composer dump-autoload --optimize

# Pastikan folder storage & cache bisa ditulis
RUN chmod -R 775 storage bootstrap/cache

# Entrypoint: tunggu DB, generate key kalau perlu, migrate+seed, lalu serve
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["docker-entrypoint.sh"]
