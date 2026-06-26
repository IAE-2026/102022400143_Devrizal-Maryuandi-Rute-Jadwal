<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class DocsController extends Controller
{
    public function swaggerUi(): Response
    {
        return response($this->swaggerHtml())->header('Content-Type', 'text/html; charset=UTF-8');
    }

    public function openApi(): JsonResponse
    {
        return response()->json([
            'openapi' => '3.0.0',
            'info' => [
                'title' => 'Service Rute & Jadwal - API',
                'version' => '1.0.0',
                'description' => 'Service Smart Transport untuk mengelola data rute & jadwal perjalanan: daftar rute, detail rute, dan pembuatan rute baru.',
            ],
            'servers' => [
                ['url' => config('app.url', 'http://localhost:3001')],
            ],
            'components' => [
                'securitySchemes' => [
                    'ApiKeyAuth' => [
                        'type' => 'apiKey',
                        'in' => 'header',
                        'name' => 'X-IAE-KEY',
                    ],
                ],
                'schemas' => $this->schemas(),
            ],
            'security' => [
                ['ApiKeyAuth' => []],
            ],
            'paths' => $this->paths(),
        ]);
    }

    private function swaggerHtml(): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Dokumentasi API Service Rute & Jadwal</title>
  <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css">
  <style>
    :root {
      --bg: #f4f6f8;
      --panel: #ffffff;
      --panel-soft: #f8fafc;
      --line: #e3e8ee;
      --text: #1b2733;
      --muted: #5b6b7b;
      --green: #00a36c;
      --blue: #2f6fed;
    }

    html,
    body {
      margin: 0;
      min-height: 100%;
      background: var(--bg);
    }

    body {
      color: var(--text);
      font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    }

    .swagger-ui {
      color: var(--text);
    }

    .swagger-ui .topbar {
      display: none;
    }

    .swagger-ui .wrapper {
      max-width: 1100px;
      padding: 0 28px;
    }

    .swagger-ui .information-container {
      background: linear-gradient(135deg, #ffffff 0%, #eefaf4 100%);
      border: 1px solid var(--line);
      border-radius: 14px;
      padding: 32px 32px 26px;
      margin-top: 28px;
      box-shadow: 0 1px 3px rgba(16, 24, 40, 0.04);
    }

    .swagger-ui .info {
      margin: 0;
    }

    .swagger-ui .info .title,
    .swagger-ui .info p,
    .swagger-ui .info li,
    .swagger-ui .info table {
      color: var(--text);
    }

    .swagger-ui .info .title {
      font-size: 32px;
      font-weight: 800;
      letter-spacing: -0.5px;
      color: #0f172a;
    }

    .swagger-ui .info .title small {
      top: -8px;
    }

    .swagger-ui .info .title small pre {
      color: #fff;
    }

    .swagger-ui a.nostyle,
    .swagger-ui a.nostyle:visited,
    .swagger-ui .opblock-tag,
    .swagger-ui .opblock .opblock-summary-description,
    .swagger-ui .opblock .opblock-summary-path,
    .swagger-ui .opblock .opblock-summary-path__deprecated,
    .swagger-ui .opblock-description-wrapper p,
    .swagger-ui .response-col_status,
    .swagger-ui .response-col_description,
    .swagger-ui .parameter__name,
    .swagger-ui .parameter__type,
    .swagger-ui .parameter__in,
    .swagger-ui table thead tr td,
    .swagger-ui table thead tr th {
      color: var(--text);
    }

    .swagger-ui .scheme-container,
    .swagger-ui .opblock-tag-section {
      background: var(--bg);
      border-top: 1px solid rgba(255, 255, 255, 0.04);
      border-bottom: 1px solid var(--line);
      box-shadow: none;
    }

    .swagger-ui .scheme-container {
      padding: 48px 0 40px;
    }

    .swagger-ui .schemes > label,
    .swagger-ui .servers-title {
      color: var(--text);
    }

    .swagger-ui select {
      background: var(--panel);
      border-color: #a9b4bd;
      color: var(--text);
    }

    .swagger-ui .btn.authorize {
      border-color: var(--green);
      color: var(--green);
      background: transparent;
    }

    .swagger-ui .btn.authorize svg {
      fill: var(--green);
    }

    .swagger-ui .opblock-tag {
      border-bottom-color: var(--line);
      font-size: 30px;
      padding: 34px 12px 16px;
    }

    .swagger-ui .opblock {
      border-radius: 10px;
      box-shadow: 0 1px 2px rgba(16, 24, 40, 0.05);
      margin: 0 0 14px;
      border: 1px solid var(--line);
    }

    .swagger-ui .opblock .opblock-summary {
      min-height: 48px;
    }

    .swagger-ui .opblock .opblock-section-header {
      background: var(--panel);
      box-shadow: none;
    }

    .swagger-ui .opblock .opblock-section-header h4,
    .swagger-ui .opblock .opblock-section-header label,
    .swagger-ui .opblock .opblock-section-header label span {
      color: var(--text);
    }

    .swagger-ui .opblock .opblock-body {
      background: var(--panel-soft);
      color: var(--text);
    }

    .swagger-ui .opblock.opblock-get {
      background: rgba(73, 163, 255, 0.12);
      border-color: var(--blue);
    }

    .swagger-ui .opblock.opblock-get .opblock-summary {
      border-color: var(--blue);
    }

    .swagger-ui .opblock.opblock-get .opblock-summary-method {
      background: var(--blue);
      color: #03111f;
    }

    .swagger-ui .opblock.opblock-post {
      background: rgba(0, 199, 129, 0.12);
      border-color: var(--green);
    }

    .swagger-ui .opblock.opblock-post .opblock-summary {
      border-color: var(--green);
    }

    .swagger-ui .opblock.opblock-post .opblock-summary-method {
      background: var(--green);
      color: #031a12;
    }

    .swagger-ui .opblock-summary-method {
      border-radius: 4px;
      font-weight: 700;
    }

    .swagger-ui textarea,
    .swagger-ui input[type=text],
    .swagger-ui input[type=password],
    .swagger-ui input[type=email] {
      background: #ffffff;
      border-color: #cfd8e3;
      color: var(--text);
      border-radius: 8px;
    }

    .swagger-ui .btn.execute {
      background: var(--green);
      border-color: var(--green);
      border-radius: 8px;
    }

    .swagger-ui .model-box,
    .swagger-ui .model,
    .swagger-ui .prop-format,
    .swagger-ui .prop-type,
    .swagger-ui .tab li,
    .swagger-ui .responses-inner h4,
    .swagger-ui .responses-inner h5 {
      color: var(--text);
    }

    .swagger-ui .highlight-code,
    .swagger-ui .microlight {
      background: #10161a;
      color: var(--text);
    }

    @media (max-width: 900px) {
      .swagger-ui .wrapper {
        padding: 0 20px;
      }

      .swagger-ui .info .title {
        font-size: 34px;
      }
    }
  </style>
</head>
<body>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js"></script>
  <script>
    window.ui = SwaggerUIBundle({
      url: '/openapi.json',
      dom_id: '#swagger-ui',
      presets: [SwaggerUIBundle.presets.apis],
      layout: 'BaseLayout',
      docExpansion: 'list',
      defaultModelsExpandDepth: -1,
      displayRequestDuration: true
    });
  </script>
</body>
</html>
HTML;
    }

    private function paths(): array
    {
        $paths = [
            '/api/v1/routes' => [
                'get' => [
                    'summary' => 'Melihat daftar seluruh rute & jadwal',
                    'tags' => ['Rute'],
                    'responses' => [
                        '200' => ['description' => 'Daftar rute berhasil diambil'],
                        '401' => ['description' => 'X-IAE-KEY tidak dikirim'],
                        '403' => ['description' => 'X-IAE-KEY tidak valid'],
                    ],
                ],
                'post' => [
                    'summary' => 'Membuat rute & jadwal baru',
                    'tags' => ['Rute'],
                    'requestBody' => [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => ['$ref' => '#/components/schemas/CreateRouteRequest'],
                            ],
                        ],
                    ],
                    'responses' => [
                        '201' => ['description' => 'Rute berhasil dibuat'],
                        '422' => ['description' => 'Validasi gagal'],
                    ],
                ],
            ],
            '/api/v1/routes/{id}' => [
                'get' => [
                    'summary' => 'Melihat detail satu rute & jadwal',
                    'tags' => ['Rute'],
                    'parameters' => [$this->idParameter()],
                    'responses' => [
                        '200' => ['description' => 'Detail rute berhasil diambil'],
                        '404' => ['description' => 'Rute tidak ditemukan'],
                    ],
                ],
            ],
        ];

        // Deklarasikan setiap response sebagai application/json supaya Swagger
        // mengirim header "Accept: application/json" (bukan */*) di curl.
        foreach ($paths as &$methods) {
            foreach ($methods as &$operation) {
                foreach ($operation['responses'] as &$response) {
                    if (! isset($response['content'])) {
                        $response['content'] = [
                            'application/json' => [
                                'schema' => ['type' => 'object'],
                            ],
                        ];
                    }
                }
            }
        }
        unset($methods, $operation, $response);

        return $paths;
    }

    private function schemas(): array
    {
        return [
            'CreateRouteRequest' => [
                'type' => 'object',
                'required' => [
                    'route_code', 'origin', 'destination', 'departure_point', 'arrival_point',
                    'departure_date', 'departure_time', 'arrival_time', 'vehicle_type',
                    'price', 'seat_capacity', 'available_seats', 'status',
                ],
                'properties' => [
                    'route_code' => ['type' => 'string', 'example' => 'BDG-JKT-003'],
                    'origin' => ['type' => 'string', 'example' => 'Bandung'],
                    'destination' => ['type' => 'string', 'example' => 'Jakarta'],
                    'departure_point' => ['type' => 'string', 'example' => 'Pool Dago'],
                    'arrival_point' => ['type' => 'string', 'example' => 'Terminal Lebak Bulus'],
                    'departure_date' => ['type' => 'string', 'example' => '2026-05-23'],
                    'departure_time' => ['type' => 'string', 'example' => '09:00'],
                    'arrival_time' => ['type' => 'string', 'example' => '12:30'],
                    'vehicle_type' => ['type' => 'string', 'example' => 'Travel Executive'],
                    'price' => ['type' => 'integer', 'example' => 150000],
                    'seat_capacity' => ['type' => 'integer', 'example' => 12],
                    'available_seats' => ['type' => 'integer', 'example' => 12],
                    'status' => ['type' => 'string', 'enum' => ['available', 'full', 'inactive', 'delayed'], 'example' => 'available'],
                ],
            ],
        ];
    }

    private function idParameter(): array
    {
        return [
            'name' => 'id',
            'in' => 'path',
            'required' => true,
            'schema' => ['type' => 'string'],
            'example' => 'rte_001',
        ];
    }
}
