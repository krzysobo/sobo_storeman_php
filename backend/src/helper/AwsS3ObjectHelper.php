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
use App\Dto\AwsS3ObjectPutParams;
use App\Dto\AwsS3ObjectPutResult;
use App\Dto\AwsS3ObjectRenameParams;
use App\Dto\AwsS3ObjectsDeleteParams;
use App\Dto\AwsS3ObjectsDeleteResult;
use App\Exceptions\FileNotFoundException;
use App\Exceptions\InvalidDataException;
use Aws\S3\S3Client;
use Psr\Http\Message\StreamInterface;
use resource;

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

    public static function getObjectToFilePath(
        S3Client $s3Client,
        string $bucketName,
        string $objectKey,
        string $filePath,
        ?AwsS3ObjectGetParams $params = null
    ): AwsS3ObjectGetResult {
        $dirName = dirname($filePath);
        mkdir($dirName, 0777, recursive: true);

        $params = ($params === null) ? new AwsS3ObjectGetParams($bucketName, $objectKey)
            : $params->cloneWithNewBucketKeyAndSaveAs($bucketName, $objectKey, $filePath);
        $args = $params->toAwsFormat();

        $result = $s3Client->getObject($args);

        return AwsS3ObjectGetResult::fromAwsFormat($result);
    }

    public static function putObjectFromFilePath(
        S3Client $s3Client,
        string $bucketName,
        string $objectKey,
        string $filePath,
        ?AwsS3ObjectPutParams $params = null
    ): AwsS3ObjectPutResult {
        // all params to use: https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#putobject
        if (! file_exists($filePath)) {
            throw new FileNotFoundException("File $filePath not found");
        }

        $params = ($params === null) ? new AwsS3ObjectPutParams($bucketName, $objectKey, sourceFile : $filePath)
            : $params->cloneWithNewBucketKeySourceFile($bucketName, $objectKey, $filePath);
        $args = $params->toAwsFormat();

        $result = $s3Client->putObject($args);

        return AwsS3ObjectPutResult::fromAwsFormat($result);
    }

    /**
     * Summary of putObjectFromFileBody
     * @param S3Client $s3Client
     * @param string $bucketName
     * @param string $objectKey
     * @param string|resource|StreamInterface $fileBody
     * @param mixed $params
     * @throws InvalidDataException
     * @return AwsS3ObjectDeleteResult
     */
    public static function putObjectFromFileBody(
        S3Client $s3Client,
        string $bucketName,
        string $objectKey,
        mixed $fileBody,
        ?AwsS3ObjectPutParams $params = null
    ): AwsS3ObjectPutResult {
        // all params to use: https://docs.aws.amazon.com/aws-sdk-php/v3/api/api-s3-2006-03-01.html#putobject
        if (empty($fileBody)) {
            throw new InvalidDataException("File body may not be empty.");
        }

        $params = ($params === null) ? new AwsS3ObjectPutParams($bucketName, $objectKey, body : $fileBody)
            : $params->cloneWithNewBucketKeyBody($bucketName, $objectKey, $fileBody);
        $args = $params->toAwsFormat();

        $result = $s3Client->putObject($args);

        return AwsS3ObjectPutResult::fromAwsFormat($result);
    }

    // NOT IMPLEMENTED YET, though added to documentation. Keeping, but not using.
    public static function renameObject(
        S3Client $s3Client,
        string $bucketName,
        string $objectKeySrc,
        string $objectKeyDst,
        ?AwsS3ObjectRenameParams $params = null
    ): bool {
        $renameSource = "{$bucketName}/{$objectKeySrc}";

        $params = ($params === null) ? new AwsS3ObjectRenameParams(
            $bucketName,
            $objectKeyDst,
            $renameSource) : $params->cloneWithNewBucketKeyRenameSource(
            $bucketName,
            $objectKeyDst,
            $renameSource);

        $s3Client->renameObject($params->toAwsFormat());

        return true; // result of renameObject always returns []
    }

    public static function copyObject(
        S3Client $s3Client,
        string $bucketNameSrc,
        string $objectKeySrc,
        string $bucketNameDst,
        string $objectKeyDst,
        ?AwsS3ObjectCopyParams $params = null
    ): AwsS3ObjectCopyResult {
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
