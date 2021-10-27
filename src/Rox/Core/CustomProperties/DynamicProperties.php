<?php

namespace Rox\Core\CustomProperties;

use Rox\Core\Context\ContextInterface;

class DynamicProperties implements DynamicPropertiesInterface
{
    /**
     * @var callable $_handler
     */
    private $_handler;

    /**
     * @param callable $handler
     * @return void
     */
    function setDynamicPropertiesRule(callable $handler)
    {
        $this->_handler = $handler;
    }

    /**
     * @return callable
     */
    function getDynamicPropertiesRule()
    {
        return $this->_handler != null
            ? $this->_handler
            : function ($propName, ContextInterface $context) {
                return $context->get($propName);
            };
    }

    /**
     * @return bool
     */
    function isDefault()
    {
        return !$this->_handler;
    }
}
