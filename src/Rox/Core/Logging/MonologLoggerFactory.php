<?php

namespace Rox\Core\Logging;

use Monolog\Handler\HandlerInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class MonologLoggerFactory implements LoggerFactoryInterface
{
    /**
     * @var HandlerInterface[] $_defaultHandlers
     */
    private $_defaultHandlers;

    /**
     * @var callable[] $_defaultProcessors
     */
    private $_defaultProcessors = [];

    /**
     * @var callable|null $_exceptionHandler
     */
    private $_exceptionHandler = null;

    /**
     * @return HandlerInterface[]
     */
    public function getDefaultHandlers()
    {
        return $this->_defaultHandlers;
    }

    /**
     * @param HandlerInterface[] $defaultHandlers
     * @return MonologLoggerFactory
     */
    public function setDefaultHandlers($defaultHandlers)
    {
        $this->_defaultHandlers = $defaultHandlers;
        return $this;
    }

    /**
     * @return callable[]
     */
    public function getDefaultProcessors()
    {
        return $this->_defaultProcessors;
    }

    /**
     * @param callable[] $defaultProcessors
     * @return MonologLoggerFactory
     */
    public function setDefaultProcessors($defaultProcessors)
    {
        $this->_defaultProcessors = $defaultProcessors;
        return $this;
    }

    /**
     * @return callable|null
     */
    public function getExceptionHandler()
    {
        return $this->_exceptionHandler;
    }

    /**
     * @param callable|null $exceptionHandler
     * @return MonologLoggerFactory
     */
    public function setExceptionHandler($exceptionHandler)
    {
        $this->_exceptionHandler = $exceptionHandler;
        return $this;
    }

    /**
     * @param string $name
     * @return LoggerInterface
     */
    function createLogger($name)
    {
        $log = new Logger($name);
        if ($this->_defaultHandlers) {
            $log->setHandlers($this->_defaultHandlers);
        }
        if ($this->_defaultProcessors) {
            foreach ($this->_defaultProcessors as $processor) {
                $log->pushProcessor($processor);
            }
        }
        if ($this->_exceptionHandler) {
            $log->setExceptionHandler($this->_exceptionHandler);
        }
        return $log;
    }
}
