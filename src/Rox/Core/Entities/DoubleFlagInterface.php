<?php

namespace Rox\Core\Entities;

use Rox\Core\Context\ContextInterface;

interface DoubleFlagInterface
{
    /**
     * @param ContextInterface|null $context
     * @return double
     */
    function getValue($context = null);
}