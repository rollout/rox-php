<?php

namespace Rox\Core\Roxx;

use Rox\Core\Context\ContextInterface;

interface ParserInterface
{
    /**
     * @param string $expression
     * @param ContextInterface $context
     * @return EvaluationResult
     */
    function evaluateExpression($expression, $context);

    /**
     * @param string $name
     * @param callable $operation
     */
    function addOperator($name, $operation);
}
