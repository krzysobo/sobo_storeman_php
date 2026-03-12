<?php
namespace App\Dto;

use App\Traits\CloneWithProps;
use App\Traits\NotNullArrayItems;

final readonly class AwsS3ObjectGetParams implements \JsonSerializable
{
    use CloneWithProps;
    use NotNullArrayItems;

    public function __construct(
        /*
            [
                'Bucket' => '<string>', // REQUIRED
                'Key' => '<string>', // REQUIRED
                'ChecksumMode' => 'ENABLED',
                'ExpectedBucketOwner' => '<string>',
                'IfMatch' => '<string>',
                'IfModifiedSince' => <integer || string || DateTime>,
                'IfNoneMatch' => '<string>',
                'IfUnmodifiedSince' => <integer || string || DateTime>,

                'PartNumber' => <integer>,
                'Range' => '<string>',
                'RequestPayer' => 'requester',

                'ResponseCacheControl' => '<string>',
                'ResponseContentDisposition' => '<string>',
                'ResponseContentEncoding' => '<string>',
                'ResponseContentLanguage' => '<string>',
                'ResponseContentType' => '<string>',
                'ResponseExpires' => <integer || string || DateTime>,

                'SSECustomerAlgorithm' => '<string>',
                'SSECustomerKey' => '<string>',
                'SSECustomerKeyMD5' => '<string>',
                'SaveAs' => '<string>',

                'VersionId' => '<string>',
            ]
        */

        public string $bucket,                             // 'Bucket' => '<string>', // REQUIRED
        public string $key,                                // 'Key' => '<string>', // REQUIRED
        public ?string $checksumMode = null,               // 'ChecksumMode' => 'ENABLED',
        public ?string $expectedBucketOwner = null,        // 'ExpectedBucketOwner' => '<string>',
        public ?string $ifMatch = null,                    // 'IfMatch' => '<string>',
        public mixed $ifModifiedSince = null,              // 'IfModifiedSince' => <integer || string || DateTime>,
        public ?string $ifNoneMatch = null,                // 'IfMatch' => '<string>',
        public mixed $ifUnmodifiedSince = null,            // 'IfModifiedSince' => <integer || string || DateTime>,
        public ?int $partNumber = null,                    // 'PartNumber' => <integer>,
        public ?int $range = null,                         // 'Range' => '<string>',
        public ?string $requestPayer = null,               // 'RequestPayer' => 'requester',
        public ?string $responseCacheControl = null,       // 'ResponseCacheControl' => '<string>',
        public ?string $responseContentDisposition = null, // 'ResponseContentDisposition' => '<string>',
        public ?string $responseContentEncoding = null,    // 'ResponseContentEncoding' => '<string>',
        public ?string $responseContentLanguage = null,    // 'ResponseContentLanguage' => '<string>',
        public ?string $responseContentType = null,        // 'ResponseContentType' => '<string>',
        public mixed $responseExpires = null,              // 'ResponseExpires' => <integer || string || DateTimepublic ?string / = null,>,
        public ?string $sSECustomerAlgorithm = null,       // 'SSECustomerAlgorithm' => '<string>',
        public ?string $sSECustomerKey = null,             // 'SSECustomerKey' => '<string>',
        public ?string $sSECustomerKeyMD5 = null,          // 'SSECustomerKeyMD5' => '<string>',
        public ?string $saveAs = null,                     // 'SaveAs' => '<string>',
        public ?string $versionId = null,                  // 'VersionId' => '<string>',
    ) {}

    public function cloneWithNewBucketAndKey(string $bucketName, string $objectKey)
    {
        return self::cloneWithProps($this, ['bucket' => $bucketName, 'key' => $objectKey]);
    }

    public static function fromAwsFormat(array $object): self
    {
        return new self(
            bucket: $object['Bucket'],
            key: $object['Key'],
            checksumMode: $object['ChecksumMode'],
            expectedBucketOwner: $object['ExpectedBucketOwner'],
            ifMatch: $object['IfMatch'],
            ifModifiedSince: $object['IfModifiedSince'],
            ifNoneMatch: $object['IfNoneMatch'],
            ifUnmodifiedSince: $object['IfUnmodifiedSince'],
            partNumber: $object['PartNumber'],
            range: $object['Range'],
            requestPayer: $object['RequestPayer'],
            responseCacheControl: $object['ResponseCacheControl'],
            responseContentDisposition: $object['ResponseContentDisposition'],
            responseContentEncoding: $object['ResponseContentEncoding'],
            responseContentLanguage: $object['ResponseContentLanguage'],
            responseContentType: $object['ResponseContentType'],
            responseExpires: $object['ResponseExpires'],
            sSECustomerAlgorithm: $object['SSECustomerAlgorithm'],
            sSECustomerKey: $object['SSECustomerKey'],
            sSECustomerKeyMD5: $object['SSECustomerKeyMD5'],
            saveAs: $object['SaveAs'],
            versionId: $object['VersionId'],
        );
    }

    public function toAwsFormat(): array
    {
        $resIn = [
            'Bucket'                     => $this->bucket,
            'Key'                        => $this->key,
            'ChecksumMode'               => $this->checksumMode,
            'ExpectedBucketOwner'        => $this->expectedBucketOwner,
            'IfMatch'                    => $this->ifMatch,
            'IfModifiedSince'            => $this->ifModifiedSince,
            'IfNoneMatch'                => $this->ifNoneMatch,
            'IfUnmodifiedSince'          => $this->ifUnmodifiedSince,
            'PartNumber'                 => $this->partNumber,
            'Range'                      => $this->range,
            'RequestPayer'               => $this->requestPayer,
            'ResponseCacheControl'       => $this->responseCacheControl,
            'ResponseContentDisposition' => $this->responseContentDisposition,
            'ResponseContentEncoding'    => $this->responseContentEncoding,
            'ResponseContentLanguage'    => $this->responseContentLanguage,
            'ResponseContentType'        => $this->responseContentType,
            'ResponseExpires'            => $this->responseExpires,
            'SSECustomerAlgorithm'       => $this->sSECustomerAlgorithm,
            'SSECustomerKey'             => $this->sSECustomerKey,
            'SSECustomerKeyMD5'          => $this->sSECustomerKeyMD5,
            'SaveAs'                     => $this->saveAs,
            'VersionId'                  => $this->versionId,
        ];

        return $this->getNotNullArrayItemsOnly($resIn);
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
