<?php

namespace Rox\Core\CustomProperties;

use Rox\Core\Context\ContextInterface;

class FixedValuePropertyGenerator implements CustomPropertyGeneratorInterface
{

    /**
     * @var mixed $_value
     */
    private $_value;

    /**
     * FixedValuePropertyGenerator constructor.
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->_value = $value;
    }

    /**
     * @param ContextInterface|null $context
     * @return mixed
     */
    function generate($context)
    {
        return $this->_value;
    }
}
