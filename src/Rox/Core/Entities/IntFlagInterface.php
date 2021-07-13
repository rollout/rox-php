<?php

namespace Rox\Core\Entities;

use Rox\Core\Context\ContextInterface;

interface IntFlagInterface
{
    /**
     * @param ContextInterface|null $context
     * @return int
     */
    function getValue($context = null);
}