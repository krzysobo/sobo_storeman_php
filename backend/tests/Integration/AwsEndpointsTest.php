<?php

namespace Tests\Integration;

use App\Dto\AwsCredentials;
use App\Helper\AwsCredentialsHelper;
use Aws\MockHandler;
use Aws\CommandInterface;
use Aws\Result;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;

class AwsEndpointsTest extends BaseTestCase
{
    private array $realCreds; // from .env or fixture

    protected function setUp(): void
    {
        parent::setUp();

        $this->realCreds = [
            'key'    => getenv('AWS_ACCESS_KEY_ID'),
            'secret' => getenv('AWS_SECRET_KEY'),
            'region' => getenv('AWS_REGION'),
        ];

        // Mock AWS SDK globally (use AWS MockHandler)
        $mockHandler = new MockHandler();
        $mockHandler->append(function (CommandInterface $cmd, PromiseInterface $request) {
            if ($cmd->getName() === 'ListBuckets') {
                return new Result(['Buckets' => [['Name' => 'test-bucket']]]);
            }
            // Add more mocks for ListObjectsV2, etc.
            return new Result([]); // fallback
        });

        // Inject mock into your AwsClientHelper (via container or static override if needed)
        // Assuming you have a way to set handler in getS3Client()
        \Aws\Sdk::$defaultArgs['handler'] = $mockHandler; // global mock for AWS SDK
    }

    public function testAwsLoginEndpoint()
    {
        $response = $this->runRequest(
            method: 'POST',
            uri: '/aws/login',
            body: $this->realCreds  // or mock invalid for failure cases
        );

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('token', $body);
    }

    public function testBucketListEndpointRequiresAuth()
    {
        $response = $this->runRequest('GET', '/aws/s3/bucket-list');
        $this->assertEquals(401, $response->getStatusCode()); // no token
    }

    public function testBucketListEndpointWithValidToken()
    {
        $creds = AwsCredentials::fromArray($this->realCreds);
        $token = AwsCredentialsHelper::storeAwsCredentialsAsToken($creds);

        $response = $this->runRequest(
            method: 'GET',
            uri: '/aws/s3/bucket-list',
            headers: ['Authorization' => "Bearer $token"]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('buckets', $body);
        $this->assertContains('test-bucket', $body['buckets']); // from mock
    }

    // Add more: invalid token, expired creds, bucket/{name}, etc.
}