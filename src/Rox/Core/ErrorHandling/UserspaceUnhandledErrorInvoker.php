<?php

namespace Rox\Core\ErrorHandling;

use Exception;
use Psr\Log\LoggerInterface;
use Rox\Core\Logging\LoggerFactory;

final class UserspaceUnhandledErrorInvoker implements UserspaceUnhandledErrorInvokerInterface
{
    /**
     * @var callable $_userUnhandledErrorHandler
     */
    private $_userUnhandledErrorHandler;

    /**
     * @var LoggerInterface $_logger
     */
    private $_logger;

    public function __construct()
    {
        $this->_logger = LoggerFactory::getInstance()
            ->createLogger(UserspaceUnhandledErrorInvoker::class);
    }

    function register(callable $handler)
    {
        $this->_userUnhandledErrorHandler = $handler;
    }

    function invoke($source, $exceptionTrigger, $exception)
    {
        if (!($handler = $this->_userUnhandledErrorHandler)) {
            $this->_logger->error("User Unhandled Error occurred, no fallback handler was set, exception ignored.", [
                'exception' => $exception
            ]);
            return;
        }

        try {
            $handler(new UserspaceUnhandledErrorArgs($source, $exceptionTrigger, $exception));
        } catch (Exception $ex) {
            $this->_logger->error("User Unhandled Error Handler itself threw an exception. original exception: {$ex->getMessage()}", [
                'ex' => $ex,
                'exception' => $exception
            ]);
        }
    }
}
