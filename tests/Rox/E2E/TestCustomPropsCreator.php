<?php

namespace Rox\E2E;

use Rox\Core\Context\ContextInterface;
use Rox\Server\Rox;

class TestCustomPropsCreator
{
    public static function createCustomProps()
    {
        Rox::setCustomStringProperty("stringProp1", "Hello");

        Rox::setCustomComputedStringProperty("stringProp2", function (ContextInterface $context) {
            TestVars::$isComputedStringPropCalled = true;
            return "World";
        });

        Rox::setCustomBooleanProperty("boolProp1", true);

        Rox::setCustomComputedBooleanProperty("boolProp2", function (ContextInterface $context) {
            TestVars::$isComputedBooleanPropCalled = true;
            return false;
        });

        Rox::setCustomIntegerProperty("intProp1", 6);
        Rox::setCustomComputedIntegerProperty("intProp2", function (ContextInterface $context) {
            TestVars::$isComputedIntPropCalled = true;
            return 28;
        });

        Rox::setCustomDoubleProperty("doubleProp1", 3.14);
        Rox::setCustomComputedDoubleProperty("doubleProp2", function (ContextInterface $context) {
            TestVars::$isComputedDoublePropCalled = true;
            return 1.618;
        });

        Rox::setCustomSemverProperty("smvrProp1", "9.11.2001");
        Rox::setCustomComputedSemverProperty("smvrProp2", function (ContextInterface $context) {
            TestVars::$isComputedSemverPropCalled = true;
            return "20.7.1969";
        });

        Rox::setCustomComputedBooleanProperty("boolPropTargetGroupForVariant", function (ContextInterface $context) {
            return (bool)($context->get("isDuckAndCover"));
        });

        Rox::setCustomComputedBooleanProperty("boolPropTargetGroupOperand1", function (ContextInterface $context) {
            return TestVars::$targetGroup1;
        });

        Rox::setCustomComputedBooleanProperty("boolPropTargetGroupOperand2", function (ContextInterface $context) {
            return TestVars::$targetGroup2;
        });

        Rox::setCustomComputedBooleanProperty("boolPropTargetGroupForDependency", function (ContextInterface $context) {
            return TestVars::$isPropForTargetGroupForDependency;
        });

        Rox::setCustomComputedBooleanProperty("boolPropTargetGroupForVariantDependency", function (ContextInterface $context) {
            return (bool)($context->get("isDuckAndCover"));
        });
    }
}
