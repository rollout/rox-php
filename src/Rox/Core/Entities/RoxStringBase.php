<?php

namespace Rox\Core\Entities;

use ArrayObject;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Context\ContextInterface;
use Rox\Core\Context\MergedContext;
use Rox\Core\Impression\ImpressionInvokerInterface;
use Rox\Core\Impression\Models\ReportingValue;
use Rox\Core\Logging\LoggerFactory;
use Rox\Core\Roxx\EvaluationContext;
use Rox\Core\Roxx\EvaluationResult;
use Rox\Core\Roxx\ParserInterface;

abstract class RoxStringBase
{
    /**
     * @var string $_name
     */
    private $_name;

    /**
     * @var string $_defaultValue
     */
    private $_defaultValue;

    /**
     * @var string[] $_variations
     */
    private $_variations;

    /**
     * @var string $_condition
     */
    private $_condition;

    /**
     * @var ParserInterface $_parser
     */
    private $_parser;

    /**
     * @var ImpressionInvokerInterface $_impressionInvoker
     */
    private $_impressionInvoker;

    /**
     * @var ExperimentModel $_experiment
     */
    private $_experiment;

    /**
     * @var LoggerInterface $_log
     */
    private $_log;

    /**
     * RoxStringBase constructor.
     * @param string $defaultValue
     * @param array $variations
     */
    public function __construct($defaultValue, $variations = [])
    {
        if (is_null($defaultValue)) {
            throw new InvalidArgumentException("Default value cannot be null");
        }
        if (in_array(null, $variations, true)) {
            throw new InvalidArgumentException("Variation cannot be null");
        }
        if (!in_array($defaultValue, $variations)) {
            $allVariations = (new ArrayObject($variations))->getArrayCopy();
            $allVariations[] = $defaultValue;
            $this->_variations = $allVariations;
        } else {
            $this->_variations = (new ArrayObject($variations))->getArrayCopy();
        }
        $this->_defaultValue = $defaultValue;
        $this->_log = LoggerFactory::getDefaultFactory()->createLogger(RoxStringBase::class);
    }

    /**
     * @param mixed $value
     * @return string
     * @throws InvalidArgumentException
     */
    protected function checkValueType($value)
    {
        $converter = $this->getConverter();
        if (!$converter->isValid($value)) {
            throw new InvalidArgumentException("Invalid value type: {$value}");
        }
        return $converter->convertToString($value);
    }

    /**
     * @param array $variations
     * @return array Variations.
     * @throws InvalidArgumentException
     */
    protected function checkVariationsType(array $variations)
    {
        $converter = $this->getConverter();
        if ($invalidVariations = array_filter($variations, function ($value) use ($converter) {
            return !$converter->isValid($value);
        })) {
            $invalidValue = array_shift($invalidVariations);
            throw new InvalidArgumentException("Invalid variation type: {$invalidValue}");
        }
        return array_map(function ($v) use ($converter) {
            return $converter->convertToString($v);
        }, $variations);
    }

    /**
     * @return FlagValueConverter
     */
    protected abstract function getConverter();

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
    public function getDefaultValue()
    {
        return $this->_defaultValue;
    }

    /**
     * @return string[]
     */
    public function getVariations()
    {
        return $this->_variations;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getCondition()
    {
        return $this->_condition;
    }

    /**
     * @param string $condition
     */
    public function setCondition($condition)
    {
        $this->_condition = $condition;
    }

    /**
     * @return ParserInterface
     */
    public function getParser()
    {
        return $this->_parser;
    }

    /**
     * @param ParserInterface $parser
     */
    public function setParser($parser)
    {
        $this->_parser = $parser;
    }

    /**
     * @return ImpressionInvokerInterface
     */
    public function getImpressionInvoker()
    {
        return $this->_impressionInvoker;
    }

    /**
     * @param ImpressionInvokerInterface $impressionInvoker
     */
    public function setImpressionInvoker($impressionInvoker)
    {
        $this->_impressionInvoker = $impressionInvoker;
    }

    /**
     * @return ExperimentModel
     */
    public function getExperiment()
    {
        return $this->_experiment;
    }

    /**
     * @param ParserInterface|null $parser
     * @param ExperimentModel|null $experiment
     * @param ImpressionInvokerInterface|null $impressionInvoker
     */
    public function setForEvaluation($parser, $experiment, $impressionInvoker)
    {
        if ($experiment != null) {
            $this->_experiment = $experiment;
            $this->_condition = $experiment->getCondition();
        } else {
            $this->_experiment = null;
            $this->_condition = "";
        }

        $this->_parser = $parser;
        $this->_impressionInvoker = $impressionInvoker;
    }

    /**
     * @param ContextInterface|null $context
     * @param string|null $alternativeDefaultValue
     * @param EvaluationContext|null $evaluationContext
     * @return string
     */
    public function getStringValue($context = null, $alternativeDefaultValue = null, $evaluationContext = null)
    {
        return $this->_getFlagValue(
            FlagValueConverters::getInstance()->getString(),
            $context, $alternativeDefaultValue, $evaluationContext);
    }

    /**
     * @param ContextInterface|null $context
     * @param string|null $alternativeDefaultValue
     * @param EvaluationContext|null $evaluationContext
     * @return int
     */
    public function getIntValue($context = null, $alternativeDefaultValue = null, $evaluationContext = null)
    {
        return $this->_getFlagValue(
            FlagValueConverters::getInstance()->getInt(),
            $context, $alternativeDefaultValue, $evaluationContext);
    }

    /**
     * @param ContextInterface|null $context
     * @param string|null $alternativeDefaultValue
     * @param EvaluationContext|null $evaluationContext
     * @return double
     */
    public function getDoubleValue($context = null, $alternativeDefaultValue = null, $evaluationContext = null)
    {
        return $this->_getFlagValue(
            FlagValueConverters::getInstance()->getDouble(),
            $context, $alternativeDefaultValue, $evaluationContext);
    }

    /**
     * @param ContextInterface|null $context
     * @param string|null $alternativeDefaultValue
     * @param EvaluationContext|null $evaluationContext
     * @return bool
     */
    public function getBooleanValue($context = null, $alternativeDefaultValue = null, $evaluationContext = null)
    {
        return $this->_getFlagValue(
            FlagValueConverters::getInstance()->getBool(),
            $context, $alternativeDefaultValue, $evaluationContext);
    }

    /**
     * @param FlagValueConverter $converter
     * @param ContextInterface $context
     * @param string|null $alternativeDefaultValue
     * @param EvaluationContext|null $evaluationContext
     * @return mixed
     */
    private function _getFlagValue(FlagValueConverter $converter, $context = null, $alternativeDefaultValue = null, $evaluationContext = null)
    {
        $evaluation = $this->_getExperimentValue($context, $evaluationContext);
        $experimentStringValue = $evaluation->stringValue();
        $finalValue = $converter->normalizeValue($experimentStringValue,
            $alternativeDefaultValue ?: $this->_defaultValue, $this->_log);
        if (!$evaluationContext || $evaluationContext->isShouldRaiseImpressionHandler()) {
            if ($this->_impressionInvoker != null) {
                $this->_impressionInvoker->invoke(
                    new ReportingValue($this->_name, $converter->convertToString($finalValue), !!$this->_experiment),
                    $this->_experiment, $evaluation->getUsedContext());
            }
        }
        return $finalValue;
    }

    /**
     * @param ContextInterface|null $context
     * @param EvaluationContext|null $evaluationContext
     * @return EvaluationResult
     */
    private function _getExperimentValue($context, $evaluationContext = null)
    {
        $globalContext = ($this->_parser != null) ? $this->_parser->getGlobalContext() : null;
        $mergedContext = new MergedContext($globalContext, $context);
        if ($this->_parser && isset($this->_condition) && ($this->_condition !== '')) {
            return $this->_parser->evaluateExpression($this->_condition, $mergedContext, $evaluationContext);
        }
        return new EvaluationResult(null, $mergedContext);
    }
}
