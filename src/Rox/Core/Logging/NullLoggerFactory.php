<?php

namespace Rox\Core\Logging;

use Psr\Log\NullLogger;

final class NullLoggerFactory implements LoggerFactoryInterface
{
    /**
     * NullLoggerFactory constructor.
     */
    private function __construct()
    {
    }

    /**
     * @inheritDoc
     */
    function createLogger($name)
    {
        return new NullLogger();
    }
}
