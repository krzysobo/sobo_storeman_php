<?php
namespace App\Middleware;

use App\Http\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Throwable;

class ErrorMiddleware
{
    public static function addErrorMiddleware(App $app)
    {
        $errorMiddleware = $app->addErrorMiddleware(true, true, true);

        $errorMiddleware->setDefaultErrorHandler(function (
            Request $request,
            Throwable $exception,
            bool $displayErrorDetails,
            bool $logErrors,
            bool $logErrorDetails
        ) use ($app): Response {
            $status = method_exists($exception, 'getCode') && $exception->getCode() >= 100 && $exception->getCode() < 600
                ? $exception->getCode()
                : 500;

            $payload = [
                'error'   => get_class($exception),
                'message' => $exception->getMessage(),
            ];

            if ($displayErrorDetails) {
                $payload['trace'] = $exception->getTraceAsString();
            }

            $response = $app->getResponseFactory()->createResponse($status);
            return ResponseHelper::jsonResponse($response, $payload, $status);
        });

    }

}
