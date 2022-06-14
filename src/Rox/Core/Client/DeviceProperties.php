<?php

namespace Rox\Core\Client;

use Rox\Core\Consts\Build;
use Rox\Core\Consts\Environment;
use Rox\Core\Consts\PropertyType;

class DeviceProperties implements DevicePropertiesInterface
{
    const DEFAULT_LIB_VERSION = '4.8.1';
    const DEFAULT_DISTINCT_ID = 'stam'; // FIXME: what?
    const BUILD_NUMBER = "50"; // FIXME: fix the build number

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
    public function __construct(
        SdkSettingsInterface $sdkSettings,
        RoxOptionsInterface $roxOptions)
    {
        $this->_sdkSettings = $sdkSettings;
        $this->_roxOptions = $roxOptions;
    }

    /**
     * @return array
     */
    function getAllProperties()
    {
        return [

            PropertyType::getLibVersion()->getName() =>
                $this->getLibVersion(),

            PropertyType::getRolloutBuild()->getName() =>
                self::BUILD_NUMBER,

            PropertyType::getApiVersion()->getName() =>
                Build::API_VERSION,

            PropertyType::getAppRelease()->getName() =>
                $this->_roxOptions->getVersion(), // used for the version filter

            PropertyType::getDistinctId()->getName() =>
                $this->getDistinctId(),

            PropertyType::getAppKey()->getName() =>
                $this->_sdkSettings->getApiKey(),

            PropertyType::getPlatform()->getName() =>
                Build::PLATFORM,

            PropertyType::getDevModeSecret()->getName() =>
                $this->_roxOptions->getDevModeKey()
        ];
    }


    /**
     * @return string
     */
    function getRolloutEnvironment()
    {
        $env = isset($_ENV[Environment::ENV_VAR_NAME])
            ? $_ENV[Environment::ENV_VAR_NAME]
            : null;
        if ($env != Environment::QA && $env != Environment::LOCAL) {
            return Environment::PRODUCTION;
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
        return $this->getAllProperties()[PropertyType::getAppKey()->getName()];
    }
}
