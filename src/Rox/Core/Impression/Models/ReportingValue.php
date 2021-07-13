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
     * @var bool $_targeting
     */
    private $_targeting;

    /**
     * ReportingValue constructor.
     * @param string $name
     * @param string $value
     * @param bool $targeting
     */
    public function __construct($name, $value, $targeting = false)
    {
        $this->_name = $name;
        $this->_value = $value;
        $this->_targeting = $targeting;
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
     * @return bool
     */
    public function isTargeting()
    {
        return $this->_targeting;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "{$this->_name}, {$this->_value}";
    }
}
