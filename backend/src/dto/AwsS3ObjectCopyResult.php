<?php
namespace App\Dto;

use App\Traits\NotNullArrayItems;

final readonly class AwsS3ObjectCopyResult implements \JsonSerializable
{
    use NotNullArrayItems;

    public function __construct(
        /*
            [
                'BucketKeyEnabled' => true || false,
                'CopyObjectResult' => [
                    'ChecksumCRC32' => '<string>',
                    'ChecksumCRC32C' => '<string>',
                    'ChecksumCRC64NVME' => '<string>',
                    'ChecksumSHA1' => '<string>',
                    'ChecksumSHA256' => '<string>',
                    'ChecksumType' => 'COMPOSITE|FULL_OBJECT',
                    'ETag' => '<string>',
                    'LastModified' => <DateTime>,
                ],
                'CopySourceVersionId' => '<string>',
                'Expiration' => '<string>',
                'ObjectURL' => '<string>',
                'RequestCharged' => 'requester',
                'SSECustomerAlgorithm' => '<string>',
                'SSECustomerKeyMD5' => '<string>',
                'SSEKMSEncryptionContext' => '<string>',
                'SSEKMSKeyId' => '<string>',
                'ServerSideEncryption' => 'AES256|aws:fsx|aws:kms|aws:kms:dsse',
                'VersionId' => '<string>',
            ]
        */
        public ?bool $bucketKeyEnabled,                    // 'BucketKeyEnabled' => true || false,
                                                          // 'CopyObjectResult' => [
        public ?string $copyObjectResultChecksumCRC32,     //     'ChecksumCRC32' => '<string>',
        public ?string $copyObjectResultChecksumCRC32C,    //     'ChecksumCRC32C' => '<string>',
        public ?string $copyObjectResultChecksumCRC64NVME, //     'ChecksumCRC64NVME' => '<string>',
        public ?string $copyObjectResultChecksumSHA1,      //     'ChecksumSHA1' => '<string>',
        public ?string $copyObjectResultChecksumSHA256,    //     'ChecksumSHA256' => '<string>',
        public ?string $copyObjectResultChecksumType,      //     'ChecksumType' => 'COMPOSITE|FULL_OBJECT',
        public ?string $copyObjectResultETag,              //     'ETag' => '<string>',
        public ?\DateTime $copyObjectResultLastModified,   //     'LastModified' => <DateTime>, ]
        public ?string $copySourceVersionId,               // 'CopySourceVersionId' => '<string>',
        public ?string $expiration,                        // 'Expiration' => '<string>',
        public ?string $objectURL,                         // 'ObjectURL' => '<string>',
        public ?string $requestCharged,                    // 'RequestCharged' => 'requester',
        public ?string $sSECustomerAlgorithm,              // 'SSECustomerAlgorithm' => '<string>',
        public ?string $sSECustomerKeyMD5,                 // 'SSECustomerKeyMD5' => '<string>',
        public ?string $sSEKMSEncryptionContext,           // 'SSEKMSEncryptionContext' => '<string>',
        public ?string $sSEKMSKeyId,                       // 'SSEKMSKeyId' => '<string>',
        public ?string $serverSideEncryption,              // 'ServerSideEncryption' => 'AES256|aws:fsx|aws:kms|aws:kms:dsse',
        public ?string $versionId,                         // 'VersionId' => '<string>',
    ) {}

    public static function fromAwsFormat(\Aws\Result  | array $object): self
    {
        return new self(
            bucketKeyEnabled: $object['BucketKeyEnabled'],
            copyObjectResultChecksumCRC32: $object['CopyObjectResult']['ChecksumCRC32'],
            copyObjectResultChecksumCRC32C: $object['CopyObjectResult']['ChecksumCRC32C'],
            copyObjectResultChecksumCRC64NVME: $object['CopyObjectResult']['ChecksumCRC64NVME'],
            copyObjectResultChecksumSHA1: $object['CopyObjectResult']['ChecksumSHA1'],
            copyObjectResultChecksumSHA256: $object['CopyObjectResult']['ChecksumSHA256'],
            copyObjectResultChecksumType: $object['CopyObjectResult']['ChecksumType'],
            copyObjectResultETag: $object['CopyObjectResult']['ETag'],
            copyObjectResultLastModified: $object['CopyObjectResult']['LastModified'],
            copySourceVersionId: $object['CopySourceVersionId'],
            expiration: $object['Expiration'],
            objectURL: $object['ObjectURL'],
            requestCharged: $object['RequestCharged'],
            sSECustomerAlgorithm: $object['SSECustomerAlgorithm'],
            sSECustomerKeyMD5: $object['SSECustomerKeyMD5'],
            sSEKMSEncryptionContext: $object['SSEKMSEncryptionContext'],
            sSEKMSKeyId: $object['SSEKMSKeyId'],
            serverSideEncryption: $object['ServerSideEncryption'],
            versionId: $object['VersionId'],
        );
    }

    public function toAwsFormat(): array
    {
        return [
            'BucketKeyEnabled'        => $this->bucketKeyEnabled,
            'CopyObjectResult'        => [
                'ChecksumCRC32'     => $this->copyObjectResultChecksumCRC32,
                'ChecksumCRC32C'    => $this->copyObjectResultChecksumCRC32C,
                'ChecksumCRC64NVME' => $this->copyObjectResultChecksumCRC64NVME,
                'ChecksumSHA1'      => $this->copyObjectResultChecksumSHA1,
                'ChecksumSHA256'    => $this->copyObjectResultChecksumSHA256,
                'ChecksumType'      => $this->copyObjectResultChecksumType,
                'ETag'              => $this->copyObjectResultETag,
                'LastModified'      => $this->copyObjectResultLastModified,
            ],
            'CopySourceVersionId'     => $this->copySourceVersionId,
            'Expiration'              => $this->expiration,
            'ObjectURL'               => $this->objectURL,
            'RequestCharged'          => $this->requestCharged,
            'SSECustomerAlgorithm'    => $this->sSECustomerAlgorithm,
            'SSECustomerKeyMD5'       => $this->sSECustomerKeyMD5,
            'SSEKMSEncryptionContext' => $this->sSEKMSEncryptionContext,
            'SSEKMSKeyId'             => $this->sSEKMSKeyId,
            'ServerSideEncryption'    => $this->serverSideEncryption,
            'VersionId'               => $this->versionId,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
