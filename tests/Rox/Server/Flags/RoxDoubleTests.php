<?php

namespace Rox\Server\Flags;

use InvalidArgumentException;
use Rox\Core\Context\ContextBuilder;

class RoxDoubleTests extends RoxFlagsTestCase
{
    /**
     * @dataProvider invalidValues
     * @param mixed $invalidValue
     */
    public function testInvalidDefaultValueThrowsException($invalidValue)
    {
        $this->expectException(InvalidArgumentException::class);
        new RoxDouble($invalidValue);
    }

    /**
     * @dataProvider invalidValues
     * @param mixed $invalidValue
     */
    public function testInvalidVariationValueThrowsException($invalidValue)
    {
        $this->expectException(InvalidArgumentException::class);
        new RoxDouble(1.1, [$invalidValue]);
    }

    /**
     * @return array[]
     */
    public function invalidValues()
    {
        return [
            [null],
            [false],
            [true],
            ["1"]
        ];
    }

    public function testWillAddDefaultToOptionsWhenNoOptions()
    {
        $variant = new RoxDouble(1.1);
        $this->assertCount(1, $variant->getVariations());
        $this->assertContains('1.1', $variant->getVariations());
    }

    public function testWillNotAddDefaultToOptionsIfExists()
    {
        $variant = new RoxDouble(1.1, [1.1, 2.2, 3.3]);
        $this->assertCount(3, $variant->getVariations());
        $this->assertContains('1.1', $variant->getVariations());
    }

    public function testWillAddDefaultToOptionsIfNotExists()
    {
        $variant = new RoxDouble(1.1, [2.2, 3.3]);
        $this->assertCount(3, $variant->getVariations());
        $this->assertContains('1.1', $variant->getVariations());
    }

    public function testWillReturnDefault()
    {
        $variant = new RoxDouble(1.1);
        $this->assertEquals(1.1, $variant->getValue());
    }

    public function testWillReturnDefaultWhenNoExperimentAfterSetup()
    {
        $variant = new RoxDouble(1.1);
        $variant->setName('test');
        $variant->setForEvaluation($this->getParser(), null, $this->getImpressionInvoker());
        $this->assertEquals(1.1, $variant->getValue());
        $this->checkLastImpression('test', '1.1');
    }

    public function testWillReturnDefaultWhenExperimentReturnsUndefined()
    {
        $variant = new RoxDouble(1.1);
        $this->setupFlag($variant, 'test', 'undefined');
        $this->assertEquals(1.1, $variant->getValue());
        $this->checkLastImpression('test', '1.1', true);
    }

    public function testWillReturnDefaultWhenExperimentReturnsNull()
    {
        $variant = new RoxDouble(1.1);
        $this->setupFlag($variant, 'test', null);
        $this->assertEquals(1.1, $variant->getValue());
        $this->checkLastImpression('test', '1.1', true);
    }

    public function testWillReturnExperimentExpressionValue()
    {
        $variant = new RoxDouble(1.1);
        $this->setupFlag($variant, 'test', '1.123');
        $this->assertEquals(1.123, $variant->getValue());
        $this->checkLastImpression('test', '1.123', true);
    }

    public function testWillReturnDefaultWhenExperimentWrongType()
    {
        $variant = new RoxDouble(1.1);
        $this->setupFlag($variant, 'test', '"2ss"');
        $this->assertEquals(1.1, $variant->getValue());
        $this->checkLastImpression('test', '1.1', true);
    }

    public function testWillUseContext()
    {
        $context = (new ContextBuilder())->build(['key' => 55]);
        $variant = new RoxDouble(1.1);
        $this->setupFlag($variant, 'test', '2.2');
        $this->assertEquals(2.2, $variant->getValue($context));
        $this->checkLastImpression('test', '2.2', true, 'key', 55);
    }
}