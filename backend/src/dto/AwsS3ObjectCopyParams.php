<?php
namespace App\Dto;

use App\Traits\CloneWithProps;
use App\Traits\NotNullArrayItems;

final readonly class AwsS3ObjectCopyParams implements \JsonSerializable
{
    use CloneWithProps;
    use NotNullArrayItems;

    public function __construct(
        /*
            [
                'Bucket' => '<string>', // REQUIRED  -- destination bucket
                'Key' => '<string>', // REQUIRED     -- destination key
                'CopySource' => '<string>', // REQUIRED  -- source "{bucket}/{key}" path

                'ACL' => 'private|public-read|public-read-write|authenticated-read|aws-exec-read|bucket-owner-read|bucket-owner-full-control',
                'BucketKeyEnabled' => true || false,
                'CacheControl' => '<string>',
                'ChecksumAlgorithm' => 'CRC32|CRC32C|SHA1|SHA256|CRC64NVME',
                'ContentDisposition' => '<string>',
                'ContentEncoding' => '<string>',
                'ContentLanguage' => '<string>',
                'ContentType' => '<string>',
                'CopySourceIfMatch' => '<string>',
                'CopySourceIfModifiedSince' => <integer || string || DateTime>,
                'CopySourceIfNoneMatch' => '<string>',
                'CopySourceIfUnmodifiedSince' => <integer || string || DateTime>,
                'CopySourceSSECustomerAlgorithm' => '<string>',
                'CopySourceSSECustomerKey' => '<string>',
                'CopySourceSSECustomerKeyMD5' => '<string>',
                'ExpectedBucketOwner' => '<string>',
                'ExpectedSourceBucketOwner' => '<string>',
                'Expires' => <integer || string || DateTime>,
                'GrantFullControl' => '<string>',
                'GrantRead' => '<string>',
                'GrantReadACP' => '<string>',
                'GrantWriteACP' => '<string>',
                'IfMatch' => '<string>',
                'IfNoneMatch' => '<string>',
                'Metadata' => ['<string>', ...],
                'MetadataDirective' => 'COPY|REPLACE',
                'ObjectLockLegalHoldStatus' => 'ON|OFF',
                'ObjectLockMode' => 'GOVERNANCE|COMPLIANCE',
                'ObjectLockRetainUntilDate' => <integer || string || DateTime>,
                'RequestPayer' => 'requester',
                'SSECustomerAlgorithm' => '<string>',
                'SSECustomerKey' => '<string>',
                'SSECustomerKeyMD5' => '<string>',
                'SSEKMSEncryptionContext' => '<string>',
                'SSEKMSKeyId' => '<string>',
                'ServerSideEncryption' => 'AES256|aws:fsx|aws:kms|aws:kms:dsse',
                'StorageClass' => 'STANDARD|REDUCED_REDUNDANCY|STANDARD_IA|ONEZONE_IA|INTELLIGENT_TIERING|GLACIER|DEEP_ARCHIVE|OUTPOSTS|GLACIER_IR|SNOW|EXPRESS_ONEZONE|FSX_OPENZFS|FSX_ONTAP',
                'Tagging' => '<string>',
                'TaggingDirective' => 'COPY|REPLACE',
                'WebsiteRedirectLocation' => '<string>',
            ]
        */

        public string $bucket,                                 // 'Bucket' => '<string>', // REQUIRED
        public string $key,                                    // 'Key' => '<string>', // REQUIRED
        public string $copySource,                             // 'CopySource' => '<string>', // REQUIRED
        public ?string $aCL = null,                            // 'ACL' => 'private|public-read|public-read-write|authenticated-read|aws-exec-read|bucket-owner-read|bucket-owner-full-control',
        public ?string $bucketKeyEnabled = null,               // 'BucketKeyEnabled' => true || false,
        public ?string $cacheControl = null,                   // 'CacheControl' => '<string>',
        public ?string $checksumAlgorithm = null,              // 'ChecksumAlgorithm' => 'CRC32|CRC32C|SHA1|SHA256|CRC64NVME',
        public ?string $contentDisposition = null,             // 'ContentDisposition' => '<string>',
        public ?string $contentEncoding = null,                // 'ContentEncoding' => '<string>',
        public ?string $contentLanguage = null,                // 'ContentLanguage' => '<string>',
        public ?string $contentType = null,                    // 'ContentType' => '<string>',
        public ?string $copySourceIfMatch = null,              // 'CopySourceIfMatch' => '<string>',
        public ?string $copySourceIfModifiedSince = null,      // 'CopySourceIfModifiedSince' => <integer || string || DateTime>,
        public ?string $copySourceIfNoneMatch = null,          // 'CopySourceIfNoneMatch' => '<string>',
        public ?string $copySourceIfUnmodifiedSince = null,    // 'CopySourceIfUnmodifiedSince' => <integer || string || DateTime>,
        public ?string $copySourceSSECustomerAlgorithm = null, // 'CopySourceSSECustomerAlgorithm' => '<string>',
        public ?string $copySourceSSECustomerKey = null,       // 'CopySourceSSECustomerKey' => '<string>',
        public ?string $copySourceSSECustomerKeyMD5 = null,    // 'CopySourceSSECustomerKeyMD5' => '<string>',
        public ?string $expectedBucketOwner = null,            // 'ExpectedBucketOwner' => '<string>',
        public ?string $expectedSourceBucketOwner = null,      // 'ExpectedSourceBucketOwner' => '<string>',
        public ?string $expires = null,                        // 'Expires' => <integer || string || DateTime>,
        public ?string $grantFullControl = null,               // 'GrantFullControl' => '<string>',
        public ?string $grantRead = null,                      // 'GrantRead' => '<string>',
        public ?string $grantReadACP = null,                   // 'GrantReadACP' => '<string>',
        public ?string $grantWriteACP = null,                  // 'GrantWriteACP' => '<string>',
        public ?string $ifMatch = null,                        // 'IfMatch' => '<string>',
        public ?string $ifNoneMatch = null,                    // 'IfNoneMatch' => '<string>',
        public ?string $metadata = null,                       // 'Metadata' => ['<string>', ...],
        public ?string $metadataDirective = null,              // 'MetadataDirective' => 'COPY|REPLACE',
        public ?string $objectLockLegalHoldStatus = null,      // 'ObjectLockLegalHoldStatus' => 'ON|OFF',
        public ?string $objectLockMode = null,                 // 'ObjectLockMode' => 'GOVERNANCE|COMPLIANCE',
        public ?string $objectLockRetainUntilDate = null,      // 'ObjectLockRetainUntilDate' => <integer || string || DateTime>,
        public ?string $requestPayer = null,                   // 'RequestPayer' => 'requester',
        public ?string $sSECustomerAlgorithm = null,           // 'SSECustomerAlgorithm' => '<string>',
        public ?string $sSECustomerKey = null,                 // 'SSECustomerKey' => '<string>',
        public ?string $sSECustomerKeyMD5 = null,              // 'SSECustomerKeyMD5' => '<string>',
        public ?string $sSEKMSEncryptionContext = null,        // 'SSEKMSEncryptionContext' => '<string>',
        public ?string $sSEKMSKeyId = null,                    // 'SSEKMSKeyId' => '<string>',
        public ?string $serverSideEncryption = null,           // 'ServerSideEncryption' => 'AES256|aws:fsx|aws:kms|aws:kms:dsse',
        public ?string $storageClass = null,                   // 'StorageClass' => 'STANDARD|REDUCED_REDUNDANCY|STANDARD_IA|ONEZONE_IA|INTELLIGENT_TIERING|GLACIER|DEEP_ARCHIVE|OUTPOSTS|GLACIER_IR|SNOW|EXPRESS_ONEZONE|FSX_OPENZFS|FSX_ONTAP',
        public ?string $tagging = null,                        // 'Tagging' => '<string>',
        public ?string $taggingDirective = null,               // 'TaggingDirective' => 'COPY|REPLACE',
        public ?string $websiteRedirectLocation = null,        // 'WebsiteRedirectLocation' => '<string>',

    ) {}

    public function cloneWithNewBucketKeyCopySource(string $bucketName, string $objectKey, string $copySource): self
    {
        return self::cloneWithProps($this, [
            'bucket'     => $bucketName,
            'key'        => $objectKey,
            'copySource' => $copySource]);
    }

    public static function fromAwsFormat(array $object): self
    {
        return new self(
            bucket: $object['Bucket'],
            key: $object['Key'],
            copySource: $object['CopySource'],
            aCL: $object['ACL'],
            bucketKeyEnabled: $object['BucketKeyEnabled'],
            cacheControl: $object['CacheControl'],
            checksumAlgorithm: $object['ChecksumAlgorithm'],
            contentDisposition: $object['ContentDisposition'],
            contentEncoding: $object['ContentEncoding'],
            contentLanguage: $object['ContentLanguage'],
            contentType: $object['ContentType'],
            copySourceIfMatch: $object['CopySourceIfMatch'],
            copySourceIfModifiedSince: $object['CopySourceIfModifiedSince'],
            copySourceIfNoneMatch: $object['CopySourceIfNoneMatch'],
            copySourceIfUnmodifiedSince: $object['CopySourceIfUnmodifiedSince'],
            copySourceSSECustomerAlgorithm: $object['CopySourceSSECustomerAlgorithm'],
            copySourceSSECustomerKey: $object['CopySourceSSECustomerKey'],
            copySourceSSECustomerKeyMD5: $object['CopySourceSSECustomerKeyMD5'],
            expectedBucketOwner: $object['ExpectedBucketOwner'],
            expectedSourceBucketOwner: $object['ExpectedSourceBucketOwner'],
            expires: $object['Expires'],
            grantFullControl: $object['GrantFullControl'],
            grantRead: $object['GrantRead'],
            grantReadACP: $object['GrantReadACP'],
            grantWriteACP: $object['GrantWriteACP'],
            ifMatch: $object['IfMatch'],
            ifNoneMatch: $object['IfNoneMatch'],
            metadata: $object['Metadata'],
            metadataDirective: $object['MetadataDirective'],
            objectLockLegalHoldStatus: $object['ObjectLockLegalHoldStatus'],
            objectLockMode: $object['ObjectLockMode'],
            objectLockRetainUntilDate: $object['ObjectLockRetainUntilDate'],
            requestPayer: $object['RequestPayer'],
            sSECustomerAlgorithm: $object['SSECustomerAlgorithm'],
            sSECustomerKey: $object['SSECustomerKey'],
            sSECustomerKeyMD5: $object['SSECustomerKeyMD5'],
            sSEKMSEncryptionContext: $object['SSEKMSEncryptionContext'],
            sSEKMSKeyId: $object['SSEKMSKeyId'],
            serverSideEncryption: $object['ServerSideEncryption'],
            storageClass: $object['StorageClass'],
            tagging: $object['Tagging'],
            taggingDirective: $object['TaggingDirective'],
            websiteRedirectLocation: $object['WebsiteRedirectLocation'],
        );
    }

    public function toAwsFormat(): array
    {
        $resIn = [
            'Bucket'                         => $this->bucket,
            'Key'                            => $this->key,
            'CopySource'                     => $this->copySource,
            'ACL'                            => $this->aCL,
            'BucketKeyEnabled'               => $this->bucketKeyEnabled,
            'CacheControl'                   => $this->cacheControl,
            'ChecksumAlgorithm'              => $this->checksumAlgorithm,
            'ContentDisposition'             => $this->contentDisposition,
            'ContentEncoding'                => $this->contentEncoding,
            'ContentLanguage'                => $this->contentLanguage,
            'ContentType'                    => $this->contentType,
            'CopySourceIfMatch'              => $this->copySourceIfMatch,
            'CopySourceIfModifiedSince'      => $this->copySourceIfModifiedSince,
            'CopySourceIfNoneMatch'          => $this->copySourceIfNoneMatch,
            'CopySourceIfUnmodifiedSince'    => $this->copySourceIfUnmodifiedSince,
            'CopySourceSSECustomerAlgorithm' => $this->copySourceSSECustomerAlgorithm,
            'CopySourceSSECustomerKey'       => $this->copySourceSSECustomerKey,
            'CopySourceSSECustomerKeyMD5'    => $this->copySourceSSECustomerKeyMD5,
            'ExpectedBucketOwner'            => $this->expectedBucketOwner,
            'ExpectedSourceBucketOwner'      => $this->expectedSourceBucketOwner,
            'Expires'                        => $this->expires,
            'GrantFullControl'               => $this->grantFullControl,
            'GrantRead'                      => $this->grantRead,
            'GrantReadACP'                   => $this->grantReadACP,
            'GrantWriteACP'                  => $this->grantWriteACP,
            'IfMatch'                        => $this->ifMatch,
            'IfNoneMatch'                    => $this->ifNoneMatch,
            'Metadata'                       => $this->metadata,
            'MetadataDirective'              => $this->metadataDirective,
            'ObjectLockLegalHoldStatus'      => $this->objectLockLegalHoldStatus,
            'ObjectLockMode'                 => $this->objectLockMode,
            'ObjectLockRetainUntilDate'      => $this->objectLockRetainUntilDate,
            'RequestPayer'                   => $this->requestPayer,
            'SSECustomerAlgorithm'           => $this->sSECustomerAlgorithm,
            'SSECustomerKey'                 => $this->sSECustomerKey,
            'SSECustomerKeyMD5'              => $this->sSECustomerKeyMD5,
            'SSEKMSEncryptionContext'        => $this->sSEKMSEncryptionContext,
            'SSEKMSKeyId'                    => $this->sSEKMSKeyId,
            'ServerSideEncryption'           => $this->serverSideEncryption,
            'StorageClass'                   => $this->storageClass,
            'Tagging'                        => $this->tagging,
            'TaggingDirective'               => $this->taggingDirective,
            'WebsiteRedirectLocation'        => $this->websiteRedirectLocation,
        ];

        return $this->getNotNullArrayItemsOnly($resIn);
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
