<?php

namespace Rox\Server\Flags;

use Rox\Core\Entities\DoubleFlagInterface;
use Rox\Core\Entities\FlagValueConverters;
use Rox\Core\Entities\RoxStringBase;

class RoxDouble extends RoxStringBase implements DoubleFlagInterface
{
    /**
     * RoxDouble constructor.
     * @inheritDoc
     */
    public function __construct($defaultValue, array $variations = [])
    {
        parent::__construct(
            $this->checkValueType($defaultValue),
            $this->checkVariationsType($variations));
    }

    /**
     * @inheritDoc
     */
    protected function getConverter()
    {
        return FlagValueConverters::getInstance()->getDouble();
    }

    /**
     * @inheritDoc
     */
    function getValue($context = null)
    {
        return $this->getDoubleValue($context);
    }
}
