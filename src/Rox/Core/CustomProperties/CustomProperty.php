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
     * @var CustomPropertyGeneratorInterface $_value
     */
    private $_value;

    /**
     * CustomProperty constructor.
     * @param string $name
     * @param CustomPropertyType $type
     * @param CustomPropertyGeneratorInterface|mixed $generator
     */
    public function __construct($name, CustomPropertyType $type, $generator)
    {
        $this->_type = $type;
        $this->_name = $name;
        $this->_value = ($generator instanceof CustomPropertyGeneratorInterface)
            ? $generator
            : new FixedValuePropertyGenerator($generator);
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
     * @return CustomPropertyGeneratorInterface
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
        // TODO: add ext-json to composer.json?
        return json_encode([
            "name" => $this->_name,
            "type" => $this->_type,
            "externalType" => $this->_type->getExternalType()
        ]);
    }
}
