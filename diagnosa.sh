#!/bin/bash
# ============================================================
# Skrip diagnosa IAE-T2 — bangun, jalankan, dan tes semua endpoint.
# Jalankan: bash diagnosa.sh
# ============================================================
set -e
cd "$(dirname "$0")"

KEY="102022400143"
PORT="${APP_PORT:-8000}"
BASE="http://localhost:${PORT}"

echo "======================================================"
echo " DIAGNOSA IAE-T2 — port ${PORT}"
echo "======================================================"

echo ""
echo ">>> [1/4] Stop & hapus container/volume lama..."
docker compose down -v 2>/dev/null || true

echo ""
echo ">>> [2/4] Build TANPA cache (paksa fresh, hindari image lama)..."
docker compose build --no-cache

echo ""
echo ">>> [3/4] Jalankan container di background..."
docker compose up -d

echo ""
echo ">>> Menunggu server siap (maks 60 detik)..."
for i in $(seq 1 60); do
    if curl -s -o /dev/null "${BASE}/up" 2>/dev/null; then
        echo "    Server siap setelah ${i} detik."
        break
    fi
    sleep 1
done

echo ""
echo ">>> Daftar route API di dalam container:"
docker compose exec -T app php artisan route:list --path=api 2>/dev/null || echo "    (gagal ambil route list)"

echo ""
echo ">>> [4/4] TES SEMUA ENDPOINT"
echo "------------------------------------------------------"

test_endpoint() {
    local label="$1"; local expected="$2"; shift 2
    local code
    code=$(curl -s -o /dev/null -w "%{http_code}" "$@")
    if [ "$code" = "$expected" ]; then
        echo "  ✅ ${label} → ${code} (harapan ${expected})"
    else
        echo "  ❌ ${label} → ${code} (harapan ${expected})"
    fi
}

test_endpoint "Tanpa key (harus 401)"          "401" "${BASE}/api/v1/routes"
test_endpoint "Dengan key (harus 200)"         "200" -H "X-IAE-KEY: ${KEY}" "${BASE}/api/v1/routes"
test_endpoint "GET id ngawur (harus 404)"      "404" -H "X-IAE-KEY: ${KEY}" "${BASE}/api/v1/routes/99999"
test_endpoint "Swagger UI (harus 200)"         "200" "${BASE}/api/documentation"
test_endpoint "Swagger JSON (harus 200)"       "200" "${BASE}/docs"
test_endpoint "GraphiQL playground (harus 200)" "200" "${BASE}/graphiql"

echo ""
echo ">>> POST create (harus 201):"
RNG=$RANDOM
curl -s -o /dev/null -w "  POST → %{http_code}\n" -X POST "${BASE}/api/v1/routes" \
  -H "X-IAE-KEY: ${KEY}" -H "Content-Type: application/json" \
  -d "{\"route_code\":\"TEST-${RNG}\",\"origin\":\"Bandung\",\"destination\":\"Jakarta\",\"departure_point\":\"Dago\",\"arrival_point\":\"Lebak Bulus\",\"departure_date\":\"2026-07-01\",\"departure_time\":\"09:00\",\"arrival_time\":\"12:30\",\"vehicle_type\":\"Executive\",\"price\":150000,\"seat_capacity\":12,\"available_seats\":12,\"status\":\"available\"}"

echo ""
echo ">>> GraphQL introspection (harus ada __schema):"
curl -s -X POST "${BASE}/graphql" -H "Content-Type: application/json" \
  -d '{"query":"{ __schema { queryType { name } } }"}'
echo ""

echo ""
echo "======================================================"
echo " Selesai. Kalau ada ❌, screenshot output ini."
echo " Server jalan di ${BASE} — stop dengan: docker compose down"
echo "======================================================"
