<?php

namespace Rox\Core\Utils;

use Rox\Core\Consts\PropertyType;

final class MD5Generator
{
    /**
     * @param array $properties
     * @param PropertyType[] $generatorList
     * @param array|null $extraValues
     * @return string
     */
    public static function generate(array $properties, array $generatorList, array $extraValues = null)
    {
        $values = [];

        foreach ($generatorList as $pt) {
            if (array_key_exists($pt->getName(), $properties)) {
                $propValue = $properties[$pt->getName()];
                if (is_array($propValue)) {
                    $propValue = preg_replace('/^(  +?)\\1(?=[^ ])/m', '$1',
                        json_encode($propValue, JSON_PRETTY_PRINT)); // intend by 2 instead of 4, just as in .NET
                } else if (is_bool($propValue)) {
                    // In .NET true becomes "True", false becomes "False"
                    $propValue = $propValue ? "True" : "False";
                }
                array_push($values, (string)$propValue);
            }
        }

        if ($extraValues != null) {
            $values = array_merge($values, $extraValues);
        }

        return strtoupper(str_replace('-', '', md5(join('|', $values))));
    }
}
