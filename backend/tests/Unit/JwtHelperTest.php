<?php

use App\Helper\JwtHelper;
use Defuse\Crypto\Key;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\TestCase;

#[CoversClass("JwtHelper")]
class JwtHelperTest extends TestCase
{
    private JwtHelper $helper;
    private array $awsCreds = [
        'key'    => 'HelloWorld',
        'secret' => 'Foobar123ABC',
        'region' => 'us-west-2'];

    protected function setUp(): void
    {
        $this->helper      = JwtHelper::instanceWithEnvSettings();
    }

    public function testGetPayloadKey()
    {
        $payloadKey = $this->helper->getPayloadKey();
        $this->assertIsString($payloadKey);
        $this->assertNotNull($payloadKey);
        $this->assertNotEmpty($payloadKey);

        return $payloadKey;
    }

    public function testCreateEncryptedTokenFromArray(): string
    {
        $encToken = $this->helper->createEncryptedTokenFromArray($this->awsCreds, 3600);

        $this->assertIsString($encToken);
        $this->assertNotNull($encToken);
        $this->assertNotEmpty($encToken);

        return $encToken;
    }

    public function testCreateExpiredEncryptedTokenFromArray(): string
    {
        $encToken = $this->helper->createEncryptedTokenFromArray($this->awsCreds, -3600);

        $this->assertIsString($encToken);
        $this->assertNotNull($encToken);
        $this->assertNotEmpty($encToken);

        return $encToken;
    }

    #[Depends("testGetPayloadKey")]
    #[Depends("testCreateEncryptedTokenFromArray")]
    public function testCreateAndDecodeRoundtrip(string $payloadKey, string $encToken)
    {
        $this->assertIsString($encToken);
        $this->assertNotNull($encToken);
        $this->assertNotEmpty($encToken);

        $decTokenData        = $this->helper->getDecryptedTokenData($encToken);
        $decTokenDataAsArray = $this->helper->getDecryptedTokenDataAsArray($encToken);

        $this->assertIsArray($decTokenData);
        $this->assertIsString($decTokenData[$payloadKey]);
        $jsonDecoded = json_decode($decTokenData[$payloadKey], true);
        $this->assertEquals($jsonDecoded, $decTokenDataAsArray[$payloadKey]);
        $this->assertIsArray($jsonDecoded);

        $this->assertIsArray($decTokenDataAsArray);
        $this->assertIsArray($decTokenDataAsArray[$payloadKey]);

        $this->assertArrayHasKey($payloadKey, $decTokenData);
        $this->assertArrayHasKey($payloadKey, $decTokenDataAsArray);
        $this->assertArrayHasKey("iat", $decTokenData);
        $this->assertArrayHasKey("iat", $decTokenDataAsArray);
        $this->assertArrayHasKey("exp", $decTokenData);
        $this->assertArrayHasKey("exp", $decTokenDataAsArray);
        $this->assertEquals($this->awsCreds, $jsonDecoded);
        $this->assertEquals($this->awsCreds, $decTokenDataAsArray[$payloadKey]);
    }

    #[Depends("testGetPayloadKey")]
    #[Depends("testCreateExpiredEncryptedTokenFromArray")]
    public function testExpiredTokenReturnsNull(string $payloadKey, string $encToken)
    {
        // echo "\n\n==> testExpiredTokenReturnsNull:: ENCRYPTED TOKEN: '$encToken' \n\n";

        $decTokenData = $this->helper->getDecryptedTokenData($encToken);
        $decTokenDataAsArray = $this->helper->getDecryptedTokenDataAsArray($encToken);

        $this->assertNull($decTokenData);
        $this->assertNull($decTokenDataAsArray);
    }
}
