<?php

namespace Rox\Core\Client;

use Rox\Core\Entities\BoolFlagValueConverter;
use Rox\Core\Repositories\ExperimentRepositoryInterface;
use Rox\Core\Roxx\ParserInterface;

class InternalFlags implements InternalFlagsInterface
{
    /**
     * @var ExperimentRepositoryInterface $_experimentRepository
     */
    private $_experimentRepository;

    /**
     * @var ParserInterface $_parser
     */
    private $_parser;

    /**
     * InternalFlags constructor.
     * @param ExperimentRepositoryInterface $_experimentRepository
     * @param ParserInterface $_parser
     */
    public function __construct(
        ExperimentRepositoryInterface $_experimentRepository,
        ParserInterface $_parser)
    {
        $this->_experimentRepository = $_experimentRepository;
        $this->_parser = $_parser;
    }

    /**
     * @param string $flagName
     * @return bool
     */
    function isEnabled($flagName)
    {
        $internalExperiment = $this->_experimentRepository->getExperimentByFlag($flagName);
        if ($internalExperiment == null) {
            return false;
        }
        $value = $this->_parser
            ->evaluateExpression($internalExperiment->getCondition(), null)
            ->stringValue();
        return $value === BoolFlagValueConverter::FLAG_TRUE_VALUE;
    }

    /**
     * @param string $flagName
     * @return int|null
     */
    function getIntValue($flagName)
    {
        $internalExperiment = $this->_experimentRepository->getExperimentByFlag($flagName);
        if ($internalExperiment == null) {
            return null;
        }
        return $this->_parser
            ->evaluateExpression($internalExperiment->getCondition(), null)
            ->integerValue();
    }
}
