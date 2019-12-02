<?php

namespace Rox\Core\Utils;

use Rox\RoxTestCase;

class DebouncerTests extends RoxTestCase
{
    public function testWillTestDebouncerCalledAfterInterval()
    {
        $time = TimeUtils::currentTimeMillis();
        TimeUtils::setFixedTime($time);

        $counter = [0];
        $debouncer = new Debouncer(1000, function () use (&$counter) {
            $counter[0]++;
        });
        $this->assertEquals(0, $counter[0]);
        $debouncer->invoke();
        $this->assertEquals(0, $counter[0]);

        TimeUtils::setFixedTime($time += 500);
        $debouncer->invoke();
        $this->assertEquals(0, $counter[0]);

        TimeUtils::setFixedTime($time += 600);
        $debouncer->invoke();
        $this->assertEquals(1, $counter[0]);
    }

    public function testWillTestDebouncerSkipDoubleInvoke()
    {
        $time = TimeUtils::currentTimeMillis();
        TimeUtils::setFixedTime($time);

        $counter = [0];
        $debouncer = new Debouncer(1000, function () use (&$counter) {
            $counter[0]++;
        });
        $this->assertEquals(0, $counter[0]);
        $debouncer->invoke();
        $this->assertEquals(0, $counter[0]);

        TimeUtils::setFixedTime($time += 500);
        $debouncer->invoke();
        $this->assertEquals(0, $counter[0]);
        $debouncer->invoke();
        $this->assertEquals(0, $counter[0]);

        TimeUtils::setFixedTime($time += 600);
        $debouncer->invoke();
        $this->assertEquals(1, $counter[0]);
        TimeUtils::setFixedTime($time += 600);
        $debouncer->invoke();
        $this->assertEquals(1, $counter[0]);
    }

    public function testWillTestDebouncerInvokeAfterInvoke()
    {
        $time = TimeUtils::currentTimeMillis();
        TimeUtils::setFixedTime($time);

        $counter = [0];
        $debouncer = new Debouncer(1000, function () use (&$counter) {
            $counter[0]++;
        });
        $this->assertEquals(0, $counter[0]);
        $debouncer->invoke();
        $this->assertEquals(0, $counter[0]);

        TimeUtils::setFixedTime($time += 1100);
        $debouncer->invoke();
        $this->assertEquals(1, $counter[0]);
        $debouncer->invoke();
        $this->assertEquals(1, $counter[0]);

        TimeUtils::setFixedTime($time += 800);
        $debouncer->invoke();
        $this->assertEquals(1, $counter[0]);

        TimeUtils::setFixedTime($time += 300);
        $debouncer->invoke();
        $this->assertEquals(2, $counter[0]);
    }
}
