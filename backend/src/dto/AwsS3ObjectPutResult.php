<?php
namespace App\Dto;

use App\Traits\NotNullArrayItems;

final readonly class AwsS3ObjectPutResult implements \JsonSerializable
{
    use NotNullArrayItems;

    public function __construct(
        /*
            [
                'BucketKeyEnabled' => true || false,
                'ChecksumCRC32' => '<string>',
                'ChecksumCRC32C' => '<string>',
                'ChecksumCRC64NVME' => '<string>',
                'ChecksumSHA1' => '<string>',
                'ChecksumSHA256' => '<string>',
                'ChecksumType' => 'COMPOSITE|FULL_OBJECT',
                'ETag' => '<string>',
                'Expiration' => '<string>',
                'ObjectURL' => '<string>',
                'RequestCharged' => 'requester',
                'SSECustomerAlgorithm' => '<string>',
                'SSECustomerKeyMD5' => '<string>',
                'SSEKMSEncryptionContext' => '<string>',
                'SSEKMSKeyId' => '<string>',
                'ServerSideEncryption' => 'AES256|aws:fsx|aws:kms|aws:kms:dsse',
                'Size' => <integer>,
                'VersionId' => '<string>',
            ]
        */

        public bool $bucketKeyEnabled,          // 'BucketKeyEnabled' => true || false,
        public string $checksumCRC32,           // 'ChecksumCRC32' => '<string>',
        public string $checksumCRC32C,          // 'ChecksumCRC32C' => '<string>',
        public string $checksumCRC64NVME,       // 'ChecksumCRC64NVME' => '<string>',
        public string $checksumSHA1,            // 'ChecksumSHA1' => '<string>',
        public string $checksumSHA256,          // 'ChecksumSHA256' => '<string>',
        public string $checksumType,            // 'ChecksumType' => 'COMPOSITE|FULL_OBJECT',
        public string $eTag,                    // 'ETag' => '<string>',
        public string $expiration,              // 'Expiration' => '<string>',
        public string $objectURL,               // 'ObjectURL' => '<string>',
        public string $requestCharged,          // 'RequestCharged' => 'requester',
        public string $sSECustomerAlgorithm,    // 'SSECustomerAlgorithm' => '<string>',
        public string $sSECustomerKeyMD5,       // 'SSECustomerKeyMD5' => '<string>',
        public string $sSEKMSEncryptionContext, // 'SSEKMSEncryptionContext' => '<string>',
        public string $sSEKMSKeyId,             // 'SSEKMSKeyId' => '<string>',
        public string $serverSideEncryption,    // 'ServerSideEncryption' => 'AES256|aws:fsx|aws:kms|aws:kms:dsse',
        public int $size,                       // 'Size' => <integer>,
        public string $versionId,               // 'VersionId' => '<string>',
    ) {}

    public static function fromAwsFormat(\Aws\Result  | array $object): self
    {
        return new self(
            bucketKeyEnabled: $object['BucketKeyEnabled'],
            checksumCRC32: $object['ChecksumCRC32'],
            checksumCRC32C: $object['ChecksumCRC32C'],
            checksumCRC64NVME: $object['ChecksumCRC64NVME'],
            checksumSHA1: $object['ChecksumSHA1'],
            checksumSHA256: $object['ChecksumSHA256'],
            checksumType: $object['ChecksumType'],
            eTag: $object['ETag'],
            expiration: $object['Expiration'],
            objectURL: $object['ObjectURL'],
            requestCharged: $object['RequestCharged'],
            sSECustomerAlgorithm: $object['SSECustomerAlgorithm'],
            sSECustomerKeyMD5: $object['SSECustomerKeyMD5'],
            sSEKMSEncryptionContext: $object['SSEKMSEncryptionContext'],
            sSEKMSKeyId: $object['SSEKMSKeyId'],
            serverSideEncryption: $object['ServerSideEncryption'],
            size: $object['Size'],
            versionId: $object['VersionId'],
        );
    }

    public function toAwsFormat(): array
    {
        return [
            'BucketKeyEnabled'        => $this->bucketKeyEnabled,
            'ChecksumCRC32'           => $this->checksumCRC32,
            'ChecksumCRC32C'          => $this->checksumCRC32C,
            'ChecksumCRC64NVME'       => $this->checksumCRC64NVME,
            'ChecksumSHA1'            => $this->checksumSHA1,
            'ChecksumSHA256'          => $this->checksumSHA256,
            'ChecksumType'            => $this->checksumType,
            'ETag'                    => $this->eTag,
            'Expiration'              => $this->expiration,
            'ObjectURL'               => $this->objectURL,
            'RequestCharged'          => $this->requestCharged,
            'SSECustomerAlgorithm'    => $this->sSECustomerAlgorithm,
            'SSECustomerKeyMD5'       => $this->sSECustomerKeyMD5,
            'SSEKMSEncryptionContext' => $this->sSEKMSEncryptionContext,
            'SSEKMSKeyId'             => $this->sSEKMSKeyId,
            'ServerSideEncryption'    => $this->serverSideEncryption,
            'Size'                    => $this->size,
            'VersionId'               => $this->versionId,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
