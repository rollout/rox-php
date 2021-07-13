<?php

namespace Rox\Core\Repositories;

use Rox\Core\CustomProperties\FlagAddedCallbackArgs;
use Rox\RoxTestCase;
use Rox\Server\Flags\RoxFlag;

class FlagRepositoryTests extends RoxTestCase
{
    public function testWillReturnNullWhenFlagNotFound()
    {
        $repo = new FlagRepository();

        $this->assertEquals($repo->getFlag("harti"), null);
    }

    public function testWillAddFlagAndSetName()
    {
        $repo = new FlagRepository();
        $flag = new RoxFlag();

        $repo->addFlag($flag, "harti");

        $this->assertEquals($repo->getFlag("harti")->getName(), "harti");
    }

    public function testWillRaiseFlagAddedEvent()
    {
        $repo = new FlagRepository();
        $flag = new RoxFlag();

        $variantFromEvent = [null];
        $repo->addFlagAddedCallback(function (FlagAddedCallbackArgs $args) use (&$variantFromEvent) {
            $variantFromEvent[0] = $args->getVariant();
        });
        $repo->addFlag($flag, "harti");

        $this->assertEquals($variantFromEvent[0]->getName(), "harti");
    }
}
