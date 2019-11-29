<?php

namespace Rox\Core\Context;

use PHPUnit\Framework\TestCase;

class MergedContextTests extends TestCase
{
    public function testWithNullLocalContext()
    {
        $globalMap = [];
        $globalMap["a"] = 1;

        $globalContext = (new ContextBuilder())->build($globalMap);
        $mergedContext = new MergedContext($globalContext, null);

        $this->assertEquals($mergedContext->get("a"), 1);
        $this->assertEquals($mergedContext->get("b"), null);
    }

    public function testWithNullGlobalContext()
    {
        $localMap = [];
        $localMap["a"] = 1;

        $localContext = (new ContextBuilder())->build($localMap);
        $mergedContext = new MergedContext(null, $localContext);

        $this->assertEquals($mergedContext->get("a"), 1);
        $this->assertEquals($mergedContext->get("b"), null);
    }

    public function testWithLocalAndGlobalContext()
    {
        $globalMap = [];
        $globalMap["a"] = 1;
        $globalMap["b"] = 2;

        $localMap = [];
        $localMap["a"] = 3;
        $localMap["c"] = 4;

        $globalContext = (new ContextBuilder())->build($globalMap);
        $localContext = (new ContextBuilder())->build($localMap);
        $mergedContext = new MergedContext($globalContext, $localContext);

        $this->assertEquals($mergedContext->get("a"), 3);
        $this->assertEquals($mergedContext->get("b"), 2);
        $this->assertEquals($mergedContext->get("c"), 4);
        $this->assertEquals($mergedContext->get("d"), null);
    }
}
