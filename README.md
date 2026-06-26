# Service Rute & Jadwal (Smart Transport)

Service Laravel untuk Tugas 2 mata kuliah **BBK2HAB3 - Integrasi Aplikasi Enterprise**. Service ini mengelola data rute & jadwal perjalanan (daftar rute, detail rute, dan pembuatan rute baru). Repository ini berisi hanya Service Rute & Jadwal dari ekosistem Smart Transport (service lain dikelola di repository terpisah oleh anggota kelompok lain).

## Identitas

| Parameter | Nilai |
|-----------|-------|
| **Mata Kuliah** | BBK2HAB3 - Integrasi Aplikasi Enterprise |
| **Mahasiswa** | Devrizal Maryuandi |
| **NIM / `X-IAE-KEY`** | `102022400143` |
| **Resource** | `routes` |
| **Framework** | Laravel 13 (PHP 8.4) |
| **Database** | MySQL 8.0 |
| **Port** | `3001` |

## Endpoint REST

Semua endpoint wajib menyertakan header:

```http
X-IAE-KEY: 102022400143
Content-Type: application/json
```

| Method | Path | Fungsi |
|--------|------|--------|
| `GET` | `/api/v1/routes` | Mengambil daftar seluruh rute & jadwal |
| `GET` | `/api/v1/routes/{id}` | Mengambil detail satu rute |
| `POST` | `/api/v1/routes` | Membuat rute & jadwal baru |

Semua respons mengikuti **Standard Integration Contract** dengan bentuk konsisten:

```json
{ "status": "success | error", "message": "...", "data": {}, "errors": null }
```

Error level framework (`404` / `405` / `422` / `500`) juga otomatis dibungkus ke format yang sama.

### Contoh body POST `/api/v1/routes`

```json
{
  "route_code": "BDG-JKT-003",
  "origin": "Bandung",
  "destination": "Jakarta",
  "departure_point": "Pool Dago",
  "arrival_point": "Terminal Lebak Bulus",
  "departure_date": "2026-05-23",
  "departure_time": "09:00",
  "arrival_time": "12:30",
  "vehicle_type": "Travel Executive",
  "price": 150000,
  "seat_capacity": 12,
  "available_seats": 12,
  "status": "available"
}
```

Nilai `status` yang valid: `available`, `full`, `inactive`, `delayed`.

## Dokumentasi API & GraphQL

| Halaman | URL |
|---------|-----|
| Swagger UI | `http://localhost:3001/api-docs` |
| OpenAPI JSON | `http://localhost:3001/openapi.json` |
| GraphQL Playground | `http://localhost:3001/graphql` |
| Health check | `http://localhost:3001/health` |

Contoh query GraphQL:

```graphql
{
  routes {
    id
    route_code
    origin
    destination
    departure_time
    price
    available_seats
    status
  }
}
```

## Menjalankan dengan Docker (Direkomendasikan)

Pastikan Docker Desktop sudah aktif.

```bash
docker compose up -d --build
```

Setelah container sehat, akses:

- `http://localhost:3001/health`
- `http://localhost:3001/api-docs`
- `http://localhost:3001/graphql`

Stack Docker terdiri dari **dua container**:

| Container | Image | Port host |
|-----------|-------|-----------|
| `app` | Build dari `Dockerfile` | `3001` |
| `mysql` | `mysql:8.0` | `3308` |

Migrasi dan seeder otomatis dijalankan oleh `docker/entrypoint.sh` setiap kontainer app start.

Hentikan stack:

```bash
docker compose down          # tanpa hapus data
docker compose down -v       # ikut menghapus volume MySQL
```

## Menjalankan Lokal (tanpa Docker)

Prasyarat: PHP 8.3+, Composer, MySQL 8.

```bash
composer install
cp .env.example .env
php artisan key:generate
# sesuaikan kredensial DB pada .env (DB_HOST, DB_DATABASE, dst.)
php artisan migrate --seed
php artisan serve --host=0.0.0.0 --port=3001
```

## Pengujian

Test otomatis memakai SQLite in-memory, jadi **tidak butuh MySQL** dan bisa langsung dijalankan setelah clone:

```bash
composer install
php artisan test
```

Cakupan test (`tests/Feature/RouteApiTest.php`): penolakan tanpa `X-IAE-KEY` (401), key tidak valid (403), `GET` daftar rute (200 + wrapper), detail tidak ditemukan (404 + wrapper), `POST` membuat rute (201), `POST` tanpa field wajib (422 + wrapper), kursi melebihi kapasitas (422), path tak dikenal (404, bukan 405), dan method tidak diizinkan (405 + wrapper).

### Smoke test cepat (setelah service jalan di port 3001)

```bash
# Tanpa key -> 401
curl -i http://localhost:3001/api/v1/routes

# Dengan key -> 200
curl -i -H "X-IAE-KEY: 102022400143" http://localhost:3001/api/v1/routes

# Path tak dikenal -> 404 (bukan 405)
curl -i -H "X-IAE-KEY: 102022400143" http://localhost:3001/api/v1/tidak-ada
```

## Lihat juga

- `AI_LOG_PROMPTING.md` — log prompt AI selama pengembangan.

---

## Catatan struktur

Struktur service ini diselaraskan (benchmark) dengan service rekan satu ekosistem Smart Transport agar konsisten lintas-anggota: pola Docker (`Dockerfile` + `docker/entrypoint.sh` + `docker-compose.yml`), middleware (`RequireIaeApiKey`, `CorsHeaders`), service layer (`RouteStore`), controller REST (`RouteController`), GraphQL custom (`GraphqlController` berbasis `webonyx/graphql-php`), dan dokumentasi (`DocsController` — Swagger UI + OpenAPI JSON) mengikuti kontrak integrasi standar yang sama. Tema disesuaikan ke domain **rute & jadwal** (resource `routes`, port `3001`, `X-IAE-KEY: 102022400143`).
