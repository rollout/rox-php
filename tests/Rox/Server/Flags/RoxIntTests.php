<?php


namespace Rox\Server\Flags;

use InvalidArgumentException;
use Rox\Core\Context\ContextBuilder;

class RoxIntTests extends RoxFlagsTestCase
{
    /**
     * @dataProvider invalidValues
     * @param mixed $invalidValue
     */
    public function testInvalidDefaultValueThrowsException($invalidValue)
    {
        $this->expectException(InvalidArgumentException::class);
        new RoxInt($invalidValue);
    }

    /**
     * @dataProvider invalidValues
     * @param mixed $invalidValue
     */
    public function testInvalidVariationValueThrowsException($invalidValue)
    {
        $this->expectException(InvalidArgumentException::class);
        new RoxInt(1, [$invalidValue]);
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
            ["1"],
            [1.2]
        ];
    }

    public function testWillAddDefaultToOptionsWhenNoOptions()
    {
        $variant = new RoxInt(1);
        $this->assertCount(1, $variant->getVariations());
        $this->assertContains('1', $variant->getVariations());
    }

    public function testWillNotAddDefaultToOptionsIfExists()
    {
        $variant = new RoxInt(1, [1, 2, 3]);
        $this->assertCount(3, $variant->getVariations());
        $this->assertContains('1', $variant->getVariations());
    }

    public function testWillAddDefaultToOptionsIfNotExists()
    {
        $variant = new RoxInt(1, [2, 3]);
        $this->assertCount(3, $variant->getVariations());
        $this->assertContains('1', $variant->getVariations());
    }

    public function testWillReturnDefaultWhenNoExperiment()
    {
        $variant = new RoxInt(3);
        $this->assertEquals(3, $variant->getValue());
    }

    public function testWillReturnDefaultWhenNoExperimentAfterSetup()
    {
        $variant = new RoxInt(3);
        $variant->setName('test');
        $variant->setForEvaluation($this->getParser(), null, $this->getImpressionInvoker());
        $this->assertEquals(3, $variant->getValue());
        $this->checkLastImpression('test', '3');
    }

    public function testWillReturnDefaultWhenExperimentReturnsUndefined()
    {
        $variant = new RoxInt(3);
        $this->setupFlag($variant, 'test', 'undefined');
        $this->assertEquals(3, $variant->getValue());
        $this->checkLastImpression('test', '3', true);
    }

    public function testWillReturnExperimentExpressionValue()
    {
        $variant = new RoxInt(1);
        $this->setupFlag($variant, 'test', '2');
        $this->assertEquals(2, $variant->getValue());
        $this->checkLastImpression('test', '2', true);
    }

    public function testWillReturnDefaultWhenWrongExperimentType()
    {
        $variant = new RoxInt(2);
        $this->setupFlag($variant, 'test', '1.44');
        $this->assertEquals(2, $variant->getValue());
        $this->checkLastImpression('test', '2', true);
    }

    public function testWillReturnDefaultEvaluationReturnsNull()
    {
        $variant = new RoxInt(2);
        $this->setupFlag($variant, 'test', null);
        $this->assertEquals(2, $variant->getValue());
        $this->checkLastImpression('test', '2', true);
    }

    public function testWillUseContext()
    {
        $context = (new ContextBuilder())->build(['key' => 55]);
        $variant = new RoxInt(1, [2, 3]);
        $this->setupFlag($variant, 'test', '2');
        $this->assertEquals(2, $variant->getValue($context));
        $this->checkLastImpression('test', '2', true, 'key', 55);
    }
}