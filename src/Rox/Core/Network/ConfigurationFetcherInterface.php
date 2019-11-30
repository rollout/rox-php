<?php

namespace Rox\Core\Network;

interface ConfigurationFetcherInterface
{
    /**
     * @return ConfigurationFetchResult
     */
    function fetch();
}
