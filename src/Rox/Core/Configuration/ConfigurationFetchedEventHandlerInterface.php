<?php

namespace Rox\Core\Configuration;

interface ConfigurationFetchedEventHandlerInterface
{
    /**
     * @param ConfigurationFetchedArgs $args
     */
    function handleEvent(ConfigurationFetchedArgs $args);
}