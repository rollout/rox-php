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
     * @see FetcherError
     */
    function invokeWithError($error);

    /**
     * @param callable $handler
     */
    function register(callable $handler);
}
