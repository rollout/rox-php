<?php

namespace Rox\Core\Client;

use Rox\Core\Configuration\Models\ExperimentModel;
use Rox\Core\Entities\EntitiesProviderInterface;
use Rox\Core\Entities\Flag;
use Rox\Core\Entities\FlagSetter;
use Rox\Core\Entities\Variant;
use Rox\Core\Repositories\ExperimentRepository;
use Rox\Core\Repositories\FlagRepository;
use Rox\Core\Roxx\Parser;
use Rox\RoxTestCase;

class DynamicApiTests extends RoxTestCase
{
    /**
     * @var EntitiesProviderInterface $_ep
     */
    private $_ep;

    protected function setUp()
    {
        parent::setUp();

        $this->_ep = \Mockery::mock(EntitiesProviderInterface::class)
            ->shouldReceive('createFlag')
            ->andReturnUsing(function ($defaultValue) {
                return new Flag($defaultValue);
            })
            ->byDefault()
            ->getMock()
            ->shouldReceive('createVariant')
            ->andReturnUsing(function ($defaultValue, array $options = []) {
                return new Variant($defaultValue, $options);
            })
            ->byDefault()
            ->getMock();
    }

    public function testIsEnabled()
    {
        $parser = new Parser();
        $flagRepo = new FlagRepository();
        $expRepo = new ExperimentRepository();
        $flagSetter = new FlagSetter($flagRepo, $parser, $expRepo, null);
        $dynamicApi = new DynamicApi($flagRepo, $this->_ep);

        $this->assertTrue($dynamicApi->isEnabled("default.newFlag", true));
        $this->assertTrue($flagRepo->getFlag("default.newFlag")->isEnabled(null));
        $this->assertFalse($dynamicApi->isEnabled("default.newFlag", false));
        $this->assertEquals(1, count($flagRepo->getAllFlags()));

        $expRepo->setExperiments(
            [
                new ExperimentModel("1", "default.newFlag", "and(true, true)", false, ["default.newFlag"], [], "stam")
            ]);
        $flagSetter->setExperiments();

        $this->assertTrue($dynamicApi->isEnabled("default.newFlag", false));
    }

    public function testIsEnabledAfterSetup()
    {
        $parser = new Parser();
        $flagRepo = new FlagRepository();
        $expRepo = new ExperimentRepository();
        $flagSetter = new FlagSetter($flagRepo, $parser, $expRepo, null);
        $dynamicApi = new DynamicApi($flagRepo, $this->_ep);

        $expRepo->setExperiments([
            new ExperimentModel("1", "default.newFlag", "and(true, true)", false, ["default.newFlag"], [], "stam")
        ]);
        $flagSetter->setExperiments();

        $this->assertTrue($dynamicApi->isEnabled("default.newFlag", false));
    }

    public function testGetValue()
    {
        $parser = new Parser();
        $flagRepo = new FlagRepository();
        $expRepo = new ExperimentRepository();
        $flagSetter = new FlagSetter($flagRepo, $parser, $expRepo, null);
        $dynamicApi = new DynamicApi($flagRepo, $this->_ep);

        $this->assertEquals("A", $dynamicApi->getValue("default.newVariant", "A", ["A", "B", "C"]));
        $this->assertEquals("A", $flagRepo->getFlag("default.newVariant")->getValue());
        $this->assertEquals("B", $dynamicApi->getValue("default.newVariant", "B", ["A", "B", "C"]));
        $this->assertEquals(1, count($flagRepo->getAllFlags()));

        $expRepo->setExperiments([
            new ExperimentModel("1", "default.newVariant", "ifThen(true, \"B\", \"A\")", false, ["default.newVariant"], [], "stam")
        ]);
        $flagSetter->setExperiments();

        $this->assertEquals("B", $dynamicApi->getValue("default.newVariant", "A", ["A", "B", "C"]));
    }
}
