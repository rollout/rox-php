<?php

namespace Rox\Core\Client;

use Rox\Core\Configuration\ConfigurationFetchedEventHandlerInterface;
use Rox\Core\CustomProperties\DynamicPropertiesRuleInterface;
use Rox\Core\Impression\ImpressionEventHandlerInterface;

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
     * @return ImpressionEventHandlerInterface|null
     */
    function getImpressionHandler();

    /**
     * @return ConfigurationFetchedEventHandlerInterface|null
     */
    function getConfigurationFetchedHandler();

    /**
     * @return string|null
     */
    function getRoxyURL();

    /**
     * @return DynamicPropertiesRuleInterface|null
     */
    function getDynamicPropertiesRule();
}
