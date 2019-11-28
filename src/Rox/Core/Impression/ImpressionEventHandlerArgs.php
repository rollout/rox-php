<?php

namespace Rox\Core\Impression;

use Rox\Core\Context\ContextInterface;
use Rox\Core\Impression\Models\Experiment;
use Rox\Core\Impression\Models\ReportingValue;

class ImpressionEventHandlerArgs
{
    /**
     * @var ReportingValue $_reportingValue
     */
    private $_reportingValue;

    /**
     * @var Experiment $_experiment
     */
    private $_experiment;

    /**
     * @var ContextInterface $_context
     */
    private $_context;

    /**
     * ImpressionEventHandlerArgs constructor.
     * @param ReportingValue $_reportingValue
     * @param Experiment $_experiment
     * @param ContextInterface $_context
     */
    public function __construct(
        ReportingValue $_reportingValue,
        Experiment $_experiment,
        ContextInterface $_context)
    {
        $this->_reportingValue = $_reportingValue;
        $this->_experiment = $_experiment;
        $this->_context = $_context;
    }

    /**
     * @return ReportingValue
     */
    public function getReportingValue()
    {
        return $this->_reportingValue;
    }

    /**
     * @return Experiment
     */
    public function getExperiment()
    {
        return $this->_experiment;
    }

    /**
     * @return ContextInterface
     */
    public function getContext()
    {
        return $this->_context;
    }
}
