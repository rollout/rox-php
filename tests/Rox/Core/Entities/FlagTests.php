<?php

namespace Rox\Core\Entities;

use Rox\RoxTestCase;

class FlagTests extends RoxTestCase
{
    public function testFlagWithoutDefaultValue()
    {
        $flag = new Flag();

        $this->assertEquals($flag->isEnabled(null), false);
    }

    public function testFlagWithDefaultValue()
    {
        $flag = new Flag(true);

        $this->assertEquals($flag->isEnabled(null), true);
    }

    public function testWillInvokeEnabledAction()
    {
        $flag = new Flag(true);

        $isCalled = [false];
        $flag->enabled(null, function () use (&$isCalled) {
            $isCalled[0] = true;
        });

        $this->assertEquals($isCalled[0], true);
    }

    public function testWillInvokeDisabledAction()
    {
        $flag = new Flag();

        $isCalled = [false];
        $flag->disabled(null, function () use (&$isCalled) {
            $isCalled[0] = true;
        });

        $this->assertEquals($isCalled[0], true);
    }
}
