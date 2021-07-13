<?php

namespace Rox\Core\Entities;

use Rox\Core\Client\InternalFlagsInterface;
use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Context\ContextInterface;
use Rox\Core\Impression\XImpressionInvoker;
use Rox\Core\Repositories\ExperimentRepository;
use Rox\Core\Repositories\FlagRepository;
use Rox\Core\Roxx\ParserInterface;
use Rox\RoxTestCase;
use Rox\Server\Flags\RoxFlag;

class FlagSetterTests extends RoxTestCase
{
    public function testWillSetFlagData()
    {
        $flagRepo = new FlagRepository();
        $expRepo = new ExperimentRepository();
        $parser = \Mockery::mock(ParserInterface::class);
        $internalFlags = \Mockery::mock(InternalFlagsInterface::class);

        $impressionInvoker = new XImpressionInvoker($internalFlags, null, null);

        $flagRepo->addFlag(new RoxFlag(), "f1");
        $expRepo->setExperiments(
            [
                new ExperimentModel("33", "1", "1", false, ["f1"], [], "stam")
            ]);
        $flagSetter = new FlagSetter($flagRepo, $parser, $expRepo, $impressionInvoker);

        $flagSetter->setExperiments();

        $this->assertEquals($flagRepo->getFlag("f1")->getCondition(), "1");
        $this->assertEquals($flagRepo->getFlag("f1")->getParser(), $parser);
        $this->assertEquals($flagRepo->getFlag("f1")->getImpressionInvoker(), $impressionInvoker);
        $this->assertEquals($flagRepo->getFlag("f1")->getExperiment()->getId(), "33");
    }

    public function testWillNotSetForOtherFlag()
    {
        $flagRepo = new FlagRepository();
        $expRepo = new ExperimentRepository();
        $parser = \Mockery::mock(ParserInterface::class);
        $internalFlags = \Mockery::mock(InternalFlagsInterface::class);
        $impressionInvoker = new XImpressionInvoker($internalFlags, null, null);

        $flagRepo->addFlag(new RoxFlag(), "f1");
        $flagRepo->addFlag(new RoxFlag(), "f2");
        $expRepo->setExperiments(
            [
                new ExperimentModel("1", "1", "1", false, ["f1"], [], "stam")
            ]);
        $flagSetter = new FlagSetter($flagRepo, $parser, $expRepo, $impressionInvoker);

        $flagSetter->setExperiments();

        $this->assertEquals($flagRepo->getFlag("f2")->getCondition(), '');
        $this->assertEquals($flagRepo->getFlag("f2")->getParser(), $parser);
        $this->assertEquals($flagRepo->getFlag("f2")->getImpressionInvoker(), $impressionInvoker);
        $this->assertNull($flagRepo->getFlag("f2")->getExperiment());
    }

    public function testWillSetExperimentForFlagAndWillRemoveIt()
    {
        $flagRepo = new FlagRepository();
        $expRepo = new ExperimentRepository();
        $parser = \Mockery::mock(ParserInterface::class);
        $internalFlags = \Mockery::mock(InternalFlagsInterface::class);
        $impressionInvoker = new XImpressionInvoker($internalFlags, null, null);

        $flagSetter = new FlagSetter($flagRepo, $parser, $expRepo, $impressionInvoker);

        $flagRepo->addFlag(new RoxFlag(), "f2");

        $flagSetter->setExperiments();

        $this->assertEquals($flagRepo->getFlag("f2")->getCondition(), '');
        $this->assertEquals($flagRepo->getFlag("f2")->getParser(), $parser);
        $this->assertEquals($flagRepo->getFlag("f2")->getImpressionInvoker(), $impressionInvoker);
        $this->assertNull($flagRepo->getFlag("f2")->getExperiment());

        $expRepo->setExperiments(
            [
                new ExperimentModel("id1", "1", "con", false, ["f2"], [], "stam")
            ]);

        $flagSetter->setExperiments();

        $this->assertEquals($flagRepo->getFlag("f2")->getCondition(), "con");
        $this->assertEquals($flagRepo->getFlag("f2")->getParser(), $parser);
        $this->assertEquals($flagRepo->getFlag("f2")->getImpressionInvoker(), $impressionInvoker);
        $this->assertEquals($flagRepo->getFlag("f2")->getExperiment()->getId(), "id1");
    }


    public function testWillSetFlagWithoutExperimentAndThenAddExperiment()
    {
        $flagRepo = new FlagRepository();
        $expRepo = new ExperimentRepository();
        $parser = \Mockery::mock(ParserInterface::class);
        $internalFlags = \Mockery::mock(InternalFlagsInterface::class);
        $impressionInvoker = new XImpressionInvoker($internalFlags, null, null);

        $flagSetter = new FlagSetter($flagRepo, $parser, $expRepo, $impressionInvoker);

        $flagRepo->addFlag(new RoxFlag(), "f2");
        $expRepo->setExperiments(
            [
                new ExperimentModel("id1", "1", "con1", false, ["f2"], [], "stam")
            ]);

        $flagSetter->setExperiments();

        $this->assertEquals($flagRepo->getFlag("f2")->getCondition(), "con1");
        $this->assertEquals($flagRepo->getFlag("f2")->getParser(), $parser);
        $this->assertEquals($flagRepo->getFlag("f2")->getImpressionInvoker(), $impressionInvoker);
        $this->assertEquals($flagRepo->getFlag("f2")->getExperiment()->getId(), "id1");

        $expRepo->setExperiments([]);
        $flagSetter->setExperiments();

        $this->assertEquals($flagRepo->getFlag("f2")->getCondition(), '');
        $this->assertEquals($flagRepo->getFlag("f2")->getParser(), $parser);
        $this->assertEquals($flagRepo->getFlag("f2")->getImpressionInvoker(), $impressionInvoker);
        $this->assertNull($flagRepo->getFlag("f2")->getExperiment());
    }


    public function testWillSetDataForAddedFlag()
    {
        $flagRepo = new FlagRepository();
        $expRepo = new ExperimentRepository();
        $parser = \Mockery::mock(ParserInterface::class);
        $context = \Mockery::mock(ContextInterface::class);
        $internalFlags = \Mockery::mock(InternalFlagsInterface::class);
        $impressionInvoker = new XImpressionInvoker($internalFlags, null, null);

        $expRepo->setExperiments(
            [
                new ExperimentModel("1", "1", "1", false, ["f1"], [], "stam")
            ]);
        $flagSetter = new FlagSetter($flagRepo, $parser, $expRepo, $impressionInvoker);
        $flagSetter->setExperiments();

        $flagRepo->addFlag(new RoxFlag(), "f1");
        $flagRepo->addFlag(new RoxFlag(), "f2");

        $this->assertEquals($flagRepo->getFlag("f1")->getCondition(), "1");
        $this->assertEquals($flagRepo->getFlag("f2")->getCondition(), '');
        $this->assertEquals($flagRepo->getFlag("f1")->getParser(), $parser);
        $this->assertEquals($flagRepo->getFlag("f2")->getParser(), $parser);
        $this->assertEquals($flagRepo->getFlag("f1")->getImpressionInvoker(), $impressionInvoker);
        $this->assertEquals($flagRepo->getFlag("f2")->getImpressionInvoker(), $impressionInvoker);
        $this->assertEquals($flagRepo->getFlag("f1")->getExperiment()->getId(), "1");
        $this->assertNull($flagRepo->getFlag("f2")->getExperiment(), null);
    }
}
