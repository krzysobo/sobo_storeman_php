<?php
namespace App\Dto;

use App\Traits\CloneWithProps;
use App\Traits\NotNullArrayItems;

final readonly class AwsS3ObjectListParams implements \JsonSerializable
{
    use CloneWithProps;
    use NotNullArrayItems;

    public function __construct(
        public string $bucket,
        public ?string $continuationToken = null,
        public ?string $delimiter = null,
        public ?string $encodingType = null,
        public ?string $expectedBucketOwner = null,
        public ?string $fetchOwner = null,
        public ?string $maxKeys = null,
        public ?string $optionalObjectAttributes = null,
        public ?string $prefix = null,
        public ?string $requestPayer = null,
        public ?string $startAfter = null,
    ) {
    }

    public function cloneWithNewBucket(string $bucketName)
    {
        return self::cloneWithProps($this, ['bucket' => $bucketName]);

    }

    public static function fromAwsFormat(array $object): self
    {
        return new self(
            bucket: $object['Bucket'],
            continuationToken: $object['ContinuationToken'] ?? null,
            delimiter: $object['Delimiter'] ?? null,
            encodingType: $object['EncodingType'] ?? null,
            expectedBucketOwner: $object['ExpectedBucketOwner'] ?? null,
            fetchOwner: $object['FetchOwner'] ?? null,
            maxKeys: $object['MaxKeys'] ?? null,
            optionalObjectAttributes: $object['OptionalObjectAttributes'] ?? null,
            prefix: $object['Prefix'] ?? null,
            requestPayer: $object['RequestPayer'] ?? null,
            startAfter: $object['StartAfter'] ?? null,
        );
    }

    public function toAwsFormat(): array
    {
        $resIn = [
            'Delimiter'                => $this->delimiter,
            'EncodingType'             => $this->encodingType,
            'ExpectedBucketOwner'      => $this->expectedBucketOwner,
            'FetchOwner'               => $this->fetchOwner,
            'MaxKeys'                  => $this->maxKeys,
            'OptionalObjectAttributes' => $this->optionalObjectAttributes,
            'Prefix'                   => $this->prefix,
            'RequestPayer'             => $this->requestPayer,
            'StartAfter'               => $this->startAfter,
        ];

        // bucket is NEVER NULL
        return ['Bucket' => $this->bucket, ...$this->getNotNullArrayItemsOnly($resIn)];
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}

/* all results for listObjects:
[
    'CommonPrefixes' => [
        [
            'Prefix' => '<string>',
        ],
        // ...
    ],
    'Contents' => [
        [
            'ChecksumAlgorithm' => ['<string>', ...],
            'ChecksumType' => 'COMPOSITE|FULL_OBJECT',
            'ETag' => '<string>',
            'Key' => '<string>',
            'LastModified' => <DateTime>,
            'Owner' => [
                'DisplayName' => '<string>',
                'ID' => '<string>',
            ],
            'RestoreStatus' => [
                'IsRestoreInProgress' => true || false,
                'RestoreExpiryDate' => <DateTime>,
            ],
            'Size' => <integer>,
            'StorageClass' => 'STANDARD|REDUCED_REDUNDANCY|GLACIER|STANDARD_IA|ONEZONE_IA|INTELLIGENT_TIERING|DEEP_ARCHIVE|OUTPOSTS|GLACIER_IR|SNOW|EXPRESS_ONEZONE|FSX_OPENZFS|FSX_ONTAP',
        ],
        // ...
    ],
    'ContinuationToken' => '<string>',
    'Delimiter' => '<string>',
    'EncodingType' => 'url',
    'IsTruncated' => true || false,
    'KeyCount' => <integer>,
    'MaxKeys' => <integer>,
    'Name' => '<string>',
    'NextContinuationToken' => '<string>',
    'Prefix' => '<string>',
    'RequestCharged' => 'requester',
    'StartAfter' => '<string>',
]

*/
