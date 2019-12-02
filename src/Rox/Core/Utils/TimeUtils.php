<?php

namespace Rox\Core\Utils;

use RuntimeException;

final class TimeUtils
{
    /**
     * @return float
     */
    public static function currentTimeMillis()
    {
        return self::toUnixTimeMilliseconds(self::_microtime());
    }

    /**
     * @param float $microtime
     * @return float
     */
    public static function toUnixTimeMilliseconds($microtime)
    {
        return floor($microtime * 1000);
    }

    /**
     * @param float|null $fixedTime Time in milliseconds
     */
    public static function setFixedTime($fixedTime = null)
    {
        if (!defined('PHPUNIT_ROX_TEST_SUITE')) {
            throw new RuntimeException('Fixed time can be set for tests only');
        }
        self::$_fixedTime = $fixedTime != null
            ? $fixedTime / 1000.
            : microtime(true);
    }

    /**
     * @return float
     */
    private static function _microtime()
    {
        if (self::$_fixedTime != null) {
            return self::$_fixedTime;
        }
        return microtime(true);
    }

    /**
     * @var float|null $_fixedTime
     */
    protected static $_fixedTime;
}
