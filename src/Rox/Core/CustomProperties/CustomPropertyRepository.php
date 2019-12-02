<?php

namespace Rox\Core\CustomProperties;

use Rox\Core\Repositories\CustomPropertyAddedArgs;
use Rox\Core\Repositories\CustomPropertyEventHandlerInterface;
use Rox\Core\Repositories\CustomPropertyRepositoryInterface;

class CustomPropertyRepository implements CustomPropertyRepositoryInterface
{
    /**
     * @var array $_customProperties
     */
    private $_customProperties = [];

    /**
     * @var CustomPropertyEventHandlerInterface[] $_eventHandlers
     */
    private $_eventHandlers = [];

    /**
     * @param CustomPropertyInterface $customProperty
     */
    function addCustomProperty($customProperty)
    {
        if (!$customProperty->getName()) {
            return;
        }

        $this->_customProperties[$customProperty->getName()] = $customProperty;
        $this->_fireCustomPropertyAdded($customProperty);
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

    /**
     * @param CustomPropertyEventHandlerInterface $eventHandler
     */
    function addCustomPropertyEventHandler(CustomPropertyEventHandlerInterface $eventHandler)
    {
        if (in_array($eventHandler, $this->_eventHandlers)) {
            $this->_eventHandlers[] = $eventHandler;
        }
    }

    /**
     * @param CustomPropertyInterface $customProperty
     */
    private function _fireCustomPropertyAdded(CustomPropertyInterface $customProperty)
    {
        foreach ($this->_eventHandlers as $eventHandler) {
            $eventHandler->onCustomPropertyAdded($this, new CustomPropertyAddedArgs($customProperty));
        }
    }
}
