<?php

namespace Rox\Core\Configuration;

class ConfigurationFetchedInvoker implements ConfigurationFetchedInvokerInterface
{
    /**
     * @var callable[] $_eventHandlers
     */
    private $_eventHandlers = [];

    /**
     * @param int $fetcherStatus
     * @param float $creationDate
     * @param bool $hasChanges
     * @see FetcherStatus
     */
    function invoke($fetcherStatus, $creationDate, $hasChanges)
    {
        $this->_fireConfigurationFetched(new ConfigurationFetchedArgs(
            FetcherError::NoError,
            $fetcherStatus,
            $creationDate,
            $hasChanges));
    }

    /**
     * @param int $error
     * @see FetcherError
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
