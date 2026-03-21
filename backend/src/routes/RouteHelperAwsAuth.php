<?php
namespace App\Routes;

use App\Dto\AwsCredentials;
use App\Helper\AwsClientHelper;
use App\Helper\AwsCredentialsHelper;
use App\Http\ResponseHelper;
use App\Settings\Settings;
use Aws\Exception\AwsException;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteGroup;

class RouteHelperAwsAuth implements RouteHelper
{
    #[OA\Post(
        path: '/aws/login',
        summary: 'Authenticate with AWS credentials and receive a JWT token',
        description: 'Validates the supplied AWS credentials by performing a lightweight ListBuckets call. On success, returns a JWT token that encodes the credentials (encrypted). This token must be passed as a Bearer token in the Authorization header for all /aws/s3/* routes.',
        operationId: 'awsLogin',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/AwsLoginRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Authentication successful',
                content: new OA\JsonContent(ref: '#/components/schemas/AwsLoginResponse'),
            ),
            new OA\Response(
                response: 400,
                description: 'Missing access_key_id or secret_access_key',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid AWS credentials',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
            new OA\Response(
                response: 419,
                description: 'AWS credentials or STS token have expired',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
            new OA\Response(
                response: 500,
                description: 'Unexpected server error',
                content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse'),
            ),
        ],
    )]
    public static function addRoutesTo(mixed $app)
    {
        $app->post('/login', function (Request $request, Response $response) {
            $body = $request->getParsedBody() ?? [];

            $accessKey    = trim($body['access_key_id'] ?? $body['access_key'] ?? '');
            $secretKey    = trim($body['secret_access_key'] ?? $body['secret_key'] ?? '');
            $sessionToken = trim($body['session_token'] ?? ''); // optional, used for temp credentials ONLY
            $region       = trim($body['region'] ?? Settings::DEFAULT_REGION);
            $expiresIn    = ! empty($body['expires']) ? strtotime($body['expires']) : null; // optional ISO or timestamp
            $expiresInStr    = ! empty($body['expires']) ? $body['expires'] : null; // optional ISO or timestamp

            if (empty($accessKey) || empty($secretKey)) {
                return ResponseHelper::jsonResponse($response, [
                    'error' => 'Missing access_key_id (alt. access_key) and/or secret_access_key (alt. secret_key)',
                ], 400);
            }

            try {
                // basic pre-check to catch the obvious mistakes
                $testS3Client = AwsClientHelper::getS3ClientWithAwsCredentialsList(
                    $region,
                    $accessKey,
                    $secretKey,
                    $sessionToken
                );

                // Lightweight test call (listBuckets is cheap and reveals if creds are basically valid).
                // if invalid/expired/malformed, exception will be thrown; if this passes, it means the credentials work,
                // and we can safely store them in SESSION.
                $testS3Client->listBuckets();
                $expiresInDtIm = ($expiresInStr !== null)? new \DateTimeImmutable($expiresInStr): null;
                $creds = AwsCredentials::fromArgsList(
                    $accessKey,
                    $secretKey,
                    $sessionToken,
                    $region,
                    $expiresInDtIm,
                    new \DateTimeImmutable()
                );
                $jwtToken = AwsCredentialsHelper::storeAwsCredentialsAsToken($creds);

                return ResponseHelper::jsonResponse($response, [
                    'status'    => 'authenticated',
                    'type'      => $sessionToken ? 'temporary' : 'permanent',
                    'expires'   => $expiresIn ? date(
                        'c',
                        $expiresIn
                    ) : 'never (permanent — rotate regularly!)',
                    'region'    => $region,
                    'jwt_token' => $jwtToken,
                ], 200);
            } catch (AwsException $e) {
                $msg  = $e->getAwsErrorMessage() ?? $e->getMessage();
                $code = $e->getAwsErrorCode() ?? 'Unknown';

                $status = 401;
                if (false !== stripos($msg, 'expired') || false !== stripos($code, 'ExpiredToken')) {
                    $msg    .= ' (token expired)';
                    $status  = 419; // or 401
                } elseif (false !== stripos($msg, 'invalid') || false !== stripos($code, 'InvalidClientTokenId')) {
                    $msg .= ' (invalid credentials)';
                }

                return ResponseHelper::jsonResponse($response, [
                    'error'   => 'AWS authentication failed',
                    'message' => $msg,
                    'code'    => $code,
                ], $status);
            } catch (\Exception $e) {
                return ResponseHelper::jsonResponse($response, [
                    'error'   => 'Unexpected error',
                    'message' => $e->getMessage(),
                ], 500);
            }
        });

    }
}
