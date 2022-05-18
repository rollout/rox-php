<?php


namespace Rox\Core\Entities;

use Psr\Log\LoggerInterface;

interface FlagValueConverter
{
    /**
     * @param mixed $value
     * @return bool
     */
    function isValid($value);

    /**
     * @param mixed $value
     * @return string
     */
    function convertToString($value);

    /**
     * @param string $stringValue
     * @param string $alternativeValue
     * @param LoggerInterface|null $log
     * @return mixed
     */
    function normalizeValue($stringValue, $alternativeValue, LoggerInterface $log = null);
}