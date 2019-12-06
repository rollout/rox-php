<?php

use Rox\Core\Consts\Environment;
use Rox\Core\Entities\RoxContainerInterface;
use Rox\Server\Flags\RoxFlag;
use Rox\Server\Rox;
use Rox\Server\RoxOptions;
use Rox\Server\RoxOptionsBuilder;

require __DIR__ . '/vendor/autoload.php';

class Container implements RoxContainerInterface
{
    public $FirstFlag;

    /**
     * Container constructor.
     */
    public function __construct()
    {
        $this->FirstFlag = new RoxFlag();
    }
}

if (!isset($_ENV[Environment::ENV_VAR_NAME])) {
    $_ENV[Environment::ENV_VAR_NAME] = Environment::LOCAL;
}

$con = new Container();
Rox::register("test", $con);
Rox::setup("5ae089f994ea359740e9e788",
    new RoxOptions((new RoxOptionsBuilder())
        ->setDevModeKey("6f66e1826dea3acd69abedec")));

if ($con->FirstFlag->isEnabled()) {
    echo 'FEATURE IS ON';
} else {
    echo 'FEATURE IS OFF';
}
