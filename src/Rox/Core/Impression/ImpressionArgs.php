<?php

namespace Rox\Core\Impression;

use Rox\Core\Context\ContextInterface;
use Rox\Core\Impression\Models\Experiment;
use Rox\Core\Impression\Models\ReportingValue;

class ImpressionArgs
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
     * ImpressionArgs constructor.
     * @param ReportingValue $_reportingValue
     * @param Experiment|null $_experiment
     * @param ContextInterface|null $_context
     */
    public function __construct(
        ReportingValue $_reportingValue,
        $_experiment,
        $_context)
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
