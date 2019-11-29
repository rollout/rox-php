<?php

namespace Rox\Core\CustomProperties;

use Rox\Core\Context\ContextInterface;

interface CustomPropertyGeneratorInterface
{
    /**
     * @param ContextInterface|null $context
     * @return mixed
     */
    function generate($context);
}