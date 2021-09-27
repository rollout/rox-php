<?php

namespace Rox\Core\ErrorHandling;

use Exception;

final class UserspaceUnhandledErrorArgs
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
     * @var Exception $_exception
     */
    private $_exception;

    /**
     * @param mixed $_exceptionSource
     * @param int $_exceptionTrigger
     * @param Exception $_exception
     */
    public function __construct(
        $_exceptionSource, $_exceptionTrigger, Exception $_exception)
    {
        $this->_exceptionTrigger = $_exceptionTrigger;
        $this->_exceptionSource = $_exceptionSource;
        $this->_exception = $_exception;
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
        return $this->_exception;
    }
}