<?php


namespace Rox\Core\Entities;


use Psr\Log\LoggerInterface;

final class BoolFlagValueConverter implements FlagValueConverter
{
    const FLAG_TRUE_VALUE = "true";
    const FLAG_FALSE_VALUE = "false";

    /**
     * @inheritDoc
     */
    function isValid($value)
    {
        return is_bool($value);
    }

    /**
     * @param bool $value
     * @return string
     */
    function convertToString($value)
    {
        return $value
            ? self::FLAG_TRUE_VALUE
            : self::FLAG_FALSE_VALUE;
    }

    /**
     * @param string $stringValue
     * @param string $alternativeValue
     * @param LoggerInterface|null $log
     * @return bool
     */
    function normalizeValue($stringValue, $alternativeValue, LoggerInterface $log = null)
    {
        return ($stringValue ?: $alternativeValue) === self::FLAG_TRUE_VALUE;
    }

}