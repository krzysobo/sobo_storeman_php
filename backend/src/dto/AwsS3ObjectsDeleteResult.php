<?php
namespace App\Dto;

use App\Traits\NotNullArrayItems;

final readonly class AwsS3ObjectsDeleteResult implements \JsonSerializable
{
    use NotNullArrayItems;

    public function __construct(
        /*
            [
                'Deleted' => [
                    [
                        'DeleteMarker' => true || false,
                        'DeleteMarkerVersionId' => '<string>',
                        'Key' => '<string>',
                        'VersionId' => '<string>',
                    ],
                    // ...
                ],
                'Errors' => [
                    [
                        'Code' => '<string>',
                        'Key' => '<string>',
                        'Message' => '<string>',
                        'VersionId' => '<string>',
                    ],
                    // ...
                ],
                'RequestCharged' => 'requester',
            ]
        */
        public array $deleted,
        public array $errors,
        public string $requestCharged,
    ) {}

    public static function fromAwsFormat(\Aws\Result  | array $object): self
    {
        return new self(
            deleted: $object['Deleted'],
            errors: $object['Errors'],
            requestCharged: $object['RequestCharged'],
        );
    }

    public function toAwsFormat(): array
    {
        return [
            'Deleted'        => $this->deleted,
            'Errors'         => $this->errors,
            'RequestCharged' => $this->requestCharged,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
