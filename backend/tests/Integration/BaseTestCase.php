<?php

namespace Tests\Integration;

use DI\ContainerBuilder;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Factory\AppFactory;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    protected App $app;

    protected function setUp(): void
    {
        parent::setUp();

        // Reuse your real container setup if you have one (PHP-DI recommended for Slim 4)
        $containerBuilder = new ContainerBuilder();
        // Add your real definitions here (e.g. from config/container.php)
        // $containerBuilder->addDefinitions(require __DIR__ . '/../../config/container.php');

        $container = $containerBuilder->build();
        AppFactory::setContainer($container);

        $this->app = AppFactory::create();

        // Add your real middlewares (same order as index.php)
        $this->app->addRoutingMiddleware();
        $this->app->addErrorMiddleware(true, true, true); // test mode

        // Add your custom middleware (AwsAuthMiddleware)
        $this->app->add(\App\Middleware\AwsAuthMiddleware::class); // assuming DI resolves it

        // Load your real routes (same as index.php)
        (require __DIR__ . '/../../routes/RouteHelperAwsAuth.php')($this->app);     // main routes
        (require __DIR__ . '/../../routes/RouteHelperAwsS3.php')($this->app);     // AWS routes

        // If you have more bootstrap (e.g. env loading), do it here
    }

    /**
     * Simulate a request and get response
     */
    protected function runRequest(
        string $method,
        string $uri,
        array $headers = [],
        array $body = []
    ): ResponseInterface {
        $psr17Factory = new Psr17Factory();

        $request = $psr17Factory->createServerRequest($method, $uri)
            ->withHeader('Content-Type', 'application/json');

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if (!empty($body)) {
            $stream = $psr17Factory->createStream(json_encode($body));
            $request = $request->withBody($stream);
        }

        return $this->app->handle($request);
    }
}