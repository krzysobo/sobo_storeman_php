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
    private static function addGetObject(App $app)
    {
        $app->get('/aws/s3/object/get/{bucket_name}/{object_key}', function (Request $request, Response $response, array $args) {
            $s3Client  = self::getS3ClientFromRequest($request);
            $validator = ArrayValidationHelper::create();

            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", $this->getRequestBody($request));
            $objectKey  = $validator->getStringValueByKeyOrThrow("object_key", $this->getRequestBody($request));

            $result = AwsS3ObjectHelper::getObject($s3Client, $bucketName, $objectKey);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }

    private static function addCopyObject(App $app)
    {
        $app->post('/aws/s3/object/copy', function (Request $request, Response $response, array $args) {
            $s3Client  = self::getS3ClientFromRequest($request);
            $validator = ArrayValidationHelper::create();

            $bucketNameSrc = $validator->getStringValueByKeyOrThrow("bucket_name_src", $this->getRequestBody($request));
            $bucketNameDst = $validator->getStringValueByKeyOrThrow("bucket_name_dst", $this->getRequestBody($request));
            $objectKeySrc  = $validator->getStringValueByKeyOrThrow("object_key_src", $this->getRequestBody($request));
            $objectKeyDst  = $validator->getStringValueByKeyOrThrow("object_key_dst", $this->getRequestBody($request));

            $result = AwsS3ObjectHelper::copyObject($s3Client, $bucketNameSrc, $objectKeySrc, $bucketNameDst, $objectKeyDst);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }

    private static function addPutObjectFilePath(App $app)
    {
        $app->post('/aws/s3/object/put/file-path', function (Request $request, Response $response, array $args) {
            $s3Client  = self::getS3ClientFromRequest($request);
            $validator = ArrayValidationHelper::create();

            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", $this->getRequestBody($request));
            $objectKey  = $validator->getStringValueByKeyOrThrow("object_key", $this->getRequestBody($request));
            $filePath   = $validator->getStringValueByKeyOrThrow("file_path", $this->getRequestBody($request));

            $result = AwsS3ObjectHelper::putObjectFromFilePath($s3Client, $bucketName, $objectKey, $filePath);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }

    private static function addPutObjectFileBody(App $app)
    {
        $app->post('/aws/s3/object/put/file-body', function (Request $request, Response $response, array $args) {
            $s3Client  = self::getS3ClientFromRequest($request);
            $validator = ArrayValidationHelper::create();

            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", $this->getRequestBody($request));
            $objectKey  = $validator->getStringValueByKeyOrThrow("object_key", $this->getRequestBody($request));
            $fileBodyIn = $validator->getStringValueByKeyOrThrow("file_body", $this->getRequestBody($request));

            $fileBody = base64_decode($fileBodyIn);

            $result = AwsS3ObjectHelper::putObjectFromFileBody($s3Client, $bucketName, $objectKey, $fileBody);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }

    private static function addDeleteObject(App $app)
    {
        $app->delete('/aws/s3/object/delete/{bucket_name}/{object_key}', function (Request $request, Response $response, array $args) {
            $s3Client  = self::getS3ClientFromRequest($request);
            $validator = ArrayValidationHelper::create();

            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", $this->getRequestBody($request));
            $objectKey  = $validator->getStringValueByKeyOrThrow("object_key", $this->getRequestBody($request));

            AwsS3ObjectHelper::deleteObject($s3Client, $bucketName, $objectKey);

            $result = AwsS3ObjectHelper::getObjectsForBucket($s3Client, $bucketName);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }

    private static function addDeleteMultipleObjects(App $app)
    {
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

    public static function addRoutesToApp(App $app)
    {
        self::addGetObject($app);
        self::addCopyObject($app);
        self::addPutObjectFilePath($app);
        self::addPutObjectFileBody($app);
        self::addDeleteObject($app);
        self::addDeleteMultipleObjects($app);
    }
}
