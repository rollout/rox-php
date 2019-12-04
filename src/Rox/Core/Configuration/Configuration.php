<?php

namespace Rox\Core\Configuration;

use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Configuration\Models\TargetGroupModel;

class Configuration
{
    /**
     * @var float $_signatureDate Signature timestamp in milliseconds.
     */
    private $_signatureDate;

    /**
     * @var ExperimentModel[] $_experiments
     */
    private $_experiments;

    /**
     * @var TargetGroupModel[] $_targetGroups
     */
    private $_targetGroups;

    /**
     * Configuration constructor.
     * @param ExperimentModel[] $_experiments
     * @param TargetGroupModel[] $_targetGroups
     * @param float $_signatureDate
     */
    public function __construct(
        array $_experiments,
        array $_targetGroups,
        $_signatureDate)
    {
        $this->_signatureDate = $_signatureDate;
        $this->_experiments = $_experiments;
        $this->_targetGroups = $_targetGroups;
    }

    /**
     * @return float
     */
    public function getSignatureDate()
    {
        return $this->_signatureDate;
    }

    /**
     * @return ExperimentModel[]
     */
    public function getExperiments()
    {
        return $this->_experiments;
    }

    /**
     * @return TargetGroupModel[]
     */
    public function getTargetGroups()
    {
        return $this->_targetGroups;
    }
}
