<?php

namespace Rox\Core\Entities;

use Rox\Core\Context\ContextInterface;

class Flag extends Variant
{
    const FLAG_TRUE_VALUE = "true";
    const FLAG_FALSE_VALUE = "false";

    /**
     * Flag constructor.
     * @param bool $defaultValue
     */
    public function __construct($defaultValue = false)
    {
        parent::__construct($defaultValue
            ? self::FLAG_TRUE_VALUE
            : self::FLAG_FALSE_VALUE,
            [self::FLAG_FALSE_VALUE, self::FLAG_TRUE_VALUE]);
    }

    /**
     * @param ContextInterface|null $context
     * @param bool $nullInsteadOfDefault
     * @return bool
     */
    public function isEnabled($context, $nullInsteadOfDefault = false)
    {
        $value = $this->getValue($context, $nullInsteadOfDefault);
        return $nullInsteadOfDefault && ($value === null) ? null : $this->isEnabledFromString($value);
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

    /**
     * @param string $value
     * @return bool
     */
    public function isEnabledFromString($value)
    {
        return $value === self::FLAG_TRUE_VALUE;
    }
}
