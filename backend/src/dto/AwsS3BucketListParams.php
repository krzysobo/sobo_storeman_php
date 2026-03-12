<?php
namespace App\Dto;

use App\Traits\CloneWithProps;
use App\Traits\NotNullArrayItems;

final readonly class AwsS3BucketListParams implements \JsonSerializable
{
    use CloneWithProps;
    use NotNullArrayItems;

    public function __construct(
        public ?string $bucketRegion = null,      // 'BucketRegion' => '<string>',
        public ?string $continuationToken = null, // 'ContinuationToken' => '<string>',
        public ?string $maxBuckets = null,        // 'MaxBuckets' => <integer>,
        public ?string $prefix = null,            // 'Prefix' => '<string>',

    ) {}

    public static function fromAwsFormat(array $object): self
    {
        return new self(
            bucketRegion: $object['BucketRegion'],
            continuationToken: $object['ContinuationToken'],
            maxBuckets: $object['MaxBuckets'],
            prefix: $object['Prefix'],
        );
    }

    public function toAwsFormat(): array
    {
        $resIn = [
            'BucketRegion'      => $this->bucketRegion,
            'ContinuationToken' => $this->continuationToken,
            'MaxBuckets'        => $this->maxBuckets,
            'Prefix'            => $this->prefix,
        ];

        return $this->getNotNullArrayItemsOnly($resIn);
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
