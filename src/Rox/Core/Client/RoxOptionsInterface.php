<?php

namespace Rox\Core\Client;

use Rox\Core\Network\HttpClientFactoryInterface;

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
     * @return HttpClientFactoryInterface
     */
    function getHttpClientFactory();

    /**
     * @return string|null
     */
    function getDistinctId();
}
