<?php

namespace Rox\Core\CustomProperties;

use Rox\Core\Entities\RoxStringBase;

class FlagAddedCallbackArgs
{
    /**
     * @var RoxStringBase $_variant
     */
    private $_variant;

    /**
     * FlagAddedCallbackArgs constructor.
     * @param RoxStringBase $variant
     */
    public function __construct(RoxStringBase $variant)
    {
        $this->_variant = $variant;
    }

    /**
     * @return RoxStringBase
     */
    public function getVariant()
    {
        return $this->_variant;
    }
}
