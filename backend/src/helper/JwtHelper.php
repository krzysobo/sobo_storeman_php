<?php
namespace App\Helper;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key as CryptoKey;
use Firebase\JWT\JWT;
use Firebase\JWT\Key as FbJwtKey;
use RuntimeException;

class JwtHelper
{
    public const string DEFAULT_PAYLOAD_KEY = 'payload';
    public const string DEFAULT_ALGO        = 'HS256';

    /**
     * Summary of instanceWithEnvSettings
     * @param string $payloadKey
     * @return JwtHelper
     */
    public static function instanceWithEnvSettings(string $payloadKey = self::DEFAULT_PAYLOAD_KEY)
    {
        // TOKEN_SECRET and TOKEN_ENC_KEY - generated with genkeys.php and stored into .env outside of git.
        // TOKEN_ENC_KEY - random 256-bit key from Key::createNewRandomKey()->saveToAsciiSafeString()
        return new self(
            secret: $_ENV["TOKEN_SECRET"],
            encKey: $_ENV["TOKEN_ENC_KEY"],
            payloadKey: $payloadKey);
    }

    /**
     * Summary of __construct
     * @param string $secret
     * @param string $encKey
     * @param string $payloadKey
     * @throws RuntimeException
     */
    public function __construct(
        private string $secret,
        private string $encKey,
        private string $payloadKey = self::DEFAULT_PAYLOAD_KEY,
        private string $algo = self::DEFAULT_ALGO
    ) {
        if ((empty($secret)) || (empty($encKey))) {
            throw new RuntimeException("TOKEN_SECRET and TOKEN_ENC_KEY may not be empty!");
        }

    }

    /**
     * Summary of createToken
     * @param array $awsCreds
     * @param int $expiresIn
     * @return string
     */
    public function createToken(array $awsCreds, int $expiresIn = 3600): string
    {
        $encryptedAws = Crypto::encrypt(
            plaintext: json_encode($awsCreds),
            key: CryptoKey::loadFromAsciiSafeString($this->encKey));

        $payload = [
            'iat'             => time(),
            'exp'             => time() + $expiresIn,
            $this->payloadKey => $encryptedAws,
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    /**
     * Summary of decodeToken
     * @param string $token
     * @return array|null
     */
    public function decodeToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode(
                jwt: $token,
                keyOrKeyArray: new FbJwtKey(keyMaterial: $this->secret, algorithm: $this->algo));

            return (array) $decoded;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Summary of getDecryptedTokenData
     * @param string $token
     * @return array{exp: mixed, iat: mixed|null}
     */
    public function getDecryptedTokenData(string $token): ?array
    {
        $decoded = $this->decodeToken($token);
        if (! $decoded || empty($decoded[$this->payloadKey])) {
            return null;
        }

        try {
            $decryptedJson = Crypto::decrypt(
                ciphertext: $decoded[$this->payloadKey],
                key: CryptoKey::loadFromAsciiSafeString($this->encKey));

            $decryptedPayload = json_decode($decryptedJson, true);

            $res = [
                'iat'             => $decoded['iat'],
                'exp'             => $decoded['exp'],
                $this->payloadKey => $decryptedPayload,
            ];

            return $res;
        } catch (\Exception $e) {
            return null;
        }
    }
}
