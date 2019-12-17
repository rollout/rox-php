<?php

namespace Rox\Core\Repositories;

use Rox\Core\CustomProperties\FlagAddedCallbackArgs;
use Rox\Core\Entities\Variant;

class FlagRepository implements FlagRepositoryInterface
{
    /**
     * @var array $_variants
     */
    private $_variants = [];

    /**
     * @var callable[] $_callbacks
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
     * @param callable $callback
     */
    function addFlagAddedCallback($callback)
    {
        if (!in_array($callback, $this->_callbacks)) {
            $this->_callbacks[] = $callback;
        }
    }

    /**
     * @param Variant $variant
     */
    private function _fireFlagAdded($variant)
    {
        foreach ($this->_callbacks as $callback) {
            $callback(new FlagAddedCallbackArgs($variant));
        }
    }
}
