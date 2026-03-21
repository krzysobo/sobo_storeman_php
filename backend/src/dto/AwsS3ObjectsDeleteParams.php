<?php
namespace App\Dto;

use App\Traits\CloneWithProps;
use App\Traits\NotNullArrayItems;

final readonly class AwsS3ObjectsDeleteParams implements \JsonSerializable
{
    use CloneWithProps;
    use NotNullArrayItems;

    public function __construct(
        /*
            [
                'Bucket' => '<string>', // REQUIRED
                'BypassGovernanceRetention' => true || false,
                'ChecksumAlgorithm' => 'CRC32|CRC32C|SHA1|SHA256|CRC64NVME',
                'Delete' => [ // REQUIRED
                    'Objects' => [ // REQUIRED
                        [
                            'ETag' => '<string>',
                            'Key' => '<string>', // REQUIRED
                            'LastModifiedTime' => <integer || string || DateTime>,
                            'Size' => <integer>,
                            'VersionId' => '<string>',
                        ],
                        // ...
                    ],
                    'Quiet' => true || false,
                ],
                'ExpectedBucketOwner' => '<string>',
                'MFA' => '<string>',
                'RequestPayer' => 'requester',
            ]

        */

        public string $bucket,                          // 'Bucket' => '<string>', // REQUIRED
        public array $deleteObjects,                    // 'Delete' => [  'Objects' => ... // REQUIRED
        public ?bool $deleteQuiet,                      //                 'Delete' => [ 'Quiet' // REQUIRED
        public ?bool $bypassGovernanceRetention = null, // 'BypassGovernanceRetention' => true || false,
        public ?string $checksumAlgorithm = null,       // 'ChecksumAlgorithm' => 'CRC32|CRC32C|SHA1|SHA256|CRC64NVME',
        public ?string $expectedBucketOwner = null,     // 'ExpectedBucketOwner' => '<string>',
        public ?string $mFA = null,                     // 'MFA' => '<string>',
        public ?string $requestPayer = null,            // 'RequestPayer' => 'requester',
    ) {}

    public static function createForBucketAndKeys(
        string $bucketName,
        array $objectKeys,
        ?bool $quiet = null
    ): AwsS3ObjectsDeleteParams {
        $deleteObjects = [];
        foreach ($objectKeys as $key) {
            $deleteObjects[] = ['Key' => $key];
        }

        if ($quiet !== null) {
            $overrides['deleteQuiet'] = $quiet;
        }

        return new self(bucket: $bucketName, deleteObjects: $deleteObjects, deleteQuiet: $quiet);
    }

    public function cloneWithNewBucketAndKeys(
        string $bucketName,
        array $objectKeys,
        ?bool $quiet = null
    ): AwsS3ObjectsDeleteParams {
        $deleteObjects = [];
        foreach ($objectKeys as $key) {
            $deleteObjects[] = ['Key' => $key];
        }

        $overrides = [
            'bucket'        => $bucketName,
            'deleteObjects' => $deleteObjects,
        ];

        if ($quiet !== null) {
            $overrides['deleteQuiet'] = $quiet;
        }

        return self::cloneWithProps($this, $overrides);
    }

    public static function fromAwsFormat(array $object): self
    {
        return new self(
            bucket: $object['Bucket'],
            bypassGovernanceRetention: $object['BypassGovernanceRetention'] ?? null,
            checksumAlgorithm: $object['ChecksumAlgorithm'] ?? null,
            deleteObjects: $object['Delete']['Objects'],
            deleteQuiet: $object['Delete']['Quiet'] ?? null,
            expectedBucketOwner: $object['ExpectedBucketOwner'] ?? null,
            mFA: $object['MFA'] ?? null,
            requestPayer: $object['RequestPayer'] ?? null,

        );
    }

    public function toAwsFormat(): array
    {
        $resIn = [
            'Bucket'                    => $this->bucket,
            'BypassGovernanceRetention' => $this->bypassGovernanceRetention,
            'ChecksumAlgorithm'         => $this->checksumAlgorithm,
            'ExpectedBucketOwner'       => $this->expectedBucketOwner,
            'MFA'                       => $this->mFA,
            'RequestPayer'              => $this->requestPayer,
        ];

        $resOut = [
            'Bucket' => $this->bucket,
            'Delete' => [
                'Objects' => $this->deleteObjects,
            ],
            ...$this->getNotNullArrayItemsOnly($resIn),
        ];

        if ($this->deleteQuiet !== null) {
            $resOut['Delete']['Quiet'] = $this->deleteQuiet;
        }

        return $resOut;
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
