<?php
namespace App\Dto;

use App\Traits\CloneWithProps;
use App\Traits\NotNullArrayItems;

final readonly class AwsS3BucketListResult implements \JsonSerializable
{
    use CloneWithProps;
    use NotNullArrayItems;

    public function __construct(
        // [
        //     'Buckets' => [
        //         [
        //             'BucketArn' => '<string>',
        //             'BucketRegion' => '<string>',
        //             'CreationDate' => <DateTime>,
        //             'Name' => '<string>',
        //         ],
        //         // ...
        //     ],
        //     'ContinuationToken' => '<string>',
        //     'Owner' => [
        //         'DisplayName' => '<string>',
        //         'ID' => '<string>',
        //     ],
        //     'Prefix' => '<string>',
        // ]

        public array $buckets,            // 'Buckets'
        public ?string $continuationToken, // 'ContinuationToken'
        public ?string $ownerDisplayName,  // 'Owner' => ['DisplayName' => '<string>', 'ID' => '<string>']
        public ?string $ownerId,           // 'Owner' => ['DisplayName' => '<string>', 'ID' => '<string>']
        public ?string $prefix,            // 'Prefix' => '<string>',
    ) {}

    public static function fromAwsFormat(\Aws\Result | array $object): self
    {
        $buckets = [];
        foreach ($object['Buckets'] as $item) {
            $buckets[] = AwsS3BucketListBucket::fromAwsFormat($item);
        }

        return new self(
            buckets: $buckets,
            continuationToken: $object['ContinuationToken'],
            ownerDisplayName: $object['Owner']['DisplayName'],
            ownerId: $object['Owner']['ID'],
            prefix: $object['Prefix'],
        );
    }

    public function toAwsFormat(): array
    {
        $bucketsOut = [];
        foreach ($this->buckets as $obj) {
            $bucketsOut[] = $obj->toAwsFormat();
        }

        return [
            'Buckets'           => $bucketsOut,
            'ContinuationToken' => $this->continuationToken,
            'Owner'             => [
                'DisplayName' => $this->ownerDisplayName,
                'ID'          => $this->ownerId],
            'Prefix', $this->prefix,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
