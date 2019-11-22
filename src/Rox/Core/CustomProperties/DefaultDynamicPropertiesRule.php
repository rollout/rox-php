<?php

namespace Rox\Core\CustomProperties;

use Rox\Core\Context\ContextInterface;

class DefaultDynamicPropertiesRule implements DynamicPropertiesRuleInterface
{
    /**
     * @param string $propName
     * @param ContextInterface $context
     * @return mixed
     */
    function invoke($propName, $context)
    {
        return ($context != null) ? $context->get($propName) : null;
    }
}