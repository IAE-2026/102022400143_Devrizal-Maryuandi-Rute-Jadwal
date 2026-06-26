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

# Copy composer.json dulu (lock lama sengaja TIDAK dipakai karena mengandung
# paket typo 'laravel/pao'). Layer ini ke-cache selama composer.json tak berubah.
COPY composer.json ./

# Resolve & download dependency. Pakai 'update' untuk membuat lock bersih.
# --no-dev mempercepat (skip phpunit dkk), --no-autoloader ditunda ke setelah COPY.
RUN composer update --no-interaction --prefer-dist --no-scripts --no-dev --no-autoloader

# Copy seluruh source code (vendor & lock lama dikecualikan via .dockerignore)
COPY . .

# Generate autoloader optimal (cepat, tidak resolve ulang dependency)
RUN composer dump-autoload --optimize --no-dev

# Pastikan folder storage & cache bisa ditulis
RUN chmod -R 775 storage bootstrap/cache

# Entrypoint: tunggu DB, generate key kalau perlu, migrate+seed, lalu serve
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["docker-entrypoint.sh"]
