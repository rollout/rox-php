<?php


namespace Rox\Core\Entities;


use Psr\Log\LoggerInterface;

final class IntFlagValueConverter implements FlagValueConverter
{
    /**
     * @inheritDoc
     */
    function isValid($value)
    {
        return is_int($value);
    }

    /**
     * @param int $value
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
     * @return int
     */
    function normalizeValue($stringValue, $alternativeValue, LoggerInterface $log = null)
    {
        if ($stringValue) {
            if (!preg_match('/\d+\.\d+/', $stringValue) && ((($intValue = intval($stringValue)) !== 0) || $stringValue === '0')) {
                return $intValue;
            } else if ($log) {
                $log->warning("Experiment type mismatch (int), returning default value");
            }
        }
        return intval($alternativeValue);
    }

}