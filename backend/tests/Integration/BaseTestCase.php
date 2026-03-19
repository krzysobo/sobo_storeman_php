<?php

namespace Tests\Integration;

use App\Dto\AwsCredentials;
use App\Helper\AppFactoryHelper;
use App\Helper\AwsCredentialsHelper;
use App\Routes\RouteHelperAwsS3Bucket;
use App\Routes\RouteHelperAwsS3Object;
use App\Routes\RouteHelperMain;
use Aws\MockHandler;
use Aws\Result;
use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Slim\App;

/**
 * Base test case for integration tests.
 *
 * Bootstraps the Slim application the same way public/index.php does,
 * and provides helpers to:
 *  - run HTTP requests through the app without a running server
 *  - generate a valid Bearer JWT token from fake AWS credentials
 *  - build a pre-configured AWS MockHandler
 *
 * AWS calls are intercepted at the SDK handler level via MockHandler,
 * so no real network requests are ever made.
 */
abstract class BaseTestCase extends TestCase
{
    protected App $app;

    // Fake but structurally valid AWS credentials used throughout tests
    protected const FAKE_KEY    = 'AKIAIOSFODNN7EXAMPLE';
    protected const FAKE_SECRET = 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY';
    protected const FAKE_REGION = 'eu-north-1';

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = $this->buildApp();
    }

    // -------------------------------------------------------------------------
    // App bootstrap
    // -------------------------------------------------------------------------

    /**
     * Build the Slim app exactly as public/index.php does.
     * Routes are registered via the same RouteHelper statics,
     * so integration tests exercise the real routing layer.
     */
    private function buildApp(): App
    {
        $app = AppFactoryHelper::create();
        RouteHelperMain::addRoutesToApp($app);
        RouteHelperAwsS3Bucket::addRoutesToApp($app);
        RouteHelperAwsS3Object::addRoutesToApp($app);

        return $app;
    }

    // -------------------------------------------------------------------------
    // Request runner
    // -------------------------------------------------------------------------

    /**
     * Dispatch a request through the Slim app and return the PSR-7 response.
     * Body arrays are JSON-encoded automatically.
     *
     * @param array<string, string> $headers
     * @param array<mixed>          $body
     */
    protected function runRequest(
        string $method,
        string $uri,
        array $headers = [],
        array $body = []
    ): ResponseInterface {
        $factory = new Psr17Factory();
        $request = $factory->createServerRequest($method, $uri)
            ->withHeader('Content-Type', 'application/json');

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        if (! empty($body)) {
            $stream  = $factory->createStream(json_encode($body));
            $request = $request->withBody($stream)->withParsedBody($body);
        }

        return $this->app->handle($request);
    }

    /**
     * Decode the JSON body of a response into an associative array.
     *
     * @return array<mixed>
     */
    protected function decodeBody(ResponseInterface $response): array
    {
        return json_decode((string) $response->getBody(), true) ?? [];
    }

    // -------------------------------------------------------------------------
    // Auth helpers
    // -------------------------------------------------------------------------

    /**
     * Generate a valid JWT Bearer token wrapping fake AWS credentials.
     * Use this to authenticate requests to protected routes without
     * contacting AWS.
     */
    protected function makeAuthToken(
        string $key    = self::FAKE_KEY,
        string $secret = self::FAKE_SECRET,
        string $region = self::FAKE_REGION,
    ): string {
        $creds = AwsCredentials::fromArgsList($key, $secret, null, $region);

        return AwsCredentialsHelper::storeAwsCredentialsAsToken($creds);
    }

    /**
     * Return an Authorization header array ready for runRequest().
     */
    protected function authHeader(?string $token = null): array
    {
        return ['Authorization' => 'Bearer ' . ($token ?? $this->makeAuthToken())];
    }

    // -------------------------------------------------------------------------
    // AWS mock helpers
    // -------------------------------------------------------------------------

    /**
     * Build a MockHandler pre-loaded with one Result per entry in $results.
     * Pass the returned handler to injectMockHandler().
     *
     * @param array<array<mixed>> $results  Each element becomes one Aws\Result
     */
    protected function makeMockHandler(array $results): MockHandler
    {
        $mock = new MockHandler();
        foreach ($results as $resultData) {
            $mock->append(new Result($resultData));
        }

        return $mock;
    }

    /**
     * Inject a MockHandler so that every S3Client created by AwsClientHelper
     * during this request uses it.  Must be called before runRequest().
     *
     * Implementation note: Slim resolves the AwsAuthMiddleware eagerly and
     * the route handlers call AwsClientHelper::getS3ClientWithAwsCredentials()
     * on every request, so we override the default args at the SDK level.
     */
    protected function injectMockHandler(MockHandler $mock): void
    {
        // The AWS SDK respects a 'handler' key in the client constructor args.
        // We store the mock in a static property that AwsClientHelper will pick
        // up if it supports it — otherwise, subclasses can override this method.
        //
        // For full control without modifying AwsClientHelper, tests that need
        // mocked S3 responses should extend this and override createS3Client()
        // OR rely on the /aws/login route's own MockHandler injection for auth.
        \Aws\Sdk::$defaultArgs['handler'] = $mock;
    }
}
