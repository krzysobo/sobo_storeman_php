<?php
namespace App\Routes;

use App\Dto\AwsCredentials;
use App\Exceptions\NoAwsCredentialsException;
use App\Helper\AwsClientHelper;
use Aws\S3\S3Client;
use Exception;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteGroup;

abstract class RouteHelperAwsS3 implements RouteHelper
{
    public static function addRoutesTo(mixed $app) {

    }

    /**
     * Summary of getS3ClientFromRequest
     * @throws NoAwsCredentialsException
     */
    protected static function getS3ClientFromRequest($request): S3Client
    {
        $creds = $request->getAttribute('aws_creds'); // Already AwsCredentials object
        if ((empty($creds)) || ! ($creds instanceof AwsCredentials)) {
            throw new NoAwsCredentialsException('Not authenticated with AWS');
        }

        return AwsClientHelper::getS3ClientWithAwsCredentials($creds);
    }

    protected static function getRequestBody($request)
    {
        return $request->getParsedBody() ?? [];
    }

}
