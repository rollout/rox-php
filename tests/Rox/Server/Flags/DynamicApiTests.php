<?php

namespace Rox\Server\Flags;

use InvalidArgumentException;
use Rox\Core\Client\DynamicApi;
use Rox\Core\Entities\BooleanFlagInterface;
use Rox\Core\Entities\DoubleFlagInterface;
use Rox\Core\Entities\EntitiesProviderInterface;
use Rox\Core\Entities\IntFlagInterface;
use Rox\Core\Entities\StringFlagInterface;

class DynamicApiTests extends RoxFlagsTestCase
{
    /**
     * @var EntitiesProviderInterface $_ep
     */
    private $_ep;

    protected function setUp()
    {
        parent::setUp();

        $this->_ep = new ServerEntitiesProvider();
    }

    public function testIsEnabledNullName()
    {
        $this->expectException(InvalidArgumentException::class);
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $dynamicApi->isEnabled(null, false);
    }

    public function testIsEnabled()
    {
        $flagRepo = $this->getFlagRepository();
        $dynamicApi = new DynamicApi($flagRepo, $this->_ep);

        $this->assertTrue($dynamicApi->isEnabled("default.newFlag", true));
        $this->checkLastImpression("default.newFlag", "true");

        $this->assertInstanceOf(BooleanFlagInterface::class, $flagRepo->getFlag("default.newFlag"));
        $this->assertTrue($flagRepo->getFlag("default.newFlag")->getBooleanValue());
        $this->checkLastImpression("default.newFlag", "true");

        $this->assertFalse($dynamicApi->isEnabled("default.newFlag", false));
        $this->assertCount(1, $flagRepo->getAllFlags());
        $this->checkLastImpression("default.newFlag", "false");

        $this->setExperiments(["default.newFlag" => "and(true, true)"]);
        $this->assertTrue($dynamicApi->isEnabled("default.newFlag", false));
        $this->checkLastImpression("default.newFlag", "true", true);
    }

    public function testIsEnabledAfterSetup()
    {
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $this->setExperiments(["default.newFlag" => "and(true, true)"]);
        $this->assertTrue($dynamicApi->isEnabled("default.newFlag", false));
        $this->checkLastImpression("default.newFlag", "true", true);
    }

    public function testIsEnabledDifferentTypeCall()
    {
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);

        $this->assertFalse($dynamicApi->isEnabled("default.newFlag", false));
        $this->checkLastImpression("default.newFlag", "false");

        $this->setExperiments(["default.newFlag" => "ifThen(true, \"true\", \"true\")"]);

        $this->assertEquals(3.4, $dynamicApi->getDouble("default.newFlag", 3.4));
        $this->checkLastImpression("default.newFlag", "3.4", true);

        $this->assertEquals("true", $dynamicApi->getValue("default.newFlag", "1"));
        $this->checkLastImpression("default.newFlag", "true", true);

        $this->assertEquals(2, $dynamicApi->getInt("default.newFlag", 2));
        $this->checkLastImpression("default.newFlag", "2", true);
    }

    public function testIsEnabledWrongExperimentType()
    {
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $this->setExperiments(["default.newFlag" => "\"otherValue\""]);
        $this->assertFalse($dynamicApi->isEnabled("default.newFlag", false));
        $this->checkLastImpression("default.newFlag", "false", true);
    }

    public function testGetValueNullName()
    {
        $this->expectException(InvalidArgumentException::class);
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $dynamicApi->getValue(null, "A", ["A", "B", "C"]);
    }

    public function testGetValueVariationNull()
    {
        $this->expectException(InvalidArgumentException::class);
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $dynamicApi->getValue("default.newVariant", "A", ["A", "B", null]);
    }

    public function testGetValueVariationWrongTypeInt()
    {
        $this->expectException(InvalidArgumentException::class);
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $dynamicApi->getValue("default.newVariant", "A", ["A", "B", 1]);
    }

    public function testGetValueVariationWrongTypeBool()
    {
        $this->expectException(InvalidArgumentException::class);
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $dynamicApi->getValue("default.newVariant", "A", ["A", "B", true]);
    }

    public function testGetValueVariationWrongTypeDouble()
    {
        $this->expectException(InvalidArgumentException::class);
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $dynamicApi->getValue("default.newVariant", "A", ["A", "B", 1.2]);
    }

    public function testGetValueVariationNullWhenVariantExists()
    {
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $this->assertEquals("A", $dynamicApi->getValue("default.newVariant", "A", ["A", "B", "C"]));
        $this->assertEquals("A", $dynamicApi->getValue("default.newVariant", "A", ["A", "B", null]));
    }

    public function testGetValue()
    {
        $flagRepo = $this->getFlagRepository();
        $dynamicApi = new DynamicApi($flagRepo, $this->_ep);

        $this->assertEquals("A", $dynamicApi->getValue("default.newVariant", "A", ["A", "B", "C"]));
        $this->checkLastImpression("default.newVariant", "A");

        $this->assertInstanceOf(StringFlagInterface::class, $flagRepo->getFlag("default.newVariant"));
        $this->assertEquals("A", $flagRepo->getFlag("default.newVariant")->getStringValue());
        $this->checkLastImpression("default.newVariant", "A");

        $this->assertEquals("B", $dynamicApi->getValue("default.newVariant", "B", ["A", "B", "C"]));
        $this->assertCount(1, $flagRepo->getAllFlags());
        $this->checkLastImpression("default.newVariant", "B");

        $this->setExperiments(["default.newVariant" => "ifThen(true, \"B\", \"A\")"]);
        $this->assertEquals("B", $dynamicApi->getValue("default.newVariant", "A", ["A", "B", "C"]));
        $this->checkLastImpression("default.newVariant", "B", true);
    }

    public function testGetValueDifferentTypeCall()
    {
        $flagRepo = $this->getFlagRepository();
        $dynamicApi = new DynamicApi($flagRepo, $this->_ep);

        $this->assertEquals("value", $dynamicApi->getValue("default.newVariant", "value"));
        $this->checkLastImpression("default.newVariant", "value");

        $this->setExperiments(["default.newVariant" => "ifThen(true, \"val1\", \"val2\")"]);
        $this->assertEquals(3.4, $dynamicApi->getDouble("default.newVariant", 3.4));
        $this->checkLastImpression("default.newVariant", "3.4", true);

        $this->assertFalse($dynamicApi->isEnabled("default.newVariant", false));
        $this->checkLastImpression("default.newVariant", "false", true);

        $this->assertEquals(2, $dynamicApi->getInt("default.newVariant", 2));
        $this->checkLastImpression("default.newVariant", "2", true);

        $this->setExperiments(["default.newVariant" => "ifThen(true, \"true\", \"true\")"]);
        $this->assertTrue($dynamicApi->isEnabled("default.newVariant", false));
        $this->checkLastImpression("default.newVariant", "true", true);
    }

    public function testGetIntNullName()
    {
        $this->expectException(InvalidArgumentException::class);
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $dynamicApi->getInt(null, 1, [2, 3]);
    }

    public function testGetIntVariationNull()
    {
        $this->expectException(InvalidArgumentException::class);
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $dynamicApi->getInt("default.newVariant", 1, [2, null]);
    }

    public function testGetIntVariationNullWhenVariantExists()
    {
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $this->assertEquals(1, $dynamicApi->getInt("default.newVariant", 1, [2, 3]));
        $this->assertEquals(1, $dynamicApi->getInt("default.newVariant", 1, [2, null]));
    }

    public function testGetIntVariationWrongTypeString()
    {
        $this->expectException(InvalidArgumentException::class);
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $dynamicApi->getInt("default.newVariant", 1, [2, "3"]);
    }

    public function testGetIntVariationWrongTypeBool()
    {
        $this->expectException(InvalidArgumentException::class);
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $dynamicApi->getInt("default.newVariant", 1, [2, true]);
    }

    public function testGetIntVariationWrongTypeDouble()
    {
        $this->expectException(InvalidArgumentException::class);
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $dynamicApi->getInt("default.newVariant", 1, [2, 3.4]);
    }

    public function testGetInt()
    {
        $flagRepo = $this->getFlagRepository();
        $dynamicApi = new DynamicApi($flagRepo, $this->_ep);

        $this->assertEquals(1, $dynamicApi->getInt("default.newVariant", 1));
        $this->checkLastImpression("default.newVariant", "1");

        $this->assertInstanceOf(IntFlagInterface::class, $flagRepo->getFlag("default.newVariant"));
        $this->assertEquals(1, $flagRepo->getFlag("default.newVariant")->getIntValue());
        $this->checkLastImpression("default.newVariant", "1");

        $this->assertEquals(2, $dynamicApi->getInt("default.newVariant", 2));
        $this->checkLastImpression("default.newVariant", "2");
        $this->assertCount(1, $flagRepo->getAllFlags());

        $this->setExperiments(["default.newVariant" => "ifThen(true, \"3\", \"4\")"]);
        $this->assertEquals(3, $dynamicApi->getInt("default.newVariant", 1));
        $this->checkLastImpression("default.newVariant", "3", true);
    }

    public function testGetIntDifferentTypeCall()
    {
        $flagRepo = $this->getFlagRepository();
        $dynamicApi = new DynamicApi($flagRepo, $this->_ep);

        $this->assertEquals(1, $dynamicApi->getInt("default.newVariant", 1));
        $this->checkLastImpression("default.newVariant", "1");

        $this->setExperiments(["default.newVariant" => "ifThen(true, \"2\", \"3\")"]);
        $this->assertEquals(2, $dynamicApi->getDouble("default.newVariant", 3.4));
        $this->checkLastImpression("default.newVariant", "2", true);

        $this->assertEquals("2", $dynamicApi->getValue("default.newVariant", "1"));
        $this->checkLastImpression("default.newVariant", "2", true);

        $this->assertFalse($dynamicApi->isEnabled("default.newVariant", true));
        $this->checkLastImpression("default.newVariant", "false", true);
    }

    public function testGetIntWrongExperimentType()
    {
        $flagRepo = $this->getFlagRepository();
        $dynamicApi = new DynamicApi($flagRepo, $this->_ep);

        $this->assertEquals(1, $dynamicApi->getInt("default.newVariant", 1));
        $this->assertInstanceOf(IntFlagInterface::class, $flagRepo->getFlag("default.newVariant"));
        $this->assertEquals(2, $dynamicApi->getInt("default.newVariant", 2));
        $this->assertCount(1, $flagRepo->getAllFlags());

        $this->setExperiments(["default.newVariant" => "ifThen(true, \"3.5\", \"4.1\")"]);
        $this->assertEquals(2, $dynamicApi->getInt("default.newVariant", 2));
    }

    public function testGetDoubleNullName()
    {
        $this->expectException(InvalidArgumentException::class);
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $dynamicApi->getDouble(null, 1.1, [2.2, 3.3]);
    }

    public function testGetDoubleVariationNull()
    {
        $this->expectException(InvalidArgumentException::class);
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $dynamicApi->getDouble("default.newVariant", 1.1, [2.2, null]);
    }

    public function testGetDoubleVariationNullWhenVariantExists()
    {
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $this->assertEquals(1.1, $dynamicApi->getDouble("default.newVariant", 1.1, [2.2, 3.3]));
        $this->assertEquals(1.1, $dynamicApi->getDouble("default.newVariant", 1.1, [2.2, null]));
    }

    public function testGetDoubleVariationWrongTypeString()
    {
        $this->expectException(InvalidArgumentException::class);
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $dynamicApi->getDouble("default.newVariant", 1.1, [2.2, "3.3"]);
    }

    public function testGetDoubleVariationWrongTypeBool()
    {
        $this->expectException(InvalidArgumentException::class);
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $dynamicApi->getDouble("default.newVariant", 1.1, [2.2, true]);
    }

    public function testGetDoubleVariationTypeInt()
    {
        $dynamicApi = new DynamicApi($this->getFlagRepository(), $this->_ep);
        $this->assertEquals(1.1, $dynamicApi->getDouble("default.newVariant", 1.1, [2.2, 3])); // int is ok
    }

    public function testGetDouble()
    {
        $flagRepo = $this->getFlagRepository();
        $dynamicApi = new DynamicApi($flagRepo, $this->_ep);

        $this->assertEquals(1.1, $dynamicApi->getDouble("default.newVariant", 1.1));
        $this->checkLastImpression("default.newVariant", "1.1");

        $this->assertInstanceOf(DoubleFlagInterface::class, $flagRepo->getFlag("default.newVariant"));

        $this->assertEquals(2.2, $dynamicApi->getDouble("default.newVariant", 2.2));
        $this->checkLastImpression("default.newVariant", "2.2");
        $this->assertCount(1, $flagRepo->getAllFlags());

        $this->setExperiments(["default.newVariant" => "ifThen(true, \"3.3\", \"4.4\")"]);
        $this->assertEquals(3.3, $dynamicApi->getDouble("default.newVariant", 2.2));
        $this->checkLastImpression("default.newVariant", "3.3", true);
    }

    public function testGetDoubleDifferentTypeCall()
    {
        $flagRepo = $this->getFlagRepository();
        $dynamicApi = new DynamicApi($flagRepo, $this->_ep);

        $this->assertEquals(2.1, $dynamicApi->getDouble("default.newVariant", 2.1));
        $this->checkLastImpression("default.newVariant", "2.1");

        $this->setExperiments(["default.newVariant" => "ifThen(true, \"3.3\", \"4.4\")"]);
        $this->assertEquals(1, $dynamicApi->getInt("default.newVariant", 1));
        $this->assertEquals("3.3", $dynamicApi->getValue("default.newVariant", "1"));
        $this->assertFalse($dynamicApi->isEnabled("default.newVariant", true));
    }

    public function testGetDoubleWrongExperimentType()
    {
        $flagRepo = $this->getFlagRepository();
        $dynamicApi = new DynamicApi($flagRepo, $this->_ep);

        $this->assertEquals(1.1, $dynamicApi->getDouble("default.newVariant", 1.1));
        $this->assertInstanceOf(DoubleFlagInterface::class, $flagRepo->getFlag("default.newVariant"));
        $this->assertEquals(2.2, $dynamicApi->getDouble("default.newVariant", 2.2));
        $this->assertCount(1, $flagRepo->getAllFlags());

        $this->setExperiments(["default.newVariant" => "ifThen(true, \"aaa\", \"bbb\")"]);
        $this->assertEquals(1, $dynamicApi->getDouble("default.newVariant", 1));
    }
}
