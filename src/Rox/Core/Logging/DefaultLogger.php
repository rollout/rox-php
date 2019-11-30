<?php

namespace Rox\Core\Logging;

use Psr\Log\AbstractLogger;

class DefaultLogger extends AbstractLogger
{
    /**
     * @var string $_name
     */
    private $_name;

    /**
     * DefaultLogger constructor.
     * @param string $name
     */
    public function __construct($name)
    {
        $this->_name = $name;
    }

    public function log($level, $message, array $context = array())
    {
        if (array_key_exists('exception', $context)) {
            error_log("[${level}] $message\n" . $context['exception']);
        } else {
            error_log("[${level}] $message");
        }
    }
}
