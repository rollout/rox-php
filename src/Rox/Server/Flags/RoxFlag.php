<?php

namespace Rox\Server\Flags;

use Rox\Core\Context\ContextInterface;
use Rox\Core\Entities\BooleanFlagInterface;
use Rox\Core\Entities\BoolFlagValueConverter;
use Rox\Core\Entities\FlagValueConverters;
use Rox\Core\Entities\RoxStringBase;

class RoxFlag extends RoxStringBase implements BooleanFlagInterface
{
    /**
     * Flag constructor.
     * @param bool $defaultValue
     */
    public function __construct($defaultValue = false)
    {
        parent::__construct(
            FlagValueConverters::getInstance()
                ->getBool()
                ->convertToString(
                    $this->checkValueType($defaultValue)),
            [BoolFlagValueConverter::FLAG_FALSE_VALUE,
                BoolFlagValueConverter::FLAG_TRUE_VALUE]);
    }

    /**
     * @inheritDoc
     */
    protected function getConverter()
    {
        return FlagValueConverters::getInstance()->getBool();
    }

    /**
     * @param ContextInterface|null $context
     * @return bool
     */
    public function isEnabled($context = null)
    {
        return $this->getBooleanValue($context);
    }

    /**
     * @param ContextInterface|null $context
     * @param callable $action
     */
    public function enabled($context, callable $action)
    {
        if ($this->isEnabled($context)) {
            $action();
        }
    }

    /**
     * @param ContextInterface|null $context
     * @param callable $action
     */
    public function disabled($context, callable $action)
    {
        if (!$this->isEnabled($context)) {
            $action();
        }
    }
}
