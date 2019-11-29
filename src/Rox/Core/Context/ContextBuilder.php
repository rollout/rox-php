<?php

namespace Rox\Core\Context;

class ContextBuilder
{
    /**
     * @param array|null $map
     * @return ContextImp
     */
    public function build($map)
    {
        return new ContextImp($map);
    }
}
