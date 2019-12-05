<?php

namespace Rox\Core\Entities;

use ArrayObject;
use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Context\ContextInterface;
use Rox\Core\Context\MergedContext;
use Rox\Core\Impression\ImpressionInvokerInterface;
use Rox\Core\Impression\Models\ReportingValue;
use Rox\Core\Roxx\ParserInterface;

class Variant
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
     * @var string[] $_options
     */
    private $_options;

    /**
     * @var string $_condition
     */
    private $_condition;

    /**
     * @var ParserInterface $_parser
     */
    private $_parser;

    /**
     * @var ContextInterface $_globalContext
     */
    private $_globalContext;

    /**
     * @var ImpressionInvokerInterface $_impressionInvoker
     */
    private $_impressionInvoker;

    /**
     * @var ExperimentModel $_experiment
     */
    private $_experiment;

    /**
     * Variant constructor.
     * @param string $defaultValue
     * @param array $options
     */
    public function __construct($defaultValue = null, $options = [])
    {
        if (!in_array($defaultValue, $options)) {
            $allOptions = (new ArrayObject($options))->getArrayCopy();
            $allOptions[] = $defaultValue;
            $this->_options = $allOptions;
        } else {
            $this->_options = (new ArrayObject($options))->getArrayCopy();
        }
        $this->_defaultValue = $defaultValue;
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
    public function getDefaultValue()
    {
        return $this->_defaultValue;
    }

    /**
     * @return string[]
     */
    public function getOptions()
    {
        return $this->_options;
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
     * @return ContextInterface
     */
    public function getGlobalContext()
    {
        return $this->_globalContext;
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

    public function setContext(ContextInterface $globalContext)
    {
        $this->_globalContext = $globalContext;
    }

    /**
     * @param ContextInterface|null $context
     * @param bool $nullInsteadOfDefault
     * @return mixed
     */
    public function getValue($context = null, $nullInsteadOfDefault = false)
    {
        $returnValue = $nullInsteadOfDefault ? null : $this->_defaultValue;
        $mergedContext = new MergedContext($this->_globalContext, $context);

        if ($this->_parser != null && $this->_condition) {
            $evaluationResult = $this->_parser->evaluateExpression($this->_condition, $mergedContext);
            if ($evaluationResult != null && $evaluationResult->stringValue()) {
                $value = $evaluationResult->stringValue();
                if ($value) {
                    $returnValue = $value;
                }
            }
        }

        if ($this->_impressionInvoker != null) {
            $this->_impressionInvoker->invoke(new ReportingValue($this->_name, $returnValue), $this->_experiment, $mergedContext);
        }

        return $returnValue;
    }
}
