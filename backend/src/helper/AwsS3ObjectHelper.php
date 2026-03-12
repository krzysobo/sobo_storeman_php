<?php
namespace App\Helper;

use App\Dto\AwsS3ListObjectsResult;
use App\Dto\AwsS3ObjectDeleteParams;
use App\Dto\AwsS3ObjectDeleteResult;
use App\Dto\AwsS3ObjectListParams;
use Aws\S3\S3Client;

class AwsS3ObjectHelper
{
    public static function getObjectsForBucket(
        S3Client $s3Client,
        string $bucketName,
        ?AwsS3ObjectListParams $params = null
    ): AwsS3ListObjectsResult {
        $params   = ($params === null) ? new AwsS3ObjectListParams($bucketName)
            : $params = $params->cloneWithNewBucket($bucketName);
        $args     = $params->toAwsFormat();
        $result   = $s3Client->listObjectsV2($args);

        return AwsS3ListObjectsResult::fromAwsFormat($result);
    }

    public static function getObject(S3Client $s3Client, string $bucketName, string $objectKey)
    {
        // TODO
        // all params to get (we only use Bucket and Key) https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#getobject

    }

    public static function deleteObject(
        S3Client $s3Client,
        string $bucketName,
        string $objectKey,
        ?AwsS3ObjectDeleteParams $params = null
    ) {
        $params   = ($params === null) ? new AwsS3ObjectDeleteParams($bucketName, $objectKey)
            : $params = $params->cloneWithNewBucketAndKey($bucketName, $objectKey);
        $args     = $params->toAwsFormat();
        $result   = $s3Client->deleteObject($args);

        return AwsS3ObjectDeleteResult::fromAwsFormat($result);
    }

    public static function deleteMultipleObjects(S3Client $s3Client, string $bucketName, array $objectKeysIn)
    {
        $objectKeysOut = [];
        foreach ($objectKeysIn as $key) {
            $objectKeysOut[] = ['Key' => $key];
        }

        $s3Client->deleteObjects([
            'Bucket' => $bucketName,
            'Delete' => [
                'Objects' => $objectKeysOut,
            ],
        ]);
    }

}
