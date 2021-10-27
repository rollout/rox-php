<?php

namespace Rox\Server\Flags;

use InvalidArgumentException;
use Rox\Core\Context\ContextBuilder;

class RoxFlagTests extends RoxFlagsTestCase
{
    /**
     * @dataProvider invalidDefaultValues
     * @param mixed $defaultValue
     */
    public function testInvalidDefaultValueThrowsException($defaultValue)
    {
        $this->expectException(InvalidArgumentException::class);
        new RoxFlag($defaultValue);
    }

    /**
     * @return array[]
     */
    public function invalidDefaultValues()
    {
        return [
            [null],
            ["false"],
            [1],
            [1.2]
        ];
    }

    public function testFlagWithoutDefaultValue()
    {
        $flag = new RoxFlag();

        $this->assertEquals(false, $flag->isEnabled(null));
    }

    public function testFlagWithDefaultValue()
    {
        $flag = new RoxFlag(true);

        $this->assertEquals(true, $flag->isEnabled(null));
    }

    public function testFlagWithDefaultValueAfterSetup()
    {
        $flag = new RoxFlag(false);
        $flag->setName("test");
        $flag->setForEvaluation(
            $this->getParser(),
            null,
            $this->getImpressionInvoker());
        $this->assertFalse($flag->isEnabled());
        $this->checkLastImpression("test", "false");
    }

    public function testFlagWithExperimentExpressionValue()
    {
        $flag = new RoxFlag(false);
        $this->setupFlag($flag, "test", "and(true, true)");
        $this->assertTrue($flag->isEnabled());
        $this->checkLastImpression("test", "true", true);
    }

    public function testFlagWithExperimentReturnsUndefined()
    {
        $flag = new RoxFlag(true);
        $this->setupFlag($flag, "test", "undefined");
        $this->assertTrue($flag->isEnabled());
        $this->checkLastImpression("test", "true", true);
    }

    public function testFlagWithExperimentEvaluationReturnsNull()
    {
        $flag = new RoxFlag(true);
        $this->setupFlag($flag, "test", null);
        $this->assertTrue($flag->isEnabled());
        $this->checkLastImpression("test", "true", true);
    }

    public function testFlagWithExperimentWrongType()
    {
        $flag = new RoxFlag(true);
        $this->setupFlag($flag, "test", "0");
        $this->assertTrue($flag->isEnabled());
        $this->checkLastImpression("test", "true", true);
    }

    public function testWillUseContext()
    {
        $context = (new ContextBuilder())->build(['key' => 55]);
        $flag = new RoxFlag(true);
        $this->setupFlag($flag, "test", "true");
        $this->assertTrue($flag->isEnabled($context));
        $this->checkLastImpression("test", "true", true, "key", 55);
    }

    public function testWillInvokeEnabledAction()
    {
        $flag = new RoxFlag(true);

        $isCalled = [false];
        $flag->enabled(null, function () use (&$isCalled) {
            $isCalled[0] = true;
        });

        $this->assertEquals(true, $isCalled[0]);
    }

    public function testWillInvokeDisabledAction()
    {
        $flag = new RoxFlag();

        $isCalled = [false];
        $flag->disabled(null, function () use (&$isCalled) {
            $isCalled[0] = true;
        });

        $this->assertEquals(true, $isCalled[0]);
    }
}
