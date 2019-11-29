<?php

namespace Rox\Core\Impression;

use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Context\ContextInterface;
use Rox\Core\Impression\Models\ReportingValue;

interface ImpressionInvokerInterface
{
    /**
     * @param ImpressionEventHandlerInterface $handler
     */
    function register(ImpressionEventHandlerInterface $handler);

    /**
     * @param ReportingValue $value
     * @param ExperimentModel $experiment
     * @param ContextInterface $context
     * @return mixed
     */
    function invoke(ReportingValue $value, ExperimentModel $experiment, ContextInterface $context);
}
