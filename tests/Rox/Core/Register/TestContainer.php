<?php

namespace Rox\Core\Register;

use Rox\Core\Entities\Flag;
use Rox\Core\Entities\RoxContainerInterface;
use Rox\Core\Entities\Variant;
use stdClass;

class TestContainer implements RoxContainerInterface
{
    private $variant1;
    private $flag1;
    protected $flag2;
    public $flag3;
    private $somethingElse;

    /**
     * TestContainer constructor.
     */
    public function __construct()
    {
        $this->variant1 = new Variant("1", ["1", "2", "3"]);
        $this->variant2 = new Variant("6", ["4", "5", "6"]);
        $this->flag1 = new Flag();
        $this->flag2 = new Flag();
        $this->flag3 = new Flag();
        $this->somethingElse = new stdClass();
    }
}
