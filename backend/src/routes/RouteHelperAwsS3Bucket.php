<?php
namespace App\Routes;

use App\Helper\ArrayValidationHelper;
use App\Helper\AwsClientHelper;
use App\Helper\AwsS3BucketHelper;
use App\Helper\AwsS3ObjectHelper;
use App\Http\ResponseHelper;
use App\Middleware\AwsAuthMiddleware;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteGroup;

class RouteHelperAwsS3Bucket extends RouteHelperAwsS3
{
    public static function addRoutesTo(mixed $app)
    {
        $app->group('/bucket', function ($grAwsBck) use ($app) {
            self::addGetBucketObjects($grAwsBck);
            self::addBucketList($grAwsBck);
            self::addBucketCreate($grAwsBck);
            self::addBucketDelete($grAwsBck);
        });

    }

    #[OA\Get(
        path: '/aws/s3/bucket/get/{bucket_name}',
        summary: 'List objects inside a bucket',
        operationId: 'getBucketObjects',
        tags: ['S3 Buckets'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'bucket_name',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
                example: 'my-bucket',
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Objects in the bucket',
                content: new OA\JsonContent(ref: '#/components/schemas/ListObjectsResult'),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/UnprocessableEntity'),
        ],
    )]
    private static function addGetBucketObjects(mixed $app)
    {
        $app->get('/get/{bucket_name}', function (Request $request, Response $response, array $args) {
            $s3Client   = self::getS3ClientFromRequest($request);
            $validator  = ArrayValidationHelper::create();
            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", $args);
            $result     = AwsS3ObjectHelper::getObjectsForBucket($s3Client, $bucketName);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }

    #[OA\Get(
        path: '/aws/s3/bucket/list',
        summary: 'List all S3 buckets',
        operationId: 'listBuckets',
        tags: ['S3 Buckets'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'List of buckets owned by the authenticated AWS account',
                content: new OA\JsonContent(ref: '#/components/schemas/BucketListResult'),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
        ],
    )]
    private static function addBucketList(mixed $app)
    {
        $app->get('/list', function (Request $request, Response $response) {
            $s3Client = self::getS3ClientFromRequest($request);
            $result   = AwsS3BucketHelper::getBucketsList($s3Client);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }

    #[OA\Post(
        path: '/aws/s3/bucket/create',
        summary: 'Create a new S3 bucket',
        operationId: 'createBucket',
        tags: ['S3 Buckets'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/BucketNameRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Bucket created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/BucketCreateResult'),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/UnprocessableEntity'),
        ],
    )]
    private static function addBucketCreate(mixed $app)
    {
        $app->post('/create', function (Request $request, Response $response) {
            $s3Client = self::getS3ClientFromRequest($request);

            $validator  = ArrayValidationHelper::create();
            $body       = self::getRequestBody($request);
            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", $body);
            $result     = AwsS3BucketHelper::createBucket($s3Client, $bucketName, null);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }

    #[OA\Delete(
        path: '/aws/s3/bucket/delete',
        summary: 'Delete an S3 bucket',
        description: 'Deletes the named S3 bucket. The bucket must be empty before it can be deleted.',
        operationId: 'deleteBucket',
        tags: ['S3 Buckets'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/BucketNameRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Bucket deleted successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/BucketCreateResult'),
            ),
            new OA\Response(response: 401, ref: '#/components/responses/Unauthorized'),
            new OA\Response(response: 422, ref: '#/components/responses/UnprocessableEntity'),
        ],
    )]
    private static function addBucketDelete(mixed $app)
    {
        $app->delete('/delete', function (Request $request, Response $response) {
            $validator = ArrayValidationHelper::create();

            $body       = self::getRequestBody($request);
            $creds      = $request->getAttribute('aws_creds'); // Already AwsCredentials object
            $s3Client   = AwsClientHelper::getS3ClientWithAwsCredentials($creds);
            $bucketName = $validator->getStringValueByKeyOrThrow("bucket_name", $body);
            $result     = AwsS3BucketHelper::deleteBucket($s3Client, $bucketName);

            return ResponseHelper::jsonResponse($response, $result, 200);
        });
    }

}
