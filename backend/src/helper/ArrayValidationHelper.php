<?php
namespace App\Helper;

use App\Exceptions\InvalidDataException;

class ArrayValidationHelper
{
    public function __construct()
    {

    }

    public static function create()
    {
        return new self();
    }

    /**
     * @throws InvalidDataException
     */
    public function getStringValueByKeyOrThrow($needle, array $haystack, bool $throwOnEmpty = true): string
    {
        if (! \array_key_exists($needle, $haystack)) {
            throw new InvalidDataException("key $needle doesn't exist");
        }

        $value = trim($haystack[$needle] ?? "");
        if (($throwOnEmpty) && (empty($value))) {
            throw new InvalidDataException("value for key $needle is required.");
        }

        return $value;
    }

    /**
     * @throws InvalidDataException
     */
    public function getArrayByKeyOrThrow($needle, array $haystack, bool $throwOnEmpty = true): array
    {
        if (! \array_key_exists($needle, $haystack)) {
            throw new InvalidDataException("key $needle doesn't exist");
        }

        $value = $haystack[$needle] ?? [];
        if (($throwOnEmpty) && (empty($value))) {
            throw new InvalidDataException("value for key $needle is required.");
        }

        return $value;
    }
}
