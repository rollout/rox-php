<?php

namespace Rox\Core\Entities;

class Variant
{
    /**
     * @var string $_name
     */
    private $_name;

    /**
     * @var string $_defaultValue
     */
    private $_defaultValue;

    /**
     * @var string[] $_options
     */
    private $_options;

    /**
     * Variant constructor.
     * @param string $defaultValue
     * @param array $options
     */
    public function __construct($defaultValue, $options = [])
    {
        $this->_defaultValue = $defaultValue;
        $this->_options = $options;
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
    public function getDefaultValue()
    {
        return $this->_defaultValue;
    }

    /**
     * @return string[]
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }
}