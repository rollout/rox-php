<?php

namespace Rox\Core\Roxx;

use Rox\Core\Entities\Flag;

class EvaluationResult
{
    private $_value;

    /**
     * EvaluationResult constructor.
     * @param mixed $_value
     */
    public function __construct($_value)
    {
        $this->_value = $_value;
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
            return (string)$this->_value;
        }

        if (is_bool($this->_value)) {
            return $this->_value ? Flag::FLAG_TRUE_VALUE : Flag::FLAG_FALSE_VALUE;
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