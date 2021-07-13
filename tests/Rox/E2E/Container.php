<?php

namespace Rox\E2E;

use Rox\Server\Flags\RoxFlag;
use Rox\Server\Flags\RoxString;

class Container
{
    /**
     * @var Container $_instance
     */
    private static $_instance;

    /**
     * @var RoxFlag $simpleFlag
     */
    public $simpleFlag;

    /**
     * @var RoxFlag $simpleFlagOverwritten
     */
    public $simpleFlagOverwritten;

    /**
     * @var RoxFlag $flagForImpression
     */
    public $flagForImpression;

    /**
     * @var RoxFlag $flagForImpressionWithExperimentAndContext
     */
    public $flagForImpressionWithExperimentAndContext;

    /**
     * @var RoxFlag $flagCustomProperties
     */
    public $flagCustomProperties;

    /**
     * @var RoxFlag $flagTargetGroupsAll
     */
    public $flagTargetGroupsAll;

    /**
     * @var RoxFlag $flagTargetGroupsAny
     */
    public $flagTargetGroupsAny;

    /**
     * @var RoxFlag $flagTargetGroupsNone
     */
    public $flagTargetGroupsNone;

    /**
     * @var RoxString $variantWithContext
     */
    public $variantWithContext;

    /**
     * @var RoxString $variant
     */
    public $variant;

    /**
     * @var RoxString $variantOverwritten
     */
    public $variantOverwritten;

    /**
     * @var RoxFlag $flagForDependency
     */
    public $flagForDependency;

    /**
     * @var RoxString $flagColorsForDependency
     */
    public $flagColorsForDependency;

    /**
     * @var RoxFlag $flagDependent
     */
    public $flagDependent;

    /**
     * @var RoxString $flagColorDependentWithContext
     */
    public $flagColorDependentWithContext;

    /**
     * Container constructor.
     */
    public function __construct()
    {
        $this->simpleFlag = new RoxFlag(true);
        $this->simpleFlagOverwritten = new RoxFlag(true);

        $this->flagForImpression = new RoxFlag(false);
        $this->flagForImpressionWithExperimentAndContext = new RoxFlag(false);

        $this->flagCustomProperties = new RoxFlag();

        $this->flagTargetGroupsAll = new RoxFlag();
        $this->flagTargetGroupsAny = new RoxFlag();
        $this->flagTargetGroupsNone = new RoxFlag();

        $this->variantWithContext = new RoxString("red", ["red", "blue", "green"]);

        $this->variant = new RoxString("red", ["red", "blue", "green"]);
        $this->variantOverwritten = new RoxString("red", ["red", "blue", "green"]);

        $this->flagForDependency = new RoxFlag(false);
        $this->flagColorsForDependency = new RoxString("White", ["White", "Blue", "Green", "Yellow"]);
        $this->flagDependent = new RoxFlag(false);
        $this->flagColorDependentWithContext = new RoxString("White", ["White", "Blue", "Green", "Yellow"]);
    }

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Container();
        }
        return self::$_instance;
    }
}
