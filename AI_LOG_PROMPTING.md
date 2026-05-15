# 🤖 AI Prompting Log

**Route & Schedule Service**  
BBK2HAB3 — Integrasi Aplikasi Enterprise

| | |
|---|---|
| **Nama** | Devrizal Maryuandi |
| **NIM** | 102022400143 |
| **Repository** | `102022400143_Devrizal-Maryuandi-Routes-Schedule` |
| **Tool AI** | ChatGPT |
| **Tanggal** | 15 Mei 2026 |

---

## 📌 Tujuan Penggunaan AI

Membantu proses analisis requirement, perencanaan, perancangan service, penyusunan REST API contract, GraphQL query, Standard Integration Contract, Docker setup, Swagger/OpenAPI documentation, serta persiapan repository untuk Tugas 2 Integrasi Aplikasi Enterprise.

Service yang dikembangkan adalah **Route & Schedule Service** pada domain **Public Transport Ticket Booking**. Service ini bertanggung jawab untuk mengelola data rute dan jadwal perjalanan dalam satu resource utama bernama `routes`.

---

## 📝 Log Prompting

### 1. Analisis Requirement Tugas

**Prompt:**
> Analisis requirement tugas ini dong

**Respons AI:**
AI menganalisis dokumen tugas dan Standard Integration Contract. Hasil analisis menjelaskan bahwa setiap mahasiswa wajib membuat mini-service individu yang memiliki REST API minimal 3 endpoint, response JSON konsisten, API Key melalui request header, Swagger/OpenAPI, GraphQL query, Docker, repository di organisasi dosen, dan file log prompting AI.

**Hasil:**  
Mendapatkan pemahaman lengkap mengenai ketentuan utama Tugas 2, termasuk bobot penilaian REST API, Swagger, GraphQL, security API Key, dan repository.

---

### 2. Penyesuaian Tema Public Transport

**Prompt:**
> Ini untuk tema nya, gw dapet tema yang public transport dan service yang rute & jadwal. Habis ini aku mau kasih kamu probis untuk tema nya bisa kah?

**Respons AI:**
AI mengonfirmasi bahwa tema **Public Transport** dan service **Rute & Jadwal** dapat digunakan untuk tugas. AI juga menjelaskan bahwa service ini dapat difokuskan pada data rute, jadwal keberangkatan, estimasi kedatangan, harga, kapasitas kursi, dan status perjalanan.

**Hasil:**  
Service yang dikerjakan diarahkan menjadi **Route & Schedule Service** dalam ekosistem public transport.

---

### 3. Analisis Proses Bisnis

**Prompt:**
> Nah ini untuk probis nya

**Respons AI:**
AI menganalisis diagram proses bisnis pemesanan tiket travel. AI membagi proses menjadi beberapa bagian, yaitu pencarian rute dan jadwal, pemilihan jadwal, booking tiket, pembayaran, e-ticket, notifikasi delay, dan detail delay. AI menyarankan agar service yang dikerjakan hanya fokus pada bagian rute dan jadwal.

**Hasil:**  
Batasan service menjadi lebih jelas. Service ini tidak menangani booking tiket, pembayaran, e-ticket, atau notifikasi delay karena bagian tersebut menjadi tanggung jawab service lain.

---

### 4. Penentuan Resource Route dan Schedule

**Prompt:**
> Ngga route sama schedule itu kita jadikan 1 yah

**Respons AI:**
AI menyesuaikan desain service agar route dan schedule digabung menjadi satu resource bernama `routes`. AI menjelaskan bahwa satu data route akan mencakup informasi asal, tujuan, titik keberangkatan, titik kedatangan, tanggal keberangkatan, jam keberangkatan, estimasi tiba, jenis kendaraan, harga, kapasitas kursi, sisa kursi, dan status.

**Hasil:**  
Diputuskan bahwa tidak akan dibuat resource terpisah seperti `schedules`. Endpoint utama yang digunakan adalah:
- `GET /api/v1/routes`
- `GET /api/v1/routes/{id}`
- `POST /api/v1/routes`

---

### 5. Review Prompt Implementasi

**Prompt:**
> Nah bener seperti itu, aku mau kasih kamu prompt untuk kerjain ini semua. Dan coba koreksi apakah prompt nya itu sesuai dengan ketentuan tugas nya

**Respons AI:**
AI mengevaluasi prompt implementasi yang dibuat. AI memberikan koreksi agar prompt lebih sesuai dengan tugas, seperti menggunakan header `X-IAE-KEY`, memastikan endpoint memakai versioning `/api/v1`, mengikuti Standard Integration Contract, tidak membuat resource `schedules`, serta membatasi scope agar tidak terlalu melebar.

**Hasil:**  
Prompt implementasi diperbaiki agar lebih sesuai dengan requirement tugas dan service yang dikerjakan.

---

### 6. Finalisasi Prompt Implementasi

**Prompt:**
> ```text

We are building an IAE Assignment 2 service-based project for the course BBK2HAB3 - Integrasi Aplikasi Enterprise.

Each team member must create their own separate GitHub repository and service. Do not make one monorepo unless the lecturer explicitly requires it. Each service must run independently with Docker and communicate with other services through HTTP endpoints, not by directly accessing another service's database.

Use Plan Mode first. Before writing code, create a clear implementation plan. If any critical information is missing, ask follow-up questions. If enough information is already provided, proceed by making reasonable assumptions and clearly state those assumptions before implementation.

Project context:

- Domain/business process: Public Transport ticket booking for travel service.

- Business process includes:

  1. Route and schedule search

  2. Schedule selection

  3. Ticket booking

  4. Payment

  5. E-ticket delivery

  6. Delay notification

  7. Delay detail

- Team services:

  1. Route & Schedule Service

  2. Delay Notification Service

  3. Ticket & Payment Service

- My assigned service: Route & Schedule Service.

- In this service, route and schedule are combined into one resource called `routes`.

- This service is responsible for managing:

  1. Route information

  2. Departure schedule

  3. Arrival estimation

  4. Departure point

  5. Arrival point

  6. Vehicle type

  7. Price

  8. Seat capacity

  9. Available seats

  10. Route status

- This service does not handle:

  1. Ticket creation

  2. Payment

  3. E-ticket delivery

  4. Delay notification

  5. Delay detail

- My NIM/API key is: 102022400143

- My preferred framework is: Laravel

- My repository name must be: 102022400143_Devrizal-Maryuandi-Routes-Schedule

Assignment requirements:

- Each student owns one service repository.

- Repository must be created in the organization provided by the lecturer.

- Repository naming format must follow: NIM_Nama-Service.

- Each service must be independently runnable.

- Docker is required.

- REST endpoints must be versioned using `/api/v1/...`.

- REST API must include at least 3 functional endpoints.

- My REST endpoints must be exactly:

  1. GET /api/v1/routes

  2. GET /api/v1/routes/{id}

  3. POST /api/v1/routes

- Do not create `/api/v1/schedules` endpoints because route and schedule are combined into the `routes` resource.

- REST endpoints must return correct HTTP status codes:

  1. 200 for successful data retrieval

  2. 201 for successful data creation

  3. 401 for missing or invalid API key

  4. 404 for route data not found

  5. 422 for validation error

- All endpoints must use JSON.

- All REST responses must use:

  1. Content-Type: application/json

  2. Charset: UTF-8

  3. Protocol: HTTP/1.1

- All REST endpoints must follow the Standard Integration Contract response format.

- All REST endpoints must be protected with an API key sent through request header:

  X-IAE-KEY: 102022400143

- If the API key is missing or invalid, return a 401 error using the standard error response format.

- The service must provide interactive Swagger/OpenAPI documentation.

- Swagger UI must document every REST endpoint, including:

  1. HTTP method

  2. Endpoint path

  3. Header `X-IAE-KEY`

  4. Path parameter for `GET /api/v1/routes/{id}`

  5. Request body for `POST /api/v1/routes`

  6. Success response examples

  7. Error response examples

- The service must provide a GraphQL endpoint.

- The service must provide at least 1 GraphQL query that returns the same route data as the REST API while allowing clients to choose fields.

- Do not create a separate `schedules` resource in GraphQL because route and schedule are combined into `routes`.

- The service must provide a GraphQL Playground or GraphiQL page for testing.

- The repository must include an AI prompting/chat history file in `.md` format.

Use this JSON success response format from the Standard Integration Contract:

{

  "status": "success",

  "message": "Operation successful",

  "data": {},

  "meta": {

    "service_name": "Route-Schedule-Service",

    "api_version": "v1"

  }

}

Use this JSON error response format:

{

  "status": "error",

  "message": "Detail pesan kesalahan...",

  "errors": null

}

The `routes` entity must include these fields:

- id

- route_code

- origin

- destination

- departure_point

- arrival_point

- departure_date

- departure_time

- arrival_time

- vehicle_type

- price

- seat_capacity

- available_seats

- status

- created_at

- updated_at

Suggested status values:

- available

- full

- inactive

- delayed

GraphQL requirements:

- Provide a GraphQL endpoint.

- Provide GraphQL Playground or GraphiQL page.

- Use `Route` as the GraphQL type.

- Do not create `Schedule` type.

- The GraphQL query must allow clients to choose fields.

Docker requirements:

- Provide Dockerfile.

- Provide docker-compose.yml.

- The service must run independently with Docker.

- Include database service if using MySQL/MariaDB/PostgreSQL.

- Include clear commands in README to run the service.

- Include migration and seeding commands.

Required documentation files:

1. README.md

2. AI_PROMPTING_LOG.md

3. TESTING_GUIDE.md or testing section inside README.md

Final implementation rule:

- Prioritize assignment compliance over over-engineered architecture.

- Use only `routes` as the main resource.

- Do not create `/api/v1/schedules`.

- Do not create a separate `Schedule` model/type/resource.

- Keep the implementation simple, clear, and aligned with the assignment rubric.

- After creating the implementation plan, wait for approval before generating code.

**Respons AI:**
AI menyatakan bahwa prompt sudah layak digunakan. AI juga menyarankan tambahan penting agar hanya menggunakan resource `routes`, tidak membuat endpoint `/api/v1/schedules`, dan memprioritaskan compliance terhadap tugas dibanding arsitektur yang terlalu kompleks.

**Hasil:**  
Prompt final siap digunakan sebagai dasar perencanaan dan implementasi service.

---

### 7. Pembuatan Implementation Plan

**Prompt:**
> Oke gas jalanin prompt ini dong

**Respons AI:**
AI menjalankan prompt dalam Plan Mode dan menghasilkan implementation plan lengkap. Plan mencakup service architecture, REST API contract, Standard Integration Contract response, API Key security, database schema, GraphQL contract, Swagger/OpenAPI plan, Docker setup, inter-service communication flow, error handling, testing plan, repository structure, README plan, dan final checklist.

**Hasil:**  
Mendapatkan blueprint teknis lengkap untuk membangun Route & Schedule Service.

---

### 8. Penyusunan Langkah Implementasi Lengkap

**Prompt:**
> Oke kasih aku langkah langkah yg lengkap untuk semua nya dong

**Respons AI:**
AI memberikan langkah implementasi teknis secara lengkap menggunakan Laravel. Langkah yang diberikan mencakup pembuatan repository, setup Laravel, setup Docker, konfigurasi `.env`, migration tabel `routes`, model, seeder, response wrapper, middleware API Key, controller REST API, route API, pengujian endpoint, setup Swagger/OpenAPI, setup GraphQL Lighthouse, pembuatan GraphiQL, README, AI prompt log, testing guide, dan push ke GitHub.

**Hasil:**  
Mendapatkan panduan teknis step-by-step untuk membangun service dari awal sampai siap dikumpulkan.

---

---

### 9. Panduan Push Project ke GitHub

**Prompt:**
> Gw mau ngepush dulu ke GitHub nya yah

**Respons AI:**
AI memberikan command Git untuk melakukan push ke repository GitHub organisasi dosen, termasuk `git add`, `git commit`, menambahkan remote origin, dan `git push`.

**Hasil:**  
Mendapatkan arahan awal untuk melakukan push project ke repository GitHub.

---

### 10. Panduan Push Pertama Kali dari Project Lokal

**Prompt:**
> Gw baru peratama kali mau push ini, blm tertaut sama repo nya karna ini bukan ngeclone

**Respons AI:**
AI menjelaskan langkah push pertama kali dari folder lokal yang belum terhubung ke repository GitHub. AI memberikan command:
- `git init`
- `git add .`
- `git commit`
- `git branch -M main`
- `git remote add origin`
- `git push -u origin main`

AI juga mengingatkan agar file `.env` tidak ikut ter-push dan yang aman di-push adalah `.env.example`.

**Hasil:**  
Mendapatkan langkah Git yang sesuai untuk project lokal yang belum pernah dihubungkan ke remote repository.

---

### 11. Fix Error MySQL Access Denied

**Prompt:**
> kenapa error yak (sail artisan migrate → Access denied for user 'sail' to database 'public_transport')

**Respons AI (Antigravity):**
AI mengidentifikasi bahwa volume MySQL lama masih menyimpan database dengan nama berbeda dari konfigurasi `.env`. AI menjalankan `sail down -v` untuk menghapus volume, lalu `sail up -d` dan `sail artisan migrate` ulang. Semua migration berhasil dijalankan.

**Hasil:**  
Database `public_transport` berhasil dibuat dan semua migration berjalan (users, cache, jobs, routes).

---

### 12. Fix Syntax Error di bootstrap/app.php

**Prompt:**
> kenapa error (make:controller → PHP Parse error: Unclosed '{' on line 13)

**Respons AI (Antigravity):**
AI menemukan duplikasi `]);` dan `})` di file `bootstrap/app.php` yang menyebabkan PHP parse error. AI memperbaiki struktur bracket yang berantakan sehingga semua artisan command bisa berjalan kembali.

**Hasil:**  
File `bootstrap/app.php` diperbaiki dan command `make:controller Api/RouteController` berhasil dijalankan.

---

### 13. Penambahan Import yang Hilang di bootstrap/app.php

**Prompt:**
> ini udah bener belum masih kah ada error? (bootstrap/app.php menggunakan ValidationException dan Request)

**Respons AI (Antigravity):**
AI mendeteksi bahwa `ValidationException` dan `Request` digunakan di dalam closure `withExceptions` tapi belum di-import. AI menambahkan `use Illuminate\Http\Request` dan `use Illuminate\Validation\ValidationException` di bagian atas file.

**Hasil:**  
File `bootstrap/app.php` menjadi valid dan exception handling untuk validasi API berfungsi dengan benar.

---

### 14. Registrasi API Routes di bootstrap/app.php

**Prompt:**
> saya ingin hasil route:list itu GET api/v1/routes, GET api/v1/routes/{id}, POST api/v1/routes. Sekarang masih get get dan put

**Respons AI (Antigravity):**
AI mengidentifikasi bahwa file `routes/api.php` belum didaftarkan di `bootstrap/app.php`. AI menambahkan parameter `api: __DIR__.'/../routes/api.php'` di bagian `withRouting()`. Setelah itu, `route:list` menampilkan 3 endpoint API yang benar.

**Hasil:**  
Semua API route berhasil terdaftar: `GET api/v1/routes`, `GET api/v1/routes/{id}`, dan `POST api/v1/routes`.

---

### 15. Setup GraphQL Schema untuk Route

**Prompt:**
> gw mau masukin schema ini dong (Route type dan query untuk GraphQL)

**Respons AI (Antigravity):**
AI menambahkan type `Route` dan query `routes` (dengan `@all`) serta `route(id)` (dengan `@find`) ke file `graphql/schema.graphql`, sambil mempertahankan type `User` dan query bawaan Lighthouse.

**Hasil:**  
GraphQL schema berhasil diupdate dengan type Route lengkap beserta query untuk mengambil semua routes dan route berdasarkan ID.

---

### 16. Fix GraphiQL Loading Stuck

**Prompt:**
> kenapa ini infinite loading (GraphiQL halaman stuck di "Loading...")

**Respons AI (Antigravity):**
AI mengidentifikasi bahwa GraphiQL v3 membutuhkan React 18 dengan `ReactDOM.createRoot()`, bukan `ReactDOM.render()` yang sudah deprecated. AI mengupdate template `graphiql.blade.php` dengan versi yang benar menggunakan `GraphiQL.createFetcher()` dan `ReactDOM.createRoot()`.

**Hasil:**  
Halaman GraphiQL berhasil dimuat dan bisa digunakan untuk testing GraphQL query.

---

### 17. Pembuatan AI Prompt Log

**Prompt:**
> Berdasarkan room chat ini, coba dong buatin log prompt yang rapi buat di taro format .md

**Respons AI:**
AI menyusun file AI Prompt Log dalam format Markdown. Isi log mencakup ringkasan prompt, output AI yang digunakan, hasil, keputusan final pengembangan, entity fields, response format, dan catatan penggunaan AI.

**Hasil:**  
Mendapatkan draft dokumentasi AI prompting log untuk dimasukkan ke repository.

---


## ✅ Kesimpulan

AI membantu dalam proses pengerjaan Tugas 2 Integrasi Aplikasi Enterprise, terutama pada tahap:
- Menganalisis requirement tugas.
- Menentukan scope Route & Schedule Service.
- Menyesuaikan proses bisnis Public Transport.
- Menentukan resource utama `routes`.
- Merancang REST API contract.
- Menyesuaikan response dengan Standard Integration Contract.
- Merancang API Key security menggunakan `X-IAE-KEY`.
- Merancang GraphQL query untuk data routes.
- Menyusun rencana Swagger/OpenAPI documentation.
- Membuat langkah implementasi Laravel dan Docker.
- Menyiapkan push ke GitHub.
- Menyusun dokumentasi AI prompting log.
- Fix error MySQL access denied saat migration.
- Fix syntax error dan missing imports di `bootstrap/app.php`.
- Registrasi API routes agar terdaftar di route list.
- Setup GraphQL schema untuk Route.
- Fix GraphiQL loading stuck.

Total sesi prompting yang terdokumentasi: **17 interaksi utama**.

---

## 📌 Final Development Decision

| Aspek | Keputusan |
|---|---|
| Domain | Public Transport Ticket Booking |
| Service | Route & Schedule Service |
| Resource utama | `routes` |
| Framework | Laravel |
| Repository | `102022400143_Devrizal-Maryuandi-Routes-Schedule` |
| API Key Header | `X-IAE-KEY` |
| API Key Value | `102022400143` |
| REST Endpoint 1 | `GET /api/v1/routes` |
| REST Endpoint 2 | `GET /api/v1/routes/{id}` |
| REST Endpoint 3 | `POST /api/v1/routes` |
| GraphQL Resource | `Route` |
| Separate Schedule Resource | Tidak digunakan |
| Documentation | Swagger/OpenAPI |
| Runtime | Docker |
| Evidence File | `AI_PROMPTING_LOG.md` |

---

## 📦 Route Entity Fields

```text
id
route_code
origin
destination
departure_point
arrival_point
departure_date
departure_time
arrival_time
vehicle_type
price
seat_capacity
available_seats
status
created_at
updated_at
```