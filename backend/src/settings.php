<?php 

namespace App\Settings;

class Settings {
    public const string DEFAULT_REGION = 'eu-north-1';  // 'us-west-2'
    public const string APP_VERSION = '0.0.2';
    private static array $publicPaths = [
            '/aws/login',
            '/',
            '/play',
            '/openapi.json',
            // TODO - static asset prefixes
        ];

    /**
     * Summary of getPublicPaths
     * @return string[]
     */
    public static function getPublicPaths(): array {
        return self::$publicPaths;
    }

    public static function getDefaultRegion(): string {
        return self::DEFAULT_REGION;
    }

    public static function getAppVersion(): string {
        return self::APP_VERSION;
    }
}