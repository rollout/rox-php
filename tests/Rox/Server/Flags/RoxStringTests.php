<?php

namespace Rox\Server\Flags;

use InvalidArgumentException;
use Rox\Core\Client\InternalFlagsInterface;
use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Context\ContextBuilder;
use Rox\Core\Impression\ImpressionArgs;
use Rox\Core\Impression\XImpressionInvoker;
use Rox\Core\Roxx\EvaluationResult;
use Rox\Core\Roxx\ParserInterface;

class RoxStringTests extends RoxFlagsTestCase
{
    /**
     * @dataProvider invalidValues
     * @param mixed $invalidValue
     */
    public function testInvalidDefaultValueThrowsException($invalidValue)
    {
        $this->expectException(InvalidArgumentException::class);
        new RoxString($invalidValue);
    }

    /**
     * @dataProvider invalidValues
     * @param mixed $invalidValue
     */
    public function testInvalidVariationValueThrowsException($invalidValue)
    {
        $this->expectException(InvalidArgumentException::class);
        new RoxString('1', [$invalidValue]);
    }

    /**
     * @return array[]
     */
    public function invalidValues()
    {
        return [
            [null],
            [false],
            [true],
            [1],
            [1.2]
        ];
    }

    public function testWillAddDefaultToOptionsWhenNoOptions()
    {
        $variant = new RoxString('1');
        $this->assertCount(1, $variant->getVariations());
        $this->assertContains('1', $variant->getVariations());
    }

    public function testWillNotAddDefaultToOptionsIfExists()
    {
        $variant = new RoxString('1', ['1', '2', '3']);
        $this->assertCount(3, $variant->getVariations());
        $this->assertContains('1', $variant->getVariations());
    }

    public function testWillAddDefaultToOptionsIfNotExists()
    {
        $variant = new RoxString('1', ['2', '3']);
        $this->assertCount(3, $variant->getVariations());
        $this->assertContains('1', $variant->getVariations());
    }

    public function testWillSetName()
    {
        $variant = new RoxString('1', ['2', '3']);

        $this->assertEquals(null, $variant->getName());
        $variant->setName('bop');
        $this->assertEquals('bop', $variant->getName());
    }

    public function testWillReturnDefaultValueWhenNoExperiment()
    {
        $variant = new RoxString('1');
        $this->assertEquals('1', $variant->getValue());
    }

    public function testWillDefaultAfterSetup()
    {
        $variant = new RoxString('1');
        $variant->setName('test');
        $variant->setForEvaluation($this->getParser(), null, $this->getImpressionInvoker());
        $this->assertEquals('1', $variant->getValue());
        $this->checkLastImpression('test', '1');
    }

    public function testFlagWithExperiment()
    {
        $variant = new RoxString('val');
        $this->setupFlag($variant, 'test', "\"dif\"");
        $this->assertEquals('dif', $variant->getValue());
        $this->checkLastImpression('test', 'dif', true);
    }

    public function testFlagWithExperimentReturnsUndefined()
    {
        $variant = new RoxString('val');
        $this->setupFlag($variant, 'test', "undefined");
        $this->assertEquals('val', $variant->getValue());
        $this->checkLastImpression('test', 'val', true);
    }

    public function testWillEvaluationReturnsNull()
    {
        $variant = new RoxString('val');
        $this->setupFlag($variant, "test", null);
        $this->assertEquals('val', $variant->getValue());
        $this->checkLastImpression('test', 'val', true);
    }

    public function testWillUseContext()
    {
        $context = (new ContextBuilder())->build(['key' => 55]);
        $variant = new RoxString('val');
        $this->setupFlag($variant, "test", "\"dif\"");
        $this->assertEquals('dif', $variant->getValue($context));
        $this->checkLastImpression("test", "dif", true, "key", 55);
    }

    public function testWillReturnDefaultValueWhenNoParserOrCondition()
    {
        $variant = new RoxString('1', ['2', '3']);

        $this->assertEquals('1', $variant->getValue(null));

        $parser = \Mockery::mock(ParserInterface::class);
        $variant->setForEvaluation($parser, null, null);

        $this->assertEquals('1', $variant->getValue(null));

        $variant->setForEvaluation(null, new ExperimentModel('id', 'name', '123', false, ['1'], [], 'stam'), null);

        $this->assertEquals('1', $variant->getValue(null));
    }

    public function testWillExpressionValueWhenResultNotInOptions()
    {
        $parser = \Mockery::mock(ParserInterface::class)
            ->shouldReceive('evaluateExpression')
            ->andReturn(new EvaluationResult('xxx'))
            ->getMock();

        $variant = new RoxString('1', ['2', '3']);

        $variant->setForEvaluation($parser,
            new ExperimentModel('id', 'name', '123', false, ['1'], [], 'stam'),
            null);

        $this->assertEquals('xxx', $variant->getValue(null));
    }

    public function testWillReturnValueWhenOnEvaluation()
    {
        $parser = \Mockery::mock(ParserInterface::class)
            ->shouldReceive('evaluateExpression')
            ->andReturn(new EvaluationResult('2'))
            ->getMock();

        $variant = new RoxString('1', ['2', '3']);

        $variant->setForEvaluation($parser,
            new ExperimentModel('id', 'name', '123', false, ['1'], [], 'stam'),
            null);

        $this->assertEquals('2', $variant->getValue(null));
    }

    public function testWillRaiseImpression()
    {
        $parser = \Mockery::mock(ParserInterface::class)
            ->shouldReceive('evaluateExpression')
            ->andReturn(new EvaluationResult('2'))
            ->getMock();

        $variant = new RoxString('1', ['2', '3']);

        $internalFlags = \Mockery::mock(InternalFlagsInterface::class)
            ->shouldReceive('isEnabled')
            ->andReturn(false)
            ->getMock();

        $impInvoker = new XImpressionInvoker($internalFlags, null, null);
        $variant->setForEvaluation($parser,
            new ExperimentModel('id', 'name', '123', false, ['1'], [], 'stam'),
            $impInvoker);

        $isImpressionRaised = [false];
        $impInvoker->register(function (ImpressionArgs $e) use (&$isImpressionRaised) {
            $isImpressionRaised[0] = true;
        });

        $this->assertEquals('2', $variant->getValue());
        $this->assertTrue($isImpressionRaised[0]);
    }
}
