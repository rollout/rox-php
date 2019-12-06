<?php

namespace Rox\E2E;

use Rox\Core\Entities\RoxContainerInterface;
use Rox\Server\Flags\RoxFlag;
use Rox\Server\Flags\RoxVariant;

class Container implements RoxContainerInterface
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
     * @var RoxVariant $variantWithContext
     */
    public $variantWithContext;

    /**
     * @var RoxVariant $variant
     */
    public $variant;

    /**
     * @var RoxVariant $variantOverwritten
     */
    public $variantOverwritten;

    /**
     * @var RoxFlag $flagForDependency
     */
    public $flagForDependency;

    /**
     * @var RoxVariant $flagColorsForDependency
     */
    public $flagColorsForDependency;

    /**
     * @var RoxFlag $flagDependent
     */
    public $flagDependent;

    /**
     * @var RoxVariant $flagColorDependentWithContext
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

        $this->variantWithContext = new RoxVariant("red", ["red", "blue", "green"]);

        $this->variant = new RoxVariant("red", ["red", "blue", "green"]);
        $this->variantOverwritten = new RoxVariant("red", ["red", "blue", "green"]);

        $this->flagForDependency = new RoxFlag(false);
        $this->flagColorsForDependency = new RoxVariant("White", ["White", "Blue", "Green", "Yellow"]);
        $this->flagDependent = new RoxFlag(false);
        $this->flagColorDependentWithContext = new RoxVariant("White", ["White", "Blue", "Green", "Yellow"]);
    }

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Container();
        }
        return self::$_instance;
    }
}
