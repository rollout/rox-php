<?php

namespace Rox\Core\Client;

use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Entities\BoolFlagValueConverter;
use Rox\Core\Repositories\ExperimentRepositoryInterface;
use Rox\Core\Roxx\EvaluationResult;
use Rox\Core\Roxx\ParserInterface;
use Rox\RoxTestCase;

class InternalFlagsTests extends RoxTestCase
{
    public function testWillReturnFalseWhenNoExperiment()
    {
        $parser = \Mockery::mock(ParserInterface::class);
        $expRepo = \Mockery::mock(ExperimentRepositoryInterface::class)
            ->shouldReceive('getExperimentByFlag')
            ->andReturn(null)
            ->getMock();

        $internalFlags = new InternalFlags($expRepo, $parser);
        $this->assertFalse($internalFlags->isEnabled('stam'));
    }

    public function testWillReturnFalseWhenExpressionIsNull()
    {
        $parser = \Mockery::mock(ParserInterface::class)
            ->shouldReceive('evaluateExpression')
            ->andReturn(new EvaluationResult(null))
            ->getMock();

        $expRepo = \Mockery::mock(ExperimentRepositoryInterface::class)
            ->shouldReceive('getExperimentByFlag')
            ->andReturn(new ExperimentModel('id', 'name', 'stam', false, null, null, 'stam'))
            ->getMock();

        $internalFlags = new InternalFlags($expRepo, $parser);
        $this->assertFalse($internalFlags->isEnabled('stam'));
    }

    public function testWillReturnFalseWhenExpressionIsFalse()
    {
        $parser = \Mockery::mock(ParserInterface::class)
            ->shouldReceive('evaluateExpression')
            ->andReturn(new EvaluationResult(BoolFlagValueConverter::FLAG_FALSE_VALUE))
            ->getMock();

        $expRepo = \Mockery::mock(ExperimentRepositoryInterface::class)
            ->shouldReceive('getExperimentByFlag')
            ->andReturn(new ExperimentModel('id', 'name', 'stam', false, null, null, 'stam'))
            ->getMock();

        $internalFlags = new InternalFlags($expRepo, $parser);

        $this->assertFalse($internalFlags->isEnabled('stam'));
    }

    public function testWillReturnTrueWhenExpressionIsTrue()
    {
        $parser = \Mockery::mock(ParserInterface::class)
            ->shouldReceive('evaluateExpression')
            ->andReturn(new EvaluationResult(BoolFlagValueConverter::FLAG_TRUE_VALUE))
            ->getMock();

        $expRepo = \Mockery::mock(ExperimentRepositoryInterface::class)
            ->shouldReceive('getExperimentByFlag')
            ->andReturn(new ExperimentModel('id', 'name', 'stam', false, null, null, 'stam'))
            ->getMock();

        $internalFlags = new InternalFlags($expRepo, $parser);
        $this->assertTrue($internalFlags->isEnabled("stam"));
    }
}
