<?php

namespace Rox\Core\CustomProperties;

use Rox\Core\Context\ContextBuilder;
use Rox\Core\Context\ContextInterface;
use Rox\RoxTestCase;

class CustomPropertyTests extends RoxTestCase
{
    public function testWillCreatePropertyWithConstValue()
    {
        $propString = new CustomProperty("prop1", CustomPropertyType::getString(), "123");

        $this->assertEquals($propString->getName(), "prop1");
        $this->assertEquals($propString->getType(), CustomPropertyType::getString());
        $this->assertEquals($propString->getValue()(null), "123");

        $propDouble = new CustomProperty("prop1", CustomPropertyType::getDouble(), 123.12);

        $this->assertEquals($propDouble->getName(), "prop1");
        $this->assertEquals($propDouble->getType(), CustomPropertyType::getDouble());
        $this->assertEquals($propDouble->getValue()(null), 123.12);

        $propInt = new CustomProperty("prop1", CustomPropertyType::getInt(), 123);

        $this->assertEquals($propInt->getName(), "prop1");
        $this->assertEquals($propInt->getType(), CustomPropertyType::getInt());
        $this->assertEquals($propInt->getValue()(null), 123);

        $propBool = new CustomProperty("prop1", CustomPropertyType::getBool(), true);

        $this->assertEquals($propBool->getName(), "prop1");
        $this->assertEquals($propBool->getType(), CustomPropertyType::getBool());
        $this->assertEquals($propBool->getValue()(null), true);

        $propSemver = new CustomProperty("prop1", CustomPropertyType::getSemver(), "1.2.3");

        $this->assertEquals($propSemver->getName(), "prop1");
        $this->assertEquals($propSemver->getType(), CustomPropertyType::getSemver());
        $this->assertEquals($propSemver->getValue()(null), "1.2.3");
    }

    public function testWillCreatePropertyWithFuncValue()
    {
        $propString = new CustomProperty("prop1", CustomPropertyType::getString(), function () {
            return "123";
        });

        $this->assertEquals($propString->getName(), "prop1");
        $this->assertEquals($propString->getType(), CustomPropertyType::getString());
        $this->assertEquals($propString->getValue()(null), "123");

        $propDouble = new CustomProperty("prop1", CustomPropertyType::getDouble(), function () {
            return 123.12;
        });

        $this->assertEquals($propDouble->getName(), "prop1");
        $this->assertEquals($propDouble->getType(), CustomPropertyType::getDouble());
        $this->assertEquals($propDouble->getValue()(null), 123.12);

        $propInt = new CustomProperty("prop1", CustomPropertyType::getInt(), function () {
            return 123;
        });

        $this->assertEquals($propInt->getName(), "prop1");
        $this->assertEquals($propInt->getType(), CustomPropertyType::getInt());
        $this->assertEquals($propInt->getValue()(null), 123);

        $propBool = new CustomProperty("prop1", CustomPropertyType::getBool(), function () {
            return true;
        });

        $this->assertEquals($propBool->getName(), "prop1");
        $this->assertEquals($propBool->getType(), CustomPropertyType::getBool());
        $this->assertEquals($propBool->getValue()(null), true);

        $propSemver = new CustomProperty("prop1", CustomPropertyType::getSemver(), function () {
            return "1.2.3";
        });

        $this->assertEquals($propSemver->getName(), "prop1");
        $this->assertEquals($propSemver->getType(), CustomPropertyType::getSemver());
        $this->assertEquals($propSemver->getValue()(null), "1.2.3");
    }

    public function testWillPassContext()
    {
        $context = (new ContextBuilder())->build([
            "a" => 1
        ]);

        $contextFromFunc = [null];
        $propString = new CustomProperty("prop1", CustomPropertyType::getString(), function (ContextInterface $c) use (&$contextFromFunc) {
            $contextFromFunc[0] = $c;
            return "123";
        });

        $this->assertEquals($propString->getValue()($context), "123");
        $this->assertEquals($contextFromFunc[0]->get('a'), 1);
    }

    public function testDevicePropWilAddRoxToTheName()
    {
        $prop = new DeviceProperty("prop1", CustomPropertyType::getBool(), "123");

        $this->assertEquals($prop->getName(), "rox.prop1");
    }
}
