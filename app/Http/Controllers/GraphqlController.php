<?php

namespace App\Http\Controllers;

use App\Services\RouteStore;
use GraphQL\GraphQL;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Throwable;

class GraphqlController extends Controller
{
    public function query(Request $request, RouteStore $store): JsonResponse
    {
        $query = $request->input('query');

        if (! is_string($query) || trim($query) === '') {
            return response()->json([
                'errors' => [
                    ['message' => 'Query GraphQL wajib diisi.'],
                ],
            ], 400);
        }

        try {
            $result = GraphQL::executeQuery(
                $this->schema($store),
                $query,
                null,
                null,
                $request->input('variables', [])
            );

            return response()->json($result->toArray());
        } catch (Throwable $exception) {
            return response()->json([
                'errors' => [
                    ['message' => $exception->getMessage()],
                ],
            ], 500);
        }
    }

    public function playground(): Response
    {
        return response($this->playgroundHtml())->header('Content-Type', 'text/html; charset=UTF-8');
    }

    private function schema(RouteStore $store): Schema
    {
        $routeType = new ObjectType([
            'name' => 'Route',
            'fields' => [
                'id' => Type::string(),
                'route_code' => Type::string(),
                'origin' => Type::string(),
                'destination' => Type::string(),
                'departure_point' => Type::string(),
                'arrival_point' => Type::string(),
                'departure_date' => Type::string(),
                'departure_time' => Type::string(),
                'arrival_time' => Type::string(),
                'vehicle_type' => Type::string(),
                'price' => Type::int(),
                'seat_capacity' => Type::int(),
                'available_seats' => Type::int(),
                'status' => Type::string(),
                'created_at' => Type::string(),
                'updated_at' => Type::string(),
            ],
        ]);

        $queryType = new ObjectType([
            'name' => 'Query',
            'fields' => [
                'routes' => [
                    'type' => Type::listOf($routeType),
                    'resolve' => fn () => $store->all(),
                ],
                'route' => [
                    'type' => $routeType,
                    'args' => [
                        'id' => Type::nonNull(Type::string()),
                    ],
                    'resolve' => fn ($root, array $args) => $store->find((string) $args['id']),
                ],
            ],
        ]);

        return new Schema(['query' => $queryType]);
    }

    private function playgroundHtml(): string
    {
        return <<<'HTML'
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>GraphQL - Service Rute & Jadwal</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/graphiql/1.5.20/graphiql.min.css">
  <style>
    body { margin: 0; height: 100vh; display: flex; flex-direction: column; }
    #auth { display: flex; align-items: center; gap: 12px; padding: 8px 16px; background: #161616; color: #fff; font-family: monospace; }
    #auth input { width: 220px; padding: 7px 10px; border: 1px solid #555; border-radius: 4px; background: #252525; color: #9cff9c; }
    #graphiql { flex: 1; min-height: 0; }
  </style>
</head>
<body>
  <div id="auth">
    <label for="iae-key">X-IAE-KEY</label>
    <input id="iae-key" value="102022400143">
  </div>
  <div id="graphiql"></div>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/react/17.0.2/umd/react.production.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/react-dom/17.0.2/umd/react-dom.production.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/graphiql/1.5.20/graphiql.min.js"></script>
  <script>
    function graphQLFetcher(params) {
      return fetch('/graphql', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-IAE-KEY': document.getElementById('iae-key').value.trim()
        },
        body: JSON.stringify(params)
      }).then(function (response) { return response.json(); });
    }

    ReactDOM.render(
      React.createElement(GraphiQL, {
        fetcher: graphQLFetcher,
        defaultQuery: '# Service Rute & Jadwal\n# Query daftar rute & jadwal\n\n{\n  routes {\n    id\n    route_code\n    origin\n    destination\n    departure_time\n    price\n    available_seats\n    status\n  }\n}'
      }),
      document.getElementById('graphiql')
    );
  </script>
</body>
</html>
HTML;
    }
}
