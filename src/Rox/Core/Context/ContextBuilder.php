<?php

namespace Rox\Core\Context;

class ContextBuilder
{
    /**
     * @param array $map
     * @return ContextImp
     */
    public function build(array $map)
    {
        return new ContextImp($map);
    }
}
