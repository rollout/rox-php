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
                array_push($values, $properties[$pt->getName()]);
            }
        }

        if ($extraValues != null) {
            $values = array_merge($values, $extraValues);
        }

        return str_replace('-', '', md5(join('|', $values)));
    }
}
