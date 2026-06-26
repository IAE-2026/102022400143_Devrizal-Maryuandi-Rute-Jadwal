<?php

namespace App\OpenAPI;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'Route & Schedule Service API',
    description: 'REST API documentation for Route & Schedule Service in IAE Assignment 2.'
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Local Docker server'
)]
#[OA\SecurityScheme(
    securityScheme: 'IAEApiKey',
    type: 'apiKey',
    in: 'header',
    name: 'X-IAE-KEY'
)]
class OpenApiInfo
{
}