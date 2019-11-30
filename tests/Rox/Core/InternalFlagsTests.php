<?php

namespace Rox\Core;

use PHPUnit\Framework\TestCase;
use Rox\Core\Client\InternalFlags;
use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Entities\Flag;
use Rox\Core\Repositories\ExperimentRepositoryInterface;
use Rox\Core\Roxx\EvaluationResult;
use Rox\Core\Roxx\ParserInterface;

class InternalFlagsTests extends TestCase
{
    protected function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
    }

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
            ->andReturn(new EvaluationResult(Flag::FLAG_FALSE_VALUE))
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
            ->andReturn(new EvaluationResult(Flag::FLAG_TRUE_VALUE))
            ->getMock();

        $expRepo = \Mockery::mock(ExperimentRepositoryInterface::class)
            ->shouldReceive('getExperimentByFlag')
            ->andReturn(new ExperimentModel('id', 'name', 'stam', false, null, null, 'stam'))
            ->getMock();

        $internalFlags = new InternalFlags($expRepo, $parser);
        $this->assertTrue($internalFlags->isEnabled("stam"));
    }
}
