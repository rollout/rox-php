<?php

namespace Rox\Core\Roxx;

class Node
{
    const TYPE_RAND = 1;
    const TYPE_RATOR = 2;
    const TYPE_UNKNOWN = 3;

    /**
     * @var int $_type
     */
    private $_type;

    /**
     * @var mixed $_value
     */
    private $_value;

    /**
     * Node constructor.
     * @param int $type
     * @param mixed $value
     */
    public function __construct($type, $value)
    {
        $this->_type = $type;
        $this->_value = $value;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }
}
