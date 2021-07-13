<?php


namespace Rox\Server\Flags;


use Rox\Core\Client\InternalFlagsInterface;
use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Entities\FlagSetter;
use Rox\Core\Entities\RoxStringBase;
use Rox\Core\Impression\ImpressionArgs;
use Rox\Core\Impression\ImpressionInvoker;
use Rox\Core\Impression\XImpressionInvoker;
use Rox\Core\Repositories\ExperimentRepository;
use Rox\Core\Repositories\ExperimentRepositoryInterface;
use Rox\Core\Repositories\FlagRepository;
use Rox\Core\Repositories\FlagRepositoryInterface;
use Rox\Core\Roxx\Parser;
use Rox\Core\Roxx\ParserInterface;
use Rox\RoxTestCase;

class RoxFlagsTestCase extends RoxTestCase
{
    /**
     * @var ParserInterface $_parser
     */
    private $_parser;

    /**
     * @var FlagRepositoryInterface $_flagRepo
     */
    private $_flagRepo;

    /**
     * @var ExperimentRepositoryInterface $_expRepo
     */
    private $_expRepo;

    /**
     * @var FlagSetter $_flagSetter
     */
    private $_flagSetter;

    /**
     * @var ImpressionInvoker $_impressionInvoker
     */
    private $_impressionInvoker;

    /**
     * @var ImpressionArgs $_lastImpression
     */
    private $_lastImpression;

    protected function setUp()
    {
        parent::setUp();

        $internalFlags = \Mockery::mock(InternalFlagsInterface::class)
            ->shouldReceive('isEnabled')
            ->andReturn(false)
            ->byDefault()
            ->getMock();

        $this->_impressionInvoker = new XImpressionInvoker($internalFlags, null, null);
        $this->_impressionInvoker->register(function (ImpressionArgs $e) {
            $this->_lastImpression = $e;
        });

        $this->_parser = new Parser();
        $this->_flagRepo = new FlagRepository();
        $this->_expRepo = new ExperimentRepository();
        $this->_flagSetter = new FlagSetter($this->_flagRepo, $this->_parser, $this->_expRepo, $this->_impressionInvoker);
    }

    /**
     * @return ParserInterface
     */
    protected function getParser()
    {
        return $this->_parser;
    }

    /**
     * @return FlagRepositoryInterface
     */
    protected function getFlagRepository()
    {
        return $this->_flagRepo;
    }

    /**
     * @return ExperimentRepositoryInterface
     */
    protected function getExperimentRepository()
    {
        return $this->_expRepo;
    }

    /**
     * @return ImpressionInvoker
     */
    protected function getImpressionInvoker()
    {
        return $this->_impressionInvoker;
    }

    /**
     * @return FlagSetter
     */
    protected function getFlagSetter()
    {
        return $this->_flagSetter;
    }

    /**
     * @param array $exp Experiments flagName => flagExpression
     */
    protected function setExperiments(array $exp)
    {
        $expCounter = [0];
        $flagNames = array_keys($exp);
        $this->_expRepo->setExperiments(array_map(function ($name) use ($exp, &$expCounter) {
            $id = strval($expCounter[0]++);
            return $this->createExperiment($id, $name, $exp[$name]);
        }, $flagNames));
        $this->_flagSetter->setExperiments();
    }

    /**
     * @param string $id
     * @param string $name
     * @param string $expression
     * @return ExperimentModel
     */
    protected function createExperiment($id, $name, $expression)
    {
        return new ExperimentModel($id, $name, $expression, false, [$name], [], "stam");
    }

    /**
     * @param RoxStringBase $flag
     * @param string $name
     * @param string|null $expression
     */
    protected function setupFlag(RoxStringBase $flag, $name, $expression = null)
    {
        $flag->setName($name);
        $flag->setForEvaluation(
            $this->getParser(),
            $this->createExperiment("1", $name, $expression),
            $this->getImpressionInvoker());
    }

    /**
     * @param string $name
     * @param string $value
     * @param bool $targeting
     * @param string $contextKey Optional context key/value pair to check.
     * @param mixed $contextValue Optional context key/value pair to check.
     */
    protected function checkLastImpression($name, $value, $targeting = false, $contextKey = null, $contextValue = null)
    {
        $impressionArgs = $this->_lastImpression;
        $this->assertNotNull($impressionArgs);
        $this->assertEquals($name, $impressionArgs->getReportingValue()->getName());
        $this->assertEquals($value, $impressionArgs->getReportingValue()->getValue());
        $this->assertEquals($targeting, $impressionArgs->getReportingValue()->isTargeting());
        if ($contextKey) {
            $context = $impressionArgs->getContext();
            $this->assertNotNull($context);
            $this->assertEquals($contextValue, $context->get($contextKey));
        }
        $this->_lastImpression = null;
    }
}