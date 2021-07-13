<?php


namespace Rox\Core\Entities;


use Psr\Log\LoggerInterface;

final class StringFlagValueConverter implements FlagValueConverter
{
    /**
     * @inheritDoc
     */
    function isValid($value)
    {
        return is_string($value);
    }

    /**
     * @param string $value
     * @return string
     */
    function convertToString($value)
    {
        return strval($value);
    }

    /**
     * @param string $stringValue
     * @param string $alternativeValue
     * @param LoggerInterface|null $log
     * @return string
     */
    function normalizeValue($stringValue, $alternativeValue, LoggerInterface $log = null)
    {
        return $stringValue ?: $alternativeValue;
    }
}
