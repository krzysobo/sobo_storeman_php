<?php
namespace App;

use OpenApi\Attributes as OA;

// ── Global OpenAPI metadata ───────────────────────────────────────────────────

#[OA\Info(
    title: 'Sobo Storeman',
    version: '0.0.4',
    description: 'AWS S3 storage manager API. All /aws/s3/* routes require a Bearer JWT token obtained from POST /aws/login.',
)]
#[OA\Server(url: 'http://localhost:8080', description: 'Local development server')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    bearerFormat: 'JWT',
    description: 'JWT token obtained from POST /aws/login. Contains encrypted AWS credentials.',
)]
class OpenApiDefinitions
{
    // Shared reusable responses (appear in components/responses)

    #[OA\Response(
        response: 'Unauthorized',
        description: 'Missing or invalid JWT token',
        content: new OA\JsonContent(
            ref: '#/components/schemas/ErrorResponse',
            example: ['error' => 'Missing or invalid token'],
        ),
    )]
    private function unauthorizedResponse(): void
    {}

    #[OA\Response(
        response: 'UnprocessableEntity',
        description: 'Missing or invalid request body field',
        content: new OA\JsonContent(
            ref: '#/components/schemas/ErrorResponse',
            example: ['error' => "key bucket_name doesn't exist"],
        ),
    )]
    private function unprocessableEntityResponse(): void
    {}
}

// ── Schemas ───────────────────────────────────────────────────────────────────

#[OA\Schema(
    schema: 'AppInfo',
    properties: [
        new OA\Property(property: 'app', type: 'string', example: 'Sobo Storeman'),
        new OA\Property(property: 'version', type: 'string', example: '0.0.4'),
    ],
)]
abstract class AppInfoSchema
{}

#[OA\Schema(
    schema: 'ErrorResponse',
    required: ['error'],
    properties: [
        new OA\Property(property: 'error', type: 'string'),
        new OA\Property(property: 'message', type: 'string'),
        new OA\Property(property: 'code', type: 'string'),
    ],
)]
abstract class ErrorResponseSchema
{}

#[OA\Schema(
    schema: 'AwsLoginRequest',
    required: ['access_key_id', 'secret_access_key'],
    properties: [
        new OA\Property(
            property: 'access_key_id',
            type: 'string',
            description: 'AWS Access Key ID (also accepted as access_key)',
            example: 'AKIAIOSFODNN7EXAMPLE',
        ),
        new OA\Property(
            property: 'secret_access_key',
            type: 'string',
            description: 'AWS Secret Access Key (also accepted as secret_key)',
            example: 'wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY',
        ),
        new OA\Property(
            property: 'session_token',
            type: 'string',
            description: 'Optional STS session token — only for temporary credentials',
            example: 'AQoXnyc4lcK4w',
        ),
        new OA\Property(
            property: 'region',
            type: 'string',
            description: 'AWS region (defaults to eu-north-1)',
            example: 'eu-north-1',
        ),
        new OA\Property(
            property: 'expires',
            type: 'string',
            description: 'Optional credential expiry — ISO 8601 date/time or Unix timestamp',
            example: '2026-12-31T23:59:59Z',
        ),
    ],
)]
abstract class AwsLoginRequestSchema
{}

#[OA\Schema(
    schema: 'AwsLoginResponse',
    properties: [
        new OA\Property(property: 'status', type: 'string', example: 'authenticated'),
        new OA\Property(property: 'type', type: 'string', enum: ['permanent', 'temporary'], example: 'permanent'),
        new OA\Property(property: 'expires', type: 'string', example: 'never (permanent — rotate regularly!)'),
        new OA\Property(property: 'region', type: 'string', example: 'eu-north-1'),
        new OA\Property(
            property: 'jwt_token',
            type: 'string',
            description: 'Bearer token to use in Authorization header for all protected routes',
        ),
    ],
)]
abstract class AwsLoginResponseSchema
{}

#[OA\Schema(
    schema: 'BucketNameRequest',
    required: ['bucket_name'],
    properties: [
        new OA\Property(property: 'bucket_name', type: 'string', example: 'my-bucket'),
    ],
)]
abstract class BucketNameRequestSchema
{}

#[OA\Schema(
    schema: 'BucketListBucket',
    properties: [
        new OA\Property(property: 'BucketArn', type: 'string', example: 'arn:aws:s3:::my-bucket'),
        new OA\Property(property: 'BucketRegion', type: 'string', example: 'eu-north-1'),
        new OA\Property(property: 'CreationDate', type: 'string', format: 'date-time'),
        new OA\Property(property: 'Name', type: 'string', example: 'my-bucket'),
    ],
)]
abstract class BucketListBucketSchema
{}

#[OA\Schema(
    schema: 'BucketListResult',
    properties: [
        new OA\Property(
            property: 'Buckets',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/BucketListBucket'),
        ),
        new OA\Property(property: 'ContinuationToken', type: 'string'),
        new OA\Property(
            property: 'Owner',
            type: 'object',
            properties: [
                new OA\Property(property: 'DisplayName', type: 'string'),
                new OA\Property(property: 'ID', type: 'string'),
            ],
        ),
        new OA\Property(property: 'Prefix', type: 'string'),
    ],
)]
abstract class BucketListResultSchema
{}

#[OA\Schema(
    schema: 'BucketCreateResult',
    properties: [
        new OA\Property(property: 'BucketArn', type: 'string', example: 'arn:aws:s3:::my-new-bucket'),
        new OA\Property(property: 'Location', type: 'string', example: '/my-new-bucket'),
    ],
)]
abstract class BucketCreateResultSchema
{}

#[OA\Schema(
    schema: 'ObjectInfoShort',
    description: 'Object metadata as returned by ListObjectsV2',
    properties: [
        new OA\Property(property: 'ChecksumAlgorithm', type: 'string'),
        new OA\Property(property: 'ChecksumType', type: 'string', enum: ['COMPOSITE', 'FULL_OBJECT']),
        new OA\Property(property: 'ETag', type: 'string', example: '"d41d8cd98f00b204e9800998ecf8427e"'),
        new OA\Property(property: 'Key', type: 'string', example: 'folder/file.txt'),
        new OA\Property(property: 'LastModified', type: 'string', format: 'date-time'),
        new OA\Property(
            property: 'Owner',
            type: 'object',
            properties: [
                new OA\Property(property: 'DisplayName', type: 'string'),
                new OA\Property(property: 'ID', type: 'string'),
            ],
        ),
        new OA\Property(
            property: 'RestoreStatus',
            type: 'object',
            properties: [
                new OA\Property(property: 'IsRestoreInProgress', type: 'boolean'),
                new OA\Property(property: 'RestoreExpiryDate', type: 'string', format: 'date-time'),
            ],
        ),
        new OA\Property(property: 'Size', type: 'integer', example: 1024),
        new OA\Property(property: 'StorageClass', type: 'string', example: 'STANDARD'),
    ],
)]
abstract class ObjectInfoShortSchema
{}

#[OA\Schema(
    schema: 'ListObjectsResult',
    properties: [
        new OA\Property(
            property: 'CommonPrefixes',
            type: 'array',
            items: new OA\Items(type: 'object'),
        ),
        new OA\Property(
            property: 'Contents',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ObjectInfoShort'),
        ),
        new OA\Property(property: 'ContinuationToken', type: 'string'),
        new OA\Property(property: 'Delimiter', type: 'string'),
        new OA\Property(property: 'EncodingType', type: 'string'),
        new OA\Property(property: 'IsTruncated', type: 'boolean'),
        new OA\Property(property: 'KeyCount', type: 'integer'),
        new OA\Property(property: 'MaxKeys', type: 'integer'),
        new OA\Property(property: 'Name', type: 'string'),
        new OA\Property(property: 'NextContinuationToken', type: 'string'),
        new OA\Property(property: 'Prefix', type: 'string'),
        new OA\Property(property: 'RequestCharged', type: 'string'),
        new OA\Property(property: 'StartAfter', type: 'string'),
    ],
)]
abstract class ListObjectsResultSchema
{}

#[OA\Schema(
    schema: 'ObjectGetResult',
    description: 'Full object metadata and body returned by GetObject',
    properties: [
        new OA\Property(property: 'AcceptRanges', type: 'string'),
        new OA\Property(property: 'Body', type: 'string', description: 'Object body content'),
        new OA\Property(property: 'BucketKeyEnabled', type: 'boolean'),
        new OA\Property(property: 'CacheControl', type: 'string'),
        new OA\Property(property: 'ChecksumCRC32', type: 'string'),
        new OA\Property(property: 'ChecksumCRC32C', type: 'string'),
        new OA\Property(property: 'ChecksumCRC64NVME', type: 'string'),
        new OA\Property(property: 'ChecksumSHA1', type: 'string'),
        new OA\Property(property: 'ChecksumSHA256', type: 'string'),
        new OA\Property(property: 'ChecksumType', type: 'string'),
        new OA\Property(property: 'ContentDisposition', type: 'string'),
        new OA\Property(property: 'ContentEncoding', type: 'string'),
        new OA\Property(property: 'ContentLanguage', type: 'string'),
        new OA\Property(property: 'ContentLength', type: 'integer'),
        new OA\Property(property: 'ContentRange', type: 'string'),
        new OA\Property(property: 'ContentType', type: 'string'),
        new OA\Property(property: 'DeleteMarker', type: 'boolean'),
        new OA\Property(property: 'ETag', type: 'string'),
        new OA\Property(property: 'Expiration', type: 'string'),
        new OA\Property(property: 'Expires', type: 'string', format: 'date-time'),
        new OA\Property(property: 'ExpiresString', type: 'string'),
        new OA\Property(property: 'LastModified', type: 'string', format: 'date-time'),
        new OA\Property(property: 'Metadata', type: 'array', items: new OA\Items(type: 'string')),
        new OA\Property(property: 'MissingMeta', type: 'integer'),
        new OA\Property(property: 'ObjectLockLegalHoldStatus', type: 'string', enum: ['ON', 'OFF']),
        new OA\Property(property: 'ObjectLockMode', type: 'string', enum: ['GOVERNANCE', 'COMPLIANCE']),
        new OA\Property(property: 'ObjectLockRetainUntilDate', type: 'string', format: 'date-time'),
        new OA\Property(property: 'PartsCount', type: 'integer'),
        new OA\Property(property: 'ReplicationStatus', type: 'string'),
        new OA\Property(property: 'RequestCharged', type: 'string'),
        new OA\Property(property: 'Restore', type: 'string'),
        new OA\Property(property: 'SSECustomerAlgorithm', type: 'string'),
        new OA\Property(property: 'SSECustomerKeyMD5', type: 'string'),
        new OA\Property(property: 'SSEKMSKeyId', type: 'string'),
        new OA\Property(property: 'ServerSideEncryption', type: 'string'),
        new OA\Property(property: 'StorageClass', type: 'string'),
        new OA\Property(property: 'TagCount', type: 'integer'),
        new OA\Property(property: 'VersionId', type: 'string'),
        new OA\Property(property: 'WebsiteRedirectLocation', type: 'string'),
    ],
)]
abstract class ObjectGetResultSchema
{}

#[OA\Schema(
    schema: 'ObjectPutFromFilePathRequest',
    required: ['bucket_name', 'object_key', 'file_path'],
    properties: [
        new OA\Property(property: 'bucket_name', type: 'string', example: 'my-bucket'),
        new OA\Property(property: 'object_key', type: 'string', example: 'folder/file.txt'),
        new OA\Property(
            property: 'file_path',
            type: 'string',
            description: 'Absolute server-side file path',
            example: '/var/data/upload.txt',
        ),
    ],
)]
abstract class ObjectPutFromFilePathRequestSchema
{}

#[OA\Schema(
    schema: 'ObjectPutFromFileBodyRequest',
    required: ['bucket_name', 'object_key', 'file_body'],
    properties: [
        new OA\Property(property: 'bucket_name', type: 'string', example: 'my-bucket'),
        new OA\Property(property: 'object_key', type: 'string', example: 'folder/file.txt'),
        new OA\Property(
            property: 'file_body',
            type: 'string',
            format: 'byte',
            description: 'Base64-encoded file content',
            example: 'SGVsbG8gV29ybGQ=',
        ),
    ],
)]
abstract class ObjectPutFromFileBodyRequestSchema
{}

#[OA\Schema(
    schema: 'ObjectPutResult',
    properties: [
        new OA\Property(property: 'BucketKeyEnabled', type: 'boolean'),
        new OA\Property(property: 'ChecksumCRC32', type: 'string'),
        new OA\Property(property: 'ChecksumCRC32C', type: 'string'),
        new OA\Property(property: 'ChecksumCRC64NVME', type: 'string'),
        new OA\Property(property: 'ChecksumSHA1', type: 'string'),
        new OA\Property(property: 'ChecksumSHA256', type: 'string'),
        new OA\Property(property: 'ChecksumType', type: 'string', enum: ['COMPOSITE', 'FULL_OBJECT']),
        new OA\Property(property: 'ETag', type: 'string', example: '"d41d8cd98f00b204e9800998ecf8427e"'),
        new OA\Property(property: 'Expiration', type: 'string'),
        new OA\Property(property: 'ObjectURL', type: 'string', example: 'https://s3.amazonaws.com/my-bucket/file.txt'),
        new OA\Property(property: 'RequestCharged', type: 'string'),
        new OA\Property(property: 'SSECustomerAlgorithm', type: 'string'),
        new OA\Property(property: 'SSECustomerKeyMD5', type: 'string'),
        new OA\Property(property: 'SSEKMSEncryptionContext', type: 'string'),
        new OA\Property(property: 'SSEKMSKeyId', type: 'string'),
        new OA\Property(property: 'ServerSideEncryption', type: 'string'),
        new OA\Property(property: 'Size', type: 'integer'),
        new OA\Property(property: 'VersionId', type: 'string'),
    ],
)]
abstract class ObjectPutResultSchema
{}

#[OA\Schema(
    schema: 'ObjectRenameRequest',
    required: ['bucket_name', 'object_key_src', 'object_key_dst'],
    properties: [
        new OA\Property(property: 'bucket_name', type: 'string', example: 'my-bucket'),
        new OA\Property(property: 'object_key_src', type: 'string', example: 'folder/original.txt'),
        new OA\Property(property: 'object_key_dst', type: 'string', example: 'folder/renamed.txt'),
    ],
)]
abstract class ObjectRenameRequestSchema
{}

#[OA\Schema(
    schema: 'ObjectCopyRequest',
    required: ['bucket_name_src', 'bucket_name_dst', 'object_key_src', 'object_key_dst'],
    properties: [
        new OA\Property(property: 'bucket_name_src', type: 'string', example: 'source-bucket'),
        new OA\Property(property: 'bucket_name_dst', type: 'string', example: 'dest-bucket'),
        new OA\Property(property: 'object_key_src', type: 'string', example: 'folder/original.txt'),
        new OA\Property(property: 'object_key_dst', type: 'string', example: 'folder/copy.txt'),
    ],
)]
abstract class ObjectCopyRequestSchema
{}

#[OA\Schema(
    schema: 'ObjectCopyResult',
    properties: [
        new OA\Property(property: 'BucketKeyEnabled', type: 'boolean'),
        new OA\Property(
            property: 'CopyObjectResult',
            type: 'object',
            properties: [
                new OA\Property(property: 'ChecksumCRC32', type: 'string'),
                new OA\Property(property: 'ChecksumCRC32C', type: 'string'),
                new OA\Property(property: 'ChecksumCRC64NVME', type: 'string'),
                new OA\Property(property: 'ChecksumSHA1', type: 'string'),
                new OA\Property(property: 'ChecksumSHA256', type: 'string'),
                new OA\Property(property: 'ChecksumType', type: 'string'),
                new OA\Property(property: 'ETag', type: 'string'),
                new OA\Property(property: 'LastModified', type: 'string', format: 'date-time'),
            ],
        ),
        new OA\Property(property: 'CopySourceVersionId', type: 'string'),
        new OA\Property(property: 'Expiration', type: 'string'),
        new OA\Property(property: 'ObjectURL', type: 'string'),
        new OA\Property(property: 'RequestCharged', type: 'string'),
        new OA\Property(property: 'SSECustomerAlgorithm', type: 'string'),
        new OA\Property(property: 'SSECustomerKeyMD5', type: 'string'),
        new OA\Property(property: 'SSEKMSEncryptionContext', type: 'string'),
        new OA\Property(property: 'SSEKMSKeyId', type: 'string'),
        new OA\Property(property: 'ServerSideEncryption', type: 'string'),
        new OA\Property(property: 'VersionId', type: 'string'),
    ],
)]
abstract class ObjectCopyResultSchema
{}

#[OA\Schema(
    schema: 'ObjectDeleteResult',
    properties: [
        new OA\Property(property: 'DeleteMarker', type: 'boolean'),
        new OA\Property(property: 'RequestCharged', type: 'string'),
        new OA\Property(property: 'VersionId', type: 'string'),
    ],
)]
abstract class ObjectDeleteResultSchema
{}

// ---- ObjectsDeleteRequest
#[OA\Schema(
    schema: 'ObjectsDeleteRequest',
    required: ['object_keys'],
    properties: [
        new OA\Property(
            property: 'object_keys',
            type: 'array',
            items: new OA\Items(type: 'string'),
            example: ['folder/file1.txt', 'folder/file2.txt'],
        ),
    ],
)]
abstract class ObjectsDeleteRequest
{}

// ---- ObjectDeleteRequest
#[OA\Schema(
    schema: 'ObjectDeleteRequest',
    required: ['bucket_name', 'object_key'],
    properties: [
        new OA\Property(property: 'bucket_name', type: 'string', example: 'my-bucket'),
        new OA\Property(property: 'object_key', type: 'string', example: 'folder/file.txt'),
    ],
)]
abstract class ObjectDeleteRequestSchema
{}
