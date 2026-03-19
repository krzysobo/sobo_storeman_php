<?php

namespace App\Tests\Helper;

use App\Dto\AwsS3BucketCreateParams;
use App\Dto\AwsS3BucketCreateResult;
use App\Dto\AwsS3BucketListResult;
use App\Helper\AwsS3BucketHelper;
use Aws\CommandInterface;
use Aws\MockHandler;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use PHPUnit\Framework\TestCase;

class AwsS3BucketHelperTest extends TestCase
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

    // -------------------------------------------------------------------------
    // getBucketsList
    // -------------------------------------------------------------------------

    public function test_getBucketsList_returnsListResult(): void
    {
        $mock = $this->makeMock([
            'Buckets' => [
                [
                    'Name'         => 'my-bucket',
                    'CreationDate' => new \DateTime('2024-01-01'),
                    'BucketRegion' => 'us-east-1',
                    'BucketArn'    => 'arn:aws:s3:::my-bucket',
                ],
            ],
            'Owner' => [
                'DisplayName' => 'test-owner',
                'ID'          => 'owner-id-123',
            ],
        ]);

        $result = AwsS3BucketHelper::getBucketsList($this->makeS3Client($mock));

        $this->assertInstanceOf(AwsS3BucketListResult::class, $result);
    }

    public function test_getBucketsList_withNoOptionalParams_doesNotThrow(): void
    {
        $mock = $this->makeMock(['Buckets' => [], 'Owner' => []]);

        $result = AwsS3BucketHelper::getBucketsList($this->makeS3Client($mock));

        $this->assertInstanceOf(AwsS3BucketListResult::class, $result);
    }

    public function test_getBucketsList_withAllOptionalParams_doesNotThrow(): void
    {
        $mock = $this->makeMock(['Buckets' => [], 'Owner' => []]);

        $result = AwsS3BucketHelper::getBucketsList(
            $this->makeS3Client($mock),
            bucketRegion: 'eu-west-1',
            continuationToken: 'token-abc',
            maxBuckets: 10,
            prefix: 'prod-'
        );

        $this->assertInstanceOf(AwsS3BucketListResult::class, $result);
    }

    public function test_getBucketsList_propagatesAwsException(): void
    {
        $mock = new MockHandler();
        $mock->append(function (CommandInterface $cmd) {
            return new S3Exception('Access Denied', $cmd);
        });

        $this->expectException(S3Exception::class);

        AwsS3BucketHelper::getBucketsList($this->makeS3Client($mock));
    }

    // -------------------------------------------------------------------------
    // createBucket
    // -------------------------------------------------------------------------

    public function test_createBucket_returnsCreateResult(): void
    {
        $mock = $this->makeMock([
            'Location' => '/my-new-bucket',
            '@metadata' => ['statusCode' => 200],
        ]);

        $result = AwsS3BucketHelper::createBucket($this->makeS3Client($mock), 'my-new-bucket');

        $this->assertInstanceOf(AwsS3BucketCreateResult::class, $result);
    }

    public function test_createBucket_withNullParams_buildsDefaultParams(): void
    {
        $mock = $this->makeMock(['Location' => '/bucket-name']);

        // Should NOT throw — null params triggers default AwsS3BucketCreateParams construction
        $result = AwsS3BucketHelper::createBucket($this->makeS3Client($mock), 'bucket-name', null);

        $this->assertInstanceOf(AwsS3BucketCreateResult::class, $result);
    }

    public function test_createBucket_withExistingParams_clonesWithNewBucket(): void
    {
        $mock = $this->makeMock(['Location' => '/overridden-bucket']);

        $params = new AwsS3BucketCreateParams('original-bucket');

        $result = AwsS3BucketHelper::createBucket($this->makeS3Client($mock), 'overridden-bucket', $params);

        $this->assertInstanceOf(AwsS3BucketCreateResult::class, $result);
    }

    public function test_createBucket_propagatesAwsException(): void
    {
        $mock = new MockHandler();
        $mock->append(function (CommandInterface $cmd) {
            return new S3Exception('BucketAlreadyExists', $cmd);
        });

        $this->expectException(S3Exception::class);

        AwsS3BucketHelper::createBucket($this->makeS3Client($mock), 'duplicate-bucket');
    }

    // -------------------------------------------------------------------------
    // deleteBucket
    // -------------------------------------------------------------------------

    public function test_deleteBucket_returnsTrue(): void
    {
        $mock = $this->makeMock(['@metadata' => ['statusCode' => 204]]);

        $result = AwsS3BucketHelper::deleteBucket($this->makeS3Client($mock), 'bucket-to-delete');

        $this->assertTrue($result);
    }

    public function test_deleteBucket_withExpectedOwner_returnsTrue(): void
    {
        $mock = $this->makeMock(['@metadata' => ['statusCode' => 204]]);

        $result = AwsS3BucketHelper::deleteBucket(
            $this->makeS3Client($mock),
            'bucket-to-delete',
            'owner-id-123'
        );

        $this->assertTrue($result);
    }

    public function test_deleteBucket_withNullOwner_doesNotThrow(): void
    {
        $mock = $this->makeMock(['@metadata' => ['statusCode' => 204]]);

        $result = AwsS3BucketHelper::deleteBucket($this->makeS3Client($mock), 'bucket-to-delete', null);

        $this->assertTrue($result);
    }

    public function test_deleteBucket_propagatesAwsException(): void
    {
        $mock = new MockHandler();
        $mock->append(function (CommandInterface $cmd) {
            return new S3Exception('NoSuchBucket', $cmd);
        });

        $this->expectException(S3Exception::class);

        AwsS3BucketHelper::deleteBucket($this->makeS3Client($mock), 'non-existent-bucket');
    }
}