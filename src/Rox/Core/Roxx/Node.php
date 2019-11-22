<?php

namespace Rox\Core\Roxx;

class Node
{
    const TYPE_RAND = 1;
    const TYPE_RATOR = 2;
    const TYPE_UNKNOWN = 3;

    /**
     * @var int $type
     */
    public $type;

    /**
     * @var mixed $value
     */
    public $value;

    /**
     * Node constructor.
     * @param int $type
     * @param mixed $value
     */
    public function __construct($type, $value)
    {
        $this->type = $type;
        $this->value = $value;
    }
}