<?php
namespace App\Dto;

use App\Traits\NotNullArrayItems;

final readonly class AwsS3BucketCreateResult implements \JsonSerializable
{
    use NotNullArrayItems;

    public function __construct(
        /*
            [
                'BucketArn' => '<string>',
                'Location' => '<string>',
            ]
        */
        public string $bucketArn, // 'BucketArn' => '<string>',
        public string $location,  // 'Location' => '<string>',
    ) {}

    public static function fromAwsFormat(\Aws\Result  | array $object): self
    {
        return new self(
            bucketArn: $object['BucketArn'],
            location: $object['Location'],
        );
    }

    public function toAwsFormat(): array
    {
        return [
            'BucketArn' => $this->bucketArn,
            'Location'  => $this->location,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
