<?php


namespace Rox\Core\Entities;

use Rox\Core\Context\ContextInterface;

interface BooleanFlagInterface
{
    /**
     * @param ContextInterface|null $context
     * @return bool
     */
    function isEnabled($context = null);

    /**
     * @param ContextInterface|null $context
     * @param callable $action
     */
    function enabled($context, callable $action);

    /**
     * @param ContextInterface|null $context
     * @param callable $action
     */
    function disabled($context, callable $action);
}