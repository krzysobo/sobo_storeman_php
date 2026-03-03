<?php
namespace App\Helper;

use App\Dto\AwsCredentials;
use App\Http\ResponseHelper;
use App\Middleware\AwsAuthMiddleware;
use App\Middleware\ErrorMiddleware;
use App\Settings\Settings;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Factory\AppFactory;

class AppFactoryHelper
{

    public static function create(): ?App
    {
        $app = AppFactory::create();
        $app->addBodyParsingMiddleware();
        $app->addRoutingMiddleware();
        ErrorMiddleware::addErrorMiddleware($app);
        $app->add(new AwsAuthMiddleware());

        return $app;
    }
}
