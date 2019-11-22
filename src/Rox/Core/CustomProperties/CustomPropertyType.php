<?php

namespace Rox\Core\CustomProperties;

class CustomPropertyType
{
    /**
     * @var string $_type
     */
    private $_type;

    /**
     * @var string $_externalType
     */
    private $_externalType;

    /**
     * CustomPropertyType constructor.
     * @param string $type
     * @param string $externalType
     */
    public function __construct($type, $externalType)
    {
        $this->_type = $type;
        $this->_externalType = $externalType;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @return string
     */
    public function getExternalType()
    {
        return $this->_externalType;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->_type;
    }
}