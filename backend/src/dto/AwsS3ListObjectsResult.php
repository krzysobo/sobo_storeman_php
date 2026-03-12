<?php
namespace App\Dto;

use App\Traits\CloneWithProps;

/** this info is used in getObject;
 * getObjectListV2 has a shorter version, hence AwsS3ObjectInfoShort */
final readonly class AwsS3ListObjectsResult implements \JsonSerializable
{
    use CloneWithProps;

    public function __construct(
        public array $contents,               // returned items (objects) in the form of objects of AwsS3ObjectInfoShort
        public string $continuationToken,     // ContinuationToken' => '<string>',
        public string $delimiter,             // Delimiter' => '<string>',
        public string $encodingType,          // EncodingType' => 'url',
        public bool $isTruncated,             // IsTruncated' => true || false,
        public int $keyCount,                 // KeyCount' => <integer>,
        public int $maxKeys,                  // MaxKeys' => <integer>,
        public string $name,                  // Name' => '<string>',
        public string $nextContinuationToken, // NextContinuationToken' => '<string>',
        public string $prefix,                // Prefix' => '<string>',
        public string $requestCharged,        // RequestCharged' => 'requester',
        public string $startAfter,            // StartAfter' => '<string>',
        public ?array $commonPrefixes = null, // CommonPrefixes => [ ....... ['Prefix' => 'xxxx'] ...... ]
    ) {}

    public static function fromAwsFormat(\Aws\Result|array $object): self
    {
        $objects = [];
        foreach ($object['Contents'] as $item) {
            $objects[] = AwsS3ObjectInfoShort::fromAwsFormat($item);
        }

        $commonPrefixes = $object['CommonPrefixes'] ?? null;

        return new self(
            contents: $objects,
            continuationToken: $object['ContinuationToken'],
            delimiter: $object['Delimiter'],
            encodingType: $object['EncodingType'],
            isTruncated: $object['IsTruncated'],
            keyCount: $object['KeyCount'],
            maxKeys: $object['MaxKeys'],
            name: $object['Name'],
            nextContinuationToken: $object['NextContinuationToken'],
            prefix: $object['Prefix'],
            requestCharged: $object['RequestCharged'],
            startAfter: $object['StartAfter'],
            commonPrefixes: $commonPrefixes,
        );
    }

    public function toAwsFormat(): array
    {
        $contentsOut = [];
        foreach ($this->contents as $obj) {
            $contentsOut[] = $obj->toAwsFormat();
        }

        return [
            'CommonPrefixes'        => $this->commonPrefixes ?? [],
            'Contents'              => $contentsOut,
            'ContinuationToken'     => $this->continuationToken,
            'Delimiter'             => $this->delimiter,
            'EncodingType'          => $this->encodingType,
            'IsTruncated'           => $this->isTruncated,
            'KeyCount'              => $this->keyCount,
            'MaxKeys'               => $this->maxKeys,
            'Name'                  => $this->name,
            'NextContinuationToken' => $this->nextContinuationToken,
            'Prefix'                => $this->prefix,
            'RequestCharged'        => $this->requestCharged,
            'StartAfter'            => $this->startAfter,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
