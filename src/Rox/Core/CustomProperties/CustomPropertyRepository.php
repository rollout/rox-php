<?php

namespace Rox\Core\CustomProperties;

use Rox\Core\Repositories\CustomPropertyRepositoryInterface;

class CustomPropertyRepository implements CustomPropertyRepositoryInterface
{
    /**
     * @var array $_customProperties
     */
    private $_customProperties = [];

    /**
     * @param CustomPropertyInterface $customProperty
     */
    function addCustomProperty($customProperty)
    {
        if (!$customProperty->getName()) {
            return;
        }

        $this->_customProperties[$customProperty->getName()] = $customProperty;

        // TODO: fire custom property added event
    }

    /**
     * @param CustomPropertyInterface $customProperty
     */
    function addCustomPropertyIfNotExists($customProperty)
    {
        if (!$customProperty->getName()) {
            return;
        }

        if (array_key_exists($customProperty->getName(), $this->_customProperties)) {
            return;
        }

        $this->addCustomProperty($customProperty);
    }

    /**
     * @param string $name
     * @return CustomPropertyInterface
     */
    function getCustomProperty($name)
    {
        if (array_key_exists($name, $this->_customProperties)) {
            return $this->_customProperties[$name];
        }
        return null;
    }

    /**
     * @return array
     */
    function getAllCustomProperties()
    {
        return $this->_customProperties;
    }
}
