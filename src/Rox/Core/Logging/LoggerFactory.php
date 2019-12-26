<?php

namespace Rox\Core\Logging;

use Exception;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;

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
            return self::getDefaultFactory();
        }
        return self::$_instance;
    }

    /**
     * @return LoggerFactoryInterface
     */
    public static function getDefaultFactory()
    {
        if (!self::$_defaultFactory) {
            try {
                self::$_defaultFactory = (new MonologLoggerFactory())
                    ->setDefaultHandlers([
                        new StreamHandler('php://stdout', Logger::toMonologLevel(LogLevel::INFO))
                    ]);
            } catch (Exception $e) {
                error_log("Failed to setup default logging: {$e->getMessage()}\n{$e->getTraceAsString()}");
            }
        }
        return self::$_defaultFactory;
    }

    /**
     * @var LoggerFactoryInterface $_instance
     */
    private static $_instance;

    /**
     * @var LoggerFactoryInterface $_defaultFactory
     */
    private static $_defaultFactory;
}
