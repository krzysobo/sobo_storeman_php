<?php
namespace App\Routes;

use App\Helper\ArrayValidationHelper;
use App\Helper\AwsClientHelper;
use App\Helper\AwsS3BucketHelper;
use App\Http\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

class RouteHelperAwsS3Bucket extends RouteHelperAwsS3
{

    public static function addRoutesToApp(App $app)
    {
        $app->get('/aws/s3/bucket/get/{bucket_name}', function (Request $request, Response $response, array $args) {
            $s3Client   = self::getS3ClientFromRequest($request);
            $validator  = ArrayValidationHelper::create();
            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", $this->getRequestBody($request));
            $result     = AwsS3BucketHelper::getObjectsForBucket($s3Client, $bucketName);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });

        $app->get('/aws/s3/bucket/list', function (Request $request, Response $response) {
            $s3Client = self::getS3ClientFromRequest($request);
            $result   = AwsS3BucketHelper::getBucketsList($s3Client);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });

        $app->post('/aws/s3/bucket/create', function (Request $request, Response $response) {
            $s3Client = self::getS3ClientFromRequest($request);

            $validator  = ArrayValidationHelper::create();
            $body       = $request->getParsedBody() ?? [];
            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", $body);
            $result     = AwsS3BucketHelper::createBucket($s3Client, $bucketName, null);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });

        $app->delete('/aws/s3/bucket/delete', function (Request $request, Response $response) {
            $validator = ArrayValidationHelper::create();

            $body     = $request->getParsedBody() ?? [];
            $creds    = $request->getAttribute('aws_creds'); // Already AwsCredentials object
            $s3Client = AwsClientHelper::getS3ClientWithAwsCredentials($creds);
            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", $body);
            $result     = AwsS3BucketHelper::createBucket($s3Client, $bucketName, null);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });

    }
}