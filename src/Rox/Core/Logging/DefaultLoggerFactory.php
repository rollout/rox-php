<?php

namespace Rox\Core\Logging;

use Psr\Log\LoggerInterface;

final class DefaultLoggerFactory implements LoggerFactoryInterface
{
    /**
     * @param string $name
     * @return LoggerInterface
     */
    function createLogger($name)
    {
        return new DefaultLogger($name);
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
