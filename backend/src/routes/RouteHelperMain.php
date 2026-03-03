<?php
namespace App\Routes;

use App\Dto\AwsCredentials;
use App\Http\ResponseHelper;
use App\Settings\Settings;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

class RouteHelperMain implements RouteHelper
{

    public static function addRoutesToApp(App $app)
    {
        $app->get('/', function (Request $request, Response $response, array $args) {
            $data = ['app' => 'Sobo Storeman', 'version' => Settings::getAppVersion()];

            return ResponseHelper::jsonResponse($response, $data, 200);
        });

        $app->get('/play', function (Request $request, Response $response, array $args) {
            $awsc = new AwsCredentials('key', 'secret', 'token', expires: new \DateTimeImmutable());
            $awsc = $awsc->cloneWithLoginAt(new \DateTimeImmutable('now'));

            $data = [
                'app'            => 'Sobo Storeman',
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
