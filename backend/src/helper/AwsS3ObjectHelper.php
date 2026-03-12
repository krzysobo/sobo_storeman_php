<?php
namespace App\Helper;

use App\Dto\AwsS3ListObjectsResult;
use App\Dto\AwsS3ObjectCopyParams;
use App\Dto\AwsS3ObjectCopyResult;
use App\Dto\AwsS3ObjectDeleteParams;
use App\Dto\AwsS3ObjectDeleteResult;
use App\Dto\AwsS3ObjectGetParams;
use App\Dto\AwsS3ObjectGetResult;
use App\Dto\AwsS3ObjectListParams;
use App\Dto\AwsS3ObjectsDeleteParams;
use App\Dto\AwsS3ObjectsDeleteResult;
use Aws\S3\S3Client;

class AwsS3ObjectHelper
{
    public static function getObjectsForBucket(
        S3Client $s3Client,
        string $bucketName,
        ?AwsS3ObjectListParams $params = null
    ): AwsS3ListObjectsResult {
        $params = ($params === null) ? new AwsS3ObjectListParams($bucketName)
            : $params->cloneWithNewBucket($bucketName);
        $args = $params->toAwsFormat();

        $result = $s3Client->listObjectsV2($args);

        return AwsS3ListObjectsResult::fromAwsFormat($result);
    }

    public static function getObject(
        S3Client $s3Client,
        string $bucketName,
        string $objectKey,
        ?AwsS3ObjectGetParams $params = null
    ): AwsS3ObjectGetResult {
        $params = ($params === null) ? new AwsS3ObjectGetParams($bucketName, $objectKey)
            : $params->cloneWithNewBucketAndKey($bucketName, $objectKey);
        $args = $params->toAwsFormat();

        $result = $s3Client->getObject($args);

        return AwsS3ObjectGetResult::fromAwsFormat($result);
    }

    public static function putObjectFromFilePath(S3Client $s3Client, string $bucketName, string $objectKey, string $filePath)
    {
        // TODO
        // all params to get (we only use Bucket and Key) https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#putobject

    }

    public static function putObjectFromFileData(S3Client $s3Client, string $bucketName, string $objectKey, string $fileData)
    {
        // TODO
        // all params to get (we only use Bucket and Key) https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#putobject
    }

    public static function copyObject(
        S3Client $s3Client,
        string $bucketNameSrc,
        string $objectKeySrc,
        string $bucketNameDst,
        string $objectKeyDst,
        ?AwsS3ObjectCopyParams $params = null
    ) {
        $copySource = "{$bucketNameSrc}/{$objectKeySrc}";

        $params = ($params === null) ? new AwsS3ObjectCopyParams(
            $bucketNameDst,
            $objectKeyDst,
            $copySource) : $params->cloneWithNewBucketKeyCopySource(
            $bucketNameDst,
            $objectKeyDst,
            $copySource);

        $result = $s3Client->copyObject([
            'Bucket'     => $bucketNameDst,
            'Key'        => $objectKeyDst,
            'CopySource' => "{$bucketNameSrc}/{$objectKeySrc}",
        ]);

        return AwsS3ObjectCopyResult::fromAwsFormat($result);
    }

    public static function deleteObject(
        S3Client $s3Client,
        string $bucketName,
        string $objectKey,
        ?AwsS3ObjectDeleteParams $params = null
    ): AwsS3ObjectDeleteResult {
        $params = ($params === null) ? new AwsS3ObjectDeleteParams($bucketName, $objectKey)
            : $params->cloneWithNewBucketAndKey($bucketName, $objectKey);
        $args = $params->toAwsFormat();

        $result = $s3Client->deleteObject($args);

        return AwsS3ObjectDeleteResult::fromAwsFormat($result);
    }

    public static function deleteMultipleObjects(
        S3Client $s3Client,
        string $bucketName,
        array $objectKeysIn,
        ?AwsS3ObjectsDeleteParams $params = null
    ): AwsS3ObjectsDeleteResult {
        $params = ($params === null) ? AwsS3ObjectsDeleteParams::createForBucketAndKeys(
            $bucketName,
            $objectKeysIn)
            : $params->cloneWithNewBucketAndKeys(
            $bucketName,
            $objectKeysIn);
        $args = $params->toAwsFormat();

        $result = $s3Client->deleteObjects($args);

        return AwsS3ObjectsDeleteResult::fromAwsFormat($result);
    }

}
