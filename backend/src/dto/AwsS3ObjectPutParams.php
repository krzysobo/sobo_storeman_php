<?php
namespace App\Dto;

use App\Traits\CloneWithProps;
use App\Traits\NotNullArrayItems;

final readonly class AwsS3ObjectPutParams implements \JsonSerializable
{
    use CloneWithProps;
    use NotNullArrayItems;

    public function __construct(
        /*
        [
            'Bucket' => '<string>', // REQUIRED
            'Key' => '<string>', // REQUIRED
            'SourceFile' => '<string>',
                'ACL' => 'private|public-read|public-read-write|authenticated-read|aws-exec-read|bucket-owner-read|bucket-owner-full-control',
                'AddContentMD5' => true || false,
                'Body' => <string || resource || Psr\Http\Message\StreamInterface>,
                'BucketKeyEnabled' => true || false,
                'CacheControl' => '<string>',
                'ChecksumAlgorithm' => 'CRC32|CRC32C|SHA1|SHA256|CRC64NVME',
                'ChecksumCRC32' => '<string>',
                'ChecksumCRC32C' => '<string>',
                'ChecksumCRC64NVME' => '<string>',
                'ChecksumSHA1' => '<string>',
                'ChecksumSHA256' => '<string>',
                'ContentDisposition' => '<string>',
                'ContentEncoding' => '<string>',
                'ContentLanguage' => '<string>',
                'ContentLength' => <integer>,
                'ContentMD5' => '<string>',
                'ContentSHA256' => '<string>',
                'ContentType' => '<string>',
                'ExpectedBucketOwner' => '<string>',
                'Expires' => <integer || string || DateTime>,
                'GrantFullControl' => '<string>',
                'GrantRead' => '<string>',
                'GrantReadACP' => '<string>',
                'GrantWriteACP' => '<string>',
                'IfMatch' => '<string>',
                'IfNoneMatch' => '<string>',
                'Metadata' => ['<string>', ...],
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
                'WebsiteRedirectLocation' => '<string>',
                'WriteOffsetBytes' => <integer>,
                ]
                */

        public string $bucket,                            // 'Bucket' => '<string>', // REQUIRED
        public string $key,                               // 'Key' => '<string>', // REQUIRED
        public ?string $sourceFile = null,                // 'SourceFile' => '<string>',
        public ?string $aCL = null,                       // 'ACL' => 'private|public-read|public-read-write|authenticated-read|aws-exec-read|bucket-owner-read|bucket-owner-full-control',
        public ?string $addContentMD5 = null,             // 'AddContentMD5' => true || false,
        // public string|resource|\Psr\Http\Message\StreamInterface|null $body = null,
        public mixed $body = null,                      // 'Body' => <string || resource || Psr\Http\Message\StreamInterface>,
        public ?string $bucketKeyEnabled = null,          // 'BucketKeyEnabled' => true || false,
        public ?string $cacheControl = null,              // 'CacheControl' => '<string>',
        public ?string $checksumAlgorithm = null,         // 'ChecksumAlgorithm' => 'CRC32|CRC32C|SHA1|SHA256|CRC64NVME',
        public ?string $checksumCRC32 = null,             // 'ChecksumCRC32' => '<string>',
        public ?string $checksumCRC32C = null,            // 'ChecksumCRC32C' => '<string>',
        public ?string $checksumCRC64NVME = null,         // 'ChecksumCRC64NVME' => '<string>',
        public ?string $checksumSHA1 = null,              // 'ChecksumSHA1' => '<string>',
        public ?string $checksumSHA256 = null,            // 'ChecksumSHA256' => '<string>',
        public ?string $contentDisposition = null,        // 'ContentDisposition' => '<string>',
        public ?string $contentEncoding = null,           // 'ContentEncoding' => '<string>',
        public ?string $contentLanguage = null,           // 'ContentLanguage' => '<string>',
        public ?string $contentLength = null,             // 'ContentLength' => <integer>,
        public ?string $contentMD5 = null,                // 'ContentMD5' => '<string>',
        public ?string $contentSHA256 = null,             // 'ContentSHA256' => '<string>',
        public ?string $contentType = null,               // 'ContentType' => '<string>',
        public ?string $expectedBucketOwner = null,       // 'ExpectedBucketOwner' => '<string>',
        public ?string $expires = null,                   // 'Expires' => <integer || string || DateTime>,
        public ?string $grantFullControl = null,          // 'GrantFullControl' => '<string>',
        public ?string $grantRead = null,                 // 'GrantRead' => '<string>',
        public ?string $grantReadACP = null,              // 'GrantReadACP' => '<string>',
        public ?string $grantWriteACP = null,             // 'GrantWriteACP' => '<string>',
        public ?string $ifMatch = null,                   // 'IfMatch' => '<string>',
        public ?string $ifNoneMatch = null,               // 'IfNoneMatch' => '<string>',
        public ?string $metadata = null,                  // 'Metadata' => ['<string>', ...],
        public ?string $objectLockLegalHoldStatus = null, // 'ObjectLockLegalHoldStatus' => 'ON|OFF',
        public ?string $objectLockMode = null,            // 'ObjectLockMode' => 'GOVERNANCE|COMPLIANCE',
        public ?string $objectLockRetainUntilDate = null, // 'ObjectLockRetainUntilDate' => <integer || string || DateTime>,
        public ?string $requestPayer = null,              // 'RequestPayer' => 'requester',
        public ?string $sSECustomerAlgorithm = null,      // 'SSECustomerAlgorithm' => '<string>',
        public ?string $sSECustomerKey = null,            // 'SSECustomerKey' => '<string>',
        public ?string $sSECustomerKeyMD5 = null,         // 'SSECustomerKeyMD5' => '<string>',
        public ?string $sSEKMSEncryptionContext = null,   // 'SSEKMSEncryptionContext' => '<string>',
        public ?string $sSEKMSKeyId = null,               // 'SSEKMSKeyId' => '<string>',
        public ?string $serverSideEncryption = null,      // 'ServerSideEncryption' => 'AES256|aws:fsx|aws:kms|aws:kms:dsse',
        public ?string $storageClass = null,              // 'StorageClass' => 'STANDARD|REDUCED_REDUNDANCY|STANDARD_IA|ONEZONE_IA|INTELLIGENT_TIERING|GLACIER|DEEP_ARCHIVE|OUTPOSTS|GLACIER_IR|SNOW|EXPRESS_ONEZONE|FSX_OPENZFS|FSX_ONTAP',
        public ?string $tagging = null,                   // 'Tagging' => '<string>',
        public ?string $websiteRedirectLocation = null,   // 'WebsiteRedirectLocation' => '<string>',
        public ?string $writeOffsetBytes = null,          // 'WriteOffsetBytes' => <integer>,
    ) {}

    public function cloneWithNewBucketKey(string $bucketName, string $objectKey): self
    {
        return self::cloneWithProps($this, [
            'bucket' => $bucketName,
            'key'    => $objectKey,
        ]);
    }

    public function cloneWithNewBucketKeySourceFile(string $bucketName, string $objectKey, string $sourceFile): self
    {
        return self::cloneWithProps($this, [
            'bucket'     => $bucketName,
            'key'        => $objectKey,
            'sourceFile' => $sourceFile,
        ]);
    }
    public function cloneWithNewBucketKeyBody(string $bucketName, string $objectKey, string $body): self
    {
        return self::cloneWithProps($this, [
            'bucket' => $bucketName,
            'key'    => $objectKey,
            'body'   => $body,
        ]);
    }

    public function createForBucketKeySourceFile(string $bucketName, string $objectKey, string $sourceFile): self
    {
        return new self(
            bucket: $bucketName,
            key: $objectKey,
            sourceFile: $sourceFile,
        );
    }

    public static function fromAwsFormat(array $object): self
    {
        return new self(
            bucket: $object['Bucket'],
            key: $object['Key'],
            sourceFile: $object['SourceFile'],
            aCL: $object['ACL'],
            addContentMD5: $object['AddContentMD5'],
            body: $object['Body'],
            bucketKeyEnabled: $object['BucketKeyEnabled'],
            cacheControl: $object['CacheControl'],
            checksumAlgorithm: $object['ChecksumAlgorithm'],
            checksumCRC32: $object['ChecksumCRC32'],
            checksumCRC32C: $object['ChecksumCRC32C'],
            checksumCRC64NVME: $object['ChecksumCRC64NVME'],
            checksumSHA1: $object['ChecksumSHA1'],
            checksumSHA256: $object['ChecksumSHA256'],
            contentDisposition: $object['ContentDisposition'],
            contentEncoding: $object['ContentEncoding'],
            contentLanguage: $object['ContentLanguage'],
            contentLength: $object['ContentLength'],
            contentMD5: $object['ContentMD5'],
            contentSHA256: $object['ContentSHA256'],
            contentType: $object['ContentType'],
            expectedBucketOwner: $object['ExpectedBucketOwner'],
            expires: $object['Expires'],
            grantFullControl: $object['GrantFullControl'],
            grantRead: $object['GrantRead'],
            grantReadACP: $object['GrantReadACP'],
            grantWriteACP: $object['GrantWriteACP'],
            ifMatch: $object['IfMatch'],
            ifNoneMatch: $object['IfNoneMatch'],
            metadata: $object['Metadata'],
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
            websiteRedirectLocation: $object['WebsiteRedirectLocation'],
            writeOffsetBytes: $object['WriteOffsetBytes            '],
        );
    }

    public function toAwsFormat(): array
    {
        $resIn = [
            'Bucket'                    => $this->bucket,
            'Key'                       => $this->key,
            'ACL'                       => $this->aCL,
            'AddContentMD5'             => $this->addContentMD5,
            'Body'                      => $this->body,
            'BucketKeyEnabled'          => $this->bucketKeyEnabled,
            'CacheControl'              => $this->cacheControl,
            'ChecksumAlgorithm'         => $this->checksumAlgorithm,
            'ChecksumCRC32'             => $this->checksumCRC32,
            'ChecksumCRC32C'            => $this->checksumCRC32C,
            'ChecksumCRC64NVME'         => $this->checksumCRC64NVME,
            'ChecksumSHA1'              => $this->checksumSHA1,
            'ChecksumSHA256'            => $this->checksumSHA256,
            'ContentDisposition'        => $this->contentDisposition,
            'ContentEncoding'           => $this->contentEncoding,
            'ContentLanguage'           => $this->contentLanguage,
            'ContentLength'             => $this->contentLength,
            'ContentMD5'                => $this->contentMD5,
            'ContentSHA256'             => $this->contentSHA256,
            'ContentType'               => $this->contentType,
            'ExpectedBucketOwner'       => $this->expectedBucketOwner,
            'Expires'                   => $this->expires,
            'GrantFullControl'          => $this->grantFullControl,
            'GrantRead'                 => $this->grantRead,
            'GrantReadACP'              => $this->grantReadACP,
            'GrantWriteACP'             => $this->grantWriteACP,
            'IfMatch'                   => $this->ifMatch,
            'IfNoneMatch'               => $this->ifNoneMatch,
            'Metadata'                  => $this->metadata,
            'ObjectLockLegalHoldStatus' => $this->objectLockLegalHoldStatus,
            'ObjectLockMode'            => $this->objectLockMode,
            'ObjectLockRetainUntilDate' => $this->objectLockRetainUntilDate,
            'RequestPayer'              => $this->requestPayer,
            'SSECustomerAlgorithm'      => $this->sSECustomerAlgorithm,
            'SSECustomerKey'            => $this->sSECustomerKey,
            'SSECustomerKeyMD5'         => $this->sSECustomerKeyMD5,
            'SSEKMSEncryptionContext'   => $this->sSEKMSEncryptionContext,
            'SSEKMSKeyId'               => $this->sSEKMSKeyId,
            'ServerSideEncryption'      => $this->serverSideEncryption,
            'SourceFile'                => $this->sourceFile,
            'StorageClass'              => $this->storageClass,
            'Tagging'                   => $this->tagging,
            'WebsiteRedirectLocation'   => $this->websiteRedirectLocation,
            'WriteOffsetBytes'          => $this->writeOffsetBytes,
        ];

        return $this->getNotNullArrayItemsOnly($resIn);
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
