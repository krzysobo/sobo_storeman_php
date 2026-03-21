<?php
namespace App\Dto;

use App\Traits\CloneWithProps;
use App\Traits\NotNullArrayItems;

final readonly class AwsS3ObjectRenameParams implements \JsonSerializable
{
    use CloneWithProps;
    use NotNullArrayItems;

    public function __construct(
        /*
            [
                'Bucket' => '<string>', // REQUIRED
                'Key' => '<string>', // REQUIRED
                'RenameSource' => '<string>', // REQUIRED
                'ClientToken' => '<string>',
                'DestinationIfMatch' => '<string>',
                'DestinationIfModifiedSince' => <integer || string || DateTime>,
                'DestinationIfNoneMatch' => '<string>',
                'DestinationIfUnmodifiedSince' => <integer || string || DateTime>,
                'SourceIfMatch' => '<string>',
                'SourceIfModifiedSince' => <integer || string || DateTime>,
                'SourceIfNoneMatch' => '<string>',
                'SourceIfUnmodifiedSince' => <integer || string || DateTime>,
            ]
        */

        public string $bucket,                               // 'Bucket' => '<string>', // REQUIRED
        public string $key,                                  // 'Key' => '<string>', // REQUIRED
        public string $renameSource,                         // 'RenameSource' => '<string>', // REQUIRED
        public ?string $clientToken = null,                  // 'ClientToken' => '<string>',
        public ?string $destinationIfMatch = null,           // 'DestinationIfMatch' => '<string>',
        public ?string $destinationIfModifiedSince = null,   // 'DestinationIfModifiedSince' => <integer || string || DateTime>,
        public ?string $destinationIfNoneMatch = null,       // 'DestinationIfNoneMatch' => '<string>',
        public ?string $destinationIfUnmodifiedSince = null, // 'DestinationIfUnmodifiedSince' => <integer || string || DateTime>,
        public ?string $sourceIfMatch = null,                // 'SourceIfMatch' => '<string>',
        public ?string $sourceIfModifiedSince = null,        // 'SourceIfModifiedSince' => <integer || string || DateTime>,
        public ?string $sourceIfNoneMatch = null,            // 'SourceIfNoneMatch' => '<string>',
        public ?string $sourceIfUnmodifiedSince = null,      // 'SourceIfUnmodifiedSince' => <integer || string || DateTime>,
    ) {}

    public function cloneWithNewBucketKeyRenameSource(string $bucketName, string $objectKey, string $renameSource): self
    {
        return self::cloneWithProps($this, [
            'bucket'       => $bucketName,
            'key'          => $objectKey,
            'renameSource' => $renameSource]);
    }

    public static function fromAwsFormat(array $object): self
    {
        return new self(
            bucket: $object['Bucket'],
            key: $object['Key'],
            renameSource: $object['RenameSource'],
            clientToken: $object['ClientToken'],
            destinationIfMatch: $object['DestinationIfMatch'],
            destinationIfModifiedSince: $object['DestinationIfModifiedSince'],
            destinationIfNoneMatch: $object['DestinationIfNoneMatch'],
            destinationIfUnmodifiedSince: $object['DestinationIfUnmodifiedSince'],
            sourceIfMatch: $object['SourceIfMatch'],
            sourceIfModifiedSince: $object['SourceIfModifiedSince'],
            sourceIfNoneMatch: $object['SourceIfNoneMatch'],
            sourceIfUnmodifiedSince: $object['SourceIfUnmodifiedSince'],
        );
    }

    public function toAwsFormat(): array
    {
        $resIn = [
            'Bucket'                       => $this->bucket,
            'Key'                          => $this->key,
            'RenameSource'                 => $this->renameSource,
            'ClientToken'                  => $this->clientToken,
            'DestinationIfMatch'           => $this->destinationIfMatch,
            'DestinationIfModifiedSince'   => $this->destinationIfModifiedSince,
            'DestinationIfNoneMatch'       => $this->destinationIfNoneMatch,
            'DestinationIfUnmodifiedSince' => $this->destinationIfUnmodifiedSince,
            'SourceIfMatch'                => $this->sourceIfMatch,
            'SourceIfModifiedSince'        => $this->sourceIfModifiedSince,
            'SourceIfNoneMatch'            => $this->sourceIfNoneMatch,
            'SourceIfUnmodifiedSince'      => $this->sourceIfUnmodifiedSince,
        ];

        return $this->getNotNullArrayItemsOnly($resIn);
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
