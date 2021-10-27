<?php

namespace Rox\Core\ErrorHandling;

use Exception;

interface UserspaceUnhandledErrorInvokerInterface
{
    /**
     * @param callable $handler
     */
    function register(callable $handler);

    /**
     * @param mixed $source
     * @param int $exceptionTrigger
     * @param Exception $exception
     * @return void
     */
    function invoke($source, $exceptionTrigger, $exception);
}
