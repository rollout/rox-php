<?php

namespace Rox\Core\CustomProperties;

use Rox\Core\Context\ContextInterface;

interface DynamicPropertiesRuleInterface
{
    /**
     * @param string $propName
     * @param ContextInterface $context
     * @return mixed
     */
    function invoke($propName, $context);
}