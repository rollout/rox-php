<?php

namespace Rox\Core\Client;

use Kevinrob\GuzzleCache\Storage\CacheStorageInterface;

interface RoxOptionsInterface
{
    /**
     * @return string
     */
    function getDevModeKey();

    /**
     * @return string
     */
    function getVersion();

    /**
     * @return callable|null
     */
    function getImpressionHandler();

    /**
     * @return callable|null
     */
    function getConfigurationFetchedHandler();

    /**
     * @return string|null
     */
    function getRoxyURL();

    /**
     * @return callable|null
     */
    function getDynamicPropertiesRule();

    /**
     * @return CacheStorageInterface|null
     */
    function getCacheStorage();

    /**
     * @return bool
     */
    function isLogCacheHitsAndMisses();

    /**
     * @return int|null
     */
    function getConfigFetchIntervalInSeconds();

    /**
     * @return NetworkConfigurationsOptions|null
     */
    function getNetworkConfigurationsOptions();

    /**
     * @return bool
     */
    function isSignatureDisabled();
}
