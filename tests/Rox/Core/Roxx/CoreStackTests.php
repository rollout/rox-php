<?php

namespace Rox\Core\Roxx;

use PHPUnit\Framework\TestCase;

/**
 * Class CoreStackTest
 * @package Rox\Core\Roxx
 * @covers CoreStack
 */
final class CoreStackTests extends TestCase
{
    public function testWillPushIntoStackString()
    {
        $testString = "stringTest";
        $stack = new CoreStack();
        $stack->Push($testString);

        $poppedItem = $stack->pop();
        $this->assertSame($testString, $poppedItem);
    }

    public function testWillPushIntoStackInteger()
    {
        $testInt = 5;
        $stack = new CoreStack();
        $stack->Push($testInt);
        $poppedItem = $stack->pop();
        $this->assertSame($poppedItem, $testInt);
    }

    public function testWillPushIntoStackIntegerAndString()
    {
        $testInt = 5;
        $testString = "testString";
        $stack = new CoreStack();
        $stack->Push($testInt);
        $stack->Push($testString);
        $poppedItemFirst = $stack->pop();
        $poppedItemSec = $stack->pop();
        $this->assertSame($poppedItemFirst, $testString);
        $this->assertSame($poppedItemSec, $testInt);
    }
}
