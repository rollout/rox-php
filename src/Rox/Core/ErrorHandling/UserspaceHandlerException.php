<?php

namespace Rox\Core\ErrorHandling;

use Exception;

class UserspaceHandlerException extends Exception
{
    /**
     * @var mixed $_exceptionSource
     */
    private $_exceptionSource;

    /**
     * @var int $_exceptionTrigger
     */
    private $_exceptionTrigger;

    /**
     * @param mixed $exceptionSource
     * @param int $exceptionTrigger
     * @param Exception $exception
     */
    public function __construct($exceptionSource, $exceptionTrigger, Exception $exception)
    {
        parent::__construct('user unhandled exception in roxx expression', 0, $exception);
        $this->_exceptionSource = $exceptionSource;
        $this->_exceptionTrigger = $exceptionTrigger;
    }

    /**
     * @return mixed
     */
    public function getExceptionSource()
    {
        return $this->_exceptionSource;
    }

    /**
     * @return int
     */
    public function getExceptionTrigger()
    {
        return $this->_exceptionTrigger;
    }

    /**
     * @return Exception
     */
    public function getException()
    {
        return $this->getPrevious();
    }
}
