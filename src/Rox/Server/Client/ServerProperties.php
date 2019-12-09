<?php

namespace Rox\Server\Client;

use Rox\Core\Client\DeviceProperties;

class ServerProperties extends DeviceProperties
{
    function getLibVersion()
    {
        return parent::getLibVersion(); // TODO: get from some (PHP?) file generated during deployment?
    }
}
