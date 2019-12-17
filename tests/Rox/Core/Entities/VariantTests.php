<?php

namespace Rox\Core\Entities;

use Rox\Core\Client\InternalFlagsInterface;
use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Impression\ImpressionArgs;
use Rox\Core\Impression\XImpressionInvoker;
use Rox\Core\Roxx\EvaluationResult;
use Rox\Core\Roxx\ParserInterface;
use Rox\RoxTestCase;

class VariantTests extends RoxTestCase
{
    public function testWillNotAddDefaultToOptionsIfExists()
    {
        $variant = new Variant('1', ['1', '2', '3']);

        $this->assertEquals(count($variant->getOptions()), 3);
    }

    public function testWillAddDefaultToOptionsIfNotExists()
    {
        $variant = new Variant('1', ['2', '3']);

        $this->assertEquals(count($variant->getOptions()), 3);
        $this->assertContains('1', $variant->getOptions());
    }

    public function testWillSetName()
    {
        $variant = new Variant('1', ['2', '3']);

        $this->assertEquals($variant->getName(), null);

        $variant->setName('bop');

        $this->assertEquals($variant->getName(), 'bop');
    }

    public function testWillReturnDefaultValueWhenNoParserOrCondition()
    {
        $variant = new Variant('1', ['2', '3']);

        $this->assertEquals($variant->getValue(null), '1');

        $parser = \Mockery::mock(ParserInterface::class);
        $variant->setForEvaluation($parser, null, null);

        $this->assertEquals($variant->getValue(null), '1');

        $variant->setForEvaluation(null, new ExperimentModel('id', 'name', '123', false, ['1'], [], 'stam'), null);

        $this->assertEquals($variant->getValue(null), '1');
    }

    public function testWillExpressionValueWhenResultNotInOptions()
    {
        $parser = \Mockery::mock(ParserInterface::class)
            ->shouldReceive('evaluateExpression')
            ->andReturn(new EvaluationResult('xxx'))
            ->getMock();

        $variant = new Variant('1', ['2', '3']);

        $variant->setForEvaluation($parser,
            new ExperimentModel('id', 'name', '123', false, ['1'], [], 'stam'),
            null);

        $this->assertEquals($variant->getValue(null), 'xxx');
    }

    public function testWillReturnValueWhenOnEvaluation()
    {
        $parser = \Mockery::mock(ParserInterface::class)
            ->shouldReceive('evaluateExpression')
            ->andReturn(new EvaluationResult('2'))
            ->getMock();

        $variant = new Variant('1', ['2', '3']);

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

        $variant = new Variant('1', ['2', '3']);

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
