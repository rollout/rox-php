<?php

namespace Rox\Core\Impression;

use Exception;
use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Context\ContextInterface;
use Rox\Core\ErrorHandling\ExceptionTrigger;
use Rox\Core\ErrorHandling\UserspaceUnhandledErrorInvokerInterface;
use Rox\Core\Impression\Models\ReportingValue;

class ImpressionInvoker implements ImpressionInvokerInterface
{
    /**
     * @var callable[] $_handlers
     */
    private $_handlers = [];

    /**
     * @var UserspaceUnhandledErrorInvokerInterface $_userUnhandledErrorInvoker
     */
    protected $_userUnhandledErrorInvoker;

    /**
     * @param UserspaceUnhandledErrorInvokerInterface $userUnhandledErrorInvoker
     */
    public function __construct(UserspaceUnhandledErrorInvokerInterface $userUnhandledErrorInvoker)
    {
        $this->_userUnhandledErrorInvoker = $userUnhandledErrorInvoker;
    }

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
        ReportingValue   $value,
        ExperimentModel  $experiment = null,
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
            try {
                $handler($args);
            } catch (Exception $e) {
                $this->_userUnhandledErrorInvoker
                    ->invoke($handler, ExceptionTrigger::ImpressionHandler, $e);
            }
        }
    }
}
