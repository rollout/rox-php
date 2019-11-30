<?php

namespace Rox\Core\CustomProperties;

class CustomProperty implements CustomPropertyInterface
{
    /**
     * @var CustomPropertyType $_type
     */
    private $_type;

    /**
     * @var string $_name
     */
    private $_name;

    /**
     * @var callable $_value
     */
    private $_value;

    /**
     * CustomProperty constructor.
     * @param string $name
     * @param CustomPropertyType $type
     * @param callable|mixed $value
     */
    public function __construct($name, CustomPropertyType $type, $value)
    {
        $this->_type = $type;
        $this->_name = $name;
        $this->_value = is_callable($value)
            ? $value
            : function ($context) use ($value) {
                return $value;
            };
    }

    /**
     * @return CustomPropertyType
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return callable
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
        return json_encode([
            "name" => $this->_name,
            "type" => $this->_type,
            "externalType" => $this->_type->getExternalType()
        ]);
    }
}
