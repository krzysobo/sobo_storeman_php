<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Http\ResponseHelper;
use App\Middleware\ErrorMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
ErrorMiddleware::addErrorMiddleware($app);

$app->get('/', function (Request $request, Response $response, array $args) {
    $data = ['app' => 'Sobo Storeman', 'version' => "1.0"];

    return ResponseHelper::jsonResponse($response, $data, 200);
});

// load sub-routes
require_once __DIR__ . '/../src/routes/aws/s3.php';

$app->run();
