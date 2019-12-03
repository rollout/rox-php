<?php

namespace Rox\Core\Client;

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
     * @return int|null
     */
    function getFetchInterval();

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
}
