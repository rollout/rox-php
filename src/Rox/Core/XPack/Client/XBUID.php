<?php

namespace Rox\Core\XPack\Client;

use Rox\Core\Client\BUIDInterface;
use Rox\Core\Client\DevicePropertiesInterface;
use Rox\Core\Client\SdkSettingsInterface;
use Rox\Core\Consts\PropertyType;
use Rox\Core\Utils\MD5Generator;

class XBUID implements BUIDInterface
{
    /**
     * @var SdkSettingsInterface $_sdkSettings
     */
    private $_sdkSettings;

    /**
     * @var DevicePropertiesInterface $_deviceProperties
     */
    private $_deviceProperties;

    /**
     * @var string $_buid
     */
    private $_buid;

    /**
     * @var PropertyType[]
     */
    private $_buidGenerators;

    /**
     * XBUID constructor.
     * @param SdkSettingsInterface $sdkSettings
     * @param DevicePropertiesInterface $deviceProperties
     */
    public function __construct(
        SdkSettingsInterface $sdkSettings,
        DevicePropertiesInterface $deviceProperties)
    {
        $this->_sdkSettings = $sdkSettings;
        $this->_deviceProperties = $deviceProperties;
        $this->_buidGenerators = [
            PropertyType::getPlatform(),
            PropertyType::getAppKey(),
            PropertyType::getLibVersion(),
            PropertyType::getApiVersion()
        ];
    }

    /**
     * @return string
     */
    function getValue()
    {
        $properties = $this->_deviceProperties->getAllProperties();
        $this->_buid = MD5Generator::generate($properties, $this->_buidGenerators);
        return $this->_buid;
    }

    /**
     * @return array
     */
    function getQueryStringParts()
    {
        return [PropertyType::getBuid()->getName() => $this->getValue()];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->_buid;
    }
}
