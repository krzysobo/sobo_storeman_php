<?php
namespace App\Middleware;

use App\Helper\AwsHelper;
use App\Http\ResponseHelper;
use App\Settings\Settings;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;
use Slim\Psr7\Response as SlimResponse;

class AwsAuthMiddleware
{
    /**
     * Summary of __construct
     */
    public function __construct()
    {}

    /**
     * Summary of __invoke
     * @param Request $request
     * @param Handler $handler
     * @return ResponseInterface
     */
    public function __invoke(Request $request, Handler $handler): ResponseInterface
    {
        $response = new SlimResponse();
        $path     = $request->getUri()->getPath();

        // List of paths that bypass auth
        $publicPaths = Settings::getPublicPaths();

        // skip the rest of Auth checking for public paths - token is unnecessary there
        if (\in_array($path, $publicPaths, true) || str_starts_with($path, '/public/')) {
            return $handler->handle($request);
        }

        $auth = $request->getHeaderLine('Authorization');
        if (! preg_match('/Bearer\s(\S+)/', $auth, $matches)) {
            return ResponseHelper::jsonResponse($response, [
                'error' => 'Missing or invalid token',
            ], 401);
        }

        $token    = $matches[1];
        $awsCreds = AwsHelper::getAwsCredentialsFromToken($token);

        if (! $awsCreds) {
            // invalid / expired / tampered
            return ResponseHelper::jsonResponse($response, [
                'error' => 'Invalid or expired token',
            ], 401);
        }

        // Attaching the credentials to request attributes, so routes can use them.
        $request = $request->withAttribute('aws_creds', $awsCreds);

        return $handler->handle($request);
    }
}
