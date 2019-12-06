<?php

namespace Rox\E2E;

use Rox\Core\Impression\ImpressionArgs;

class TestVars
{
    /**
     * @var bool $isComputedBooleanPropCalled
     */
    static public $isComputedBooleanPropCalled = false;

    /**
     * @var bool $isComputedStringPropCalled
     */
    static public $isComputedStringPropCalled = false;

    /**
     * @var bool $isComputedIntPropCalled
     */
    static public $isComputedIntPropCalled = false;

    /**
     * @var bool $isComputedDoublePropCalled
     */
    static public $isComputedDoublePropCalled = false;

    /**
     * @var bool $isComputedSemverPropCalled
     */
    static public $isComputedSemverPropCalled = false;

    /**
     * @var bool $targetGroup1
     */
    static public $targetGroup1 = false;

    /**
     * @var bool $targetGroup2
     */
    static public $targetGroup2 = false;

    /**
     * @var bool $isImpressionRaised
     */
    static public $isImpressionRaised = false;

    /**
     * @var bool $isPropForTargetGroupForDependency
     */
    static public $isPropForTargetGroupForDependency = false;

    /**
     * @var bool $configurationFetchedCount
     */
    static public $configurationFetchedCount = 0;

    /**
     * @var ImpressionArgs $impressionReturnedArgs
     */
    static public $impressionReturnedArgs = null;
}
