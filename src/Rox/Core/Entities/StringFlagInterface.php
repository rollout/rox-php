<?php

namespace Rox\Core\Entities;

use Rox\Core\Context\ContextInterface;

interface StringFlagInterface
{
    /**
     * @param ContextInterface|null $context
     * @return string
     */
    function getValue($context = null);
}