<?php

namespace Rox\Core\Logging;

use Psr\Log\LoggerInterface;
use Psr\Log\Test\TestLogger;

class TestLoggerFactory implements LoggerFactoryInterface
{
    /**
     * @var TestLogger
     */
    private $_logger;

    /**
     * TestLoggerFactory constructor.
     */
    public function __construct()
    {
        $this->_logger = new TestLogger();
    }

    /**
     * @return TestLogger
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @param string $name
     * @return LoggerInterface
     */
    function createLogger($name)
    {
        return $this->_logger;
    }
}
