<?php
namespace App\Helper;

use App\Helper\JwtHelper;
use Aws\S3\S3Client;
use AWS\Sts\StsClient;
use RuntimeException;

class AwsHelper
{
    public static function getStsClient(
        string $region,
        string $accessKey,
        string $secretKey,
        string $version = 'latest'): StsClient {
        $sts = new StsClient([
            'region'      => $region,
            'version'     => $version,
            'credentials' => [
                'key'    => $accessKey,
                'secret' => $secretKey,
            ],
        ]);

        return $sts;
    }

    /**
     *
     * @param string $region
     * @param string $accessKey
     * @param string $secretKey
     * @param mixed $sessionToken
     * @return S3Client
     */
    public static function getS3ClientWithCredentials(
        string $region,
        string $accessKey,
        string $secretKey,
        ?string $sessionToken = null
    ): S3Client {
        $credentials = [
            'key'    => $accessKey,
            'secret' => $secretKey,
            'token'  => $sessionToken ?: null, // null = permanent creds, not null - temporary creds
        ];

        $s3Client = new S3Client([
            'region'      => $region,
            'version'     => 'latest',
            'credentials' => $credentials,
        ]);

        return $s3Client;
    }

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
            'key'          => $accessKey,
            'secret'       => $secretKey,
            'token'        => $sessionToken ?: null, // null = permanent creds
            'region'       => $region,
            'expires'      => $expiresIn, // may be null for permanent
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
        string $region,
        string $accessKey,
        string $secretKey,
        ?string $expiresIn = null,
        ?string $sessionToken = null
    ): string {
        $creds = [
            'key'          => $accessKey,
            'secret'       => $secretKey,
            'token'        => $sessionToken ?: null, // null = permanent creds
            'region'       => $region,
            'expires'      => $expiresIn, // may be null for permanent
            'logged_in_at' => time(),
        ];

        $jwtHelper = self::getJwtHelperForAws();
        $token = $jwtHelper->createToken($creds);

        return $token;
    }

    /**
     * Summary of getS3ClientWithSessionData
     * @throws RuntimeException
     * @return S3Client
     */
    public static function getS3ClientWithCredentialsFromSession(): S3Client
    {
        if (empty($_SESSION['aws_creds'])) {
            throw new RuntimeException('Not authenticated with AWS');
        }

        $creds = $_SESSION['aws_creds'];

        // Optional: check expiration
        if (time() > $creds['expires']) {
            unset($_SESSION['aws_temp']);
            throw new RuntimeException('AWS session expired');
        }

        return new S3Client([
            'region'      => $creds['region'],
            'version'     => 'latest',
            'credentials' => [
                'key'    => $creds['key'],
                'secret' => $creds['secret'],
                'token'  => $creds['token'],
            ],
        ]);
    }

    /**
     * Summary of getS3ClientWithCredentialsFromToken
     * @param string $token
     * @throws RuntimeException
     * @return S3Client
     */
    public static function getS3ClientWithCredentialsFromToken(string $token): S3Client
    {
        $jwtHelper = self::getJwtHelperForAws();

        $decodedToken = $jwtHelper->getDecryptedTokenData($token);
        if (($decodedToken === null) || (empty($decodedToken))) {
            throw new RuntimeException('Incorrect JWT token - AWS credentials not found.');
        }

        if (time() > $decodedToken['exp']) {
            throw new RuntimeException('JWT Token with AWS credentials has expired');
        }

        return new S3Client([
            'region'      => $decodedToken['region'],
            'version'     => 'latest',
            'credentials' => [
                'key'    => $decodedToken['aws_creds']['key'],
                'secret' => $decodedToken['aws_creds']['secret'],
                'token'  => $decodedToken['aws_creds']['token'],
            ],
        ]);

    }

    /**
     * Summary of getJwtHelperForAws
     * @return JwtHelper
     */
    private static function getJwtHelperForAws(): JwtHelper {
        $jwtHelper = JwtHelper::instanceWithEnvSettings();

        return $jwtHelper;
    }

}

