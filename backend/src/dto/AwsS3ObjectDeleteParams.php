<?php
namespace App\Dto;

use App\Traits\CloneWithProps;
use App\Traits\NotNullArrayItems;

final readonly class AwsS3ObjectDeleteParams implements \JsonSerializable
{
    use CloneWithProps;
    use NotNullArrayItems;

    public function __construct(
        /*
            [
                'Bucket' => '<string>', // REQUIRED
                'BypassGovernanceRetention' => true || false,
                'ExpectedBucketOwner' => '<string>',
                'IfMatch' => '<string>',
                'IfMatchLastModifiedTime' => <integer || string || DateTime>,
                'IfMatchSize' => <integer>,
                'MFA' => '<string>',
                'RequestPayer' => 'requester',
                'VersionId' => '<string>',
            ]
        */

        public string $bucket,                          // 'Bucket' => '<string>', // REQUIRED
        public string $key,                             // 'Key' => '<string>', // REQUIRED
        public ?bool $bypassGovernanceRetention = null, // 'BypassGovernanceRetention' => true || false,
        public ?string $expectedBucketOwner = null,     // 'ExpectedBucketOwner' => '<string>',
        public ?string $ifMatch = null,                 // 'IfMatch' => '<string>',
        public mixed $ifMatchLastModifiedTime = null,   // 'IfMatchLastModifiedTime' => <integer || string || DateTime>,
        public ?string $ifMatchSize = null,             // 'IfMatch' => '<string>',
        public ?string $mFA = null,                     // 'MFA' => '<string>',
        public ?string $requestPayer = null,            // 'RequestPayer' => 'requester',
        public ?string $versionId = null,               // 'VersionId' => '<string>',

    ) {}

    public function cloneWithNewBucketAndKey(string $bucketName, string $objectKey)
    {
        return self::cloneWithProps($this, ['bucket' => $bucketName, 'key' => $objectKey]);
    }

    public static function fromAwsFormat(array $object): self
    {
        return new self(
            bucket: $object['Bucket'],
            key: $object['Key'],
            bypassGovernanceRetention: $object['BypassGovernanceRetention'] ?? null,
            expectedBucketOwner: $object['ExpectedBucketOwner'] ?? null,
            ifMatch: $object['IfMatch'] ?? null,
            ifMatchLastModifiedTime: $object['IfMatchLastModifiedTime'] ?? null,
            ifMatchSize: $object['IfMatchSize'] ?? null,
            mFA: $object['MFA'] ?? null,
            requestPayer: $object['RequestPayer'] ?? null,
            versionId: $object['VersionId'] ?? null,
        );
    }

    public function toAwsFormat(): array
    {
        $resIn = [
            'Bucket'                    => $this->bucket,
            'Key'                       => $this->key,
            'BypassGovernanceRetention' => $this->bypassGovernanceRetention,
            'ExpectedBucketOwner'       => $this->expectedBucketOwner,
            'IfMatch'                   => $this->ifMatch,
            'IfMatchLastModifiedTime'   => $this->ifMatchLastModifiedTime,
            'IfMatchSize'               => $this->ifMatchSize,
            'MFA'                       => $this->mFA,
            'RequestPayer'              => $this->requestPayer,
            'VersionId'                 => $this->versionId,
        ];

        return $this->getNotNullArrayItemsOnly($resIn);
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
