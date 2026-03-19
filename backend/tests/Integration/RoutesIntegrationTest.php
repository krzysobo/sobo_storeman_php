<?php

namespace Tests\Integration;

use Aws\CommandInterface;
use Aws\MockHandler;
use Aws\Result;
use Aws\S3\Exception\S3Exception;

/**
 * Integration tests for all Sobo Storeman routes.
 *
 * Strategy
 * --------
 * - The Slim app is booted in-process via BaseTestCase (no running server needed).
 * - AWS SDK calls are intercepted via MockHandler — no real AWS traffic.
 * - JWT tokens are generated from fake credentials using AwsCredentialsHelper,
 *   so the AwsAuthMiddleware passes without touching AWS.
 * - Each test group focuses on a single route: happy path + auth guard + validation.
 *
 * Known source issue documented inline
 * --------------------------------------
 * RouteHelperAwsS3Bucket::addBucketDelete() calls createBucket() instead of
 * deleteBucket() — this is noted where relevant so the tests reflect actual
 * current behaviour, and will need updating once the bug is fixed.
 */
class RoutesIntegrationTest extends BaseTestCase
{
    // =========================================================================
    // GET /
    // =========================================================================

    public function test_root_returns200WithAppInfo(): void
    {
        $response = $this->runRequest('GET', '/');

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey('app', $body);
        $this->assertArrayHasKey('version', $body);
        $this->assertSame('Sobo Storeman', $body['app']);
    }

    public function test_root_doesNotRequireAuth(): void
    {
        // No Authorization header — must still return 200
        $response = $this->runRequest('GET', '/');
        $this->assertSame(200, $response->getStatusCode());
    }

    // =========================================================================
    // GET /play
    // =========================================================================

    public function test_play_returns200WithPlaygroundData(): void
    {
        $response = $this->runRequest('GET', '/play');

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertTrue($body['is_playground']);
        $this->assertArrayHasKey('php_version_id', $body);
        $this->assertArrayHasKey('playground', $body);
    }

    public function test_play_doesNotRequireAuth(): void
    {
        $response = $this->runRequest('GET', '/play');
        $this->assertSame(200, $response->getStatusCode());
    }

    // =========================================================================
    // POST /aws/login
    // =========================================================================

    public function test_awsLogin_returns200WithJwtTokenOnValidCredentials(): void
    {
        // The login route calls listBuckets() to validate credentials.
        // We need the MockHandler in place before the request is processed.
        $mock = $this->makeMockHandler([
            ['Buckets' => [], 'Owner' => ['DisplayName' => '', 'ID' => '']],
        ]);
        $this->injectMockHandler($mock);

        $response = $this->runRequest('POST', '/aws/login', [], [
            'access_key_id'     => self::FAKE_KEY,
            'secret_access_key' => self::FAKE_SECRET,
            'region'            => self::FAKE_REGION,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertSame('authenticated', $body['status']);
        $this->assertArrayHasKey('jwt_token', $body);
        $this->assertNotEmpty($body['jwt_token']);
    }

    public function test_awsLogin_returns200WithPermanentTypeWhenNoSessionToken(): void
    {
        $mock = $this->makeMockHandler([['Buckets' => []]]);
        $this->injectMockHandler($mock);

        $response = $this->runRequest('POST', '/aws/login', [], [
            'access_key_id'     => self::FAKE_KEY,
            'secret_access_key' => self::FAKE_SECRET,
        ]);

        $body = $this->decodeBody($response);
        $this->assertSame('permanent', $body['type']);
    }

    public function test_awsLogin_returns200WithTemporaryTypeWhenSessionTokenProvided(): void
    {
        $mock = $this->makeMockHandler([['Buckets' => []]]);
        $this->injectMockHandler($mock);

        $response = $this->runRequest('POST', '/aws/login', [], [
            'access_key_id'     => self::FAKE_KEY,
            'secret_access_key' => self::FAKE_SECRET,
            'session_token'     => 'some-sts-token',
        ]);

        $body = $this->decodeBody($response);
        $this->assertSame('temporary', $body['type']);
    }

    public function test_awsLogin_returns400WhenAccessKeyMissing(): void
    {
        $response = $this->runRequest('POST', '/aws/login', [], [
            'secret_access_key' => self::FAKE_SECRET,
        ]);

        $this->assertSame(400, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey('error', $body);
    }

    public function test_awsLogin_returns400WhenSecretKeyMissing(): void
    {
        $response = $this->runRequest('POST', '/aws/login', [], [
            'access_key_id' => self::FAKE_KEY,
        ]);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_awsLogin_returns400WhenBothKeysMissing(): void
    {
        $response = $this->runRequest('POST', '/aws/login', [], []);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function test_awsLogin_returns401WhenAwsRejectsCredentials(): void
    {
        $mock = new MockHandler();
        $mock->append(function (CommandInterface $cmd) {
            return new S3Exception('InvalidClientTokenId', $cmd, [
                'code'    => 'InvalidClientTokenId',
                'message' => 'The security token included in the request is invalid.',
            ]);
        });
        $this->injectMockHandler($mock);

        $response = $this->runRequest('POST', '/aws/login', [], [
            'access_key_id'     => 'BADKEY',
            'secret_access_key' => 'BADSECRET',
        ]);

        $this->assertSame(401, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey('error', $body);
    }

    public function test_awsLogin_acceptsAltFieldNames(): void
    {
        // The route also accepts access_key / secret_key as aliases
        $mock = $this->makeMockHandler([['Buckets' => []]]);
        $this->injectMockHandler($mock);

        $response = $this->runRequest('POST', '/aws/login', [], [
            'access_key' => self::FAKE_KEY,
            'secret_key' => self::FAKE_SECRET,
        ]);

        $this->assertSame(200, $response->getStatusCode());
    }

    // =========================================================================
    // Auth guard — shared across all protected routes
    // =========================================================================

    public function test_protectedRoute_returns401WithNoToken(): void
    {
        $response = $this->runRequest('GET', '/aws/s3/bucket/list');
        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_protectedRoute_returns401WithMalformedToken(): void
    {
        $response = $this->runRequest(
            'GET',
            '/aws/s3/bucket/list',
            ['Authorization' => 'Bearer this.is.not.a.valid.token']
        );
        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_protectedRoute_returns401WithoutBearerScheme(): void
    {
        $response = $this->runRequest(
            'GET',
            '/aws/s3/bucket/list',
            ['Authorization' => $this->makeAuthToken()] // missing "Bearer " prefix
        );
        $this->assertSame(401, $response->getStatusCode());
    }

    // =========================================================================
    // GET /aws/s3/bucket/list
    // =========================================================================

    public function test_bucketList_returns200WithBuckets(): void
    {
        $mock = $this->makeMockHandler([[
            'Buckets' => [
                ['Name' => 'bucket-one', 'BucketRegion' => 'eu-north-1', 'CreationDate' => new \DateTime(), 'BucketArn' => 'arn:aws:s3:::bucket-one'],
            ],
            'ContinuationToken' => '',
            'Owner'  => ['DisplayName' => 'test-owner', 'ID' => 'owner-123'],
            'Prefix' => '',
        ]]);
        $this->injectMockHandler($mock);

        $response = $this->runRequest('GET', '/aws/s3/bucket/list', $this->authHeader());

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey('Buckets', $body);
    }

    public function test_bucketList_returns401WithNoAuth(): void
    {
        $response = $this->runRequest('GET', '/aws/s3/bucket/list');
        $this->assertSame(401, $response->getStatusCode());
    }

    // =========================================================================
    // GET /aws/s3/bucket/get/{bucket_name}
    // =========================================================================

    public function test_getBucketObjects_returns200WithContents(): void
    {
        $mock = $this->makeMockHandler([[
            'Contents'              => [],
            'ContinuationToken'     => '',
            'Delimiter'             => '',
            'EncodingType'          => '',
            'IsTruncated'           => false,
            'KeyCount'              => 0,
            'MaxKeys'               => 1000,
            'Name'                  => 'my-bucket',
            'NextContinuationToken' => '',
            'Prefix'                => '',
            'RequestCharged'        => '',
            'StartAfter'            => '',
        ]]);
        $this->injectMockHandler($mock);

        $response = $this->runRequest(
            'GET',
            '/aws/s3/bucket/get/my-bucket',
            $this->authHeader()
        );

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey('Contents', $body);
    }

    public function test_getBucketObjects_returns401WithNoAuth(): void
    {
        $response = $this->runRequest('GET', '/aws/s3/bucket/get/my-bucket');
        $this->assertSame(401, $response->getStatusCode());
    }

    // =========================================================================
    // POST /aws/s3/bucket/create
    // =========================================================================

    public function test_createBucket_returns200OnSuccess(): void
    {
        $mock = $this->makeMockHandler([[
            'BucketArn' => 'arn:aws:s3:::new-bucket',
            'Location'  => '/new-bucket',
        ]]);
        $this->injectMockHandler($mock);

        $response = $this->runRequest(
            'POST',
            '/aws/s3/bucket/create',
            $this->authHeader(),
            ['bucket_name' => 'new-bucket']
        );

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey('Location', $body);
        $this->assertSame('/new-bucket', $body['Location']);
    }

    public function test_createBucket_returns401WithNoAuth(): void
    {
        $response = $this->runRequest('POST', '/aws/s3/bucket/create', [], ['bucket_name' => 'x']);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_createBucket_returns422WhenBucketNameMissing(): void
    {
        $response = $this->runRequest('POST', '/aws/s3/bucket/create', $this->authHeader(), []);
        // ArrayValidationHelper throws InvalidDataException -> ErrorMiddleware maps to 422
        $this->assertSame(422, $response->getStatusCode());
    }

    // =========================================================================
    // DELETE /aws/s3/bucket/delete
    // NOTE: current implementation calls createBucket() instead of deleteBucket()
    // — tests reflect actual behaviour and should be updated when bug is fixed.
    // =========================================================================

    public function test_deleteBucket_returns200(): void
    {
        $mock = $this->makeMockHandler([[
            'BucketArn' => 'arn:aws:s3:::old-bucket',
            'Location'  => '/old-bucket',
        ]]);
        $this->injectMockHandler($mock);

        $response = $this->runRequest(
            'DELETE',
            '/aws/s3/bucket/delete',
            $this->authHeader(),
            ['bucket_name' => 'old-bucket']
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function test_deleteBucket_returns401WithNoAuth(): void
    {
        $response = $this->runRequest('DELETE', '/aws/s3/bucket/delete', [], ['bucket_name' => 'x']);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_deleteBucket_returns422WhenBucketNameMissing(): void
    {
        $response = $this->runRequest('DELETE', '/aws/s3/bucket/delete', $this->authHeader(), []);
        $this->assertSame(422, $response->getStatusCode());
    }

    // =========================================================================
    // GET /aws/s3/object/get/{bucket_name}/{object_key}
    // =========================================================================

    public function test_getObject_returns200WithObjectData(): void
    {
        $dt = new \DateTime();
        $mock = $this->makeMockHandler([[
            'AcceptRanges'              => 'bytes',
            'Body'                      => 'hello world',
            'BucketKeyEnabled'          => false,
            'CacheControl'              => '',
            'ChecksumCRC32'             => '',
            'ChecksumCRC32C'            => '',
            'ChecksumCRC64NVME'         => '',
            'ChecksumSHA1'              => '',
            'ChecksumSHA256'            => '',
            'ChecksumType'              => 'FULL_OBJECT',
            'ContentDisposition'        => '',
            'ContentEncoding'           => '',
            'ContentLanguage'           => '',
            'ContentLength'             => 11,
            'ContentRange'              => '',
            'ContentType'               => 'text/plain',
            'DeleteMarker'              => false,
            'ETag'                      => '"etag123"',
            'Expiration'                => '',
            'Expires'                   => $dt,
            'ExpiresString'             => '',
            'LastModified'              => $dt,
            'Metadata'                  => [],
            'MissingMeta'               => 0,
            'ObjectLockLegalHoldStatus' => '',
            'ObjectLockMode'            => '',
            'ObjectLockRetainUntilDate' => $dt,
            'PartsCount'                => 0,
            'ReplicationStatus'         => '',
            'RequestCharged'            => '',
            'Restore'                   => '',
            'SSECustomerAlgorithm'      => '',
            'SSECustomerKeyMD5'         => '',
            'SSEKMSKeyId'               => '',
            'ServerSideEncryption'      => '',
            'StorageClass'              => 'STANDARD',
            'TagCount'                  => 0,
            'VersionId'                 => '',
            'WebsiteRedirectLocation'   => '',
        ]]);
        $this->injectMockHandler($mock);

        $response = $this->runRequest(
            'GET',
            '/aws/s3/object/get/my-bucket/folder%2Ffile.txt',
            $this->authHeader()
        );

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey('ETag', $body);
        $this->assertSame('"etag123"', $body['ETag']);
    }

    public function test_getObject_returns401WithNoAuth(): void
    {
        $response = $this->runRequest('GET', '/aws/s3/object/get/my-bucket/file.txt');
        $this->assertSame(401, $response->getStatusCode());
    }

    // =========================================================================
    // POST /aws/s3/object/copy
    // =========================================================================

    public function test_copyObject_returns200OnSuccess(): void
    {
        $dt   = new \DateTime();
        $mock = $this->makeMockHandler([[
            'BucketKeyEnabled'                => false,
            'CopyObjectResult'                => [
                'ChecksumCRC32'     => '',
                'ChecksumCRC32C'    => '',
                'ChecksumCRC64NVME' => '',
                'ChecksumSHA1'      => '',
                'ChecksumSHA256'    => '',
                'ChecksumType'      => 'FULL_OBJECT',
                'ETag'              => '"copied-etag"',
                'LastModified'      => $dt,
            ],
            'CopySourceVersionId'             => '',
            'Expiration'                      => '',
            'ObjectURL'                       => '',
            'RequestCharged'                  => '',
            'SSECustomerAlgorithm'            => '',
            'SSECustomerKeyMD5'               => '',
            'SSEKMSEncryptionContext'         => '',
            'SSEKMSKeyId'                     => '',
            'ServerSideEncryption'            => '',
            'VersionId'                       => '',
        ]]);
        $this->injectMockHandler($mock);

        $response = $this->runRequest(
            'POST',
            '/aws/s3/object/copy',
            $this->authHeader(),
            [
                'bucket_name_src' => 'src-bucket',
                'bucket_name_dst' => 'dst-bucket',
                'object_key_src'  => 'original.txt',
                'object_key_dst'  => 'copy.txt',
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey('CopyObjectResult', $body);
    }

    public function test_copyObject_returns401WithNoAuth(): void
    {
        $response = $this->runRequest('POST', '/aws/s3/object/copy', [], [
            'bucket_name_src' => 'src', 'bucket_name_dst' => 'dst',
            'object_key_src'  => 'a',   'object_key_dst'  => 'b',
        ]);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_copyObject_returns422WhenFieldsMissing(): void
    {
        $response = $this->runRequest('POST', '/aws/s3/object/copy', $this->authHeader(), [
            'bucket_name_src' => 'src-bucket',
            // missing bucket_name_dst, object_key_src, object_key_dst
        ]);
        $this->assertSame(422, $response->getStatusCode());
    }

    // =========================================================================
    // POST /aws/s3/object/put/file-path
    // =========================================================================

    public function test_putObjectFromFilePath_returns200OnSuccess(): void
    {
        $filePath = sys_get_temp_dir() . '/integration_test_' . uniqid() . '.txt';
        file_put_contents($filePath, 'hello from integration test');

        $mock = $this->makeMockHandler([[
            'BucketKeyEnabled'        => false,
            'ChecksumCRC32'           => '',
            'ChecksumCRC32C'          => '',
            'ChecksumCRC64NVME'       => '',
            'ChecksumSHA1'            => '',
            'ChecksumSHA256'          => '',
            'ChecksumType'            => 'FULL_OBJECT',
            'ETag'                    => '"put-etag"',
            'Expiration'              => '',
            'ObjectURL'               => 'https://s3.amazonaws.com/my-bucket/file.txt',
            'RequestCharged'          => '',
            'SSECustomerAlgorithm'    => '',
            'SSECustomerKeyMD5'       => '',
            'SSEKMSEncryptionContext' => '',
            'SSEKMSKeyId'             => '',
            'ServerSideEncryption'    => 'AES256',
            'Size'                    => 27,
            'VersionId'               => '',
        ]]);
        $this->injectMockHandler($mock);

        $response = $this->runRequest(
            'POST',
            '/aws/s3/object/put/file-path',
            $this->authHeader(),
            [
                'bucket_name' => 'my-bucket',
                'object_key'  => 'file.txt',
                'file_path'   => $filePath,
            ]
        );

        unlink($filePath);

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey('ETag', $body);
    }

    public function test_putObjectFromFilePath_returns401WithNoAuth(): void
    {
        $response = $this->runRequest('POST', '/aws/s3/object/put/file-path', [], [
            'bucket_name' => 'b', 'object_key' => 'k', 'file_path' => '/tmp/x',
        ]);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_putObjectFromFilePath_returns422WhenFieldsMissing(): void
    {
        $response = $this->runRequest('POST', '/aws/s3/object/put/file-path', $this->authHeader(), [
            'bucket_name' => 'my-bucket',
            // missing object_key and file_path
        ]);
        $this->assertSame(422, $response->getStatusCode());
    }

    // =========================================================================
    // POST /aws/s3/object/put/file-body
    // =========================================================================

    public function test_putObjectFromFileBody_returns200OnSuccess(): void
    {
        $mock = $this->makeMockHandler([[
            'BucketKeyEnabled'        => false,
            'ChecksumCRC32'           => '',
            'ChecksumCRC32C'          => '',
            'ChecksumCRC64NVME'       => '',
            'ChecksumSHA1'            => '',
            'ChecksumSHA256'          => '',
            'ChecksumType'            => 'FULL_OBJECT',
            'ETag'                    => '"body-etag"',
            'Expiration'              => '',
            'ObjectURL'               => 'https://s3.amazonaws.com/my-bucket/file.txt',
            'RequestCharged'          => '',
            'SSECustomerAlgorithm'    => '',
            'SSECustomerKeyMD5'       => '',
            'SSEKMSEncryptionContext' => '',
            'SSEKMSKeyId'             => '',
            'ServerSideEncryption'    => 'AES256',
            'Size'                    => 5,
            'VersionId'               => '',
        ]]);
        $this->injectMockHandler($mock);

        $response = $this->runRequest(
            'POST',
            '/aws/s3/object/put/file-body',
            $this->authHeader(),
            [
                'bucket_name' => 'my-bucket',
                'object_key'  => 'file.txt',
                'file_body'   => base64_encode('hello'), // route decodes this
            ]
        );

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey('ETag', $body);
    }

    public function test_putObjectFromFileBody_returns401WithNoAuth(): void
    {
        $response = $this->runRequest('POST', '/aws/s3/object/put/file-body', [], [
            'bucket_name' => 'b', 'object_key' => 'k', 'file_body' => base64_encode('x'),
        ]);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_putObjectFromFileBody_returns422WhenFieldsMissing(): void
    {
        $response = $this->runRequest('POST', '/aws/s3/object/put/file-body', $this->authHeader(), [
            'bucket_name' => 'my-bucket',
            // missing object_key and file_body
        ]);
        $this->assertSame(422, $response->getStatusCode());
    }

    // =========================================================================
    // DELETE /aws/s3/object/delete/{bucket_name}/{object_key}
    // Route deletes the object then returns updated bucket listing
    // =========================================================================

    public function test_deleteObject_returns200WithUpdatedListing(): void
    {
        $emptyListing = [
            'Contents'              => [],
            'ContinuationToken'     => '',
            'Delimiter'             => '',
            'EncodingType'          => '',
            'IsTruncated'           => false,
            'KeyCount'              => 0,
            'MaxKeys'               => 1000,
            'Name'                  => 'my-bucket',
            'NextContinuationToken' => '',
            'Prefix'                => '',
            'RequestCharged'        => '',
            'StartAfter'            => '',
        ];

        // MockHandler needs two results: one for deleteObject, one for listObjectsV2
        $mock = $this->makeMockHandler([
            ['DeleteMarker' => false, 'RequestCharged' => '', 'VersionId' => ''],
            $emptyListing,
        ]);
        $this->injectMockHandler($mock);

        $response = $this->runRequest(
            'DELETE',
            '/aws/s3/object/delete/my-bucket/file.txt',
            $this->authHeader()
        );

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey('Contents', $body);
    }

    public function test_deleteObject_returns401WithNoAuth(): void
    {
        $response = $this->runRequest('DELETE', '/aws/s3/object/delete/my-bucket/file.txt');
        $this->assertSame(401, $response->getStatusCode());
    }

    // =========================================================================
    // DELETE /aws/s3/object/delete-multiple/{bucket_name}
    // Route deletes all objects then returns updated bucket listing
    // =========================================================================

    public function test_deleteMultipleObjects_returns200WithUpdatedListing(): void
    {
        $emptyListing = [
            'Contents'              => [],
            'ContinuationToken'     => '',
            'Delimiter'             => '',
            'EncodingType'          => '',
            'IsTruncated'           => false,
            'KeyCount'              => 0,
            'MaxKeys'               => 1000,
            'Name'                  => 'my-bucket',
            'NextContinuationToken' => '',
            'Prefix'                => '',
            'RequestCharged'        => '',
            'StartAfter'            => '',
        ];

        $mock = $this->makeMockHandler([
            ['Deleted' => [['Key' => 'file1.txt'], ['Key' => 'file2.txt']], 'Errors' => [], 'RequestCharged' => ''],
            $emptyListing,
        ]);
        $this->injectMockHandler($mock);

        $response = $this->runRequest(
            'DELETE',
            '/aws/s3/object/delete-multiple/my-bucket',
            $this->authHeader(),
            ['object_keys' => ['file1.txt', 'file2.txt']]
        );

        $this->assertSame(200, $response->getStatusCode());
        $body = $this->decodeBody($response);
        $this->assertArrayHasKey('Contents', $body);
    }

    public function test_deleteMultipleObjects_returns401WithNoAuth(): void
    {
        $response = $this->runRequest(
            'DELETE',
            '/aws/s3/object/delete-multiple/my-bucket',
            [],
            ['object_keys' => ['file.txt']]
        );
        $this->assertSame(401, $response->getStatusCode());
    }

    public function test_deleteMultipleObjects_returns422WhenObjectKeysMissing(): void
    {
        $response = $this->runRequest(
            'DELETE',
            '/aws/s3/object/delete-multiple/my-bucket',
            $this->authHeader(),
            [] // missing object_keys
        );
        $this->assertSame(422, $response->getStatusCode());
    }
}
