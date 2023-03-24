<?php

namespace Rox\Core\Network;

final class ConfigurationSource
{
    const CDN = 1;
    const API = 2;
    const Roxy = 3;

    /**
     * @param int $source
     * @return string
     */
    public static function toString($source)
    {
        switch ($source) {
            case self::CDN:
                return 'CDN';
            case self::API:
                return 'API';
            case self::Roxy:
                return 'Roxy';
            default:
                return (string)$source;
        }
    }
}
