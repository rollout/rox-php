<?php

namespace Rox\Core\Repositories;

use Rox\Core\CustomProperties\FlagAddedCallbackArgs;
use Rox\Core\Entities\Flag;
use Rox\RoxTestCase;

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
        $flag = new Flag();

        $repo->addFlag($flag, "harti");

        $this->assertEquals($repo->getFlag("harti")->getName(), "harti");
    }

    public function testWillRaiseFlagAddedEvent()
    {
        $repo = new FlagRepository();
        $flag = new Flag();

        $variantFromEvent = [null];
        $repo->addFlagAddedCallback(function (FlagRepositoryInterface $sender, FlagAddedCallbackArgs $args) use (&$variantFromEvent) {
            $variantFromEvent[0] = $args->getVariant();
        });
        $repo->addFlag($flag, "harti");

        $this->assertEquals($variantFromEvent[0]->getName(), "harti");
    }
}
