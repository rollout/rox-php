<?php

namespace Rox\Core\Configuration;

use Exception;
use Rox\Core\ErrorHandling\ExceptionTrigger;
use Rox\Core\ErrorHandling\UserspaceUnhandledErrorInvokerInterface;

abstract class AbstractConfigurationFetchedInvoker implements ConfigurationFetchedInvokerInterface
{
    /**
     * @var callable[] $_eventHandlers
     */
    private $_eventHandlers = [];

    /**
     * @var UserspaceUnhandledErrorInvokerInterface $_userUnhandledErrorInvoker
     */
    protected $_userUnhandledErrorInvoker;

    /**
     * @param UserspaceUnhandledErrorInvokerInterface $userUnhandledErrorInvoker
     */
    public function __construct(UserspaceUnhandledErrorInvokerInterface $userUnhandledErrorInvoker)
    {
        $this->_userUnhandledErrorInvoker = $userUnhandledErrorInvoker;
    }

    /**
     * @param callable $handler
     */
    final function register(callable $handler)
    {
        if (!in_array($handler, $this->_eventHandlers)) {
            $this->_eventHandlers[] = $handler;
        }
    }

    /**
     * @param int $fetcherStatus
     * @param float $creationDate
     * @param bool $hasChanges
     * @see FetcherStatus
     */
    final function invoke($fetcherStatus, $creationDate, $hasChanges)
    {
        $this->internalInvoke(new ConfigurationFetchedArgs(
            FetcherError::NoError,
            $fetcherStatus,
            $creationDate,
            $hasChanges));
    }

    /**
     * @param int $error
     * @see FetcherError
     */
    final function invokeWithError($error)
    {
        $this->internalInvoke(new ConfigurationFetchedArgs($error));
    }

    /**
     * @param ConfigurationFetchedArgs $cfa
     */
    protected abstract function internalInvoke(ConfigurationFetchedArgs $cfa);

    /**
     * @param ConfigurationFetchedArgs $args
     */
    protected final function fireConfigurationFetched(ConfigurationFetchedArgs $args)
    {
        foreach ($this->_eventHandlers as $eventHandler) {
            try {
                $eventHandler($args);
            } catch (Exception $e) {
                $this->_userUnhandledErrorInvoker->invoke($this, ExceptionTrigger::ConfigurationFetchedHandler, $e);
            }
        }
    }
}
