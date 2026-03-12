<?php

// use App\Dto\AwsCredentials;
// use App\Helper\AwsClientHelper;
// use App\Helper\AwsCredentialsHelper;
// use Aws\AwsClient;
// use Aws\S3\S3Client;
// use PHPUnit\Framework\Attributes\CoversClass;
// use PHPUnit\Framework\Attributes\Depends;
// use PHPUnit\Framework\TestCase;
// use Slim\Factory\AppFactory;
// use Psr\Http\Message\ServerRequestInterface as Request;
// use Nyholm\Psr7\Factory\Psr17Factory;


// #[CoversClass("AwsClientHelper")]
// class AwsClientHelperTest extends TestCase
// {
//     private $s3Mock;
//     private array $awsCreds = [
//         'key'    => 'access_key_id',
//         'secret' => 'secret_access_key',
//         'region' => 'region',
//         'token'  => null, // needed for temp credentials only, NOT tested for now
//     ];

//     protected function setUp(): void
//     {
//         $this->awsCreds['key']    = getEnv("AWS_ACCESS_KEY_ID");
//         $this->awsCreds['secret'] = getEnv("AWS_SECRET_KEY");
//         $this->awsCreds['region'] = getEnv("AWS_REGION");

//         // $this->s3Mock = $this->createMock(\Aws\S3\S3Client::class);
//         // $this->s3Mock->method('listBuckets')
//         //     ->willReturn(['Buckets' => [['Name' => 'test-bucket']]]);
//     }

//     public function testCredentialsinEnvOk()
//     {
//         $this->assertIsString($this->awsCreds['key']);
//         $this->assertNotEmpty($this->awsCreds['key']);

//         $this->assertIsString($this->awsCreds['secret']);
//         $this->assertNotEmpty($this->awsCreds['secret']);

//         $this->assertIsString($this->awsCreds['region']);
//         $this->assertNotEmpty($this->awsCreds['region']);
//     }

//     public function testGetS3ClientWithCredentialsObjNoMock()
//     {
//         $awsCredsObj = AwsCredentials::fromArray(data: $this->awsCreds);
//         $s3          = AwsClientHelper::getS3ClientWithAwsCredentials($awsCredsObj);

//         $this->assertInstanceOf(S3Client::class, $s3);
//     }

//     #[Depends("testStoreCredentialsAsToken")]
//     public function testS3BucketsList(string $token)
//     {
//         $s3          = AwsClientHelper::getS3ClientWithCredentialsFromToken($token);
//         $this->assertInstanceOf(S3Client::class, $s3);
        
//     }


    
// }


/*
use Slim\Factory\AppFactory;
use Psr\Http\Message\ServerRequestInterface as Request;
use Nyholm\Psr7\Factory\Psr17Factory;

class ListBucketsEndpointTest extends TestCase
{
    public function testBucketListWithValidToken()
    {
        $app = AppFactory::create();
        // ... register routes, middleware, etc.

        $request = (new Psr17Factory())->createServerRequest('GET', '/aws/s3/bucket-list')
            ->withHeader('Authorization', 'Bearer ' . $validJwt);

        $response = $app->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
        // assert JSON body contains buckets
    }
}
*/