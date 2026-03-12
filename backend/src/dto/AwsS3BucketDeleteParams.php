<?php
namespace App\Dto;

use App\Traits\CloneWithProps;
use App\Traits\NotNullArrayItems;

final readonly class AwsS3BucketDeleteParams implements \JsonSerializable
{
    use CloneWithProps;
    use NotNullArrayItems;

    public function __construct(
        /*
            [
                'Bucket' => '<string>', // REQUIRED
                'ExpectedBucketOwner' => '<string>',
            ]
        */

        public string $bucket,                      //    'Bucket' => '<string>', // REQUIRED
        public ?string $expectedBucketOwner = null, //    'ExpectedBucketOwner' => '<string>',
    ) {}

    public function cloneWithNewBucket(string $bucketName)
    {
        return self::cloneWithProps($this, ['bucket' => $bucketName]);
    }

    public static function fromAwsFormat(array $object): self
    {
        return new self(
            bucket: $object['Bucket'],
            expectedBucketOwner: $object['ExpectedBucketOwner'] ?? null,
        );
    }

    public function toAwsFormat(): array
    {
        $resIn = [
            'Bucket'              => $this->bucket,
            'ExpectedBucketOwner' => $this->expectedBucketOwner,
        ];

        return $this->getNotNullArrayItemsOnly($resIn);
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
