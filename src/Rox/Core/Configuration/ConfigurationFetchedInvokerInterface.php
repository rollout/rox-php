<?php

namespace Rox\Core\Configuration;

use DateTime;

interface ConfigurationFetchedInvokerInterface
{
    /**
     * @param int $fetcherStatus
     * @param DateTime $creationDate
     * @param bool $hasChanges
     * @see FetcherStatus
     */
    function invoke($fetcherStatus, DateTime $creationDate, $hasChanges);

    /**
     * @param int $error
     * @see FetchedError
     */
    function invokeWithError($error);

    /**
     * @param ConfigurationFetchedEventHandlerInterface $handler
     */
    function register(ConfigurationFetchedEventHandlerInterface $handler);
}
