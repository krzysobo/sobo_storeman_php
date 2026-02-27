<?php
namespace App\Helper;

use App\Dto\AwsCredentials;
use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use AWS\Sts\StsClient;
use RuntimeException;

class AwsClientHelper
{
    /**
     * Summary of getStsClient
     * @param string $region
     * @param string $accessKey
     * @param string $secretKey
     * @param string $version
     * @return StsClient
     */
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
     * Summary of getS3ClientWithAwsCredentials
     * @param AwsCredentials $creds
     * @param mixed $version
     * @return S3Client
     */
    public static function getS3ClientWithAwsCredentials(AwsCredentials $creds, $version = 'latest'): S3Client
    {
        $credentials = new Credentials(
            $creds->key,
            $creds->secret,
            $creds->token,
            null);

        $s3Client = new S3Client([
            'region'      => $creds->region,
            'version'     => $version,
            'credentials' => $credentials,
        ]);

        return $s3Client;
    }

    /**
     *
     * @param string $region
     * @param string $accessKey
     * @param string $secretKey
     * @param mixed $sessionToken
     * @return S3Client
     */
    public static function getS3ClientWithAwsCredentialsList(
        string $region,
        string $accessKey,
        string $secretKey,
        ?string $sessionToken = null
    ): S3Client {
        $creds = new AwsCredentials(
            $accessKey,
            $secretKey,
            $sessionToken,
            $region);

        return self::getS3ClientWithAwsCredentials($creds);
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
        $awsCreds = AwsCredentialsHelper::getAwsCredentialsFromToken($token);

        return new S3Client([
            'region'      => $awsCreds->region,
            'version'     => 'latest',
            'credentials' => [
                'key'    => $awsCreds->key,
                'secret' => $awsCreds->secret,
                'token'  => $awsCreds->token,
            ],
        ]);

    }

}
