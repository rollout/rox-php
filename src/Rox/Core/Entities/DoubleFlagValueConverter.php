<?php


namespace Rox\Core\Entities;


use Psr\Log\LoggerInterface;

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
        if ($stringValue) {
            if (preg_match('/^\d+(\.\d*)?$/', $stringValue) &&
                ((($doubleValue = floatval($stringValue)) !== 0.0) ||
                    preg_match('/^0+(\.0*)?$/', $stringValue))) {
                return $doubleValue;
            } else if ($log) {
                $log->warning("Experiment type mismatch (double), returning default value");
            }
        }
        return floatval($alternativeValue);
    }
}
