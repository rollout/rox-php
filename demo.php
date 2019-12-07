<?php

use Rox\Core\Consts\Environment;
use Rox\Core\Entities\RoxContainerInterface;
use Rox\Server\Flags\RoxFlag;
use Rox\Server\Rox;
use Rox\Server\RoxOptions;
use Rox\Server\RoxOptionsBuilder;

const DEFAULT_API_KEY = '5b3356d00d81206da3055bc0';
const DEFAULT_DEV_MODE_KEY = '01fcd0d21eeaed9923dff6d8';

require __DIR__ . '/vendor/autoload.php';

class Container implements RoxContainerInterface
{
    public $demoFlag;

    public function __construct()
    {
        $this->demoFlag = new RoxFlag();
    }
}

if (!isset($_ENV[Environment::ENV_VAR_NAME])) {
    $_ENV[Environment::ENV_VAR_NAME] = Environment::QA;
}

$apiKey = isset($_ENV['ROLLOUT_API_KEY'])
    ? $_ENV['ROLLOUT_API_KEY']
    : DEFAULT_API_KEY;

$devModeKey = isset($_ENV['ROLLOUT_DEV_MODE_KEY'])
    ? $_ENV['ROLLOUT_DEV_MODE_KEY']
    : DEFAULT_DEV_MODE_KEY;

$con = new Container();
Rox::register('demo', $con);
Rox::setup($apiKey,
    new RoxOptions((new RoxOptionsBuilder())
        ->setDevModeKey($devModeKey)));

if ($con->demoFlag->isEnabled()) {
    echo 'demo.demoFlag: FEATURE IS ON';
} else {
    echo 'demo.demoFlag: FEATURE IS OFF';
}