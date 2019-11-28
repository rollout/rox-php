<?php

namespace Rox\Core\Client;

class DeviceProperties implements DevicePropertiesInterface
{
    const DEFAULT_LIB_VERSION = '1.0.0';
    const DEFAULT_DISTINCT_ID = 'stam'; // FIXME: what?
    const ENV_VAR_NAME = 'ROLLOUT_MODE';
    const ENV_PRODUCTION = 'PRODUCTION';
    const ENV_QA = 'QA';
    const ENV_LOCAL = 'LOCAL';

    /**
     * @var SdkSettingsInterface $_sdkSettings
     */
    private $_sdkSettings;

    /**
     * @var RoxOptionsInterface
     */
    private $_roxOptions;

    /**
     * DeviceProperties constructor.
     * @param SdkSettingsInterface $sdkSettings
     * @param RoxOptionsInterface $roxOptions
     */
    public function __construct(SdkSettingsInterface $sdkSettings, RoxOptionsInterface $roxOptions)
    {
        $this->_sdkSettings = $sdkSettings;
        $this->_roxOptions = $roxOptions;
    }

    /**
     * @return array
     */
    function GetAllProperties()
    {
        // TODO: Implement GetAllProperties() method.
    }

    /**
     * @return string
     */
    function getRolloutEnvironment()
    {
        $env = $_ENV[self::ENV_VAR_NAME];
        if ($env != self::ENV_QA && $env != self::ENV_LOCAL) {
            return self::ENV_PRODUCTION;
        }
        return $env;
    }

    /**
     * @return string
     */
    function getLibVersion()
    {
        return self::DEFAULT_LIB_VERSION;
    }

    /**
     * @return string
     */
    function getDistinctId()
    {
        return self::DEFAULT_DISTINCT_ID;
    }

    /**
     * @return string
     */
    function getRolloutKey()
    {
        // TODO: Implement getRolloutKey() method.
    }
}
