<?php

namespace Rox\Core\Configuration;

use DateTime;

class ConfigurationFetchedInvoker implements ConfigurationFetchedInvokerInterface
{
    /**
     * @var callable[] $_eventHandlers
     */
    private $_eventHandlers = [];

    /**
     * @param int $fetcherStatus
     * @param DateTime $creationDate
     * @param bool $hasChanges
     * @see FetcherStatus
     */
    function invoke($fetcherStatus, DateTime $creationDate, $hasChanges)
    {
        $this->_fireConfigurationFetched(new ConfigurationFetchedArgs(
            FetchedError::NoError,
            $fetcherStatus,
            $creationDate,
            $hasChanges));
    }

    /**
     * @param int $error
     * @see FetchedError
     */
    function invokeWithError($error)
    {
        $this->_fireConfigurationFetched(new ConfigurationFetchedArgs($error));
    }

    /**
     * @param callable $handler
     */
    function register(callable $handler)
    {
        if (array_search($handler, $this->_eventHandlers) === false) {
            array_push($this->_eventHandlers, $handler);
        }
    }

    /**
     * @param ConfigurationFetchedArgs $args
     */
    private function _fireConfigurationFetched(ConfigurationFetchedArgs $args)
    {
        foreach ($this->_eventHandlers as $eventHandler) {
            $eventHandler($this, $args);
        }
    }
}
