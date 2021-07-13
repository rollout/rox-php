<?php

namespace Rox\Core\Roxx;

use Rox\Core\Context\ContextInterface;

interface ParserInterface
{
    /**
     * @param string $expression
     * @param ContextInterface $context
     * @param EvaluationContext $evaluationContext
     * @return EvaluationResult
     */
    function evaluateExpression($expression, $context, $evaluationContext = null);

    /**
     * @param string $name
     * @param callable $operation
     */
    function addOperator($name, $operation);
}
