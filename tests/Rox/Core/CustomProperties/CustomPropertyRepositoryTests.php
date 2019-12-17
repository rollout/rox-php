<?php

namespace Rox\Core\CustomProperties;

use Rox\Core\Repositories\CustomPropertyAddedArgs;
use Rox\RoxTestCase;

class CustomPropertyRepositoryTests extends RoxTestCase
{
    public function testWillReturnNullWhenPropNotFound()
    {
        $repo = new CustomPropertyRepository();

        $this->assertEquals($repo->getCustomProperty("harti"), null);
    }

    public function testWillAddProp()
    {
        $repo = new CustomPropertyRepository();
        $cp = new CustomProperty("prop1", CustomPropertyType::getString(), "123");

        $repo->addCustomProperty($cp);

        $customProperty = $repo->getCustomProperty("prop1");
        $this->assertEquals($customProperty->getName(), "prop1");
    }

    public function testWillNotOverrideProp()
    {
        $repo = new CustomPropertyRepository();
        $cp = new CustomProperty("prop1", CustomPropertyType::getString(), "123");
        $cp2 = new CustomProperty("prop1", CustomPropertyType::getString(), "234");

        $repo->addCustomPropertyIfNotExists($cp);
        $repo->addCustomPropertyIfNotExists($cp2);

        $value = $repo->getCustomProperty("prop1")->getValue();
        $this->assertEquals($value(null), "123");
    }

    public function testWillOverrideProp()
    {
        $repo = new CustomPropertyRepository();
        $cp = new CustomProperty("prop1", CustomPropertyType::getString(), "123");
        $cp2 = new CustomProperty("prop1", CustomPropertyType::getString(), "234");

        $repo->addCustomPropertyIfNotExists($cp);
        $repo->addCustomProperty($cp2);

        $value = $repo->getCustomProperty("prop1")->getValue();
        $this->assertEquals($value(null), "234");
    }

    public function testWillRaisePropAddedEvent()
    {
        $repo = new CustomPropertyRepository();
        $cp = new CustomProperty("prop1", CustomPropertyType::getString(), "123");

        $propFromEvent = [null];
        $repo->addCustomPropertyEventHandler(function (CustomPropertyAddedArgs $args) use (&$propFromEvent) {
            $propFromEvent[0] = $args->getCustomProperty();
        });
        $repo->addCustomProperty($cp);
        $this->assertNotNull($propFromEvent[0]);
        $this->assertEquals($propFromEvent[0]->getName(), "prop1");
    }
}
