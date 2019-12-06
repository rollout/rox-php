<?php

namespace Rox\Server\Client;

use Ramsey\Uuid\Uuid;
use Rox\Core\Client\DeviceProperties;
use Rox\Core\Client\RoxOptionsInterface;
use Rox\Core\Client\SdkSettingsInterface;

class ServerProperties extends DeviceProperties
{
    /**
     * @var string $_distinctId
     */
    private $_distinctId;

    /**
     * ServerProperties constructor.
     * @param SdkSettingsInterface $sdkSettings
     * @param RoxOptionsInterface $roxOptions
     */
    public function __construct(
        SdkSettingsInterface $sdkSettings,
        RoxOptionsInterface $roxOptions)
    {
        parent::__construct($sdkSettings, $roxOptions);

        try {
            $this->_distinctId = Uuid::uuid4()->toString();
        } catch (\Exception $e) {
            $this->_distinctId = uniqid('rox-php-sdk');
        }
    }

    function getDistinctId()
    {
        return $this->_distinctId;
    }

    function getLibVersion()
    {
        return parent::getLibVersion(); // TODO: get from some (PHP?) file generated during deployment?
    }
}
