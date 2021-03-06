<?php

namespace Rox\Core\Context;

class ContextBuilder
{
    /**
     * @param array|null $map
     * @return ContextImp
     */
    public function build($map = null)
    {
        return new ContextImp($map);
    }
}
