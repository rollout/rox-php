<?php

namespace Rox\Core\Impression\Models;

use ArrayObject;
use Rox\Core\Configuration\Models\ExperimentModel;

class Experiment
{
    /**
     * @var string $_name
     */
    private $_name;

    /**
     * @var string $_identifier
     */
    private $_identifier;


    /**
     * @var bool $_archived
     */
    private $_archived;

    /**
     * @var string[] $_labels
     */
    private $_labels;

    /**
     * Experiment constructor.
     * @param ExperimentModel $experiment
     */
    public function __construct($experiment)
    {
        $this->_name = $experiment->getName();
        $this->_identifier = $experiment->getId();
        $this->_archived = $experiment->isArchived();
        $this->_labels = (new ArrayObject($experiment->getLabels()))->getArrayCopy();
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
    public function getIdentifier()
    {
        return $this->_identifier;
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
    public function getLabels()
    {
        return $this->_labels;
    }

    public function __toString()
    {
        return $this->_name;
    }
}
