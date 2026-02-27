<?php

namespace App\Helper;

use App\Dto\AwsCredentials;
use App\Helper\JwtHelper;
use Aws\Credentials\Credentials;
use JsonException;
use RuntimeException;

class AwsCredentialsHelper
{
    /**
     * Summary of storeAwsCredentialsInSession
     * @param string $region
     * @param string $accessKey
     * @param string $secretKey
     * @param mixed $expiresIn
     * @param mixed $sessionToken
     * @return void
     */
    public static function storeAwsCredentialsInSession(
        string $region,
        string $accessKey,
        string $secretKey,
        ?string $expiresIn = null,
        ?string $sessionToken = null
    ): void {
        $_SESSION['aws_creds'] = [
            'key' => $accessKey,
            'secret' => $secretKey,
            'token' => $sessionToken ?: null,  // null = permanent creds
            'region' => $region,
            'expires' => $expiresIn,  // may be null for permanent
            'logged_in_at' => time(),
        ];
    }

    /**
     * Summary of storeAwsCredentialsAsToken
     * @param string $region
     * @param string $accessKey
     * @param string $secretKey
     * @param mixed $expiresIn
     * @param mixed $sessionToken
     * @return string
     */
    public static function storeAwsCredentialsAsToken(
        AwsCredentials $creds,
    ): string {
        $jwtHelper = self::getJwtHelperForAws();
        $token = $jwtHelper->createEncryptedTokenFromArray($creds->toArray());

        return $token;
    }

    /**
     * Summary of storeAwsCredentialsAsToken
     * @param string $region
     * @param string $accessKey
     * @param string $secretKey
     * @param mixed $expiresIn
     * @param mixed $sessionToken
     * @return string
     */
    public static function storeAwsCredentialsListAsToken(
        string $region,
        string $accessKey,
        string $secretKey,
        ?string $expiresIn = null,
        ?string $sessionToken = null
    ): string {
        $creds = new AwsCredentials(
            $accessKey,
            $secretKey,
            $sessionToken,
            $region,
            $expiresIn
        );

        return self::storeAwsCredentialsAsToken($creds);
    }

    /**
     * Summary of getAwsCredentialsFromToken
     * @param string $token
     * @return AwsCredentials
     */
    public static function getAwsCredentialsFromToken(string $token): AwsCredentials
    {
        $jwtHelper = self::getJwtHelperForAws();

        $decodedToken = $jwtHelper->getDecryptedTokenData($token);
        if (($decodedToken === null) || (empty($decodedToken))) {
            throw new RuntimeException('Incorrect JWT token - AWS credentials not found.');
        }

        if (time() > $decodedToken['exp']) {
            throw new RuntimeException('JWT Token with AWS credentials has expired');
        }

        try {
            $tokenPayload = json_decode($decodedToken['payload'], true, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $msg = $e->getMessage();
            throw new RuntimeException("Malformed token payload JSON: $msg");
        }

        $creds = AwsCredentials::fromArray($tokenPayload);

        return $creds;
    }

    /**
     * Summary of getJwtHelperForAws
     * @return JwtHelper
     */
    private static function getJwtHelperForAws(): JwtHelper
    {
        $jwtHelper = JwtHelper::instanceWithEnvSettings();

        return $jwtHelper;
    }
}
