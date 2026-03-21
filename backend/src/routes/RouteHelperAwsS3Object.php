<?php
namespace App\Routes;

use App\Helper\ArrayValidationHelper;
use App\Helper\AwsS3ObjectHelper;
use App\Http\ResponseHelper;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteGroup;

class RouteHelperAwsS3Object extends RouteHelperAwsS3
{
    public static function addRoutesTo(mixed $app)
    {

        $app->group('/object', function ($grAwsObj) {
            self::addGetObject($grAwsObj);
            // self::addRenameObject($grAwsObj);
            self::addCopyObject($grAwsObj);
            self::addPutObjectFilePath($grAwsObj);
            self::addPutObjectFileBodyJson($grAwsObj);
            self::addPutObjectFileBodyForm($grAwsObj);
            self::addDeleteObject($grAwsObj);
            self::addDeleteMultipleObjects($grAwsObj);
        });

    }

    #[OA\Get(
        path: '/aws/s3/object/get/{bucket_name}/{object_key}',
        summary: 'Get (download) an object from S3',
        operationId: 'getObject',
        tags: ['S3 Objects'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'bucket_name', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'my-bucket'),
            new OA\Parameter(name: 'object_key', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'folder/file.txt'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Object metadata and body',
                content: new OA\JsonContent(ref: '#/components/schemas/ObjectGetResult'),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/UnprocessableEntity'),
        ],
    )]
    private static function addGetObject(mixed $app)
    {
        $app->get('/get/{bucket_name}/{object_key}', function (Request $request, Response $response, array $args) {
            $s3Client  = self::getS3ClientFromRequest($request);
            $validator = ArrayValidationHelper::create();

            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", $args);
            $objectKey  = $validator->getStringValueByKeyOrThrow("object_key", $args);

            $result = AwsS3ObjectHelper::getObject($s3Client, $bucketName, $objectKey);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }


    #[OA\Post(
        path: '/aws/s3/object/copy',
        summary: 'Copy an object between buckets or within a bucket',
        operationId: 'copyObject',
        tags: ['S3 Objects'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ObjectCopyRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Object copied successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ObjectCopyResult'),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/UnprocessableEntity'),
        ],
    )]
    private static function addCopyObject(mixed $app)
    {
        $app->post('/copy', function (Request $request, Response $response, array $args) {
            $s3Client  = self::getS3ClientFromRequest($request);
            $validator = ArrayValidationHelper::create();

            $bucketNameSrc = $validator->getStringValueByKeyOrThrow("bucket_name_src", self::getRequestBody($request));
            $bucketNameDst = $validator->getStringValueByKeyOrThrow("bucket_name_dst", self::getRequestBody($request));
            $objectKeySrc  = $validator->getStringValueByKeyOrThrow("object_key_src", self::getRequestBody($request));
            $objectKeyDst  = $validator->getStringValueByKeyOrThrow("object_key_dst", self::getRequestBody($request));

            $result = AwsS3ObjectHelper::copyObject($s3Client, $bucketNameSrc, $objectKeySrc, $bucketNameDst, $objectKeyDst);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }

    #[OA\Post(
        path: '/aws/s3/object/put/file-path',
        summary: 'Upload an object to S3 from a server-side file path',
        description: 'Reads the file at the given server-side path and uploads it to the specified bucket/key.',
        operationId: 'putObjectFromFilePath',
        tags: ['S3 Objects'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ObjectPutFromFilePathRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Object uploaded successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ObjectPutResult'),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/UnprocessableEntity'),
        ],
    )]
    private static function addPutObjectFilePath(mixed $app)
    {
        $app->post('/put/file-path', function (Request $request, Response $response, array $args) {
            $s3Client  = self::getS3ClientFromRequest($request);
            $validator = ArrayValidationHelper::create();

            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", self::getRequestBody($request));
            $objectKey  = $validator->getStringValueByKeyOrThrow("object_key", self::getRequestBody($request));
            $filePath   = $validator->getStringValueByKeyOrThrow("file_path", self::getRequestBody($request));

            $result = AwsS3ObjectHelper::putObjectFromFilePath($s3Client, $bucketName, $objectKey, $filePath);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }

    #[OA\Post(
        path: '/aws/s3/object/put/file-body-json',
        summary: 'Upload an object to S3 from a base64-encoded body',
        description: 'Accepts a base64-encoded file body and uploads the decoded bytes to the specified bucket/key.',
        operationId: 'putObjectFromFileBodyJson',
        tags: ['S3 Objects'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ObjectPutFromFileBodyRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Object uploaded successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ObjectPutResult'),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/UnprocessableEntity'),
        ],
    )]
    private static function addPutObjectFileBodyJson(mixed $app)
    {
        $app->post('/put/file-body-json', function (Request $request, Response $response, array $args) {
            $s3Client  = self::getS3ClientFromRequest($request);
            $validator = ArrayValidationHelper::create();

            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", self::getRequestBody($request));
            $objectKey  = $validator->getStringValueByKeyOrThrow("object_key", self::getRequestBody($request));
            $fileBodyIn = $validator->getStringValueByKeyOrThrow("file_body", self::getRequestBody($request));

            $fileBody = base64_decode($fileBodyIn);

            $result = AwsS3ObjectHelper::putObjectFromFileBody($s3Client, $bucketName, $objectKey, $fileBody);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }

    #[OA\Post(
        path: '/aws/s3/object/put/file-body-form',
        summary: 'Upload an object to S3 from a FORM body',
        description: 'Accepts a form body and uploads the decoded bytes to the specified bucket/key.',
        operationId: 'putObjectFromFileBodyForm',
        tags: ['S3 Objects'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ObjectPutFromFileBodyRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Object uploaded successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/ObjectPutResult'),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/UnprocessableEntity'),
        ],
    )]
    private static function addPutObjectFileBodyForm(mixed $app)
    {
        $app->post('/put/file-body-form', function (Request $request, Response $response, array $args) {
            $s3Client  = self::getS3ClientFromRequest($request);
            $validator = ArrayValidationHelper::create();

            $body = $request->getParsedBody() ?? []; // works for multipart

            $bucketName = $body['bucket_name'] ?? null;
            $objectKey  = $body['object_key'] ?? null;

            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", $body);
            $objectKey  = $validator->getStringValueByKeyOrThrow("object_key", $body);

            $uploadedFiles = $request->getUploadedFiles();
            if (empty($uploadedFiles['file'])) {
                throw new \RuntimeException('No file uploaded under field "file"');
            }

            $uploadedFile = $uploadedFiles['file'];

            if ($uploadedFile->getError() !== UPLOAD_ERR_OK) {
                throw new \RuntimeException('Upload error: ' . $uploadedFile->getError());
            }

            $result = AwsS3ObjectHelper::putObjectFromFileBody($s3Client, $bucketName, $objectKey, $uploadedFile->getStream());

            return ResponseHelper::jsonResponse($response, $result, 200);
        });

    }

    #[OA\Delete(
        path: '/aws/s3/object/delete',
        summary: 'Delete a single object from S3',
        description: 'Deletes the object and returns the updated list of objects remaining in the bucket.',
        operationId: 'deleteObject',
        tags: ['S3 Objects'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ObjectDeleteRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Object deleted; returns updated bucket object listing',
                content: new OA\JsonContent(ref: '#/components/schemas/ListObjectsResult'),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/UnprocessableEntity'),
        ],
    )]
    private static function addDeleteObject(mixed $app)
    {
        $app->delete('/delete', function (Request $request, Response $response, array $args) {
            $s3Client  = self::getS3ClientFromRequest($request);
            $validator = ArrayValidationHelper::create();

            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", self::getRequestBody($request));
            $objectKey  = $validator->getStringValueByKeyOrThrow("object_key", self::getRequestBody($request));

            AwsS3ObjectHelper::deleteObject($s3Client, $bucketName, $objectKey);

            $result = AwsS3ObjectHelper::getObjectsForBucket($s3Client, $bucketName);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }

    #[OA\Delete(
        path: '/aws/s3/object/delete-multiple/{bucket_name}',
        summary: 'Delete multiple objects from a bucket in one call',
        description: 'Deletes all listed object keys and returns the updated bucket object listing.',
        operationId: 'deleteMultipleObjects',
        tags: ['S3 Objects'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'bucket_name', in: 'path', required: true, schema: new OA\Schema(type: 'string'), example: 'my-bucket'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ObjectsDeleteRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Objects deleted; returns updated bucket object listing',
                content: new OA\JsonContent(ref: '#/components/schemas/ListObjectsResult'),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/UnprocessableEntity'),
        ],
    )]
    private static function addDeleteMultipleObjects(mixed $app)
    {
        $app->delete('/delete-multiple/{bucket_name}', function (Request $request, Response $response, array $args) {
            $s3Client  = self::getS3ClientFromRequest($request);
            $validator = ArrayValidationHelper::create();

            $bucketName = $validator->getStringValueByKeyOrThrow(
                "bucket_name",
                $args);
            $objectKeys = $validator->getArrayByKeyOrThrow(
                "object_keys",
                self::getRequestBody($request));

            AwsS3ObjectHelper::deleteMultipleObjects($s3Client, $bucketName, $objectKeys);

            $result = AwsS3ObjectHelper::getObjectsForBucket($s3Client, $bucketName);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }
}
