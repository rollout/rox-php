<?php

namespace Rox\Core\Configuration\Models;

class ExperimentModel
{
    /**
     * @var string $_id
     */
    private $_id;

    /**
     * @var string $_name
     */
    private $_name;

    /**
     * @var string $_condition
     */
    private $_condition;

    /**
     * @var bool $_archived
     */
    private $_archived;

    /**
     * @var string[] $_flags
     */
    private $_flags;

    /**
     * @var string[] $_labels
     */
    private $_labels;

    /**
     * @var string $_stickinessProperty
     */
    private $_stickinessProperty;

    /**
     * ExperimentModel constructor.
     * @param string $id
     * @param string $name
     * @param string $condition
     * @param bool $archived
     * @param string[] $flags
     * @param string[] $labels
     * @param string $stickinessProperty
     */
    public function __construct($id, $name, $condition, $archived, $flags, $labels, $stickinessProperty)
    {
        $this->_id = $id;
        $this->_name = $name;
        $this->_condition = $condition;
        $this->_archived = $archived;
        $this->_flags = $flags;
        $this->_labels = $labels;
        $this->_stickinessProperty = $stickinessProperty;
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
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->_condition;
    }

    /**
     * @return bool
     */
    public function isArchived()
    {
        return $this->_archived;
    }

    /**
     * @return string[]
     */
    public function getFlags()
    {
        return $this->_flags;
    }

    /**
     * @return string[]
     */
    public function getLabels()
    {
        return $this->_labels;
    }

    /**
     * @return string
     */
    public function getStickinessProperty()
    {
        return $this->_stickinessProperty;
    }
}