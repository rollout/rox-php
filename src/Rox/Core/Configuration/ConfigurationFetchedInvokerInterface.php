<?php

namespace Rox\Core\Configuration;

interface ConfigurationFetchedInvokerInterface
{
    /**
     * @param int $fetcherStatus
     * @param float $creationDate Creation timestamp in milliseconds.
     * @param bool $hasChanges
     * @see FetcherStatus
     */
    function invoke($fetcherStatus, $creationDate, $hasChanges);

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
