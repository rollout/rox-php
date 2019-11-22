<?php

namespace Rox\Core\CustomProperties;

use Rox\Core\Context\ContextInterface;

interface CustomPropertyGeneratorInterface
{
    /**
     * @param ContextInterface $context
     * @return mixed
     */
    function generate($context);
}