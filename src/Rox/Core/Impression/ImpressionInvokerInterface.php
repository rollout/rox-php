<?php

namespace Rox\Core\Impression;

use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Context\ContextInterface;
use Rox\Core\Impression\Models\ReportingValue;

interface ImpressionInvokerInterface
{
    /**
     * @param callable $handler
     */
    function register(callable $handler);

    /**
     * @param ReportingValue $value
     * @param ExperimentModel|null $experiment
     * @param ContextInterface|null $context
     */
    function invoke(
        ReportingValue $value,
        ExperimentModel $experiment = null,
        ContextInterface $context = null);
}
