<?php 

namespace App\Traits;

trait NotNullArrayItems {
    protected static function getNotNullArrayItemsOnly(array $arrayIn) {
        $res = [];
        foreach ($arrayIn as $field => $val) {
            if ($val !== null) {
                $res[$field] = $val;
            }
        }

        return $res;
    }
}