<?php

namespace Rox\Core\Register;

use Rox\Server\Flags\RoxFlag;
use Rox\Server\Flags\RoxString;
use stdClass;

class TestContainer
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
        $this->variant1 = new RoxString("1", ["1", "2", "3"]);
        $this->variant2 = new RoxString("6", ["4", "5", "6"]);
        $this->flag1 = new RoxFlag();
        $this->flag2 = new RoxFlag();
        $this->flag3 = new RoxFlag();
        $this->somethingElse = new stdClass();
    }
}
