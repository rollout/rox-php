<?php

namespace Rox\Core\Roxx;

use Rox\Core\Client\InternalFlagsInterface;
use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Context\ContextBuilder;
use Rox\Core\Context\ContextInterface;
use Rox\Core\CustomProperties\CustomProperty;
use Rox\Core\CustomProperties\CustomPropertyRepository;
use Rox\Core\CustomProperties\CustomPropertyType;
use Rox\Core\CustomProperties\DynamicProperties;
use Rox\Core\Entities\FlagSetter;
use Rox\Core\Impression\ImpressionArgs;
use Rox\Core\Impression\XImpressionInvoker;
use Rox\Core\Repositories\ExperimentRepository;
use Rox\Core\Repositories\ExperimentRepositoryInterface;
use Rox\Core\Repositories\FlagRepository;
use Rox\Core\Repositories\FlagRepositoryInterface;
use Rox\Core\Repositories\TargetGroupRepository;
use Rox\RoxTestCase;
use Rox\Server\Flags\RoxFlag;
use Rox\Server\Flags\RoxString;

class ExperimentsExtensionsTests extends RoxTestCase
{
    public function testCustomPropertyWithSimpleValue()
    {
        $parser = new Parser();
        $targetGroupsRepository = new TargetGroupRepository();
        $experimentsExtensions = new ExperimentsExtensions($parser, $targetGroupsRepository,
            \Mockery::mock(FlagRepositoryInterface::class),
            \Mockery::mock(ExperimentRepositoryInterface::class));
        $experimentsExtensions->extend();

        $this->assertEquals($parser->evaluateExpression("isInTargetGroup(\"targetGroup1\")")->boolValue(), false);
    }

    public function testIsInPercentageRange()
    {
        $parser = new Parser();
        $targetGroupsRepository = new TargetGroupRepository();
        $experimentsExtensions =
            new ExperimentsExtensions($parser, $targetGroupsRepository,
                \Mockery::mock(FlagRepositoryInterface::class),
                \Mockery::mock(ExperimentRepositoryInterface::class));
        $experimentsExtensions->extend();

        $this->assertEquals($parser->evaluateExpression("isInPercentageRange(0, 0.5, \"device2.seed2\")")->boolValue(), true);
    }

    public function testNotIsInPercentageRange()
    {
        $parser = new Parser();
        $targetGroupsRepository = new TargetGroupRepository();
        $experimentsExtensions =
            new ExperimentsExtensions($parser, $targetGroupsRepository,
                \Mockery::mock(FlagRepositoryInterface::class),
                \Mockery::mock(ExperimentRepositoryInterface::class));

        $experimentsExtensions->extend();

        $this->assertEquals($parser->evaluateExpression("isInPercentageRange(0.5, 1, \"device2.seed2\")")->boolValue(), false);
    }

    public function testGetBucket()
    {
        $parser = new Parser();
        $targetGroupsRepository = new TargetGroupRepository();
        $experimentsExtensions =
            new ExperimentsExtensions($parser, $targetGroupsRepository,
                \Mockery::mock(FlagRepositoryInterface::class),
                \Mockery::mock(ExperimentRepositoryInterface::class));

        $result = $experimentsExtensions->getBucket("device2.seed2");

        $this->assertEquals($result, 0.18721251450181298);
    }

    public function testFlagValueNoFlagNoExperiment()
    {
        $parser = new Parser();
        $targetGroupsRepository = new TargetGroupRepository();
        $experimentRepository = new ExperimentRepository();
        $flagRepository = new FlagRepository();

        $experimentsExtensions =
            new ExperimentsExtensions($parser, $targetGroupsRepository, $flagRepository, $experimentRepository);
        $experimentsExtensions->extend();

        $this->assertEquals($parser->evaluateExpression("flagValue(\"f1\")")->stringValue(), "false");
    }

    public function testFlagValueNoFlagEvaluateExperiment()
    {
        $parser = new Parser();
        $targetGroupsRepository = new TargetGroupRepository();
        $experimentRepository = new ExperimentRepository();
        $flagRepository = new FlagRepository();

        $experimentsExtensions =
            new ExperimentsExtensions($parser, $targetGroupsRepository, $flagRepository, $experimentRepository);
        $experimentsExtensions->extend();

        $experiments = [];
        $experiments[] = new ExperimentModel("id", "name", "\"op2\"", false, ["f1"], [], "stam");
        $experimentRepository->setExperiments($experiments);

        $this->assertEquals("op2", $parser->evaluateExpression("flagValue(\"f1\")")->stringValue());
    }


    public function testFlagValueFlagEvaluationDefault()
    {
        $parser = new Parser();
        $targetGroupsRepository = new TargetGroupRepository();
        $experimentRepository = new ExperimentRepository();
        $flagRepository = new FlagRepository();

        $experimentsExtensions =
            new ExperimentsExtensions($parser, $targetGroupsRepository, $flagRepository, $experimentRepository);
        $experimentsExtensions->extend();

        $v = new RoxString("op1", ["op2"]);
        $flagRepository->addFlag($v, "f1");
        $this->assertEquals("op1", $parser->evaluateExpression("flagValue(\"f1\")")->stringValue());
    }

    public function testFlagDependencyValue()
    {
        $parser = new Parser();
        $targetGroupsRepository = new TargetGroupRepository();
        $experimentRepository = new ExperimentRepository();
        $flagRepository = new FlagRepository();

        $experimentsExtensions =
            new ExperimentsExtensions($parser, $targetGroupsRepository, $flagRepository, $experimentRepository);
        $experimentsExtensions->extend();

        $f = new RoxFlag();
        $flagRepository->addFlag($f, "f1");

        $v = new RoxString("blue", ["red", "green"]);
        $flagRepository->addFlag($v, "v1");
        $v->setCondition("ifThen(eq(\"true\", flagValue(\"f1\")), \"red\", \"green\")");
        $v->setParser($parser);

        $this->assertEquals("green", $v->getValue());
    }


    public function testFlagDependencyImpressionHandler()
    {
        $parser = new Parser();
        $targetGroupsRepository = new TargetGroupRepository();
        $experimentRepository = new ExperimentRepository();
        $flagRepository = new FlagRepository();
        $internalFlags = \Mockery::mock(InternalFlagsInterface::class);
        $ii = new XImpressionInvoker($internalFlags, null, null);
        $experimentsExtensions =
            new ExperimentsExtensions($parser, $targetGroupsRepository, $flagRepository, $experimentRepository);
        $experimentsExtensions->extend();

        $f = new RoxFlag();
        $flagRepository->addFlag($f, "f1");
        $f->setImpressionInvoker($ii);

        $impressionList = [];
        $v = new RoxString("blue", ["red", "green"]);
        $flagRepository->addFlag($v, "v1");
        $v->setCondition("ifThen(eq(\"true\", flagValue(\"f1\")), \"red\", \"green\")");
        $v->setParser($parser);
        $v->setImpressionInvoker($ii);

        $ii->register(function (ImpressionArgs $e) use (&$impressionList) {
            $impressionList[] = $e;
        });

        $this->assertEquals("green", $v->getValue());

        $this->assertCount(2, $impressionList);

        $this->assertEquals("f1", $impressionList[0]->getReportingValue()->getName());
        $this->assertEquals("false", $impressionList[0]->getReportingValue()->getValue());

        $this->assertEquals("v1", $impressionList[1]->getReportingValue()->getName());
        $this->assertEquals("green", $impressionList[1]->getReportingValue()->getValue());
    }

    public function testFlagDependency2LevelsBottomNotExists()
    {
        $parser = new Parser();
        $targetGroupsRepository = new TargetGroupRepository();
        $experimentRepository = new ExperimentRepository();
        $flagRepository = new FlagRepository();

        $experimentsExtensions =
            new ExperimentsExtensions($parser, $targetGroupsRepository, $flagRepository, $experimentRepository);
        $experimentsExtensions->extend();

        $f = new RoxFlag();
        $flagRepository->addFlag($f, "f1");
        $f->setParser($parser);
        $f->setCondition("flagValue(\"someFlag\")");

        $v = new RoxString("blue", ["red", "green"]);
        $flagRepository->addFlag($v, "v1");
        $v->setCondition("ifThen(eq(\"true\", flagValue(\"f1\")), \"red\", \"green\")");
        $v->setParser($parser);

        $this->assertEquals("green", $v->getValue());
    }

    public function testFlagDependencyUnexistingFlagButExistingExperiment()
    {
        $parser = new Parser();
        $flagRepository = new FlagRepository();
        $targetGroupRepository = new TargetGroupRepository();
        $experimentRepository = new ExperimentRepository();
        $experimentModels = [
            new ExperimentModel("exp1id", "exp1name", "ifThen(true, \"true\", \"false\")", false, ["someFlag"], [], "stam"),
            new ExperimentModel("exp2id", "exp2name", "ifThen(eq(\"true\", flagValue(\"someFlag\")), \"blue\", \"green\")", false, ["colorVar"], [], "stam")
        ];

        $flagSetter = new FlagSetter($flagRepository, $parser, $experimentRepository);

        $experimentRepository->setExperiments($experimentModels);
        $flagSetter->setExperiments();
        $experimentsExtensions =
            new ExperimentsExtensions($parser, $targetGroupRepository, $flagRepository, $experimentRepository);
        $experimentsExtensions->extend();

        $colorVar = new RoxString("red", ["red", "green", "blue"]);

        $colorVar->setParser($parser);
        $flagRepository->addFlag($colorVar, "colorVar");

        $result = $colorVar->getValue();

        $this->assertEquals("blue", $result);
    }

    public function testFlagDependencyUnexistingFlagAndExperimentUndefined()
    {
        $parser = new Parser();
        $flagRepository = new FlagRepository();
        $targetGroupRepository = new TargetGroupRepository();
        $experimentRepository = new ExperimentRepository();
        $experimentModels = [
            new ExperimentModel("exp1id", "exp1name", "undefined", false, ["someFlag"], [], "stam"),
            new ExperimentModel("exp2id", "exp2name", "ifThen(eq(\"true\", flagValue(\"someFlag\")), \"blue\", \"green\")", false, ["colorVar"], [], "stam")
        ];

        $flagSetter = new FlagSetter($flagRepository, $parser, $experimentRepository);

        $experimentRepository->setExperiments($experimentModels);
        $flagSetter->setExperiments();
        $experimentsExtensions =
            new ExperimentsExtensions($parser, $targetGroupRepository, $flagRepository, $experimentRepository);
        $experimentsExtensions->extend();

        $colorVar = new RoxString("red", ["red", "green", "blue"]);

        $colorVar->setParser($parser);
        $flagRepository->addFlag($colorVar, "colorVar");

        $result = $colorVar->getValue();

        $this->assertEquals("green", $result);
    }

    public function testFlagDependencyWithContext()
    {
        $parser = new Parser();
        $flagRepository = new FlagRepository();
        $targetGroupRepository = new TargetGroupRepository();
        $experimentRepository = new ExperimentRepository();
        $dynamicProperties = new DynamicProperties();
        $propertiesRepository = new CustomPropertyRepository();
        (new PropertiesExtensions($parser, $propertiesRepository, $dynamicProperties))->extend();
        (new ExperimentsExtensions($parser, $targetGroupRepository, $flagRepository, $experimentRepository))->extend();

        $propertiesRepository->addCustomProperty(new CustomProperty("prop", CustomPropertyType::getBool(), function (ContextInterface $context) {
            return (bool)$context->get("isPropOn");
        }));

        $flag1 = new RoxFlag();
        $flag1->setCondition("property(\"prop\")");
        $flag1->setParser($parser);
        $flagRepository->addFlag($flag1, "flag1");

        $flag2 = new RoxFlag();
        $flag2->setCondition("flagValue(\"flag1\")");
        $flag2->setParser($parser);
        $flagRepository->addFlag($flag2, "flag2");

        $flagValue = $flag2->isEnabled((new ContextBuilder())->build(["isPropOn" => true]));

        $this->assertTrue($flagValue);
    }

    public function testFlagDependencyWithContextUsedOnExperimentWithNoFlag()
    {
        $parser = new Parser();
        $flagRepository = new FlagRepository();
        $targetGroupRepository = new TargetGroupRepository();
        $experimentRepository = new ExperimentRepository();
        $dynamicProperties = new DynamicProperties();
        $propertiesRepository = new CustomPropertyRepository();
        (new PropertiesExtensions($parser, $propertiesRepository, $dynamicProperties))->extend();
        (new ExperimentsExtensions($parser, $targetGroupRepository, $flagRepository, $experimentRepository))->extend();

        $propertiesRepository->addCustomProperty(new CustomProperty("prop", CustomPropertyType::getBool(), function (ContextInterface $context) {
            return (bool)$context->get("isPropOn");
        }));

        $flag3 = new RoxFlag();
        $flag3->setCondition("flagValue(\"flag2\")");
        $flag3->setParser($parser);
        $flagRepository->addFlag($flag3, "flag3");

        $experimentModels = [new ExperimentModel("exp1id", "exp1name", "property(\"prop\")", false, ["flag2"], [], "stam")];

        $experimentRepository->setExperiments($experimentModels);

        $flagValue = $flag3->isEnabled((new ContextBuilder())->build(["isPropOn" => true]));

        $this->assertTrue($flagValue);
    }

    public function testFlagDependencyWithContext2LevelMidLevelNoFlagEvalExperiment()
    {
        $parser = new Parser();
        $flagRepository = new FlagRepository();
        $targetGroupRepository = new TargetGroupRepository();
        $experimentRepository = new ExperimentRepository();
        $dynamicProperties = new DynamicProperties();
        $propertiesRepository = new CustomPropertyRepository();
        (new PropertiesExtensions($parser, $propertiesRepository, $dynamicProperties))->extend();
        (new ExperimentsExtensions($parser, $targetGroupRepository, $flagRepository, $experimentRepository))->extend();

        $propertiesRepository->addCustomProperty(new CustomProperty("prop", CustomPropertyType::getBool(), function (ContextInterface $context) {
            return (bool)$context->get("isPropOn");
        }));

        $flag1 = new RoxFlag();
        $flag1->setCondition("property(\"prop\")");
        $flag1->setParser($parser);
        $flagRepository->addFlag($flag1, "flag1");

        $flag3 = new RoxFlag();
        $flag3->setCondition("flagValue(\"flag2\")");
        $flag3->setParser($parser);
        $flagRepository->addFlag($flag3, "flag3");

        $experimentModels = [new ExperimentModel("exp1id", "exp1name", "flagValue(\"flag1\")", false, ["flag2"], [], "stam")];

        $experimentRepository->setExperiments($experimentModels);

        $flagValue = $flag3->isEnabled((new ContextBuilder())->build(["isPropOn" => true]));

        $this->assertTrue($flagValue);
    }
}
