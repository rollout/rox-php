<?php

namespace Rox\Core\Network;

use Exception;
use RuntimeException;

class HttpClientException extends RuntimeException
{
    public function __construct()
    {
        $message = null;
        $cause = null;

        foreach (func_get_args() as $arg) {
            if (is_string($arg)) {
                $message = $arg;
            } else if ($arg instanceof Exception) {
                $cause = $arg;
            }
        }

        parent::__construct($message, 0, $cause);
    }
}