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

        $poppedItem = $stack->Pop();
        $this->assertSame($testString, $poppedItem);
    }

    public function testWillPushIntoStackInteger()
    {
        $testInt = 5;
        $stack = new CoreStack();
        $stack->Push($testInt);
        $poppedItem = $stack->Pop();
        $this->assertSame($poppedItem, $testInt);
    }

    public function testWillPushIntoStackIntegerAndString()
    {
        $testInt = 5;
        $testString = "testString";
        $stack = new CoreStack();
        $stack->Push($testInt);
        $stack->Push($testString);
        $poppedItemFirst = $stack->Pop();
        $poppedItemSec = $stack->Pop();
        $this->assertSame($poppedItemFirst, $testString);
        $this->assertSame($poppedItemSec, $testInt);
    }
}