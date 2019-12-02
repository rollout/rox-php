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
        return preg_replace('/^(  +?)\\1(?=[^ ])/m', '$1',
            json_encode($value, JSON_PRETTY_PRINT)); // intend by 2 instead of 4, just as in .NET
    }
}
