<?php

namespace Rox\Core\Roxx;

use Rox\RoxTestCase;

/**
 * Class CoreStackTest
 * @package Rox\Core\Roxx
 * @covers CoreStack
 */
final class CoreStackTests extends RoxTestCase
{
    public function testWillPushIntoStackString()
    {
        $testString = "stringTest";
        $stack = new CoreStack();
        $stack->push($testString);

        $poppedItem = $stack->pop();
        $this->assertSame($testString, $poppedItem);
    }

    public function testWillPushIntoStackInteger()
    {
        $testInt = 5;
        $stack = new CoreStack();
        $stack->push($testInt);
        $poppedItem = $stack->pop();
        $this->assertSame($poppedItem, $testInt);
    }

    public function testWillPushIntoStackIntegerAndString()
    {
        $testInt = 5;
        $testString = "testString";
        $stack = new CoreStack();
        $stack->push($testInt);
        $stack->push($testString);
        $poppedItemFirst = $stack->pop();
        $poppedItemSec = $stack->pop();
        $this->assertSame($poppedItemFirst, $testString);
        $this->assertSame($poppedItemSec, $testInt);
    }
}
