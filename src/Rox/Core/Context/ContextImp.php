<?php

namespace Rox\Core\Context;

use ArrayObject;

class ContextImp implements ContextInterface
{
    /**
     * @var array $_map
     */
    private $_map = [];

    /**
     * ContextImp constructor.
     * @param array|null $map
     */
    public function __construct($map)
    {
        if ($map != null) {
            $this->_map = (new ArrayObject($map))->getArrayCopy();
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    function get($key)
    {
        if (!$key) {
            return null;
        }
        if (array_key_exists($key, $this->_map)) {
            return $this->_map[$key];
        }
        return null;
    }
}
