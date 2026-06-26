#!/bin/sh
set -e

echo "==> Menunggu database MySQL siap..."
until php -r "exit(@fsockopen(getenv('DB_HOST') ?: 'mysql', (int)(getenv('DB_PORT') ?: 3306)) ? 0 : 1);" 2>/dev/null; do
    echo "   database belum siap, tunggu 3 detik..."
    sleep 3
done
echo "==> Database siap."

# Pastikan ada file .env (disalin dari .env.example kalau belum ada)
if [ ! -f .env ]; then
    echo "==> .env tidak ada, menyalin dari .env.example"
    cp .env.example .env 2>/dev/null || touch .env
fi

# Generate APP_KEY kalau belum ada
if ! grep -q "^APP_KEY=base64:" .env 2>/dev/null; then
    echo "==> Generate APP_KEY"
    php artisan key:generate --force || true
fi

echo "==> Menjalankan migrasi + seeder..."
php artisan migrate --force --seed || php artisan migrate --force

echo "==> Membersihkan config & cache lama (cegah error cache GraphQL)..."
php artisan config:clear || true
php artisan cache:clear || true

echo "==> Generate dokumentasi Swagger..."
php artisan l5-swagger:generate || true

echo "==> Menjalankan server di 0.0.0.0:8000"
exec php artisan serve --host=0.0.0.0 --port=8000
