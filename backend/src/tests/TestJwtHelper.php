<?php

use App\Helper\JwtHelper;
use Defuse\Crypto\Key;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class JwtHelperTest extends TestCase
{
    private JwtHelper $helper;
    private string $jwtSecret = 'very-long-random-secret-for-testing';
    private string $encKeyAscii; // generate once or mock

    protected function setUp(): void
    {
        $this->encKeyAscii = Key::createNewRandomKey()->saveToAsciiSafeString();
        $this->helper = new JwtHelper($this->jwtSecret, $this->encKeyAscii);
    }

    public function testCreateAndDecodeRoundtrip()
    {
        $creds = ['key' => 'AKI...', 'secret' => 'abc123', 'region' => 'us-west-2'];
        $token = $this->helper->create($creds, 3600);

        $decodedCreds = $this->helper->getAwsCreds($token);

        $this->assertIsArray($decodedCreds);
        $this->assertEquals($creds, $decodedCreds);
    }

    public function testExpiredTokenReturnsNull()
    {
        // create token that expires immediately
        $token = $this->helper->create([], -60);
        $this->assertNull($this->helper->getAwsCreds($token));
    }
}
