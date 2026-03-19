<?php

namespace App\Tests\Helper;

use App\Dto\AwsS3ListObjectsResult;
use App\Dto\AwsS3ObjectCopyParams;
use App\Dto\AwsS3ObjectCopyResult;
use App\Dto\AwsS3ObjectDeleteParams;
use App\Dto\AwsS3ObjectDeleteResult;
use App\Dto\AwsS3ObjectGetParams;
use App\Dto\AwsS3ObjectGetResult;
use App\Dto\AwsS3ObjectListParams;
use App\Dto\AwsS3ObjectPutParams;
use App\Dto\AwsS3ObjectPutResult;
use App\Dto\AwsS3ObjectsDeleteParams;
use App\Dto\AwsS3ObjectsDeleteResult;
use App\Exceptions\FileNotFoundException;
use App\Exceptions\InvalidDataException;
use App\Helper\AwsS3ObjectHelper;
use Aws\CommandInterface;
use Aws\MockHandler;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use PHPUnit\Framework\TestCase;

class AwsS3ObjectHelperTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeS3Client(MockHandler $mock): S3Client
    {
        return new S3Client([
            'region'      => 'us-east-1',
            'version'     => 'latest',
            'credentials' => [
                'key'    => 'test-key',
                'secret' => 'test-secret',
            ],
            'handler' => $mock,
        ]);
    }

    private function makeMock(array $resultData): MockHandler
    {
        $mock = new MockHandler();
        $mock->append(new Result($resultData));
        return $mock;
    }

    private function makeTempFile(string $content = 'test content'): string
    {
        $path = sys_get_temp_dir() . '/aws_test_' . uniqid() . '.txt';
        file_put_contents($path, $content);
        return $path;
    }

    /**
     * AwsS3ObjectPutResult has all non-nullable fields, so mocked AWS responses
     * must supply every field. This helper centralises that boilerplate.
     */
    private function makePutResultData(): array
    {
        return [
            'BucketKeyEnabled'        => false,
            'ChecksumCRC32'           => '',
            'ChecksumCRC32C'          => '',
            'ChecksumCRC64NVME'       => '',
            'ChecksumSHA1'            => '',
            'ChecksumSHA256'          => '',
            'ChecksumType'            => 'FULL_OBJECT',
            'ETag'                    => '"etag-abc123"',
            'Expiration'              => '',
            'ObjectURL'               => 'https://s3.amazonaws.com/my-bucket/key.txt',
            'RequestCharged'          => '',
            'SSECustomerAlgorithm'    => '',
            'SSECustomerKeyMD5'       => '',
            'SSEKMSEncryptionContext' => '',
            'SSEKMSKeyId'             => '',
            'ServerSideEncryption'    => 'AES256',
            'Size'                    => 42,
            'VersionId'               => '',
        ];
    }

    // -------------------------------------------------------------------------
    // getObjectsForBucket
    // -------------------------------------------------------------------------

    public function test_getObjectsForBucket_returnsListObjectsResult(): void
    {
        $mock = $this->makeMock([
            'Contents' => [
                [
                    'Key'          => 'folder/file.txt',
                    'Size'         => 1024,
                    'LastModified' => new \DateTime('2024-06-01'),
                    'ETag'         => '"abc123"',
                ],
            ],
            'Name'        => 'my-bucket',
            'MaxKeys'     => 1000,
            'IsTruncated' => false,
        ]);

        $result = AwsS3ObjectHelper::getObjectsForBucket($this->makeS3Client($mock), 'my-bucket');

        $this->assertInstanceOf(AwsS3ListObjectsResult::class, $result);
    }

    public function test_getObjectsForBucket_withNullParams_usesDefaults(): void
    {
        $mock = $this->makeMock(['Contents' => [], 'IsTruncated' => false]);

        $result = AwsS3ObjectHelper::getObjectsForBucket($this->makeS3Client($mock), 'my-bucket', null);

        $this->assertInstanceOf(AwsS3ListObjectsResult::class, $result);
    }

    public function test_getObjectsForBucket_withExistingParams_clonesWithNewBucket(): void
    {
        $mock = $this->makeMock(['Contents' => [], 'IsTruncated' => false]);

        $params = new AwsS3ObjectListParams('original-bucket');
        $result = AwsS3ObjectHelper::getObjectsForBucket($this->makeS3Client($mock), 'new-bucket', $params);

        $this->assertInstanceOf(AwsS3ListObjectsResult::class, $result);
    }

    public function test_getObjectsForBucket_propagatesAwsException(): void
    {
        $mock = new MockHandler();
        $mock->append(fn(CommandInterface $cmd) => new S3Exception('NoSuchBucket', $cmd));

        $this->expectException(S3Exception::class);

        AwsS3ObjectHelper::getObjectsForBucket($this->makeS3Client($mock), 'missing-bucket');
    }

    // -------------------------------------------------------------------------
    // getObject
    // -------------------------------------------------------------------------

    public function test_getObject_returnsGetResult(): void
    {
        $mock = $this->makeMock([
            'Body'          => 'hello world',
            'ContentLength' => 11,
            'ContentType'   => 'text/plain',
            'ETag'          => '"etag-value"',
        ]);

        $result = AwsS3ObjectHelper::getObject($this->makeS3Client($mock), 'my-bucket', 'my-key.txt');

        $this->assertInstanceOf(AwsS3ObjectGetResult::class, $result);
    }

    public function test_getObject_withNullParams_usesDefaults(): void
    {
        $mock = $this->makeMock(['Body' => 'data']);

        $result = AwsS3ObjectHelper::getObject($this->makeS3Client($mock), 'my-bucket', 'file.txt', null);

        $this->assertInstanceOf(AwsS3ObjectGetResult::class, $result);
    }

    public function test_getObject_withExistingParams_clonesWithNewBucketAndKey(): void
    {
        $mock = $this->makeMock(['Body' => 'data']);

        $params = new AwsS3ObjectGetParams('original-bucket', 'original-key');
        $result = AwsS3ObjectHelper::getObject($this->makeS3Client($mock), 'new-bucket', 'new-key.txt', $params);

        $this->assertInstanceOf(AwsS3ObjectGetResult::class, $result);
    }

    public function test_getObject_propagatesAwsException(): void
    {
        $mock = new MockHandler();
        $mock->append(fn(CommandInterface $cmd) => new S3Exception('NoSuchKey', $cmd));

        $this->expectException(S3Exception::class);

        AwsS3ObjectHelper::getObject($this->makeS3Client($mock), 'my-bucket', 'missing-key.txt');
    }

    // -------------------------------------------------------------------------
    // putObjectFromFilePath
    // -------------------------------------------------------------------------

    public function test_putObjectFromFilePath_returnsPutResult(): void
    {
        $filePath = $this->makeTempFile();
        $mock = $this->makeMock($this->makePutResultData());

        $result = AwsS3ObjectHelper::putObjectFromFilePath(
            $this->makeS3Client($mock),
            'my-bucket',
            'uploaded/file.txt',
            $filePath
        );

        $this->assertInstanceOf(AwsS3ObjectPutResult::class, $result);

        unlink($filePath);
    }

    public function test_putObjectFromFilePath_resultContainsExpectedEtag(): void
    {
        $filePath = $this->makeTempFile();
        $data = $this->makePutResultData();
        $data['ETag'] = '"my-special-etag"';
        $mock = $this->makeMock($data);

        $result = AwsS3ObjectHelper::putObjectFromFilePath(
            $this->makeS3Client($mock),
            'my-bucket',
            'file.txt',
            $filePath
        );

        $this->assertSame('"my-special-etag"', $result->eTag);

        unlink($filePath);
    }

    public function test_putObjectFromFilePath_throwsFileNotFoundExceptionForMissingFile(): void
    {
        $mock = $this->makeMock([]);

        $this->expectException(FileNotFoundException::class);

        AwsS3ObjectHelper::putObjectFromFilePath(
            $this->makeS3Client($mock),
            'my-bucket',
            'key.txt',
            '/tmp/this-file-does-not-exist-' . uniqid() . '.txt'
        );
    }

    public function test_putObjectFromFilePath_withNullParams_buildsDefaultParams(): void
    {
        $filePath = $this->makeTempFile();
        $mock = $this->makeMock($this->makePutResultData());

        $result = AwsS3ObjectHelper::putObjectFromFilePath(
            $this->makeS3Client($mock),
            'my-bucket',
            'file.txt',
            $filePath,
            null
        );

        $this->assertInstanceOf(AwsS3ObjectPutResult::class, $result);

        unlink($filePath);
    }

    public function test_putObjectFromFilePath_withExistingParams_clonesWithNewBucketKeySourceFile(): void
    {
        $filePath = $this->makeTempFile('some content');
        $mock = $this->makeMock($this->makePutResultData());

        $params = new AwsS3ObjectPutParams('original-bucket', 'original-key', sourceFile: $filePath);
        $result = AwsS3ObjectHelper::putObjectFromFilePath(
            $this->makeS3Client($mock),
            'new-bucket',
            'new-key.txt',
            $filePath,
            $params
        );

        $this->assertInstanceOf(AwsS3ObjectPutResult::class, $result);

        unlink($filePath);
    }

    public function test_putObjectFromFilePath_propagatesAwsException(): void
    {
        $filePath = $this->makeTempFile();
        $mock = new MockHandler();
        $mock->append(fn(CommandInterface $cmd) => new S3Exception('NoSuchBucket', $cmd));

        $this->expectException(S3Exception::class);

        try {
            AwsS3ObjectHelper::putObjectFromFilePath(
                $this->makeS3Client($mock),
                'bad-bucket',
                'key.txt',
                $filePath
            );
        } finally {
            unlink($filePath);
        }
    }

    // -------------------------------------------------------------------------
    // putObjectFromFileBody
    // -------------------------------------------------------------------------

    public function test_putObjectFromFileBody_returnsPutResult(): void
    {
        $mock = $this->makeMock($this->makePutResultData());

        $result = AwsS3ObjectHelper::putObjectFromFileBody(
            $this->makeS3Client($mock),
            'my-bucket',
            'file.txt',
            'raw file content here'
        );

        $this->assertInstanceOf(AwsS3ObjectPutResult::class, $result);
    }

    public function test_putObjectFromFileBody_resultContainsExpectedEtag(): void
    {
        $data = $this->makePutResultData();
        $data['ETag'] = '"body-etag"';
        $mock = $this->makeMock($data);

        $result = AwsS3ObjectHelper::putObjectFromFileBody(
            $this->makeS3Client($mock),
            'my-bucket',
            'file.txt',
            'some body content'
        );

        $this->assertSame('"body-etag"', $result->eTag);
    }

    public function test_putObjectFromFileBody_throwsInvalidDataExceptionForEmptyBody(): void
    {
        $mock = $this->makeMock([]);

        $this->expectException(InvalidDataException::class);
        $this->expectExceptionMessage('File body may not be empty.');

        AwsS3ObjectHelper::putObjectFromFileBody(
            $this->makeS3Client($mock),
            'my-bucket',
            'file.txt',
            ''
        );
    }

    public function test_putObjectFromFileBody_withNullParams_buildsDefaultParams(): void
    {
        $mock = $this->makeMock($this->makePutResultData());

        $result = AwsS3ObjectHelper::putObjectFromFileBody(
            $this->makeS3Client($mock),
            'my-bucket',
            'file.txt',
            'body content',
            null
        );

        $this->assertInstanceOf(AwsS3ObjectPutResult::class, $result);
    }

    public function test_putObjectFromFileBody_withExistingParams_clonesWithNewBucketKeyBody(): void
    {
        $mock = $this->makeMock($this->makePutResultData());

        $params = new AwsS3ObjectPutParams('original-bucket', 'original-key', body: 'original body');
        $result = AwsS3ObjectHelper::putObjectFromFileBody(
            $this->makeS3Client($mock),
            'new-bucket',
            'new-key.txt',
            'new body content',
            $params
        );

        $this->assertInstanceOf(AwsS3ObjectPutResult::class, $result);
    }

    public function test_putObjectFromFileBody_propagatesAwsException(): void
    {
        $mock = new MockHandler();
        $mock->append(fn(CommandInterface $cmd) => new S3Exception('AccessDenied', $cmd));

        $this->expectException(S3Exception::class);

        AwsS3ObjectHelper::putObjectFromFileBody(
            $this->makeS3Client($mock),
            'my-bucket',
            'file.txt',
            'some body content'
        );
    }

    // -------------------------------------------------------------------------
    // copyObject
    // -------------------------------------------------------------------------

    public function test_copyObject_returnsCopyResult(): void
    {
        $mock = $this->makeMock([
            'CopyObjectResult' => [
                'ETag'         => '"new-etag"',
                'LastModified' => new \DateTime(),
            ],
        ]);

        $result = AwsS3ObjectHelper::copyObject(
            $this->makeS3Client($mock),
            'src-bucket', 'src-key.txt',
            'dst-bucket', 'dst-key.txt'
        );

        $this->assertInstanceOf(AwsS3ObjectCopyResult::class, $result);
    }

    public function test_copyObject_withExistingParams_clonesCorrectly(): void
    {
        $mock = $this->makeMock(['CopyObjectResult' => ['ETag' => '"etag"']]);

        $params = new AwsS3ObjectCopyParams('orig-dst', 'orig-dst-key', 'orig-src/orig-src-key');
        $result = AwsS3ObjectHelper::copyObject(
            $this->makeS3Client($mock),
            'src-bucket', 'src-key.txt',
            'dst-bucket', 'dst-key.txt',
            $params
        );

        $this->assertInstanceOf(AwsS3ObjectCopyResult::class, $result);
    }

    public function test_copyObject_propagatesAwsException(): void
    {
        $mock = new MockHandler();
        $mock->append(fn(CommandInterface $cmd) => new S3Exception('NoSuchKey', $cmd));

        $this->expectException(S3Exception::class);

        AwsS3ObjectHelper::copyObject(
            $this->makeS3Client($mock),
            'src-bucket', 'missing-key.txt',
            'dst-bucket', 'dst-key.txt'
        );
    }

    // -------------------------------------------------------------------------
    // deleteObject
    // -------------------------------------------------------------------------

    public function test_deleteObject_returnsDeleteResult(): void
    {
        $mock = $this->makeMock(['@metadata' => ['statusCode' => 204]]);

        $result = AwsS3ObjectHelper::deleteObject($this->makeS3Client($mock), 'my-bucket', 'file.txt');

        $this->assertInstanceOf(AwsS3ObjectDeleteResult::class, $result);
    }

    public function test_deleteObject_withNullParams_usesDefaults(): void
    {
        $mock = $this->makeMock(['@metadata' => ['statusCode' => 204]]);

        $result = AwsS3ObjectHelper::deleteObject($this->makeS3Client($mock), 'my-bucket', 'file.txt', null);

        $this->assertInstanceOf(AwsS3ObjectDeleteResult::class, $result);
    }

    public function test_deleteObject_withExistingParams_clonesWithNewBucketAndKey(): void
    {
        $mock = $this->makeMock(['@metadata' => ['statusCode' => 204]]);

        $params = new AwsS3ObjectDeleteParams('orig-bucket', 'orig-key');
        $result = AwsS3ObjectHelper::deleteObject($this->makeS3Client($mock), 'my-bucket', 'file.txt', $params);

        $this->assertInstanceOf(AwsS3ObjectDeleteResult::class, $result);
    }

    public function test_deleteObject_propagatesAwsException(): void
    {
        $mock = new MockHandler();
        $mock->append(fn(CommandInterface $cmd) => new S3Exception('NoSuchBucket', $cmd));

        $this->expectException(S3Exception::class);

        AwsS3ObjectHelper::deleteObject($this->makeS3Client($mock), 'bad-bucket', 'file.txt');
    }

    // -------------------------------------------------------------------------
    // deleteMultipleObjects
    // -------------------------------------------------------------------------

    public function test_deleteMultipleObjects_returnsDeletesResult(): void
    {
        $mock = $this->makeMock([
            'Deleted' => [['Key' => 'file1.txt'], ['Key' => 'file2.txt']],
            'Errors'  => [],
        ]);

        $result = AwsS3ObjectHelper::deleteMultipleObjects(
            $this->makeS3Client($mock),
            'my-bucket',
            ['file1.txt', 'file2.txt']
        );

        $this->assertInstanceOf(AwsS3ObjectsDeleteResult::class, $result);
    }

    public function test_deleteMultipleObjects_withNullParams_usesDefaults(): void
    {
        $mock = $this->makeMock(['Deleted' => [['Key' => 'a.txt']], 'Errors' => []]);

        $result = AwsS3ObjectHelper::deleteMultipleObjects(
            $this->makeS3Client($mock),
            'my-bucket',
            ['a.txt'],
            null
        );

        $this->assertInstanceOf(AwsS3ObjectsDeleteResult::class, $result);
    }

    public function test_deleteMultipleObjects_withExistingParams_clonesWithNewBucketAndKeys(): void
    {
        $mock = $this->makeMock(['Deleted' => [], 'Errors' => []]);

        $params = AwsS3ObjectsDeleteParams::createForBucketAndKeys('orig-bucket', ['orig.txt']);
        $result = AwsS3ObjectHelper::deleteMultipleObjects(
            $this->makeS3Client($mock),
            'new-bucket',
            ['new1.txt', 'new2.txt'],
            $params
        );

        $this->assertInstanceOf(AwsS3ObjectsDeleteResult::class, $result);
    }

    public function test_deleteMultipleObjects_propagatesAwsException(): void
    {
        $mock = new MockHandler();
        $mock->append(fn(CommandInterface $cmd) => new S3Exception('AccessDenied', $cmd));

        $this->expectException(S3Exception::class);

        AwsS3ObjectHelper::deleteMultipleObjects(
            $this->makeS3Client($mock),
            'bad-bucket',
            ['file1.txt', 'file2.txt']
        );
    }
}
