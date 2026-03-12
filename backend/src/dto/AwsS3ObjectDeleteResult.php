<?php
namespace App\Dto;

use App\Traits\NotNullArrayItems;

final readonly class AwsS3ObjectDeleteResult implements \JsonSerializable
{
    use NotNullArrayItems;

    public function __construct(
        /*
            [
                'DeleteMarker' => true || false,
                'RequestCharged' => 'requester',
                'VersionId' => '<string>',
            ]
        */
        public bool $deleteMarker,
        public string $requestCharged,
        public string $versionId,
    ) {}

    public static function fromAwsFormat(\Aws\Result  | array $object): self
    {
        return new self(
            deleteMarker: $object['DeleteMarker'],
            requestCharged: $object['RequestCharged'],
            versionId: $object['VersionId'],
        );
    }

    public function toAwsFormat(): array
    {
        return [
            'DeleteMarker'   => $this->deleteMarker,
            'RequestCharged' => $this->requestCharged,
            'VersionId'      => $this->versionId,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
