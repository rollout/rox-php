<?php

namespace Rox\Core\Logging;

/**
 * LoggerFactory singleton.
 * @package Rox\Core\Logging
 */
class LoggerFactory
{
    /**
     * @param LoggerFactoryInterface $instance
     */
    static function setup(LoggerFactoryInterface $instance)
    {
        self::$_instance = $instance;
    }

    /**
     * @return LoggerFactoryInterface
     */
    static function getInstance()
    {
        if (self::$_instance == null) {
            return DefaultLoggerFactory::getInstance();
        }
        return self::$_instance;
    }

    /**
     * @var LoggerFactoryInterface $_instance
     */
    private static $_instance;
}
