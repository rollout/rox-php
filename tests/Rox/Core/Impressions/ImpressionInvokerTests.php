<?php

namespace Rox\Core\Impressions;

use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Client\InternalFlagsInterface;
use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Consts\PropertyType;
use Rox\Core\Context\ContextBuilder;
use Rox\Core\CustomProperties\CustomProperty;
use Rox\Core\CustomProperties\CustomPropertyType;
use Rox\Core\Impression\ImpressionArgs;
use Rox\Core\Impression\ImpressionInvokerInterface;
use Rox\Core\Impression\Models\Experiment;
use Rox\Core\Impression\Models\ReportingValue;
use Rox\Core\Impression\XImpressionInvoker;
use Rox\Core\Repositories\CustomPropertyRepositoryInterface;
use Rox\Core\Utils\TimeUtils;
use Rox\Core\XPack\Analytics\ClientInterface;
use Rox\Core\XPack\Analytics\Model\Event;
use Rox\RoxTestCase;

class ImpressionInvokerTests extends RoxTestCase
{
    public function testWillSetImpressionInvokerEmptyInvokeNotThrowingException()
    {
        $internalFlags = \Mockery::mock(InternalFlagsInterface::class)
            ->shouldReceive('isEnabled')
            ->andThrow(\Exception::class)
            ->getMock();

        $impressionInvoker = new XImpressionInvoker($internalFlags, null, null);
        $impressionInvoker->invoke(new ReportingValue('foo', 'bar'), null, null);
    }

    public function testWillTestImpressionInvokerInvokeAndParameters()
    {
        $internalFlags = \Mockery::mock(InternalFlagsInterface::class);
        $impressionInvoker = new XImpressionInvoker($internalFlags, null, null);

        $context = (new ContextBuilder())->build(['obj1' => 1]);

        $reportingValue = new ReportingValue('name', 'value');

        $originalExperiment = new ExperimentModel('id', 'name', 'cond', true, null, ['label1'], 'stam');
        $experiment = new Experiment($originalExperiment);

        $isImpressionRaised = [false];
        $impressionInvoker->register(function (ImpressionArgs $e) use (
            $context,
            $experiment,
            $reportingValue,
            $impressionInvoker,
            &$isImpressionRaised
        ) {

            $this->assertEquals($e->getReportingValue(), $reportingValue);
            $this->assertEquals($e->getExperiment()->getIdentifier(), $experiment->getIdentifier());
            $this->assertEquals($e->getExperiment()->getName(), $experiment->getName());
            $this->assertEquals($e->getExperiment()->isArchived(), $experiment->isArchived());
            $this->assertEquals(count($e->getExperiment()->getLabels()), count($experiment->getLabels()));
            $this->assertEquals(count($e->getExperiment()->getLabels()), 1);
            $this->assertEquals($e->getExperiment()->getLabels()[0], 'label1');
            $this->assertEquals($e->getContext(), $context);

            $isImpressionRaised[0] = true;
        });

        $impressionInvoker->invoke($reportingValue, $originalExperiment, $context);

        $this->assertTrue($isImpressionRaised[0]);
    }

    public function testExperimentConstructor()
    {
        $originalExperiment = new ExperimentModel('id', 'name', 'cond', true, null, ['name1'], 'stam');

        $experiment = new Experiment($originalExperiment);

        $this->assertEquals($originalExperiment->getName(), $experiment->getName());
        $this->assertEquals($originalExperiment->getId(), $experiment->getIdentifier());
        $this->assertEquals($originalExperiment->isArchived(), $experiment->isArchived());
        $this->assertEquals($originalExperiment->getLabels()[0], 'name1');
    }

    public function testReportingValueConstructor()
    {
        $reportingValue = new ReportingValue('pi', 'ka');

        $this->assertEquals('pi', $reportingValue->getName());
        $this->assertEquals('ka', $reportingValue->getValue());
    }

    public function testImpressionArgsConstructor()
    {
        $context = (new ContextBuilder())->build(['obj1' => 1]);

        $reportingValue = new ReportingValue('name', 'value');

        $originalExperiment = new ExperimentModel('id', 'name', 'cond', true, null, [], 'stam');
        $experiment = new Experiment($originalExperiment);

        $impressionArgs = new ImpressionArgs($reportingValue, $experiment, $context);

        $this->assertEquals($reportingValue, $impressionArgs->getReportingValue());
        $this->assertEquals($experiment, $impressionArgs->getExperiment());
        $this->assertEquals($context, $impressionArgs->getContext());
    }

    public function testWillNotInvokeAnalyticsWhenFlagIsOff()
    {
        $internalFlags = \Mockery::mock(InternalFlagsInterface::class);
        $analytics = \Mockery::mock(ClientInterface::class);
        $impressionInvoker = new XImpressionInvoker($internalFlags, null, null);

        $context = (new ContextBuilder())->build(['obj1' => 1]);

        $reportingValue = new ReportingValue('name', 'value');

        $originalExperiment = new ExperimentModel('id', 'name', 'cond', true, null, [], 'stam');
        $experiment = new Experiment($originalExperiment);

        $isImpressionRaised = [false];
        $impressionInvoker->register(function (ImpressionArgs $e) use ($context, $experiment, $reportingValue, $impressionInvoker, &$isImpressionRaised) {

            $this->assertEquals($e->getReportingValue(), $reportingValue);
            $this->assertEquals($e->getExperiment()->getIdentifier(), $experiment->getIdentifier());
            $this->assertEquals($e->getContext(), $context);

            $isImpressionRaised[0] = true;
        });

        $impressionInvoker->invoke($reportingValue, $originalExperiment, $context);

        $this->assertTrue($isImpressionRaised[0]);
    }

    public function testWillNotInvokeAnalyticsWhenIsRoxy()
    {
        $internalFlags = \Mockery::mock(InternalFlagsInterface::class)
            ->shouldReceive('isEnabled')
            ->with('rox.internal.analytics')
            ->andReturn(true)
            ->getMock();

        $customProps = \Mockery::mock(CustomPropertyRepositoryInterface::class)
            ->shouldReceive('getCustomProperty')
            ->andReturnUsing(function ($arg) {
                if ($arg == 'rox.' . PropertyType::getDistinctId()->getName()) {
                    return new CustomProperty('rox.' . PropertyType::getDistinctId()->getName(), CustomPropertyType::getString(), 'stam');
                }
                return null;
            })
            ->getMock();

        $deviceProps = \Mockery::mock(DevicePropertiesInterface::class)
            ->shouldReceive('getDistinctId')
            ->andReturn('stamId')
            ->getMock();

        $analytics = \Mockery::mock(ClientInterface::class)
            ->shouldNotReceive('track')
            ->getMock();

        // FIXME: bad test, it doesn't pass analytics client into constructor and doesn't expect it to be called.
        // FIXME: (ported from .NET code as is).
        $impressionInvoker = new XImpressionInvoker($internalFlags, $customProps, null);

        $context = (new ContextBuilder())->build(['obj1' => 1]);

        $reportingValue = new ReportingValue('name', 'value');

        $originalExperiment = new ExperimentModel('id', 'name', 'cond', true, null, [], 'stam');
        $experiment = new Experiment($originalExperiment);

        $impressionInvoker->register(function (ImpressionArgs $e) use ($context, $experiment, $reportingValue, $impressionInvoker) {

            $this->assertEquals($e->getReportingValue(), $reportingValue);
            $this->assertEquals($e->getExperiment()->getIdentifier(), $experiment->getIdentifier());
            $this->assertEquals($e->getContext(), $context);
        });

        $impressionInvoker->invoke($reportingValue, $originalExperiment, $context);
    }

    public function testWillInvokeAnalytics()
    {
        $internalFlags = \Mockery::mock(InternalFlagsInterface::class)
            ->shouldReceive('isEnabled')
            ->with('rox.internal.analytics')
            ->andReturn(true)
            ->getMock();

        $customProps = \Mockery::mock(CustomPropertyRepositoryInterface::class)
            ->shouldReceive('getCustomProperty')
            ->andReturnUsing(function ($arg) {
                if ($arg == 'rox.' . PropertyType::getDistinctId()->getName()) {
                    return new CustomProperty('rox.' . PropertyType::getDistinctId()->getName(), CustomPropertyType::getString(), 'stam');
                }
                return null;
            })
            ->getMock();

        $deviceProps = \Mockery::mock(DevicePropertiesInterface::class)
            ->shouldReceive('getDistinctId')
            ->andReturn('stamId')
            ->getMock();

        $outEvent = [null];
        $analytics = \Mockery::mock(ClientInterface::class);
        $analytics->shouldReceive('track')
            ->with(\Mockery::on(function (Event $args) use (&$outEvent) {
                $outEvent[0] = $args;
                return true;
            }))
            ->once()
            ->getMock();

        $impressionInvoker = new XImpressionInvoker($internalFlags, $customProps, $analytics);

        $context = (new ContextBuilder())->build(['obj1' => 1]);

        $reportingValue = new ReportingValue('name', 'value');

        $originalExperiment = new ExperimentModel('id', 'name', 'cond', true, null, [], 'stam');
        $experiment = new Experiment($originalExperiment);

        $impressionInvoker->register(function (ImpressionArgs $e) use ($context, $experiment, $reportingValue, $impressionInvoker) {

            $this->assertEquals($e->getReportingValue(), $reportingValue);
            $this->assertEquals($e->getExperiment()->getIdentifier(), $experiment->getIdentifier());
            $this->assertEquals($e->getContext(), $context);
        });

        $impressionInvoker->invoke($reportingValue, $originalExperiment, $context);

        $this->assertEquals($outEvent[0]->getDistinctId(), 'stam');
        $this->assertEquals($outEvent[0]->getExperimentId(), 'id');
        $this->assertEquals($outEvent[0]->getExperimentVersion(), '0');
        $this->assertEquals($outEvent[0]->getFlag(), 'name');
        $this->assertEquals($outEvent[0]->getValue(), 'value');
        $this->assertEquals($outEvent[0]->getType(), 'IMPRESSION');
        $this->assertTrue($outEvent[0]->getTime() <= TimeUtils::currentTimeMillis());
    }

    public function testWillInvokeAnalyticsWithStickinessProp()
    {
        $internalFlags = \Mockery::mock(InternalFlagsInterface::class)
            ->shouldReceive('isEnabled')
            ->with('rox.internal.analytics')
            ->andReturn(true)
            ->getMock();

        $customProps = \Mockery::mock(CustomPropertyRepositoryInterface::class)
            ->shouldReceive('getCustomProperty')
            ->andReturnUsing(function ($arg) {
                if ($arg == 'rox.' . PropertyType::getDistinctId()->getName()) {
                    return new CustomProperty('rox.' . PropertyType::getDistinctId()->getName(), CustomPropertyType::getString(), 'stamDist');
                }
                if ($arg == 'stickProp') {
                    return new CustomProperty('rox.' . PropertyType::getDistinctId()->getName(), CustomPropertyType::getString(), 'stamStick');
                }
                return null;
            })
            ->getMock();

        $deviceProps = \Mockery::mock(DevicePropertiesInterface::class)
            ->shouldReceive('getDistinctId')
            ->andReturn('stamId')
            ->getMock();

        $outEvent = [null];
        $analytics = \Mockery::mock(ClientInterface::class);
        $analytics->shouldReceive('track')
            ->with(\Mockery::on(function (Event $args) use (&$outEvent) {
                $outEvent[0] = $args;
                return true;
            }))
            ->once()
            ->getMock();

        $impressionInvoker = new XImpressionInvoker($internalFlags, $customProps, $analytics);

        $context = (new ContextBuilder())->build(['obj1' => 1]);

        $reportingValue = new ReportingValue('name', 'value');

        $originalExperiment = new ExperimentModel('id', 'name', 'cond', true, null, [], 'stickProp');
        $experiment = new Experiment($originalExperiment);

        $impressionInvoker->register(function (ImpressionArgs $e) use ($context, $experiment, $reportingValue, $impressionInvoker) {

            $this->assertEquals($e->getReportingValue(), $reportingValue);
            $this->assertEquals($e->getExperiment()->getIdentifier(), $experiment->getIdentifier());
            $this->assertEquals($e->getContext(), $context);
        });

        $impressionInvoker->invoke($reportingValue, $originalExperiment, $context);

        $this->assertNotNull($outEvent[0]);
        $this->assertEquals($outEvent[0]->getDistinctId(), 'stamStick');
        $this->assertEquals($outEvent[0]->getExperimentId(), 'id');
        $this->assertEquals($outEvent[0]->getExperimentVersion(), '0');
        $this->assertEquals($outEvent[0]->getFlag(), 'name');
        $this->assertEquals($outEvent[0]->getValue(), 'value');
        $this->assertEquals($outEvent[0]->getType(), 'IMPRESSION');
        $this->assertTrue($outEvent[0]->getTime() <= TimeUtils::currentTimeMillis());
    }

    public function testWillInvokeAnalyticsWithDefaultPropWhenNoStickinessProp()
    {
        $internalFlags = \Mockery::mock(InternalFlagsInterface::class)
            ->shouldReceive('isEnabled')
            ->with('rox.internal.analytics')
            ->andReturn(true)
            ->getMock();

        $customProps = \Mockery::mock(CustomPropertyRepositoryInterface::class)
            ->shouldReceive('getCustomProperty')
            ->andReturnUsing(function ($arg) {
                if ($arg == 'rox.' . PropertyType::getDistinctId()->getName()) {
                    return new CustomProperty('rox.' . PropertyType::getDistinctId()->getName(), CustomPropertyType::getString(), 'stamDist');
                }
                if ($arg == 'stickProp') {
                    return new CustomProperty('rox.' . PropertyType::getDistinctId()->getName(), CustomPropertyType::getString(), 'stamStick');
                }
                return null;
            })
            ->getMock();

        $deviceProps = \Mockery::mock(DevicePropertiesInterface::class)
            ->shouldReceive('getDistinctId')
            ->andReturn('stamId')
            ->getMock();

        $outEvent = [null];
        $analytics = \Mockery::mock(ClientInterface::class);
        $analytics->shouldReceive('track')
            ->with(\Mockery::on(function (Event $args) use (&$outEvent) {
                $outEvent[0] = $args;
                return true;
            }))
            ->once()
            ->getMock();

        $impressionInvoker = new XImpressionInvoker($internalFlags, $customProps, $analytics);

        $context = (new ContextBuilder())->build(['obj1' => 1]);

        $reportingValue = new ReportingValue('name', 'value');

        $originalExperiment = new ExperimentModel('id', 'name', 'cond', true, null, [], 'stickPropy');
        $experiment = new Experiment($originalExperiment);

        $impressionInvoker->register(function (ImpressionArgs $e) use ($context, $experiment, $reportingValue, $impressionInvoker) {

            $this->assertEquals($e->getReportingValue(), $reportingValue);
            $this->assertEquals($e->getExperiment()->getIdentifier(), $experiment->getIdentifier());
            $this->assertEquals($e->getContext(), $context);
        });

        $impressionInvoker->invoke($reportingValue, $originalExperiment, $context);

        $this->assertNotNull($outEvent[0]);
        $this->assertEquals($outEvent[0]->getDistinctId(), 'stamDist');
        $this->assertEquals($outEvent[0]->getExperimentId(), 'id');
        $this->assertEquals($outEvent[0]->getExperimentVersion(), '0');
        $this->assertEquals($outEvent[0]->getFlag(), 'name');
        $this->assertEquals($outEvent[0]->getValue(), 'value');
        $this->assertEquals($outEvent[0]->getType(), 'IMPRESSION');
        $this->assertTrue($outEvent[0]->getTime() <= TimeUtils::currentTimeMillis());
    }

    public function testWillInvokeAnalyticsWithBadDistinctId()
    {
        $internalFlags = \Mockery::mock(InternalFlagsInterface::class)
            ->shouldReceive('isEnabled')
            ->with('rox.internal.analytics')
            ->andReturn(true)
            ->getMock();

        $customProps = \Mockery::mock(CustomPropertyRepositoryInterface::class)
            ->shouldReceive('getCustomProperty')
            ->andReturn(null)
            ->getMock();

        $deviceProps = \Mockery::mock(DevicePropertiesInterface::class)
            ->shouldReceive('getDistinctId')
            ->andReturn('stamId')
            ->getMock();

        $outEvent = [null];
        $analytics = \Mockery::mock(ClientInterface::class);
        $analytics->shouldReceive('track')
            ->with(\Mockery::on(function (Event $args) use (&$outEvent) {
                $outEvent[0] = $args;
                return true;
            }))
            ->once()
            ->getMock();

        $impressionInvoker = new XImpressionInvoker($internalFlags, $customProps, $analytics);

        $context = (new ContextBuilder())->build(['obj1' => 1]);

        $reportingValue = new ReportingValue('name', 'value');

        $originalExperiment = new ExperimentModel('id', 'name', 'cond', true, null, [], 'stam');
        $experiment = new Experiment($originalExperiment);

        $impressionInvoker->register(function (ImpressionArgs $e) use ($context, $experiment, $reportingValue, $impressionInvoker) {

            $this->assertEquals($e->getReportingValue(), $reportingValue);
            $this->assertEquals($e->getExperiment()->getIdentifier(), $experiment->getIdentifier());
            $this->assertEquals($e->getContext(), $context);
        });

        $impressionInvoker->invoke($reportingValue, $originalExperiment, $context);

        $this->assertNotNull($outEvent[0]);
        $this->assertEquals($outEvent[0]->getDistinctId(), '(null_distinct_id');
        $this->assertEquals($outEvent[0]->getExperimentId(), 'id');
        $this->assertEquals($outEvent[0]->getExperimentVersion(), '0');
        $this->assertEquals($outEvent[0]->getFlag(), 'name');
        $this->assertEquals($outEvent[0]->getValue(), 'value');
        $this->assertEquals($outEvent[0]->getType(), 'IMPRESSION');
        $this->assertTrue($outEvent[0]->getTime() <= TimeUtils::currentTimeMillis());
    }
}
