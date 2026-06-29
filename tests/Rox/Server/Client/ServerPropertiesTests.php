<?php

namespace Rox\Server\Client;

use Rox\Core\Client\SdkSettings;
use Rox\RoxTestCase;
use Rox\Server\RoxOptions;
use Rox\Server\RoxOptionsBuilder;

class ServerPropertiesTests extends RoxTestCase
{
    public function testDistinctIdUsesEnvVarWhenSet()
    {
        putenv(ServerProperties::INSTANCE_ID_ENV_VAR_NAME . '=my-instance-001');
        $props = new ServerProperties(
            new SdkSettings('test-api-key', ''),
            new RoxOptions((new RoxOptionsBuilder()))
        );
        $this->assertEquals('my-instance-001', $props->getDistinctId());
        putenv(ServerProperties::INSTANCE_ID_ENV_VAR_NAME);
    }

    public function testDistinctIdFallsBackToScriptPropertiesHashWhenEnvVarNotSet()
    {
        putenv(ServerProperties::INSTANCE_ID_ENV_VAR_NAME);
        $props = new ServerProperties(
            new SdkSettings('test-api-key', ''),
            new RoxOptions((new RoxOptionsBuilder()))
        );
        $expected = md5(join('.', [
            getmyuid(),
            getmygid(),
            get_current_user(),
            getmyinode(),
            getlastmod()
        ]));
        $this->assertEquals($expected, $props->getDistinctId());
    }
}
