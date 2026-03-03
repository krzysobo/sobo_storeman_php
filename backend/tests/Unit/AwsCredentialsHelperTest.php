<?php

use App\Dto\AwsCredentials;
use App\Helper\AwsCredentialsHelper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

#[CoversClass("AwsCredentialsHelper")]
class AwsCredentialsHelperTest extends TestCase
{
    private array $awsCreds = [
        'key'    => 'access_key_id',
        'secret' => 'secret_access_key',
        'region' => 'region',
        'token'  => null, // needed for temp credentials only, NOT tested for now
    ];

    protected function setUp(): void
    {
        $this->awsCreds['key']    = getEnv("AWS_ACCESS_KEY_ID");
        $this->awsCreds['secret'] = getEnv("AWS_SECRET_KEY");
        $this->awsCreds['region'] = getEnv("AWS_REGION");
    }

    public function testCredentialsinEnvOk()
    {
        $this->assertIsString($this->awsCreds['key']);
        $this->assertNotEmpty($this->awsCreds['key']);

        $this->assertIsString($this->awsCreds['secret']);
        $this->assertNotEmpty($this->awsCreds['secret']);

        $this->assertIsString($this->awsCreds['region']);
        $this->assertNotEmpty($this->awsCreds['region']);
    }

    public function testStoreCredentialsListAsToken(): string
    {
        $token = AwsCredentialsHelper::storeAwsCredentialsListAsToken(
            $this->awsCreds['region'],
            $this->awsCreds['key'],
            $this->awsCreds['secret']);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        return $token;
    }

    public function testStoreCredentialsAsToken(): string
    {
        $awsCredsObj = AwsCredentials::fromArray(data: $this->awsCreds);
        $token       = AwsCredentialsHelper::storeAwsCredentialsAsToken($awsCredsObj);

        $this->assertIsString($token);
        $this->assertNotEmpty($token);

        return $token;
    }

    #[Depends("testStoreCredentialsListAsToken")]
    public function testGetAwsCredentialsFromTokenOne(string $token)
    {
        $awsCreds = AwsCredentialsHelper::getAwsCredentialsFromToken($token);
        $this->assertInstanceOf(AwsCredentials::class, $awsCreds);

        $this->assertEquals($awsCreds->key, $this->awsCreds['key']);
        $this->assertEquals($awsCreds->secret, $this->awsCreds['secret']);
        $this->assertEquals($awsCreds->token, $this->awsCreds['token']);
    }

    #[Depends("testStoreCredentialsAsToken")]
    public function testGetAwsCredentialsFromTokenTwo(string $token)
    {
        $awsCreds = AwsCredentialsHelper::getAwsCredentialsFromToken($token);
        $this->assertInstanceOf(AwsCredentials::class, $awsCreds);

        $this->assertEquals($awsCreds->key, $this->awsCreds['key']);
        $this->assertEquals($awsCreds->secret, $this->awsCreds['secret']);
        $this->assertEquals($awsCreds->token, $this->awsCreds['token']);
    }

}
