<?php

namespace Rox\Core\Logging;

use Psr\Log\LoggerInterface;

interface LoggerFactoryInterface
{
    /**
     * @param string $name
     * @return LoggerInterface
     */
    function createLogger($name);
}