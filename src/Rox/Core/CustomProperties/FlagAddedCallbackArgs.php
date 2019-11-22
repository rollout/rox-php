<?php

namespace Rox\Core\CustomProperties;

use Rox\Core\Entities\Variant;

class FlagAddedCallbackArgs
{
    /**
     * @var Variant $_variant
     */
    private $_variant;

    /**
     * FlagAddedCallbackArgs constructor.
     * @param Variant $variant
     */
    public function __construct(Variant $variant)
    {
        $this->_variant = $variant;
    }

    /**
     * @return Variant
     */
    public function getVariant()
    {
        return $this->_variant;
    }
}
