<?php

namespace Rox\Core\Register;

use InvalidArgumentException;
use Rox\Core\Repositories\FlagRepository;
use Rox\RoxTestCase;

class RegistererTests extends RoxTestCase
{
    public function testWillThrowWhenNSNull()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $flagRepo = new FlagRepository();
        $container = new TestContainer();
        $registerer = new Registerer($flagRepo);

        $registerer->registerInstance($container, null);
    }

    public function testWillThrowWhenNSRegisteredTwice()
    {
        $this->setExpectedException(InvalidArgumentException::class);

        $flagRepo = new FlagRepository();
        $container = new TestContainer();
        $registerer = new Registerer($flagRepo);

        $registerer->registerInstance($container, "ns1");

        $registerer->registerInstance($container, "ns1");
    }

    public function testWillRegisterVariantAndFlag()
    {
        $flagRepo = new FlagRepository();
        $container = new TestContainer();
        $registerer = new Registerer($flagRepo);

        $registerer->registerInstance($container, "ns1");

        $this->assertEquals(count($flagRepo->getAllFlags()), 4);

        $this->assertEquals($flagRepo->getFlag("ns1.variant1")->getDefaultValue(), "1");
        $this->assertEquals($flagRepo->getFlag("ns1.flag1")->getDefaultValue(), "false");
    }

    public function testWillRegisterWithEmptyNS()
    {
        $flagRepo = new FlagRepository();
        $container = new TestContainer();
        $registerer = new Registerer($flagRepo);

        $registerer->registerInstance($container, "");

        $this->assertEquals(count($flagRepo->getAllFlags()), 4);

        $this->assertEquals($flagRepo->getFlag("variant1")->getDefaultValue(), "1");
        $this->assertEquals($flagRepo->getFlag("flag1")->getDefaultValue(), "false");
    }
}
