<?php
namespace App\Routes;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteGroup;

class RouteHelperOpenApi implements RouteHelper
{
    public static function addRoutesTo(mixed $app): void
    {
        // Serves the pre-generated openapi.json.
        // Regenerate with: composer openapi
        $app->get('/openapi.json', function (Request $request, Response $response) {
            $file = __DIR__ . '/../../openapi.json';
            $response->getBody()->write((string) file_get_contents($file));

            return $response->withHeader('Content-Type', 'application/json');
        });

        // Swagger UI — same experience as FastAPI /docs or Axum /swagger-ui
        $app->get('/docs', function (Request $request, Response $response) {
            $html = <<<'HTML'
<!DOCTYPE html>
<html>
<head>
    <title>Sobo Storeman — API Docs</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist/swagger-ui.css">
</head>
<body>
<div id="swagger-ui"></div>
<script src="https://unpkg.com/swagger-ui-dist/swagger-ui-bundle.js"></script>
<script>
    SwaggerUIBundle({
        url: '/openapi.json',
        dom_id: '#swagger-ui',
        presets: [SwaggerUIBundle.presets.apis, SwaggerUIBundle.SwaggerUIStandalonePreset],
        layout: 'BaseLayout',
        deepLinking: true,
    });
</script>
</body>
</html>
HTML;
            $response->getBody()->write($html);

            return $response->withHeader('Content-Type', 'text/html');
        });
    }
}
