<?php
namespace App\Helper;

use App\Dto\AwsS3BucketCreateParams;
use App\Dto\AwsS3BucketCreateResult;
use App\Dto\AwsS3BucketDeleteParams;
use App\Dto\AwsS3BucketListParams;
use App\Dto\AwsS3BucketListResult;
use Aws\S3\S3Client;

class AwsS3BucketHelper
{
    public static function getBucketsList(S3Client $s3Client, $bucketRegion = null, $continuationToken = null, $maxBuckets = null, $prefix = null): AwsS3BucketListResult
    {
        // all params for listBuckets:
        // [
        //     'BucketRegion' => '<string>',
        //     'ContinuationToken' => '<string>',
        //     'MaxBuckets' => <integer>,
        //     'Prefix' => '<string>',
        // ]
        $params = new AwsS3BucketListParams(
            $bucketRegion,
            $continuationToken,
            $maxBuckets,
            $prefix);

        $result = $s3Client->listBuckets($params->toAwsFormat());

        // result format:
        /*
            [
                'Buckets' => [
                    [
                        'BucketArn' => '<string>',
                        'BucketRegion' => '<string>',
                        'CreationDate' => <DateTime>,
                        'Name' => '<string>',
                    ],
                    // ...
                ],
                'ContinuationToken' => '<string>',
                'Owner' => [
                    'DisplayName' => '<string>',
                    'ID' => '<string>',
                ],
                'Prefix' => '<string>',
            ]
            */
        $resultObj = AwsS3BucketListResult::fromAwsFormat($result);
        return $resultObj;
    }

    public static function createBucket(
        S3Client $s3Client,
        string $bucketName,
        ?AwsS3BucketCreateParams $params = null,
    ): AwsS3BucketCreateResult {
        $params   = ($params === null) ? new AwsS3BucketCreateParams($bucketName)
            : $params = $params->cloneWithNewBucket($bucketName);

        $result = $s3Client->createBucket($params->toAwsFormat());

        return AwsS3BucketCreateResult::fromAwsFormat($result);
    }

    public static function deleteBucket(
        S3Client $s3Client,
        string $bucketName,
        ?string $expectedBucketOwner = null,
    ): bool {
        $params = new AwsS3BucketDeleteParams($bucketName, $expectedBucketOwner);
        $s3Client->deleteBucket($params->toAwsFormat());

        return true;
    }
}
