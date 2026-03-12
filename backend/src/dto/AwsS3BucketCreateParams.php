<?php
namespace App\Dto;

use App\Traits\CloneWithProps;
use App\Traits\NotNullArrayItems;

final readonly class AwsS3BucketCreateParams implements \JsonSerializable
{
    use CloneWithProps;
    use NotNullArrayItems;

    public function __construct(
        /*
        [
            'ACL' => 'private|public-read|public-read-write|authenticated-read',
            'Bucket' => '<string>', // REQUIRED
            'CreateBucketConfiguration' => [
                'Bucket' => [
                    'DataRedundancy' => 'SingleAvailabilityZone|SingleLocalZone',
                    'Type' => 'Directory',
                ],
                'Location' => [
                    'Name' => '<string>',
                    'Type' => 'AvailabilityZone|LocalZone',
                ],
                'LocationConstraint' => 'ap-northeast-1|ap-southeast-2|ap-southeast-1|cn-north-1|eu-central-1|eu-west-1|us-east-1|us-west-1|us-west-2|sa-east-1',
                'Tags' => [
                    [
                        'Key' => '<string>', // REQUIRED
                        'Value' => '<string>', // REQUIRED
                    ],
                    // ...
                ],
            ],
            'GrantFullControl' => '<string>',
            'GrantRead' => '<string>',
            'GrantReadACP' => '<string>',
            'GrantWrite' => '<string>',
            'GrantWriteACP' => '<string>',
            'ObjectLockEnabledForBucket' => true || false,
            'ObjectOwnership' => 'BucketOwnerPreferred|ObjectWriter|BucketOwnerEnforced',
        ]
        */

        public string $bucket,                                 //    'Bucket' => '<string>', // REQUIRED
        public ?string $acl = null,                            //    'ACL' => 'private|public-read|public-read-write|authenticated-read',
        public ?string $bucketConfBucketDataRedundancy = null, // 'CreateBucketConfiguration' => [ 'Bucket' => ['DataRedundancy' => 'SingleAvailabilityZone|SingleLocalZone',
        public ?string $bucketConfBucketType = null,           // 'CreateBucketConfiguration' => [ 'Bucket' => ['Type' => 'Directory'
        public ?string $bucketConfLocationName = null,         // 'CreateBucketConfiguration' => ['Location' => ['Name' => '<string>','Type' => 'AvailabilityZone|LocalZone',
        public ?string $bucketConfLocationType = null,         // 'CreateBucketConfiguration' => ['Location' => ['Name' => '<string>','Type' => 'AvailabilityZone|LocalZone',
        public ?string $bucketConfLocationConstraint = null,   // 'CreateBucketConfiguration' => ['LocationConstraint' => 'ap-northeast-1|ap-southeast-2|ap-southeast-1|cn-north-1|eu-central-1|eu-west-1|us-east-1|us-west-1|us-west-2|sa-east-1',
        public ?array $bucketConfTags = null,                  // 'CreateBucketConfiguration' => ['Tags' => ['Key' => '<string>', // REQUIRED,  'Value' => '<string>', // REQUIRED
        public ?string $grantFullControl = null,               // 'GrantFullControl' => '<string>',
        public ?string $grantRead = null,                      // 'GrantRead' => '<string>',
        public ?string $grantReadACP = null,                   // 'GrantReadACP' => '<string>',
        public ?string $grantWrite = null,                     // 'GrantWrite' => '<string>',
        public ?string $grantWriteACP = null,                  // 'GrantWriteACP' => '<string>',
        public ?bool $objectLockEnabledForBucket = null,       // 'ObjectLockEnabledForBucket' => true || false,
        public ?string $objectOwnership = null,                // 'ObjectOwnership' => 'BucketOwnerPreferred|ObjectWriter|BucketOwnerEnforced',

    ) {}

    public function cloneWithNewBucket(string $bucketName)
    {
        return self::cloneWithProps($this, ['bucket' => $bucketName]);
    }

    public static function fromAwsFormat(array $object): self
    {
        return new self(
            bucket: $object['Bucket'],
            acl: $object['ACL'] ?? null,
            bucketConfBucketDataRedundancy: $object['CreateBucketConfiguration']['Bucket']['DataRedundancy'] ?? null,
            bucketConfBucketType: $object['CreateBucketConfiguration']['Bucket']['Type'] ?? null,
            bucketConfLocationName: $object['CreateBucketConfiguration']['Location']['Name'] ?? null,
            bucketConfLocationType: $object['CreateBucketConfiguration']['Location']['Type'] ?? null,
            bucketConfLocationConstraint: $object['CreateBucketConfiguration']['LocationConstraint'] ?? null,
            bucketConfTags: $object['CreateBucketConfiguration']['Tags'] ?? null,
            grantFullControl: $object['GrantFullControl'],
            grantRead: $object['GrantRead'],
            grantReadACP: $object['GrantReadACP'],
            grantWrite: $object['GrantWrite'],
            grantWriteACP: $object['GrantWriteACP'],
            objectLockEnabledForBucket: $object['ObjectLockEnabledForBucket'],
            objectOwnership: $object['ObjectOwnership'],
        );
    }

    public function toAwsFormat(): array
    {
        $createBucketConfIn = [];

        if ($this->bucketConfBucketDataRedundancy !== null) {
            $createBucketConfIn['Bucket']['DataRedundancy'] = $this->bucketConfBucketDataRedundancy;
        }

        if ($this->bucketConfBucketType !== null) {
            $createBucketConfIn['Bucket']['Type'] = $this->bucketConfBucketType;
        }

        if ($this->bucketConfLocationName !== null) {
            $createBucketConfIn['Location']['Name'] = $this->bucketConfLocationName;
        }

        if ($this->bucketConfLocationType !== null) {
            $createBucketConfIn['Location']['Type'] = $this->bucketConfLocationType;
        }

        if ($this->bucketConfLocationConstraint !== null) {
            $createBucketConfIn['LocationConstraint'] = $this->bucketConfLocationConstraint;
        }

        if ($this->bucketConfTags !== null) {
            $createBucketConfIn['Tags'] = $this->bucketConfTags;
        }

        $resIn = [
            'ACL'                        => $this->acl,
            'GrantFullControl'           => $this->grantFullControl,
            'GrantRead'                  => $this->grantRead,
            'GrantReadACP'               => $this->grantReadACP,
            'GrantWrite'                 => $this->grantWrite,
            'GrantWriteACP'              => $this->grantWriteACP,
            'ObjectLockEnabledForBucket' => $this->objectLockEnabledForBucket,
            'ObjectOwnership'            => $this->objectOwnership,
        ];

        $resOut = ['Bucket' => $this->bucket, ...$this->getNotNullArrayItemsOnly($resIn)];
        if ($createBucketConfIn != null) {
            $resOut['CreateBucketConfiguration'] = $createBucketConfIn;
        }

        return $resOut;
    }

    public function jsonSerialize(): array
    {
        return $this->toAwsFormat();
    }
}
