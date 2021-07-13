<?php

namespace Rox\Core\Client;

use Rox\Core\Context\ContextInterface;

interface DynamicApiInterface
{
    /**
     * @param string $name
     * @param bool $defaultValue
     * @param ContextInterface|null $context
     * @return bool
     */
    function isEnabled($name, $defaultValue, ContextInterface $context = null);

    /**
     * @param string $name
     * @param string $defaultValue
     * @param array $variations
     * @param ContextInterface|null $context
     * @return string
     */
    function getValue($name, $defaultValue, $variations = [], ContextInterface $context = null);

    /**
     * @param string $name
     * @param int $defaultValue
     * @param array $variations
     * @param ContextInterface|null $context
     * @return int
     */
    function getInt($name, $defaultValue, $variations = [], ContextInterface $context = null);

    /**
     * @param string $name
     * @param double $defaultValue
     * @param array $variations
     * @param ContextInterface|null $context
     * @return double
     */
    function getDouble($name, $defaultValue, $variations = [], ContextInterface $context = null);
}
