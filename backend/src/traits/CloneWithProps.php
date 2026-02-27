<?php

namespace App\Traits;

trait CloneWithProps
{
    /**
     * Creates a clone of the object with specified properties overridden.
     *
     * On PHP 8.5+ uses the native clone($obj, $overrides) syntax via runtime dispatch.
     * On older versions falls back to reflection-style reconstruction.
     *
     * @param self  $obj       The object to clone (usually $this)
     * @param array $overrides Associative array of property => new value
     */
    protected static function cloneWithProps(self $obj, array $overrides): self
    {
        if (PHP_VERSION_ID >= 80500) {
            // a workaround to avoid syntax error if version lower than 8.5.0 (PHP_VERSION_ID < 80500)
            return \call_user_func('\clone', $obj, $overrides);
        }

        $values = get_object_vars($obj);
        foreach ($overrides as $prop => $value) {
            if (property_exists($obj, $prop)) {
                $values[$prop] = $value;
            } else {
                throw new \LogicException("Cannot override unknown property '{$prop}'");
            }
        }

        return new self(...$values);
    }
}
