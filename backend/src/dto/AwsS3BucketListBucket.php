<?php
namespace App\Dto;

final readonly class AwsS3BucketListBucket implements \JsonSerializable
{
    public function __construct(
        public string $bucketArn,       //  'BucketArn' => '<string>',
        public string $bucketRegion,    //  'BucketRegion' => '<string>',
        public \DateTime $creationDate, //  'CreationDate' => <DateTime>,
        public string $name,            //  'Name' => '<string>',

    ) {}

    public static function fromAwsFormat(array $object): self
    {
        return new self(
            bucketArn: $object['BucketArn'],
            bucketRegion: $object['BucketRegion'],
            creationDate: $object['CreationDate'],
            name: $object['Name'],
        );
    }

    public function toAwsFormat(): array
    {
        return [
            'BucketArn'    => $this->bucketArn,
            'BucketRegion' => $this->bucketRegion,
            'CreationDate' => $this->creationDate,
            'Name'         => $this->name,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
