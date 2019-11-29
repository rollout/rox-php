<?php

namespace Rox\Core\Repositories;

use Rox\Core\CustomProperties\FlagAddedCallbackArgs;
use Rox\Core\CustomProperties\FlagAddedCallbackInterface;
use Rox\Core\Entities\Variant;

class FlagRepository implements FlagRepositoryInterface
{
    /**
     * @var array $_variants
     */
    private $_variants = [];

    /**
     * @var FlagAddedCallbackInterface[] $_callbacks
     */
    private $_callbacks = [];

    /**
     * @param Variant $variant
     * @param string $name
     * @return void
     */
    function addFlag($variant, $name)
    {
        if (!$variant->getName()) {
            $variant->setName($name);
        }

        $this->_variants[$name] = $variant;
        $this->_fireFlagAdded($variant);
    }

    /**
     * @param string $name
     * @return Variant|null
     */
    function getFlag($name)
    {
        if (array_key_exists($name, $this->_variants)) {
            return $this->_variants[$name];
        }
        return null;
    }

    /**
     * @return array
     */
    function getAllFlags()
    {
        return $this->_variants;
    }

    /**
     * @param FlagAddedCallbackInterface $callback
     */
    function addFlagAddedCallback($callback)
    {
        array_push($this->_callbacks, $callback);
    }

    /**
     * @param Variant $variant
     */
    private function _fireFlagAdded($variant)
    {
        foreach ($this->_callbacks as $callback) {
            $callback->onFlagAdded($this, new FlagAddedCallbackArgs($variant));
        }
    }
}
