<?php

namespace Rox\Core\Utils;

use Rox\Core\Roxx\TokenType;

class NumericUtils
{
    /**
     * @param float $a
     * @param float $b
     * @return bool
     */
    public static function numbersEqual($a, $b)
    {
        return abs($a - $b) < PHP_FLOAT_EPSILON;
    }

    /**
     * @param mixed $val
     * @return bool
     */
    public static function isNumericStrict($val)
    {
        return is_int($val) || is_float($val);
    }

    /**
     * @param mixed $operandValue
     * @param mixed $operatorDecimalValue Reference to the decimal output.
     * @return bool
     */
    public static function parseNumber($operandValue, &$operatorDecimalValue)
    {
        $operatorDecimalValue = 0;

        if (TokenType::getUndefined() === $operandValue) {
            return false;
        }

        if (self::isNumericStrict($operandValue)) {
            $operatorDecimalValue = (float)$operandValue;
            return true;
        }

        if (preg_match('/^\d+(\.\d*)?$/', $operandValue) &&
            ((($operatorDecimalValue = floatval($operandValue)) !== 0.0) ||
                preg_match('/^0+(\.0*)?$/', $operandValue))) {
            return true;
        }

        return false;
    }
}