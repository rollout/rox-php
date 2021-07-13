<?php

namespace Rox\Server\Flags;

use Rox\Core\Entities\FlagValueConverters;
use Rox\Core\Entities\RoxStringBase;
use Rox\Core\Entities\StringFlagInterface;

class RoxString extends RoxStringBase implements StringFlagInterface
{
    /**
     * RoxString constructor.
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
        return FlagValueConverters::getInstance()->getString();
    }

    /**
     * @inheritDoc
     */
    function getValue($context = null)
    {
        return $this->getStringValue($context);
    }
}
