<?php

namespace Rox\Core\Utils;

final class TimeUtils
{
    /**
     * @return float
     */
    public static function currentTimeMillis()
    {
        return self::toUnixTimeMilliseconds(microtime(true));
    }

    /**
     * @param float $microtime
     * @return float
     */
    public static function toUnixTimeMilliseconds($microtime)
    {
        return floor($microtime * 1000);
    }
}
