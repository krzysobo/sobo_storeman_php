<?php
namespace App\Routes;

use App\Helper\ArrayValidationHelper;
use App\Helper\AwsS3ObjectHelper;
use App\Http\ResponseHelper;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

class RouteHelperAwsS3Object extends RouteHelperAwsS3
{
    public static function addRoutesToApp(App $app)
    {
        $app->get('/aws/s3/object/get/{bucket_name}/{object_key}', function (Request $request, Response $response, array $args) {
            $s3Client  = self::getS3ClientFromRequest($request);
            $validator = ArrayValidationHelper::create();
            $resItems  = [];

            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", $this->getRequestBody($request));
            $objectKey  = $validator->getStringValueByKeyOrThrow("object_key", $this->getRequestBody($request));

            $file = $s3Client->getObject([
                'Bucket' => $bucketName,
                'Key'    => $objectKey,
            ]);

            $body = $file->getBody();

            foreach ($contents['Contents'] as $item) {
                $resItems[] = $item;
            }

            $data = ['bucket_name' => $bucketName, 'items' => $resItems];

            return ResponseHelper::jsonResponse($response, $data, 200);
        });

        $app->delete('/aws/s3/object/delete/{bucket_name}/{object_key}', function (Request $request, Response $response, array $args) {
            $s3Client  = self::getS3ClientFromRequest($request);
            $validator = ArrayValidationHelper::create();

            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", $this->getRequestBody($request));
            $objectKey  = $validator->getStringValueByKeyOrThrow("object_key", $this->getRequestBody($request));

            AwsS3ObjectHelper::deleteObject($s3Client, $bucketName, $objectKey);

            $result = AwsS3ObjectHelper::getObjectsForBucket($s3Client, $bucketName);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });

        $app->delete('/aws/s3/object/delete-multiple/{bucket_name}', function (Request $request, Response $response, array $args) {
            $s3Client  = self::getS3ClientFromRequest($request);
            $validator = ArrayValidationHelper::create();

            $bucketName = $validator->getStringValueByKeyOrThrow(
                "bucket_name",
                $this->getRequestBody($request));
            $objectKeys = $validator->getArrayByKeyOrThrow(
                "object_keys",
                $this->getRequestBody($request));

            AwsS3ObjectHelper::deleteMultipleObjects($s3Client, $bucketName, $objectKeys);

            $result = AwsS3ObjectHelper::getObjectsForBucket($s3Client, $bucketName);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }
}
