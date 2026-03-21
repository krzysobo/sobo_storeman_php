<?php
namespace App\Routes;

use App\Dto\AwsCredentials;
use App\Http\ResponseHelper;
use App\Settings\Settings;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteGroup;

class RouteHelperMain implements RouteHelper
{

    #[OA\Get(
        path: '/',
        summary: 'Health check / app info',
        operationId: 'getRoot',
        tags: ['General'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'App name and version',
                content: new OA\JsonContent(ref: '#/components/schemas/AppInfo'),
            ),
        ],
    )]
    #[OA\Get(
        path: '/play',
        summary: 'Playground endpoint — returns extended debug/test data',
        operationId: 'getPlay',
        tags: ['General'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Playground data including PHP version and a sample AwsCredentials object',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'app',            type: 'string'),
                        new OA\Property(property: 'version',        type: 'string'),
                        new OA\Property(property: 'is_playground',  type: 'boolean'),
                        new OA\Property(property: 'php_version_id', type: 'integer'),
                        new OA\Property(property: 'phpversion()',   type: 'string'),
                        new OA\Property(property: 'playground',     type: 'object'),
                    ],
                    type: 'object',
                ),
            ),
        ],
    )]
    public static function addRoutesTo(mixed $app)
    {
        $app->get('/', function (Request $request, Response $response, array $args) {
            $data = ['app' => 'Sobo Storeman', 'version' => Settings::getAppVersion()];

            return ResponseHelper::jsonResponse($response, $data, 200);
        });

        $app->get('/play', function (Request $request, Response $response, array $args) {
            $awsc = new AwsCredentials('key', 'secret', 'token', expires: new \DateTimeImmutable());
            $awsc = $awsc->cloneWithLoginAt(new \DateTimeImmutable('now'));

            $data = [
                'app'            => Settings::getAppName(),
                'version'        => Settings::getAppVersion(),
                'is_playground'  => true,
                'php_version_id' => PHP_VERSION_ID,
                'phpversion()'   => phpversion(),
                'playground'     => [
                    'aws_creds' => $awsc->toArrayWithLogin(),
                ],
            ];

            return ResponseHelper::jsonResponse($response, $data, 200);
        });
    }
}
