<?php

namespace Rox\Core\Context;

class DummyContext implements ContextInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    function get($key)
    {
        return null;
    }
}