<?php
namespace App\Http;

use Psr\Http\Message\ResponseInterface as Response;

class ResponseHelper
{
    public static function jsonResponse(Response $response, mixed $data, int $status = 200): Response
    {
        $payload = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

        $response->getBody()->write($payload);

        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
