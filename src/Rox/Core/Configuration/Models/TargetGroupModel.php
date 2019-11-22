<?php

namespace Rox\Core\Configuration\Models;

class TargetGroupModel
{
    /**
     * @var string $_id
     */
    private $_id;

    /**
     * @var string $_condition
     */
    private $_condition;

    /**
     * TargetGroupModel constructor.
     * @param string $id
     * @param string $condition
     */
    public function __construct($id, $condition)
    {
        $this->_id = $id;
        $this->_condition = $condition;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->_condition;
    }
}