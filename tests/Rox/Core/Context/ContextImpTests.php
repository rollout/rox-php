<?php

namespace Rox\Core\Context;

use Rox\RoxTestCase;

class ContextImpTests extends RoxTestCase
{
    public function testContextWillReturnValue()
    {
        $map = [];
        $map["a"] = "b";

        $context = (new ContextBuilder())->build($map);

        $this->assertEquals($context->get("a"), "b");
    }

    public function testContextWillReturnNull()
    {
        $map = [];

        $context = (new ContextBuilder())->build($map);

        $this->assertEquals($context->get("a"), null);
    }

    public function testContextWithNullMap()
    {
        $map = null;

        $context = (new ContextBuilder())->build($map);

        $this->assertEquals($context->get("a"), null);
    }
}
