<?php

namespace Rox\Core\Configuration;

use DateTime;

class ConfigurationFetchedInvoker implements ConfigurationFetchedInvokerInterface
{
    /**
     * @var ConfigurationFetchedEventHandlerInterface[] $_eventHandlers
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
     * @param ConfigurationFetchedEventHandlerInterface $handler
     */
    function register(ConfigurationFetchedEventHandlerInterface $handler)
    {
        $this->addEventHandler($handler);
    }

    /**
     * @param ConfigurationFetchedArgs $args
     */
    private function _fireConfigurationFetched(ConfigurationFetchedArgs $args)
    {
        foreach ($this->_eventHandlers as $eventHandler) {
            $eventHandler->handleEvent($args);
        }
    }

    /**
     * @param ConfigurationFetchedEventHandlerInterface $eventHandler
     */
    private function addEventHandler(ConfigurationFetchedEventHandlerInterface $eventHandler)
    {
        if (array_search($eventHandler, $this->_eventHandlers) === false) {
            array_push($this->_eventHandlers, $eventHandler);
        }
    }

    /**
     * @param ConfigurationFetchedEventHandlerInterface $eventHandler
     */
    private function removeEventHandler(ConfigurationFetchedEventHandlerInterface $eventHandler)
    {
        $index = array_search($eventHandler, $this->_eventHandlers);
        if ($index !== false) {
            array_splice($this->_eventHandlers, $index, 1);
        }
    }
}
