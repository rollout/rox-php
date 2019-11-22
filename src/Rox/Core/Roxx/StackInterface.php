<?php

namespace Rox\Core\Roxx;

interface StackInterface
{
    /**
     * @param mixed $value
     * @return void
     */
    function push($value);

    /**
     * @return mixed
     */
    function pop();
}