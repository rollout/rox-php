<?php

namespace Rox\Server\Flags;

use Rox\Core\Entities\FlagValueConverters;
use Rox\Core\Entities\IntFlagInterface;
use Rox\Core\Entities\RoxStringBase;

class RoxInt extends RoxStringBase implements IntFlagInterface
{
    /**
     * RoxInt constructor.
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
        return FlagValueConverters::getInstance()->getInt();
    }

    /**
     * @inheritDoc
     */
    function getValue($context = null)
    {
        return $this->getIntValue($context);
    }
}
