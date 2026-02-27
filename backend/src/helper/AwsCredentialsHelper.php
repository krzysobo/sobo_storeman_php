<?php

namespace App\Helper;

use App\Dto\AwsCredentials;

class AwsCredentialsHelper
{
    /**
     * Summary of storeAwsCredentialsInSession.
     *
     * @param mixed $expiresIn
     * @param mixed $sessionToken
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
     * Summary of storeAwsCredentialsAsToken.
     */
    public static function storeAwsCredentialsAsToken(
        AwsCredentials $creds,
    ): string {
        $jwtHelper = self::getJwtHelperForAws();

        return $jwtHelper->createEncryptedTokenFromArray($creds->toArray());
    }

    /**
     * Summary of storeAwsCredentialsAsToken.
     *
     * @param mixed $expiresIn
     * @param mixed $sessionToken
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
     * Summary of getAwsCredentialsFromToken.
     */
    public static function getAwsCredentialsFromToken(string $token): AwsCredentials
    {
        $jwtHelper = self::getJwtHelperForAws();

        $decodedToken = $jwtHelper->getDecryptedTokenData($token);
        if ((null === $decodedToken) || (empty($decodedToken))) {
            throw new \RuntimeException('Incorrect JWT token - AWS credentials not found.');
        }

        if (time() > $decodedToken['exp']) {
            throw new \RuntimeException('JWT Token with AWS credentials has expired');
        }

        try {
            $tokenPayload = json_decode($decodedToken['payload'], true, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $msg = $e->getMessage();

            throw new \RuntimeException("Malformed token payload JSON: {$msg}");
        }

        return AwsCredentials::fromArray($tokenPayload);
    }

    /**
     * Summary of getJwtHelperForAws.
     */
    private static function getJwtHelperForAws(): JwtHelper
    {
        return JwtHelper::instanceWithEnvSettings();
    }
}
