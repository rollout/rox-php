<?php

namespace Rox\Core\Utils;

/**
 * .NET compatibility functions.
 * Since PHP SDK version is just a port of .NET SDK
 * it needs to be here.
 *
 * @package Rox\Core\Utils
 */
final class DotNetCompat
{
    /**
     * @param mixed $value
     * @return string
     */
    static function toJson($value)
    {
        $json = json_encode($value, JSON_PRETTY_PRINT);
        $json = preg_replace("/\[\s+\]/m", '[]', $json); // PHP 5.X issue, it outputs empty JSON array as [ ... newlines ... ]
        return preg_replace('/^(  +?)\\1(?=[^ ])/m', '$1', $json); // intend by 2 instead of 4, just as in .NET
    }

    /**
     * @param mixed $val
     * @return bool
     */
    public static function isNumericStrict($val)
    {
        return is_int($val) || is_float($val);
    }
}
