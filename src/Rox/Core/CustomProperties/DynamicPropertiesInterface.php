<?php

namespace Rox\Core\CustomProperties;

interface DynamicPropertiesInterface
{
    /**
     * @param callable $handler
     * @return void
     */
    function setDynamicPropertiesRule(callable $handler);

    /**
     * @return callable
     */
    function getDynamicPropertiesRule();
}