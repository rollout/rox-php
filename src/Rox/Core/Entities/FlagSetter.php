<?php

namespace Rox\Core\Entities;

use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\CustomProperties\FlagAddedCallbackArgs;
use Rox\Core\Impression\ImpressionInvokerInterface;
use Rox\Core\Repositories\ExperimentRepositoryInterface;
use Rox\Core\Repositories\FlagRepositoryInterface;
use Rox\Core\Roxx\ParserInterface;

class FlagSetter
{
    /**
     * @var FlagRepositoryInterface $_flagRepository
     */
    private $_flagRepository;

    /**
     * @var ParserInterface $_parser
     */
    private $_parser;

    /**
     * @var ExperimentRepositoryInterface $_experimentRepository
     */
    private $_experimentRepository;

    /**
     * @var ImpressionInvokerInterface $_impressionInvoker
     */
    private $_impressionInvoker;

    /**
     * FlagSetter constructor.
     * @param FlagRepositoryInterface $flagRepository
     * @param ParserInterface $parser
     * @param ExperimentRepositoryInterface $experimentRepository
     * @param ImpressionInvokerInterface|null $impressionInvoker
     */
    public function __construct(
        FlagRepositoryInterface $flagRepository,
        ParserInterface $parser,
        ExperimentRepositoryInterface $experimentRepository,
        ImpressionInvokerInterface $impressionInvoker = null)
    {
        $this->_flagRepository = $flagRepository;
        $this->_parser = $parser;
        $this->_experimentRepository = $experimentRepository;
        $this->_impressionInvoker = $impressionInvoker;

        $flagRepository->addFlagAddedCallback(function (FlagAddedCallbackArgs $args) {
            $exp = $this->_experimentRepository->getExperimentByFlag($args->getVariant()->getName());
            $this->_setFlagData($args->getVariant(), $exp);
        });
    }

    public function setExperiments()
    {
        $flagsWithCondition = [];
        foreach ($this->_experimentRepository->getAllExperiments() as $exp) {
            foreach ($exp->getFlags() as $flagName) {
                $flag = $this->_flagRepository->getFlag($flagName);
                if ($flag != null) {
                    $this->_setFlagData($flag, $exp);
                    array_push($flagsWithCondition, $flagName);
                }
            }
        }

        foreach (array_values($this->_flagRepository->getAllFlags()) as /*RoxStringBase*/ $variant) {
            if (!in_array($variant->getName(), $flagsWithCondition)) {
                $this->_setFlagData($variant);
            }
        }
    }

    private function _setFlagData(RoxStringBase $variant, ExperimentModel $experiment = null)
    {
        $variant->setForEvaluation($this->_parser, $experiment, $this->_impressionInvoker);
    }
}
