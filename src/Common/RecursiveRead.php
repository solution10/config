<?php

namespace Solution10\Config\Common;

/**
 * Class RecursiveRead
 *
 * Recursively walks down a tree of arrays reading an item
 * given by a flat string key.
 *
 * @package     Solution10\Config\Common
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
trait RecursiveRead
{
    protected function recursiveRead(string $key, array $values, $default = null)
    {
        $keyparts = explode('.', $key);

        $totalParts = count($keyparts);
        $i = 1;
        $value = $values;
        foreach ($keyparts as $part) {
            // Otherwise, set the value:
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } else {
                $value = $default;
                break;
            }
            $i ++;
        }

        return $value;
    }
}
