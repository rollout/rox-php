<?php

namespace Rox\Core\Roxx;

use Rox\Core\Entities\BoolFlagValueConverter;

class EvaluationResult
{
    private $_value;
    private $_usedContext;

    /**
     * EvaluationResult constructor.
     * @param mixed $value
     * @param mixed $context
     */
    public function __construct($value, $context = null)
    {
        $this->_value = $value;
        $this->_usedContext = $context;
    }

    /**
     * @return mixed
     */
    public function getUsedContext()
    {
        return $this->_usedContext;
    }

    /**
     * @return bool
     */
    public function boolValue()
    {
        $value = $this->_value;
        if ($value == null) {
            return false;
        }
        if (is_bool($value)) {
            return (bool)$value;
        }
        return null;
    }

    /**
     * @return int
     */
    public function integerValue()
    {
        $value = (string)$this->_value;
        if (!is_numeric($value)) {
            return null;
        }
        return (int)$value;
    }

    /**
     * @return double
     */
    public function doubleValue()
    {
        $value = (string)$this->_value;
        if (!is_numeric($value)) {
            return null;
        }
        return (double)$this->_value;
    }

    /**
     * @return string
     */
    public function stringValue()
    {
        if (is_string($this->_value)) {
            return $this->_value;
        }

        if (is_numeric($this->_value)) {
            return strval($this->_value);
        }

        if (is_bool($this->_value)) {
            return $this->_value
                ? BoolFlagValueConverter::FLAG_TRUE_VALUE
                : BoolFlagValueConverter::FLAG_FALSE_VALUE;
        }

        return null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->_value;
    }
}