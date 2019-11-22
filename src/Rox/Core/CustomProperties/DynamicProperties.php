<?php

namespace Rox\Core\CustomProperties;

class DynamicProperties implements DynamicPropertiesInterface
{
    /**
     * @var DynamicPropertiesRuleInterface $_handler
     */
    private $_handler;

    /**
     * @param DynamicPropertiesRuleInterface $handler
     * @return void
     */
    function setDynamicPropertiesRule($handler)
    {
        $this->_handler = $handler;
    }

    /**
     * @return DynamicPropertiesRuleInterface
     */
    function getDynamicPropertiesRule()
    {
        return $this->_handler != null
            ? $this->_handler
            : new DefaultDynamicPropertiesRule();
    }
}
