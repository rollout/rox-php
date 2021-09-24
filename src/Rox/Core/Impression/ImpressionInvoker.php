<?php

namespace Rox\Core\Impression;

use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Context\ContextInterface;
use Rox\Core\Impression\Models\ReportingValue;

class ImpressionInvoker implements ImpressionInvokerInterface
{
    /**
     * @var callable[] $_handlers
     */
    private $_handlers = [];

    /**
     * @inheritDoc
     */
    function register(callable $handler)
    {
        if (!in_array($handler, $this->_handlers)) {
            $this->_handlers[] = $handler;
        }
    }

    /**
     * @inheritDoc
     */
    function invoke(
        ReportingValue $value,
        ExperimentModel $experiment = null,
        ContextInterface $context = null)
    {
        $this->_fireImpression($value, $experiment, $context);
    }

    /**
     * @param ReportingValue $value
     * @param ExperimentModel|null $experiment
     * @param ContextInterface|null $context
     */
    private function _fireImpression(ReportingValue $value, $experiment, $context)
    {
        $args = new ImpressionArgs($value, $context);
        foreach ($this->_handlers as $handler) {
            $handler($args);
        }
    }
}
