<?php

namespace Rox\Core\Context;

interface ContextInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    function get($key);
}
