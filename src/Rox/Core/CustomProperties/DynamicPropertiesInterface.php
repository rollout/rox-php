<?php

namespace Rox\Core\CustomProperties;

interface DynamicPropertiesInterface
{
    /**
     * @param DynamicPropertiesRuleInterface $handler
     * @return void
     */
    function setDynamicPropertiesRule($handler);

    /**
     * @return DynamicPropertiesRuleInterface
     */
    function getDynamicPropertiesRule();
}