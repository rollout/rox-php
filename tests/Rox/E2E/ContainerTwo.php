<?php

namespace Rox\E2E;

use Rox\Server\Flags\RoxFlag;
use Rox\Server\Flags\RoxString;

class ContainerTwo
{
    /**
     * @var ContainerTwo $_instance
     */
    private static $_instance;

    /**
     * @var RoxFlag $flag2
     */
    public $flag2;

    /**
     * @var RoxString $variant
     */
    public $variant2;

    /**
     * ContainerTwo constructor.
     */
    public function __construct()
    {
        $this->flag2 = new RoxFlag(true);
        $this->variant2 = new RoxString("red", ["red", "blue", "green"]);
    }

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new ContainerTwo();
        }
        return self::$_instance;
    }
}