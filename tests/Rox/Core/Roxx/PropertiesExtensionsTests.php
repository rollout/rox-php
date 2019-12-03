<?php

namespace Rox\Core\Roxx;

use Rox\Core\Context\ContextBuilder;
use Rox\Core\Context\ContextInterface;
use Rox\Core\CustomProperties\CustomProperty;
use Rox\Core\CustomProperties\CustomPropertyRepository;
use Rox\Core\CustomProperties\CustomPropertyType;
use Rox\Core\CustomProperties\DynamicProperties;
use Rox\RoxTestCase;

class PropertiesExtensionsTests extends RoxTestCase
{
    public function testRoxxPropertiesExtensionsString()
    {
        $customPropertiesRepository = new CustomPropertyRepository();
        $parser = new Parser();
        $dynamicProperties = new DynamicProperties();
        $roxxPropertiesExtensions =
            new PropertiesExtensions($parser, $customPropertiesRepository, $dynamicProperties);

        $roxxPropertiesExtensions->extend();

        $customPropertiesRepository->addCustomProperty(
            new CustomProperty("testKey", CustomPropertyType::getString(), "test"));

        $this->assertEquals($parser->evaluateExpression("eq(\"test\", property(\"testKey\"))")->boolValue(), true);
    }

    public function testRoxxPropertiesExtensionsInt()
    {
        $customPropertiesRepository = new CustomPropertyRepository();
        $parser = new Parser();
        $dynamicProperties = new DynamicProperties();
        $roxxPropertiesExtensions =
            new PropertiesExtensions($parser, $customPropertiesRepository, $dynamicProperties);

        $roxxPropertiesExtensions->extend();

        $customPropertiesRepository->addCustomProperty(
            new CustomProperty("testKey", CustomPropertyType::getInt(), 3));

        $this->assertEquals($parser->evaluateExpression("eq(3, property(\"testKey\"))")->boolValue(), true);
    }

    public function testRoxxPropertiesExtensionsDouble()
    {
        $customPropertiesRepository = new CustomPropertyRepository();
        $parser = new Parser();
        $dynamicProperties = new DynamicProperties();
        $roxxPropertiesExtensions =
            new PropertiesExtensions($parser, $customPropertiesRepository, $dynamicProperties);

        $roxxPropertiesExtensions->extend();

        $customPropertiesRepository->addCustomProperty(
            new CustomProperty("testKey", CustomPropertyType::getDouble(), 3.3));

        $this->assertEquals($parser->evaluateExpression("eq(3.3, property(\"testKey\"))")->boolValue(), true);
    }


    public function testRoxxPropertiesExtensionsWithContextString()
    {
        $customPropertiesRepository = new CustomPropertyRepository();
        $parser = new Parser();
        $dynamicProperties = new DynamicProperties();
        $roxxPropertiesExtensions =
            new PropertiesExtensions($parser, $customPropertiesRepository, $dynamicProperties);

        $roxxPropertiesExtensions->extend();

        $customPropertiesRepository->addCustomProperty(new CustomProperty("CustomPropertyTestKey",
            CustomPropertyType::getString(), function (ContextInterface $c) {
                return (string)$c->get("ContextTestKey");
            }));

        $context = (new ContextBuilder())->build(["ContextTestKey" => "test"]);
        $this->assertEquals($parser->evaluateExpression("eq(\"test\", property(\"CustomPropertyTestKey\"))", $context)->boolValue(), true);
    }

    public function testRoxxPropertiesExtensionsWithContextInt()
    {
        $customPropertiesRepository = new CustomPropertyRepository();
        $parser = new Parser();
        $dynamicProperties = new DynamicProperties();
        $roxxPropertiesExtensions =
            new PropertiesExtensions($parser, $customPropertiesRepository, $dynamicProperties);

        $roxxPropertiesExtensions->extend();

        $customPropertiesRepository->addCustomProperty(new CustomProperty("CustomPropertyTestKey",
            CustomPropertyType::getInt(), function (ContextInterface $c) {
                return (int)(string)$c->get("ContextTestKey");
            }));

        $context = (new ContextBuilder())->build(["ContextTestKey" => 3]);
        $this->assertEquals($parser->evaluateExpression("eq(3, property(\"CustomPropertyTestKey\"))", $context)->boolValue(), true);
    }

    public function testRoxxPropertiesExtensionsWithContextIntWithString()
    {
        $customPropertiesRepository = new CustomPropertyRepository();
        $parser = new Parser();
        $dynamicProperties = new DynamicProperties();
        $roxxPropertiesExtensions =
            new PropertiesExtensions($parser, $customPropertiesRepository, $dynamicProperties);

        $roxxPropertiesExtensions->extend();

        $customPropertiesRepository->addCustomProperty(new CustomProperty("CustomPropertyTestKey",
            CustomPropertyType::getInt(), function (ContextInterface $c) {
                return (int)(string)$c->get("ContextTestKey");
            }));

        $context = (new ContextBuilder())->build(["ContextTestKey" => 3]);
        $this->assertEquals($parser->evaluateExpression("eq(\"3\", property(\"CustomPropertyTestKey\"))", $context)->boolValue(), false);
    }

    public function testRoxxPropertiesExtensionsWithContextIntNotEqual()
    {
        $customPropertiesRepository = new CustomPropertyRepository();
        $parser = new Parser();
        $dynamicProperties = new DynamicProperties();
        $roxxPropertiesExtensions =
            new PropertiesExtensions($parser, $customPropertiesRepository, $dynamicProperties);

        $roxxPropertiesExtensions->extend();

        $customPropertiesRepository->addCustomProperty(new CustomProperty("CustomPropertyTestKey",
            CustomPropertyType::getInt(), function (ContextInterface $c) {
                return (int)(string)$c->get("ContextTestKey");
            }));

        $context = (new ContextBuilder())->build(["ContextTestKey" => 3]);
        $this->assertEquals($parser->evaluateExpression("eq(4, property(\"CustomPropertyTestKey\"))", $context)->boolValue(), false);
    }

    public function testUnknownProperty()
    {
        $customPropertiesRepository = new CustomPropertyRepository();
        $parser = new Parser();
        $dynamicProperties = new DynamicProperties();
        $roxxPropertiesExtensions =
            new PropertiesExtensions($parser, $customPropertiesRepository, $dynamicProperties);

        $roxxPropertiesExtensions->extend();

        $customPropertiesRepository->addCustomProperty(
            new CustomProperty("testKey", CustomPropertyType::getString(), "test"));

        $this->assertEquals($parser->evaluateExpression("eq(\"test\", property(\"testKey1\"))")->boolValue(), false);
    }

    public function testNullProperty()
    {
        $customPropertiesRepository = new CustomPropertyRepository();
        $parser = new Parser();
        $dynamicProperties = new DynamicProperties();
        $roxxPropertiesExtensions =
            new PropertiesExtensions($parser, $customPropertiesRepository, $dynamicProperties);

        $roxxPropertiesExtensions->extend();

        $customPropertiesRepository->addCustomProperty(
            new CustomProperty("testKey", CustomPropertyType::getString(), function (ContextInterface $c) {
                return null;
            }));

        $this->assertEquals($parser->evaluateExpression("eq(undefined, property(\"testKey\"))")->boolValue(), true);
    }

    public function testDefaultDynamicRule()
    {
        $customPropertiesRepository = new CustomPropertyRepository();
        $parser = new Parser();
        $dynamicProperties = new DynamicProperties();
        $roxxPropertiesExtensions =
            new PropertiesExtensions($parser, $customPropertiesRepository, $dynamicProperties);

        $roxxPropertiesExtensions->extend();

        $context = (new ContextBuilder())->build(["testKeyRule" => "test"]);
        $this->assertEquals($parser->evaluateExpression("eq(\"test\", property(\"testKeyRule\"))", $context)->boolValue(), true);
    }

    public function testCustomDynamicRule()
    {
        $customPropertiesRepository = new CustomPropertyRepository();
        $parser = new Parser();
        $dynamicProperties = new DynamicProperties();
        $roxxPropertiesExtensions =
            new PropertiesExtensions($parser, $customPropertiesRepository, $dynamicProperties);

        $dynamicProperties->setDynamicPropertiesRule(function ($propName, ContextInterface $ctx) {
            return ((int)$ctx->get($propName)) + 1;
        });

        $roxxPropertiesExtensions->extend();

        $context = (new ContextBuilder())->build(["testKeyRule" => 5]);
        $this->assertEquals($parser->evaluateExpression("eq(6, property(\"testKeyRule\"))", $context)->boolValue(), true);
    }

    public function testDynamicRuleReturnsNull()
    {
        $customPropertiesRepository = new CustomPropertyRepository();
        $parser = new Parser();
        $dynamicProperties = new DynamicProperties();
        $roxxPropertiesExtensions =
            new PropertiesExtensions($parser, $customPropertiesRepository, $dynamicProperties);

        $roxxPropertiesExtensions->extend();

        $context = (new ContextBuilder())->build(["testKeyRule" => null]);
        $this->assertEquals($parser->evaluateExpression("eq(undefined, property(\"testKeyRule\"))", $context)->boolValue(), true);
    }

    public function testDynamicRuleReturnsSupportedType()
    {
        $customPropertiesRepository = new CustomPropertyRepository();
        $parser = new Parser();
        $dynamicProperties = new DynamicProperties();
        $roxxPropertiesExtensions =
            new PropertiesExtensions($parser, $customPropertiesRepository, $dynamicProperties);

        $roxxPropertiesExtensions->extend();

        $context = (new ContextBuilder())->build([
            "testKeyRule" => "test1",
            "testKeyRule2" => true,
            "testKeyRule3" => 3.9999,
            "testKeyRule4" => 100
        ]);

        $this->assertEquals($parser->evaluateExpression("eq(\"test1\", property(\"testKeyRule\"))", $context)->boolValue(), true);
        $this->assertEquals($parser->evaluateExpression("eq(true, property(\"testKeyRule2\"))", $context)->boolValue(), true);
        $this->assertEquals($parser->evaluateExpression("eq(3.9999, property(\"testKeyRule3\"))", $context)->boolValue(), true);
        $this->assertEquals($parser->evaluateExpression("eq(100, property(\"testKeyRule4\"))", $context)->boolValue(), true);
    }

    public function testDynamicRuleReturnUnsupportedType()
    {
        $customPropertiesRepository = new CustomPropertyRepository();
        $parser = new Parser();
        $dynamicProperties = new DynamicProperties();
        $roxxPropertiesExtensions =
            new PropertiesExtensions($parser, $customPropertiesRepository, $dynamicProperties);

        $roxxPropertiesExtensions->extend();

        $context = (new ContextBuilder())->build(["testKeyRule", []]);
        $this->assertEquals($parser->evaluateExpression("eq(undefined, property(\"testKeyRule\"))", $context)->boolValue(), true);
    }
}
