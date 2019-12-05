<?php

namespace Rox\Core\Client;

use Rox\Core\Context\ContextInterface;

interface DynamicApiInterface
{
    /**
     * @param string $name
     * @param string $defaultValue
     * @param ContextInterface|null $context
     * @return bool
     */
    function isEnabled($name, $defaultValue, $context = null);

    /**
     * @param string $name
     * @param string $defaultValue
     * @param array $options
     * @param ContextInterface|null $context
     * @return string
     */
    function getValue($name, $defaultValue, $options = [], $context = null);
}
