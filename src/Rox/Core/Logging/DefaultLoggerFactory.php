<?php

namespace Rox\Core\Logging;

use Exception;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;

final class DefaultLoggerFactory implements LoggerFactoryInterface
{
    /**
     * @var HandlerInterface[] $_defaultHandlers
     */
    private $_defaultHandlers = [];

    /**
     * @var string $_defaultLevel
     * @see LogLevel
     */
    private $_defaultLevel;

    /**
     * DefaultLoggerFactory constructor.
     */
    public function __construct()
    {
        try {
            $this->_defaultLevel = LogLevel::DEBUG;
            $this->_defaultHandlers = [
                new StreamHandler('php://stdout', Logger::toMonologLevel($this->_defaultLevel))
            ];
        } catch (Exception $e) {
            throw new RuntimeException("Failed to setup default logger factory", 0, $e);
        }
    }

    /**
     * @return HandlerInterface[]
     */
    public function getDefaultHandlers()
    {
        return $this->_defaultHandlers;
    }

    /**
     * @param HandlerInterface[] $defaultHandlers
     * @return DefaultLoggerFactory
     */
    public function setDefaultHandlers($defaultHandlers)
    {
        $this->_defaultHandlers = $defaultHandlers;
        return $this;
    }

    /**
     * @return string
     * @see LogLevel
     */
    public function getDefaultLevel()
    {
        return $this->_defaultLevel;
    }

    /**
     * @param string $defaultLevel
     * @return DefaultLoggerFactory
     * @see LogLevel
     */
    public function setDefaultLevel($defaultLevel)
    {
        $this->_defaultLevel = $defaultLevel;
        return $this;
    }

    /**
     * @param string $name
     * @return LoggerInterface
     */
    function createLogger($name)
    {
        $log = new Logger($name);
        $log->setHandlers($this->_defaultHandlers);
        return $log;
    }

    /**
     * @return DefaultLoggerFactory
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new DefaultLoggerFactory();
        }
        return self::$_instance;
    }

    /**
     * @var DefaultLoggerFactory $_instance
     */
    private static $_instance;
}
