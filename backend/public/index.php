<?php

require_once __DIR__.'/../vendor/autoload.php';

use App\Dto\AwsCredentials;
use App\Http\ResponseHelper;
use App\Middleware\AwsAuthMiddleware;
use App\Middleware\ErrorMiddleware;
use App\Settings\Settings;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
ErrorMiddleware::addErrorMiddleware($app);
$app->add(new AwsAuthMiddleware());
$app->get('/', function (Request $request, Response $response, array $args) {
    $data = ['app' => 'Sobo Storeman', 'version' => Settings::getAppVersion()];

    return ResponseHelper::jsonResponse($response, $data, 200);
});

$app->get('/play', function (Request $request, Response $response, array $args) {
    $awsc = new AwsCredentials('key', 'secret', 'token', expires: new DateTimeImmutable());
    $awsc = $awsc->cloneWithLoginAt(new DateTimeImmutable('now'));

    $data = [
        'app' => 'Sobo Storeman',
        'version' => Settings::getAppVersion(),
        'is_playground' => true,
        'php_version_id' => PHP_VERSION_ID,
        'phpversion()' => phpversion(),
        'playground' => [
            'aws_creds' => $awsc->toArrayWithLogin(),
        ],
    ];

    return ResponseHelper::jsonResponse($response, $data, 200);
});

// load sub-routes
require_once __DIR__.'/../src/routes/aws/s3.php';

$app->run();
