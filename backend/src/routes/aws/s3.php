<?php
namespace App\Routes\Aws\S3;

use App\Helper\AwsHelper;
use App\Http\ResponseHelper;
use App\Settings\Settings;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/aws/s3/bucket/{bucket_name}', function (Request $request, Response $response, array $args) {
    $bucketName = $args['bucket_name'];
    $resItems   = [];
    $creds      = $request->getAttribute('aws_creds');
    $s3Client   = AwsHelper::getS3ClientWithDecodedTokenCredentials($creds);

    $contents = $s3Client->listObjectsV2([
        'Bucket' => $bucketName,
    ]);

    foreach ($contents['Contents'] as $item) {
        $resItems[] = $item;
    }

    $data = ['bucket_name' => $bucketName, 'items' => $resItems];

    return ResponseHelper::jsonResponse($response, $data, 200);
});

$app->get('/aws/s3/bucket-list', function (Request $request, Response $response) {
    $creds    = $request->getAttribute('aws_creds');
    $s3Client = AwsHelper::getS3ClientWithDecodedTokenCredentials($creds);

    $result  = $s3Client->listBuckets();
    $buckets = array_column($result['Buckets'], 'Name');
    $data    = ['buckets' => $buckets];

    return ResponseHelper::jsonResponse($response, $data, 200);
});

$app->post('/aws/login', function (Request $request, Response $response) {
    $body = $request->getParsedBody() ?? [];

    $accessKey    = trim($body['access_key_id'] ?? $body['access_key'] ?? '');
    $secretKey    = trim($body['secret_access_key'] ?? $body['secret_key'] ?? '');
    $sessionToken = trim($body['session_token'] ?? ''); // optional, used for temp credentials ONLY
    $region       = trim($body['region'] ?? Settings::DEFAULT_REGION);
    $expiresIn    = ! empty($body['expires']) ? strtotime($body['expires']) : null; // optional ISO or timestamp

    if (empty($accessKey) || empty($secretKey)) {
        return ResponseHelper::jsonResponse($response, [
            'error' => 'Missing accessKeyId and/or secretAccessKey',
        ], 400);
    }

    try {
        // basic pre-check to catch the obvious mistakes
        $testS3Client = AwsHelper::getS3ClientWithCredentials(
            $region,
            $accessKey,
            $secretKey,
            $sessionToken);

        // Lightweight test call (listBuckets is cheap and reveals if creds are basically valid).
        // if invalid/expired/malformed, exception will be thrown; if this passes, it means the credentials work,
        // and we can safely store them in SESSION.
        $testS3Client->listBuckets();

        $jwtToken = AwsHelper::storeAwsCredentialsAsToken(
            $region,
            $accessKey,
            $secretKey,
            $expiresIn,
            $sessionToken);

        return ResponseHelper::jsonResponse($response, [
            'status'    => 'authenticated',
            'type'      => $sessionToken ? 'temporary' : 'permanent',
            'expires'   => $expiresIn ? date(
                'c', $expiresIn) : 'never (permanent — rotate regularly!)',
            'region'    => $region,
            'jwt_token' => $jwtToken,
        ], 200);
    } catch (\Aws\Exception\AwsException $e) {
        $msg  = $e->getAwsErrorMessage() ?? $e->getMessage();
        $code = $e->getAwsErrorCode() ?? 'Unknown';

        $status = 401;
        if (stripos($msg, 'expired') !== false || stripos($code, 'ExpiredToken') !== false) {
            $msg    .= ' (token expired)';
            $status  = 419; // or 401
        } elseif (stripos($msg, 'invalid') !== false || stripos($code, 'InvalidClientTokenId') !== false) {
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
