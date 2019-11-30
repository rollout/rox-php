<?php

namespace Rox\Core\Reporting;

use Exception;

interface ErrorReporterInterface
{
    /**
     * @param string $message
     * @param Exception $ex
     */
    function report($message, Exception $ex);
}