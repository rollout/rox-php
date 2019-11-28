<?php

namespace Rox\Core\Impression\Models;

class ReportingValue
{
    /**
     * @var string $_name
     */
    private $_name;

    /**
     * @var string $_value
     */
    private $_value;

    /**
     * ReportingValue constructor.
     * @param string $name
     * @param string $value
     */
    public function __construct($name, $value)
    {
        $this->_name = $name;
        $this->_value = $value;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "{$this->_name}, {$this->_value}";
    }
}
