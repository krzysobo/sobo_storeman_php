<?php
namespace App\Dto;

use App\Traits\CloneWithProps;

/** this info is used in getObject;
 * getObjectListV2 has a shorter version, hence AwsS3ObjectInfoShort */
final readonly class AwsS3ObjectInfoShort implements \JsonSerializable
{
    use CloneWithProps;

    public function __construct(
        public string $checksumAlgorithm,       // ['<string>', ...],
        public string $checksumType,            // 'COMPOSITE|FULL_OBJECT',
        public string $eTag,                    // '<string>',
        public string $key,                     // '<string>',
        public string $lastModified,            // <DateTime>,
        public string $ownerDisplayName,        //   Owner' => ['DisplayName' => '<string>', 'ID' => '<string>'],
        public string $ownerId,                 //   Owner' => ['DisplayName' => '<string>', 'ID' => '<string>'],
        public bool $isRestoreStatusInProgress, // 'RestoreStatus' => ['IsRestoreInProgress' => true || false, 'RestoreExpiryDate'   =>  < DateTime > ,],
        public \DateTime $restoreExpiryDate,
        public int $size,           //       'Size'         =>  < integer > ,
        public string $storageClass //  'StorageClass' => 'STANDARD|REDUCED_REDUNDANCY|GLACIER|STANDARD_IA|ONEZONE_IA|INTELLIGENT_TIERING|DEEP_ARCHIVE|OUTPOSTS|GLACIER_IR|SNOW|EXPRESS_ONEZONE|FSX_OPENZFS|FSX_ONTAP',
    ) {}

    public static function fromAwsFormat(array $object): self
    {
        return new self(
            checksumAlgorithm: $object['ChecksumAlgorithm'],
            checksumType: $object['ChecksumType'],
            eTag: $object['ETag'],
            key: $object['Key'],
            lastModified: $object['LastModified'],
            ownerDisplayName: $object['Owner']['DisplayName'],
            ownerId: $object['Owner']['ID'],
            isRestoreStatusInProgress: $object['RestoreStatus']['IsRestoreInProgress'],
            restoreExpiryDate: $object['RestoreStatus']['RestoreExpiryDate'],
            size: $object['Size'],
            storageClass: $object['StorageClass']
        );
    }

    public function toAwsFormat(): array
    {
        return [
            'ChecksumAlgorithm' => $this->checksumAlgorithm,
            'ChecksumType'      => $this->checksumType,
            'ETag'              => $this->eTag,
            'Key'               => $this->key,
            'LastModified'      => $this->lastModified,
            'Owner'             => [
                'DisplayName' => $this->ownerDisplayName,
                'ID'          => $this->ownerId],
            'RestoreStatus'     => [
                'IsRestoreInProgress' => $this->isRestoreStatusInProgress,
                'RestoreExpiryDate'   => $this->restoreExpiryDate],
            'Size'              => $this->size,
            'StorageClass'      => $this->storageClass,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}

/*
[
    'AcceptRanges' => ' < string > ',
    'Body' => <string || resource || Psr\Http\Message\StreamInterface>,
    'BucketKeyEnabled' => true || false,
    'CacheControl' => ' < string > ',
    'ChecksumCRC32' => ' < string > ',
    'ChecksumCRC32C' => ' < string > ',
    'ChecksumCRC64NVME' => ' < string > ',
    'ChecksumSHA1' => ' < string > ',
    'ChecksumSHA256' => ' < string > ',
    'ChecksumType' => 'COMPOSITE | FULL_OBJECT',
    'ContentDisposition' => ' < string > ',
    'ContentEncoding' => ' < string > ',
    'ContentLanguage' => ' < string > ',
    'ContentLength' => <integer>,
    'ContentRange' => ' < string > ',
    'ContentType' => ' < string > ',
    'DeleteMarker' => true || false,
    'ETag' => ' < string > ',
    'Expiration' => ' < string > ',
    'Expires' => <DateTime>,
    'ExpiresString' => ' < string > ',
    'LastModified' => <DateTime>,
    'Metadata' => [' < string > ', ...],
    'MissingMeta' => <integer>,
    'ObjectLockLegalHoldStatus' => 'ON | OFF',
    'ObjectLockMode' => 'GOVERNANCE | COMPLIANCE',
    'ObjectLockRetainUntilDate' => <DateTime>,
    'PartsCount' => <integer>,
    'ReplicationStatus' => 'COMPLETE | PENDING | FAILED | REPLICA | COMPLETED',
    'RequestCharged' => 'requester',
    'Restore' => ' < string > ',
    'SSECustomerAlgorithm' => ' < string > ',
    'SSECustomerKeyMD5' => ' < string > ',
    'SSEKMSKeyId' => ' < string > ',
    'ServerSideEncryption' => 'AES256 | aws: fsx | aws: kms | aws: kms: dsse',
    'StorageClass' => 'STANDARD | REDUCED_REDUNDANCY | STANDARD_IA | ONEZONE_IA | INTELLIGENT_TIERING | GLACIER | DEEP_ARCHIVE | OUTPOSTS | GLACIER_IR | SNOW | EXPRESS_ONEZONE | FSX_OPENZFS | FSX_ONTAP',
    'TagCount' => <integer>,
    'VersionId' => ' < string > ',
    'WebsiteRedirectLocation' => ' < string > ',
]

*/
