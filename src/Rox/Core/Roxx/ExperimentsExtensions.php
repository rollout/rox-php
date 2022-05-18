<?php

namespace Rox\Core\Roxx;

use Rox\Core\Context\ContextInterface;
use Rox\Core\Entities\BoolFlagValueConverter;
use Rox\Core\Repositories\ExperimentRepositoryInterface;
use Rox\Core\Repositories\FlagRepositoryInterface;
use Rox\Core\Repositories\TargetGroupRepositoryInterface;

class ExperimentsExtensions
{
    /**
     * @var ParserInterface $_parser
     */
    private $_parser;

    /**
     * @var TargetGroupRepositoryInterface $_targetGroupsRepository
     */
    private $_targetGroupsRepository;

    /**
     * @var FlagRepositoryInterface
     */
    private $_flagsRepository;

    /**
     * @var ExperimentRepositoryInterface $_experimentRepository
     */
    private $_experimentRepository;

    /**
     * ExperimentsExtensions constructor.
     * @param ParserInterface $parser
     * @param TargetGroupRepositoryInterface $targetGroupRepository
     * @param FlagRepositoryInterface $flagsRepository
     * @param ExperimentRepositoryInterface $experimentRepository
     */
    public function __construct(
        ParserInterface $parser,
        TargetGroupRepositoryInterface $targetGroupRepository,
        FlagRepositoryInterface $flagsRepository,
        ExperimentRepositoryInterface $experimentRepository)
    {
        $this->_parser = $parser;
        $this->_targetGroupsRepository = $targetGroupRepository;
        $this->_flagsRepository = $flagsRepository;
        $this->_experimentRepository = $experimentRepository;
    }

    public function extend()
    {
        $this->_parser->addOperator("mergeSeed", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $seed1 = (string)$stack->pop();
            $seed2 = (string)$stack->pop();
            $stack->push("${seed1}.${seed2}");
        });

        $this->_parser->addOperator("isInPercentage", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $percentage = (double)$stack->pop();
            $seed = (string)$stack->pop();

            $bucket = $this->getBucket($seed);
            $isInPercentage = $bucket <= $percentage;

            $stack->push($isInPercentage);
        });

        $this->_parser->addOperator("isInPercentageRange", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $percentageLow = (double)($stack->pop());
            $percentageHigh = (double)($stack->pop());
            $seed = (string)$stack->pop();

            $bucket = $this->getBucket($seed);
            $isInPercentage = $bucket >= $percentageLow && $bucket < $percentageHigh;

            $stack->push($isInPercentage);
        });

        $this->_parser->addOperator("flagValue", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context, EvaluationContext $evaluationContext = null) {
            $featureFlagIdentifier = (string)$stack->pop();

            $result = BoolFlagValueConverter::FLAG_FALSE_VALUE;
            $variant = $this->_flagsRepository->getFlag($featureFlagIdentifier);
            if ($variant != null) {
                $result = $variant->getStringValue($context, null, $evaluationContext);
            } else {
                $flagsExperiment = $this->_experimentRepository->getExperimentByFlag($featureFlagIdentifier);
                if ($flagsExperiment != null && $flagsExperiment->getCondition()) {
                    $experimentEvalResult = $parser->evaluateExpression($flagsExperiment->getCondition(), $context)->stringValue();
                    if ($experimentEvalResult) {
                        $result = $experimentEvalResult;
                    }
                }
            }
            $stack->push($result);
        });

        $this->_parser->addOperator("isInTargetGroup", function (ParserInterface $parser, StackInterface $stack, ContextInterface $context) {
            $targetGroupIdentifier = (string)$stack->pop();

            $targetGroup = $this->_targetGroupsRepository->getTargetGroup($targetGroupIdentifier);
            if ($targetGroup == null) {
                $stack->push(false);
                return;
            }

            $isInTargetGroup = $parser->evaluateExpression($targetGroup->getCondition(), $context)->boolValue();
            $stack->push($isInTargetGroup);
        });
    }

    /**
     * @param string $seed
     * @return float
     */
    function getBucket($seed)
    {
        $bytes = md5($seed, true);
        $hash = (ord($bytes[0]) & 0xFF) | ((ord($bytes[1]) & 0xFF) << 8) | ((ord($bytes[2]) & 0xFF) << 16) | ((ord($bytes[3]) & 0xFF) << 24);
        $hash &= 0xffffffff;
        $bucket = $hash / (pow(2, 32) - 1);
        if ($bucket == 1) {
            $bucket = 0;
        }
        return $bucket;
    }
}
