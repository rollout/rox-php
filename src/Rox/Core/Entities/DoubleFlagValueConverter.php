<?php


namespace Rox\Core\Entities;


use Psr\Log\LoggerInterface;
use Rox\Core\Utils\NumericUtils;

final class DoubleFlagValueConverter implements FlagValueConverter
{
    /**
     * @inheritDoc
     */
    function isValid($value)
    {
        return is_double($value) || is_int($value);
    }


    /**
     * @param double $value
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
     * @return double
     */
    function normalizeValue($stringValue, $alternativeValue, LoggerInterface $log = null)
    {
        if (!is_null($stringValue) && NumericUtils::parseNumber($stringValue, $doubleValue)) {
            return $doubleValue;
        }
        $log->warning("Experiment type mismatch (double), returning default value");
        return floatval($alternativeValue);
    }
}
