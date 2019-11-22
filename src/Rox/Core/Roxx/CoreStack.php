<?php

namespace Rox\Core\Roxx;

class CoreStack implements StackInterface
{
    /**
     * @var mixed $_array
     */
    private $_array = [];

    /**
     * @param mixed $value
     * @return void
     */
    function push($value)
    {
        if ($value === null) {
            $value = Symbols::RoxxUndefined;
        }
        array_push($this->_array, $value);
    }

    /**
     * @return mixed
     */
    function pop()
    {
        $value = array_pop($this->_array);
        return ($value !== null) ? $value : Symbols::RoxxUndefined;
    }
}
